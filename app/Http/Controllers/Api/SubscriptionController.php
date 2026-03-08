<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Subscription;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    protected AsaasService $asaasService;
    protected float $monthlyPrice = 19.00; // R$19/mês
    protected bool $serviceConfigured = true;
    protected ?string $serviceError = null;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
        // Always configured since we have sandbox mode
        $this->serviceConfigured = true;
        $this->serviceError = null;
    }

    /**
     * Verificar se o serviço está configurado
     */
    private function checkServiceConfigured()
    {
        if (!$this->serviceConfigured) {
            return response()->json([
                'error' => 'Serviço de pagamento não configurado',
                'message' => $this->serviceError ?? 'Configure ASAAS_API_KEY nas variáveis de ambiente',
            ], 503);
        }
        return null;
    }

    /**
     * Obter status da assinatura do usuario
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->first();

        if (!$subscription) {
            return response()->json([
                'has_subscription' => false,
                'status' => 'inactive',
                'message' => 'Nenhuma assinatura ativa',
            ]);
        }

        return response()->json([
            'has_subscription' => true,
            'status' => $subscription->status,
            'asaas_customer_id' => $subscription->asaas_customer_id,
            'asaas_subscription_id' => $subscription->asaas_subscription_id,
            'plan' => 'monthly',
            'price' => $this->monthlyPrice,
            'current_period_end' => $subscription->current_period_end,
            'created_at' => $subscription->created_at,
        ]);
    }

    /**
     * Criar assinatura - Step 1: Criar cliente no Asaas
     */
    public function createCustomer(Request $request)
    {
        if ($response = $this->checkServiceConfigured()) {
            return $response;
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cpf_cnpj' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        try {
            $asaasCustomer = $this->asaasService->createCustomer([
                'name' => $request->name,
                'email' => $request->email,
                'cpf_cnpj' => $request->cpf_cnpj,
                'phone' => $request->phone,
                'external_reference' => $user->id,
            ]);

            // Salvar customer_id no banco
            Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'asaas_customer_id' => $asaasCustomer['id'],
                    'status' => 'pending_payment',
                ]
            );

            return response()->json([
                'success' => true,
                'customer_id' => $asaasCustomer['id'],
                'message' => 'Cliente criado com sucesso. Próximo passo: pagamento.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar customer Asaas', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao criar cliente'], 500);
        }
    }

    /**
     * Criar assinatura - Step 2: Gerar cobranca proporcional
     */
    public function createSubscription(Request $request)
    {
        if ($response = $this->checkServiceConfigured()) {
            return $response;
        }
        
        $validator = Validator::make($request->all(), [
            'billing_type' => 'nullable|in:BOLETO,CREDIT_CARD,PIX',
            'card.card_number' => 'nullable|string',
            'card.card_holder_name' => 'nullable|string',
            'card.card_expiry_month' => 'nullable|string',
            'card.card_expiry_year' => 'nullable|string',
            'card.card_cvv' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->first();

        if (!$subscription || empty($subscription->asaas_customer_id)) {
            return response()->json([
                'error' => 'Primeiro crie o cliente',
            ], 400);
        }

        try {
            // Calcular valor proporcional
            $proportionalValue = $this->asaasService->calculateProportionalValue($this->monthlyPrice);
            $nextDueDate = $this->asaasService->calculateNextDueDate();

            $asaasSubscription = $this->asaasService->createSubscription([
                'customer_id' => $subscription->asaas_customer_id,
                'value' => $proportionalValue,
                'description' => "Assinatura ServicoSimples - Valor proporcional até dia 5 ({$nextDueDate})",
                'billing_type' => $request->billing_type ?? 'CREDIT_CARD',
                'external_reference' => $user->id,
                'card' => $request->card,
            ]);

            // Atualizar assinatura no banco
            $subscription->update([
                'asaas_subscription_id' => $asaasSubscription['id'],
                'price' => $proportionalValue,
                'current_period_end' => $nextDueDate,
                'status' => 'pending_payment',
            ]);

            $billingType = $asaasSubscription['billingType'] ?? 'CREDIT_CARD';
            $status = $asaasSubscription['status'] ?? 'pending_payment';
            
            if ($billingType === 'CREDIT_CARD' && $status === 'ACTIVE') {
                $message = "Assinatura ativada com sucesso! Pagamento de R$ {$proportionalValue} processado.";
            } else {
                $message = "Cobrança de R$ {$proportionalValue} gerada. Vence em {$nextDueDate}.";
            }

            return response()->json([
                'success' => true,
                'subscription_id' => $asaasSubscription['id'],
                'value' => $proportionalValue,
                'due_date' => $nextDueDate,
                'billing_type' => $billingType,
                'invoice_url' => $asaasSubscription['invoiceUrl'] ?? null,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar assinatura Asaas', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao criar assinatura'], 500);
        }
    }

    /**
     * Gerar link de pagamento
     */
    public function getPaymentLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|numeric|min:1',
            'billing_type' => 'nullable|in:BOLETO,CREDIT_CARD,PIX',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $paymentLink = $this->asaasService->createPaymentLink([
                'name' => 'Assinatura ServicoSimples',
                'description' => 'Pagamento de assinatura mensal',
                'value' => $request->value,
                'billing_type' => $request->billing_type ?? 'BOLETO',
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $paymentLink['url'],
                'payment_id' => $paymentLink['id'],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar link de pagamento', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao criar link'], 500);
        }
    }

    /**
     * Webhook do Asaas
     */
    public function webhook(Request $request)
    {
        Log::info('Asaas webhook received', $request->all());

        try {
            $this->asaasService->handleWebhook($request->all());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erro no webhook Asaas', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro'], 500);
        }
    }

    /**
     * Cancelar assinatura
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->first();

        if (!$subscription || empty($subscription->asaas_subscription_id)) {
            return response()->json(['error' => 'Nenhuma assinatura ativa'], 400);
        }

        try {
            $this->asaasService->cancelSubscription($subscription->asaas_subscription_id);

            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assinatura cancelada com sucesso',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar assinatura', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao cancelar'], 500);
        }
    }

    /**
     * Pausar assinatura
     */
    public function pause(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
            'cycles' => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$subscription || empty($subscription->asaas_subscription_id)) {
            return response()->json(['error' => 'Nenhuma assinatura ativa para pausar'], 400);
        }

        try {
            $this->asaasService->pauseSubscription(
                $subscription->asaas_subscription_id,
                $request->cycles ?? 1
            );

            $subscription->update([
                'status' => 'paused',
                'paused_at' => now(),
                'paused_reason' => $request->reason ?? 'Pause solicitado pelo usuário',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assinatura pausada com sucesso',
                'paused_at' => now()->toIso8601String(),
                'reason' => $request->reason ?? 'Pause solicitado pelo usuário',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao pausar assinatura', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao pausar'], 500);
        }
    }

    /**
     * Retomar assinatura pausada
     */
    public function resume(Request $request)
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'paused')
            ->first();

        if (!$subscription || empty($subscription->asaas_subscription_id)) {
            return response()->json(['error' => 'Nenhuma assinatura pausada'], 400);
        }

        try {
            $this->asaasService->resumeSubscription($subscription->asaas_subscription_id);

            $subscription->update([
                'status' => 'active',
                'paused_at' => null,
                'paused_reason' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assinatura retomada com sucesso',
                'resumed_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao retomar assinatura', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao retomar'], 500);
        }
    }

    /**
     * Obter detalhes completos da assinatura
     */
    public function details(Request $request)
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->first();

        if (!$subscription) {
            return response()->json([
                'has_subscription' => false,
                'message' => 'Nenhuma assinatura encontrada',
            ]);
        }

        // Obter detalhes do Asaas se tiver subscription_id
        $asaasDetails = null;
        if (!empty($subscription->asaas_subscription_id)) {
            try {
                $asaasDetails = $this->asaasService->getSubscription($subscription->asaas_subscription_id);
            } catch (\Exception $e) {
                Log::warning('Não foi possível obter detalhes do Asaas', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'has_subscription' => true,
            'status' => $subscription->status,
            'plan' => [
                'name' => 'Mensal',
                'price' => $this->monthlyPrice,
                'currency' => 'BRL',
            ],
            'current_period' => [
                'start' => $subscription->created_at?->toIso8601String(),
                'end' => $subscription->current_period_end?->toIso8601String(),
            ],
            'billing' => [
                'type' => $asaasDetails['billingType'] ?? 'BOLETO',
                'value' => $subscription->price,
            ],
            'paused' => [
                'is_paused' => $subscription->status === 'paused',
                'paused_at' => $subscription->paused_at?->toIso8601String(),
                'reason' => $subscription->paused_reason,
            ],
            'cancelled' => [
                'is_cancelled' => $subscription->status === 'cancelled',
                'cancelled_at' => $subscription->cancelled_at?->toIso8601String(),
            ],
            'created_at' => $subscription->created_at->toIso8601String(),
            'asaas_customer_id' => $subscription->asaas_customer_id,
            'asaas_subscription_id' => $subscription->asaas_subscription_id,
        ]);
    }
}

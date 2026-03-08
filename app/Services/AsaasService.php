<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected bool $sandbox;

    public function __construct()
    {
        $this->apiKey = config('services.asaas.api_key') ?? env('ASAAS_API_KEY', '');
        $this->baseUrl = config('services.asaas.base_url') ?? env('ASAAS_BASE_URL', 'https://www.asaas.com/api/v3');
        $this->sandbox = config('services.asaas.sandbox') ?? env('ASAAS_SANDBOX', true); // Default true for testing
    }

    /**
     * Verificar se o serviço está configurado
     */
    public function isConfigured(): bool
    {
        // Allow sandbox mode without API key
        return $this->sandbox || !empty($this->apiKey);
    }

    /**
     * Verificar se está em modo sandbox
     */
    public function isSandbox(): bool
    {
        return $this->sandbox || empty($this->apiKey);
    }

    /**
     * Criar cliente no Asaas
     */
    public function createCustomer(array $data): array
    {
        // Sandbox mode - return mock response
        if ($this->isSandbox() && empty($this->apiKey)) {
            Log::info('Asaas Sandbox: Creating mock customer', $data);
            return [
                'id' => 'cus_sandbox_' . uniqid(),
                'name' => $data['name'],
                'email' => $data['email'],
            ];
        }

        if (!$this->isConfigured()) {
            throw new \Exception('API Key do Asaas não configurada. Configure ASAAS_API_KEY nas variáveis de ambiente.');
        }
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/customers", [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpfCnpj' => $data['cpf_cnpj'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobilePhone' => $data['mobile_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['address_number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'postalCode' => $data['postal_code'] ?? null,
            'externalReference' => $data['external_reference'] ?? null,
        ]);

        if ($response->failed()) {
            Log::error('Asaas createCustomer failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Erro ao criar cliente no Asaas');
        }

        return $response->json();
    }

    /**
     * Criar assinatura (cobranca recorrente)
     * Cobra proporcional ao dia de criacao ate o dia 5
     */
    public function createSubscription(array $data): array
    {
        // Sandbox mode - return mock response
        if ($this->isSandbox() && empty($this->apiKey)) {
            Log::info('Asaas Sandbox: Creating mock subscription', $data);
            
            $billingType = $data['billing_type'] ?? 'CREDIT_CARD';
            $status = ($billingType === 'CREDIT_CARD') ? 'ACTIVE' : 'PENDING';
            
            return [
                'id' => 'sub_sandbox_' . uniqid(),
                'customer' => $data['customer_id'],
                'billingType' => $billingType,
                'value' => $data['value'],
                'status' => $status,
                'cycle' => 'MONTHLY',
                'nextDueDate' => $this->calculateNextDueDate(),
                'invoiceUrl' => ($billingType === 'CREDIT_CARD') ? 'https://sandbox.asaas.com/subscription-paid' : null,
            ];
        }

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/subscriptions", [
            'customer' => $data['customer_id'],
            'billingType' => $data['billing_type'] ?? 'BOLETO', // BOLETO, CREDIT_CARD, PIX
            'value' => $data['value'], // Valor proporcional calculado
            'description' => $data['description'] ?? 'Assinatura ServicoSimples',
            'externalReference' => $data['external_reference'] ?? null,
            'nextDueDate' => $this->calculateNextDueDate(), // Dia 5 do proximo mes
            'cycle' => 'MONTHLY',
        ]);

        if ($response->failed()) {
            Log::error('Asaas createSubscription failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Erro ao criar assinatura no Asaas');
        }

        return $response->json();
    }

    /**
     * Calcular valor proporcional (ate dia 5)
     * Se criou no dia 15, cobra (5/30) * valor mensal
     */
    public function calculateProportionalValue(float $monthlyValue, ?\DateTime $startDate = null): float
    {
        $startDate = $startDate ?? new \DateTime();
        $dayOfMonth = (int)$startDate->format('j');
        $daysInMonth = (int)$startDate->format('t');

        // Cobrar ate o dia 5
        $daysToCharge = max(1, min(5, $dayOfMonth));
        $proportionalValue = ($monthlyValue / $daysInMonth) * $daysToCharge;

        return round($proportionalValue, 2);
    }

    /**
     * Calcular proxima data de vencimento (dia 5)
     */
    public function calculateNextDueDate(): string
    {
        $nextMonth = new \DateTime('first day of next month');
        $nextMonth->setDate((int)$nextMonth->format('Y'), (int)$nextMonth->format('m'), 5);
        return $nextMonth->format('Y-m-d');
    }

    /**
     * Gerar link de pagamento (checkout)
     */
    public function createPaymentLink(array $data): array
    {
        // Sandbox mode - return mock response
        if ($this->isSandbox() && empty($this->apiKey)) {
            Log::info('Asaas Sandbox: Creating mock payment link', $data);
            return [
                'id' => 'pl_sandbox_' . uniqid(),
                'name' => $data['name'] ?? 'Assinatura ServicoSimples',
                'value' => $data['value'],
                'billingType' => $data['billing_type'] ?? 'BOLETO',
                'status' => 'ACTIVE',
                'url' => 'https://sandbox.asaas.com/mock-payment-link',
            ];
        }

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/paymentLinks", [
            'name' => $data['name'] ?? 'Assinatura ServicoSimples',
            'description' => $data['description'] ?? 'Pagamento de assinatura',
            'value' => $data['value'],
            'billingType' => $data['billing_type'] ?? 'BOLETO',
            'active' => true,
        ]);

        if ($response->failed()) {
            Log::error('Asaas createPaymentLink failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Erro ao criar link de pagamento');
        }

        return $response->json();
    }

    /**
     * Obter status do pagamento
     */
    public function getPayment(string $paymentId): array
    {
        // Sandbox mode - return mock response
        if ($this->isSandbox() && empty($this->apiKey)) {
            return [
                'id' => $paymentId,
                'status' => 'CONFIRMED',
                'value' => 19.00,
            ];
        }

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->get("{$this->baseUrl}/payments/{$paymentId}");

        if ($response->failed()) {
            throw new \Exception('Erro ao obter pagamento');
        }

        return $response->json();
    }

    /**
     * Cancelar assinatura
     */
    public function cancelSubscription(string $subscriptionId): array
    {
        // Sandbox mode - return mock response
        if ($this->isSandbox() && empty($this->apiKey)) {
            Log::info('Asaas Sandbox: Canceling mock subscription', ['id' => $subscriptionId]);
            return [
                'id' => $subscriptionId,
                'status' => 'CANCELED',
            ];
        }

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->delete("{$this->baseUrl}/subscriptions/{$subscriptionId}");

        if ($response->failed()) {
            Log::error('Asaas cancelSubscription failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Erro ao cancelar assinatura');
        }

        return $response->json();
    }

    /**
     * Pausar assinatura
     */
    public function pauseSubscription(string $subscriptionId, ?int $cycles = null): array
    {
        // Sandbox mode - return mock response
        if ($this->isSandbox() && empty($this->apiKey)) {
            return [
                'id' => $subscriptionId,
                'status' => 'PAUSED',
            ];
        }

        $body = ['cycles' => $cycles] + ['immediate' => true];

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/subscriptions/{$subscriptionId}/pause", $body);

        if ($response->failed()) {
            Log::error('Asaas pauseSubscription failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Erro ao pausar assinatura');
        }

        return $response->json();
    }

    /**
     * Retomar assinatura pausada
     */
    public function resumeSubscription(string $subscriptionId): array
    {
        // Sandbox mode - return mock response
        if ($this->isSandbox() && empty($this->apiKey)) {
            return [
                'id' => $subscriptionId,
                'status' => 'ACTIVE',
            ];
        }

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/subscriptions/{$subscriptionId}/resume");

        if ($response->failed()) {
            Log::error('Asaas resumeSubscription failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Erro ao retomar assinatura');
        }

        return $response->json();
    }

    /**
     * Obter detalhes da assinatura
     */
    public function getSubscription(string $subscriptionId): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->get("{$this->baseUrl}/subscriptions/{$subscriptionId}");

        if ($response->failed()) {
            throw new \Exception('Erro ao obter assinatura');
        }

        return $response->json();
    }

    /**
     * Webhook - processar notificacoes do Asaas
     */
    public function handleWebhook(array $payload): void
    {
        $event = $payload['event'] ?? null;
        $payment = $payload['payment'] ?? null;

        switch ($event) {
            case 'PAYMENT_RECEIVED':
            case 'SUBSCRIPTION_RECOVERED':
                $this->handlePaymentReceived($payment);
                break;
            case 'PAYMENT_OVERDUE':
                $this->handlePaymentOverdue($payment);
                break;
            case 'PAYMENT_DELETED':
                $this->handlePaymentDeleted($payment);
                break;
        }
    }

    protected function handlePaymentReceived(array $payment): void
    {
        Log::info('Asaas payment received', $payment);
        // Atualizar status do usuario para ativo
    }

    protected function handlePaymentOverdue(array $payment): void
    {
        Log::info('Asaas payment overdue', $payment);
        // Notificar usuario ou suspender servico
    }

    protected function handlePaymentDeleted(array $payment): void
    {
        Log::info('Asaas payment deleted', $payment);
    }
}

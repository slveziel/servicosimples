<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    public function index()
    {
        $oss = OrdemServico::where('user_id', Auth::id())
            ->with('cliente')
            ->orderBy('data', 'desc')
            ->get();
        return response()->json($oss);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'data' => 'required|date',
            'descricao' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'status' => 'nullable|in:pendente,concluido,pago',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verifica se cliente pertence ao usuário
        $cliente = \App\Models\Cliente::find($request->cliente_id);
        if (!$cliente || $cliente->user_id !== Auth::id()) {
            return response()->json(['errors' => ['cliente_id' => ['Cliente não encontrado ou não autorizado']]], 422);
        }

        $os = OrdemServico::create(array_merge(
            $request->all(),
            ['user_id' => Auth::id()]
        ));
        return response()->json($os->load('cliente'), 201);
    }

    public function show(OrdemServico $ordemServico)
    {
        if ($ordemServico->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
        return response()->json($ordemServico->load('cliente'));
    }

    public function update(Request $request, OrdemServico $ordemServico)
    {
        if ($ordemServico->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'data' => 'required|date',
            'descricao' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'status' => 'nullable|in:pendente,concluido,pago',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verifica se cliente pertence ao usuário
        $cliente = \App\Models\Cliente::find($request->cliente_id);
        if (!$cliente || $cliente->user_id !== Auth::id()) {
            return response()->json(['errors' => ['cliente_id' => ['Cliente não encontrado ou não autorizado']]], 422);
        }

        $ordemServico->update($request->all());
        return response()->json($ordemServico->load('cliente'));
    }

    public function destroy(OrdemServico $ordemServico)
    {
        if ($ordemServico->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $ordemServico->delete();
        return response()->json(null, 204);
    }

    public function dashboard()
    {
        $query = OrdemServico::where('user_id', Auth::id());
        
        $total = $query->count();
        $pendente = $query->where('status', 'pendente')->count();
        $concluido = $query->where('status', 'concluido')->count();
        $pago = $query->where('status', 'pago')->count();
        $faturamentoMes = $query->clone()
            ->where('status', 'pago')
            ->whereMonth('data', date('m'))
            ->whereYear('data', date('Y'))
            ->sum('valor');
        $faturamentoTotal = $query->clone()
            ->where('status', 'pago')
            ->sum('valor');

        return response()->json([
            'total' => $total,
            'pendente' => $pendente,
            'concluido' => $concluido,
            'pago' => $pago,
            'faturamento_mes' => $faturamentoMes,
            'faturamento_total' => $faturamentoTotal,
        ]);
    }
}

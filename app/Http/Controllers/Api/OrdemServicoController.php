<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrdemServicoController extends Controller
{
    public function index()
    {
        return response()->json(OrdemServico::with('cliente')->orderBy('data', 'desc')->get());
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

        $os = OrdemServico::create($request->all());
        return response()->json($os->load('cliente'), 201);
    }

    public function show(OrdemServico $ordemServico)
    {
        return response()->json($ordemServico->load('cliente'));
    }

    public function update(Request $request, OrdemServico $ordemServico)
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

        $ordemServico->update($request->all());
        return response()->json($ordemServico->load('cliente'));
    }

    public function destroy(OrdemServico $ordemServico)
    {
        $ordemServico->delete();
        return response()->json(null, 204);
    }

    public function dashboard()
    {
        $total = OrdemServico::count();
        $pendente = OrdemServico::where('status', 'pendente')->count();
        $concluido = OrdemServico::where('status', 'concluido')->count();
        $pago = OrdemServico::where('status', 'pago')->count();
        $faturamentoMes = OrdemServico::where('status', 'pago')
            ->whereMonth('data', date('m'))
            ->whereYear('data', date('Y'))
            ->sum('valor');
        $faturamentoTotal = OrdemServico::where('status', 'pago')->sum('valor');

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

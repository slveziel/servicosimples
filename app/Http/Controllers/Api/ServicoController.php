<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicoController extends Controller
{
    public function index()
    {
        return response()->json(Servico::orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_padrao' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $servico = Servico::create($request->all());
        return response()->json($servico, 201);
    }

    public function show(Servico $servico)
    {
        return response()->json($servico);
    }

    public function update(Request $request, Servico $servico)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_padrao' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $servico->update($request->all());
        return response()->json($servico);
    }

    public function destroy(Servico $servico)
    {
        $servico->delete();
        return response()->json(null, 204);
    }
}

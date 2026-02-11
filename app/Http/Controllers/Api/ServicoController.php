<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ServicoController extends Controller
{
    public function index()
    {
        $servicos = Servico::where('user_id', Auth::id())
            ->orderBy('nome')
            ->get();
        return response()->json($servicos);
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

        $servico = Servico::create(array_merge(
            $request->all(),
            ['user_id' => Auth::id()]
        ));
        return response()->json($servico, 201);
    }

    public function show(Servico $servico)
    {
        if ($servico->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
        return response()->json($servico);
    }

    public function update(Request $request, Servico $servico)
    {
        if ($servico->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

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
        if ($servico->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $servico->delete();
        return response()->json(null, 204);
    }
}

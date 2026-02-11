<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::where('user_id', Auth::id())
            ->orderBy('nome')
            ->get();
        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'required|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente = Cliente::create(array_merge(
            $request->all(),
            ['user_id' => Auth::id()]
        ));
        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        // Verifica se pertence ao usuário
        if ($cliente->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
        return response()->json($cliente);
    }

    public function update(Request $request, Cliente $cliente)
    {
        // Verifica se pertence ao usuário
        if ($cliente->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'required|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente->update($request->all());
        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente)
    {
        // Verifica se pertence ao usuário
        if ($cliente->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $cliente->delete();
        return response()->json(null, 204);
    }
}

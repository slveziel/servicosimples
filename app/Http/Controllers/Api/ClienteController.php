<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index()
    {
        return response()->json(Cliente::orderBy('nome')->get());
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

        $cliente = Cliente::create($request->all());
        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        return response()->json($cliente);
    }

    public function update(Request $request, Cliente $cliente)
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

        $cliente->update($request->all());
        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return response()->json(null, 204);
    }
}

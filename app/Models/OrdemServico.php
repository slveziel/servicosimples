<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    protected $fillable = [
        'cliente_id',
        'servico_id',
        'data',
        'descricao',
        'valor',
        'status',
        'observacoes',
    ];
    
    protected $hidden = [];
    
    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'date',
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
}

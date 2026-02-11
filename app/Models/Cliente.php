<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'user_id',
        'nome',
        'email',
        'telefone',
        'endereco',
        'observacoes',
    ];
    
    protected $hidden = [];
    
    protected $casts = [];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

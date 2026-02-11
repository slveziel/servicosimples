<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ordem_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('servico_id')->nullable();
            $table->date('data');
            $table->text('descricao');
            $table->decimal('valor', 10, 2);
            $table->enum('status', ['pendente', 'concluido', 'pago'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_servicos');
    }
};

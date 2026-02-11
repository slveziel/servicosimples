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
        // Adicionar user_id nas tabelas que não têm (se a coluna não existir)
        if (!Schema::hasColumn('clientes', 'user_id')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }

        if (!Schema::hasColumn('servicos', 'user_id')) {
            Schema::table('servicos', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }

        if (!Schema::hasColumn('ordem_servicos', 'user_id')) {
            Schema::table('ordem_servicos', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }

        // Atualizar registros existentes para o primeiro usuário
        try {
            $userId = \App\Models\User::first()?->id;
            if ($userId) {
                \App\Models\Cliente::whereNull('user_id')->update(['user_id' => $userId]);
                \App\Models\Servico::whereNull('user_id')->update(['user_id' => $userId]);
                \App\Models\OrdemServico::whereNull('user_id')->update(['user_id' => $userId]);
            }
        } catch (\Exception $e) {
            // Continua mesmo se der erro
        }

        // Adicionar foreign keys e tornar not null
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });

        Schema::table('servicos', function (Blueprint $table) {
            if (Schema::hasColumn('servicos', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });

        Schema::table('ordem_servicos', function (Blueprint $table) {
            if (Schema::hasColumn('ordem_servicos', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('servicos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

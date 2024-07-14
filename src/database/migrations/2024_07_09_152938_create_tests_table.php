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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('wallet_id');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('type');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'wallet_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};

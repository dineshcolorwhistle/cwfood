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
        Schema::create('cognito_user_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('cognito_access_token', 2048);
            $table->string('cognito_refresh_token', 2048)->nullable();
            $table->string('cognito_id_token', 2048)->nullable();
            $table->timestamp('cognito_issued_at')->nullable();
            $table->timestamp('cognito_expires_in')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cognito_user_tokens');
    }
};

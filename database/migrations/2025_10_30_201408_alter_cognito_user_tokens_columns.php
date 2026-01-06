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
        Schema::table('cognito_user_tokens', function (Blueprint $table) {
            // Change timestamp columns to integer types
            $table->bigInteger('cognito_issued_at')->nullable()->change();
            $table->integer('cognito_expires_in')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cognito_user_tokens', function (Blueprint $table) {
            // Revert back to timestamp if needed
            $table->timestamp('cognito_issued_at')->nullable()->change();
            $table->timestamp('cognito_expires_in')->nullable()->change();
        });
    }
};

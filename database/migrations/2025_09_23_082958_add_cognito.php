<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('cognito_sub', 191)->nullable()->unique()->after('email');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropUnique(['cognito_sub']);
            $t->dropColumn('cognito_sub');
        });
    }
};

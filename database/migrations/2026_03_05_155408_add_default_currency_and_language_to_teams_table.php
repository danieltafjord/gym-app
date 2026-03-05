<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('default_currency', 3)
                ->default('USD')
                ->after('description');
            $table->string('default_language', 5)
                ->default('en')
                ->after('default_currency');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'default_currency',
                'default_language',
            ]);
        });
    }
};

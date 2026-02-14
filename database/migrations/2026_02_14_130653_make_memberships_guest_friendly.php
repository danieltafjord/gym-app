<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->string('email')->default('')->after('user_id');
            $table->string('customer_name')->default('')->after('email');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->string('access_code', 8)->default('')->after('customer_phone');
        });

        // Backfill existing rows from user data
        DB::table('memberships')
            ->whereNotNull('user_id')
            ->where('email', '')
            ->chunkById(100, function ($memberships) {
                foreach ($memberships as $membership) {
                    $user = DB::table('users')->find($membership->user_id);
                    if ($user) {
                        DB::table('memberships')
                            ->where('id', $membership->id)
                            ->update([
                                'email' => $user->email,
                                'customer_name' => $user->name,
                                'access_code' => strtoupper(Str::random(8)),
                            ]);
                    }
                }
            });

        Schema::table('memberships', function (Blueprint $table) {
            $table->string('email')->default(null)->change();
            $table->string('customer_name')->default(null)->change();
            $table->string('access_code', 8)->default(null)->change();

            $table->index('email');
            $table->unique('access_code');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['email']);
            $table->dropUnique(['access_code']);

            $table->dropColumn(['email', 'customer_name', 'customer_phone', 'access_code']);

            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

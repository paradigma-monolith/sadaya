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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'simply_user_id')) {
                $table->unsignedBigInteger('simply_user_id')->nullable()->index()->after('username');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'status_pegawai_aktif')) {
                $table->string('status_pegawai_aktif', 30)->default('Aktif')->after('phone');
            }
        });

        // Make email nullable if needed so users without an email in Simply can still sync
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // Ignore if change is not supported on certain drivers
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'simply_user_id', 'phone', 'status_pegawai_aktif']);
        });
    }
};

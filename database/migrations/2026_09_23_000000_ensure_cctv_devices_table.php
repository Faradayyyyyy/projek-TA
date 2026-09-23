<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cctv_devices')) {
            Schema::create('cctv_devices', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('ip_address', 45);
                $table->string('oid', 50)->nullable();
                $table->unsignedInteger('agent_oid')->nullable();
                $table->unsignedSmallInteger('rtsp_port')->default(554);
                $table->unsignedSmallInteger('onvif_port')->default(2020);
                $table->string('username', 50)->nullable();
                $table->string('password', 100)->nullable();
                $table->string('stream_path', 50)->default('/stream2');
                $table->boolean('is_active')->default(true);
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        } else {
            // Pastikan kolom oid ada jika tabel sudah dibuat sebelumnya
            if (!Schema::hasColumn('cctv_devices', 'oid')) {
                Schema::table('cctv_devices', function (Blueprint $table) {
                    $table->string('oid', 50)->nullable()->after('ip_address');
                });
            }
        }

        // Sinkronkan data oid dari agent_oid jika kolom oid masih kosong
        if (Schema::hasColumn('cctv_devices', 'oid') && Schema::hasColumn('cctv_devices', 'agent_oid')) {
            DB::statement("UPDATE cctv_devices SET oid = CAST(agent_oid AS CHAR) WHERE (oid IS NULL OR oid = '') AND agent_oid IS NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tetap pertahankan tabel untuk keamanan data
    }
};

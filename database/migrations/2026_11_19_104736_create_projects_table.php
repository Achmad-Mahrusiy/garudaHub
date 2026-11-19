<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // Pemilik proyek (user berperan klien). User dihapus -> proyeknya ikut terhapus.
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi');
            $table->string('kategori', 50)->index();
            $table->unsignedBigInteger('budget_min')->default(0);
            $table->unsignedBigInteger('budget_max')->default(0);
            $table->date('batas_lelang');
            $table->enum('status', ['terbuka', 'ditutup'])->default('terbuka')->index();
            $table->timestamps();
        });

        // Schema Builder belum punya CHECK constraint, jadi ditulis dengan SQL biasa.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE projects ADD CONSTRAINT chk_projects_budget CHECK (budget_max >= budget_min)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

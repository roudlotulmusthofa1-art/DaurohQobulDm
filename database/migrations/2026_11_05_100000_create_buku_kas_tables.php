<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keuangan > Buku Kas: catatan pemasukan & pengeluaran kas.
 * Data milik sesi (angkatan) masing-masing, jadi ikut dijalankan di database tiap sesi (php artisan sesi:migrate).
 * - kas_kategori  : kategori transaksi (mis. SPP, Kitab), masing-masing untuk "masuk" atau "keluar".
 * - kas_transaksi : satu baris = satu transaksi. Jumlah disimpan dalam rupiah (bilangan bulat).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kas_kategori')) {
            Schema::create('kas_kategori', function (Blueprint $t) {
                $t->id();
                $t->string('nama', 60);
                $t->string('tipe', 10);              // masuk | keluar
                $t->timestamps();
                $t->unique(['nama', 'tipe']);
            });

            $sekarang = now();
            $isi = [
                ['SPP', 'masuk'], ['Uang Masuk', 'masuk'], ['Donasi & Infaq', 'masuk'], ['Pemasukan Lain', 'masuk'],
                ['Kitab', 'keluar'], ['Konsumsi', 'keluar'], ['Operasional', 'keluar'], ['Bisyaroh Asatid', 'keluar'], ['Pengeluaran Lain', 'keluar'],
            ];
            DB::table('kas_kategori')->insert(array_map(fn ($k) => [
                'nama' => $k[0], 'tipe' => $k[1], 'created_at' => $sekarang, 'updated_at' => $sekarang,
            ], $isi));
        }

        if (! Schema::hasTable('kas_transaksi')) {
            Schema::create('kas_transaksi', function (Blueprint $t) {
                $t->id();
                $t->date('tanggal');
                $t->string('tipe', 10);                                   // masuk | keluar
                $t->foreignId('kas_kategori_id')->nullable()->constrained('kas_kategori')->nullOnDelete();
                $t->string('deskripsi', 255);
                $t->string('pihak', 150)->nullable();                     // sumber (masuk) / penerima (keluar)
                $t->unsignedBigInteger('jumlah');                          // rupiah
                $t->string('bukti')->nullable();                           // file di disk public: buku-kas/...
                $t->unsignedBigInteger('user_id')->nullable();             // admin yang mencatat
                $t->timestamps();
                $t->index(['tanggal', 'tipe']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_transaksi');
        Schema::dropIfExists('kas_kategori');
    }
};

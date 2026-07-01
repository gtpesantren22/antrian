<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID meja dari tabel meja
        $mejaResepsionis = DB::table('meja')->where('tipe', 'resepsionis')->first();
        $mejaLayanan     = DB::table('meja')->where('tipe', 'layanan')->get();
        $mejaPembayaran  = DB::table('meja')->where('tipe', 'pembayaran')->get();

        // Resepsionis
        DB::table('users')->insert([
            'nama'       => 'Petugas Resepsionis',
            'pin'        => Hash::make('1111'),
            'meja_id'    => $mejaResepsionis->id,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4 Petugas Layanan
        $pinLayanan = ['2111', '2222', '2333', '2444'];
        foreach ($mejaLayanan as $index => $meja) {
            DB::table('users')->insert([
                'nama'       => "Petugas {$meja->nama_meja}",
                'pin'        => Hash::make($pinLayanan[$index]),
                'meja_id'    => $meja->id,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Kasir
        $pinKasir = ['3111', '3222'];
        foreach ($mejaPembayaran as $index => $meja) {
            DB::table('users')->insert([
                'nama'       => "Petugas {$meja->nama_meja}",
                'pin'        => Hash::make($pinKasir[$index]),
                'meja_id'    => $meja->id,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

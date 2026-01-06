<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Student pertama
        Student::create([
            'nama' => 'Ahmad Fauzi',
            'nisn' => '1234567890',
            'tahun_lulus' => '2023',
            'sekolah' => 'SMA Negeri 1 Jakarta',
            'phone' => '628123456789',
        ]);

        // Student kedua (baru)
        Student::create([
            'nama' => 'Muhammad Ndryan Putra Pratama',
            'nisn' => '0072538844',
            'tahun_lulus' => '2025',
            'sekolah' => 'SMKN 1 Kab Tangerang',
            'phone' => '62895330347429', // kosong jika belum ada nomor telepon
        ]);

                // Student kedua (baru)
        Student::create([
            'nama' => 'Muhammad Ndryan',
            'nisn' => '0082538844',
            'tahun_lulus' => '2025',
            'sekolah' => 'SMKN 1 Kab Tangerang',
            'phone' => '62895330347429', // kosong jika belum ada nomor telepon
        ]);
    }
}

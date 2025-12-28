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
    Student::create([
    'nama' => 'Ahmad Fauzi',
    'nisn' => '1234567890',
    'tahun_lulus' => '2023',
    'sekolah' => 'SMA Negeri 1 Jakarta',
    'phone' => '628123456789',
]);
    }
}

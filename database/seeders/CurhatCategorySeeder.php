<?php

namespace Database\Seeders;

use App\Models\CurhatCategory;
use Illuminate\Database\Seeder;

class CurhatCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Akademik',
            'Pertemanan',
            'Keluarga',
            'Adaptasi Sekolah',
            'Motivasi Belajar',
            'Kepercayaan Diri',
            'Bullying / Perundungan',
            'Hubungan Sosial',
            'Karier & Masa Depan',
            'Lainnya'
        ];
        foreach ($categories as $name) {
            CurhatCategory::create(['name' => $name]);
        }
    }
}

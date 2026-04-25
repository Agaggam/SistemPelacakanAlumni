<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlumniUmmCsvSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('alumni_sample.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("File alumni_sample.csv tidak ditemukan!");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->command->error("Gagal membuka file CSV!");
            return;
        }

        // Read header: "Nama Lulusan","NIM","Tahun Masuk","Tanggal Lulus","Fakultas","Program Studi"
        $header = fgetcsv($handle);
        $this->command->info("Header CSV: " . implode(' | ', $header));

        $batch = [];
        $batchSize = 500;
        $total = 0;
        $now = now()->toDateTimeString();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;

            $batch[] = [
                'nama'         => trim($row[0]),
                'nim'          => trim($row[1]),
                'tahun_masuk'  => trim($row[2]) ?: null,
                'tanggal_lulus'=> trim($row[3]) ?: null,
                'fakultas'     => trim($row[4]) ?: null,
                'prodi'        => trim($row[5]) ?: null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('alumni_umm')->insert($batch);
                $total += count($batch);
                $batch = [];
                $this->command->info("  Imported: {$total} records...");
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            DB::table('alumni_umm')->insert($batch);
            $total += count($batch);
        }

        fclose($handle);
        $this->command->info("✅ Selesai! Total {$total} data alumni diimport.");
    }
}

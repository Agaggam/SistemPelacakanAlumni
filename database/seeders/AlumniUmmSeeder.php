<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlumniUmmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Abd. Rahem',
                'nim' => '05.21.32.1',
                'prodi' => 'Teknik Elektro',
                'linkedin' => 'https://linkedin.com/in/abd-rahem',
                'instagram' => '@abd.rahem',
                'facebook' => 'Abd Rahem',
                'tiktok' => '@rahem_alumni',
                'email' => 'rahem@example.com',
                'no_hp' => '081234567801',
                'tempat_kerja' => 'PLN Indonesia',
                'alamat_kerja' => 'Jl. Jendral Sudirman, Jakarta',
                'posisi' => 'Senior Engineer',
                'status_kerja' => 'BUMN',
                'sosmed_perusahaan' => '@pln_id'
            ],
            [
                'nama' => 'Tohir Bachri',
                'nim' => '05.21.32.2',
                'prodi' => 'Manajemen',
                'linkedin' => 'https://linkedin.com/in/tohir-bachri',
                'instagram' => '@tohir_b',
                'facebook' => 'Tohir Bachri',
                'tiktok' => '@tohir_mgt',
                'email' => 'tohir@example.com',
                'no_hp' => '081234567802',
                'tempat_kerja' => 'Bank Mandiri',
                'alamat_kerja' => 'Gatot Subroto, Jakarta',
                'posisi' => 'Branch Manager',
                'status_kerja' => 'BUMN',
                'sosmed_perusahaan' => '@bankmandiri'
            ],
            [
                'nama' => 'Sugeng Sutjahjono',
                'nim' => '05.21.32.3',
                'prodi' => 'Hukum',
                'linkedin' => 'https://linkedin.com/in/sugeng-s',
                'instagram' => '@sugeng.sut',
                'facebook' => 'Sugeng Sutjahjono',
                'tiktok' => '@sugeng_law',
                'email' => 'sugeng@example.com',
                'no_hp' => '081234567803',
                'tempat_kerja' => 'Sutjahjono & Partners Law Firm',
                'alamat_kerja' => 'Sudirman Central Business District',
                'posisi' => 'Managing Partner',
                'status_kerja' => 'Wirausaha',
                'sosmed_perusahaan' => '@sutjahjono_law'
            ],
            [
                'nama' => 'Wahjuti',
                'nim' => '05.21.32.4',
                'prodi' => 'Akuntansi',
                'linkedin' => 'https://linkedin.com/in/wahjuti',
                'instagram' => '@wahjuti_acc',
                'facebook' => 'Wahjuti',
                'tiktok' => '@wahjuti_finance',
                'email' => 'wahjuti@example.com',
                'no_hp' => '081234567804',
                'tempat_kerja' => 'PwC Indonesia',
                'alamat_kerja' => 'WTC 3, Kuningan, Jakarta',
                'posisi' => 'Audit Manager',
                'status_kerja' => 'Swasta',
                'sosmed_perusahaan' => '@pwc_indonesia'
            ],
            [
                'nama' => 'Nazar Naamy',
                'nim' => '05.21.32.5',
                'prodi' => 'Informatika',
                'linkedin' => 'https://linkedin.com/in/nazar-naamy',
                'instagram' => '@nazar_naamy',
                'facebook' => 'Nazar Naamy',
                'tiktok' => '@nazar_tech',
                'email' => 'nazar@example.com',
                'no_hp' => '081234567805',
                'tempat_kerja' => 'Gojek (GoTo Financial)',
                'alamat_kerja' => 'Pasaraya Blok M, Jakarta',
                'posisi' => 'Senior Software Engineer',
                'status_kerja' => 'Swasta',
                'sosmed_perusahaan' => '@lifeatgojek'
            ],
            [
                'nama' => 'M. Islam',
                'nim' => '05.21.32.6',
                'prodi' => 'Agroteknologi',
                'linkedin' => 'https://linkedin.com/in/m-islam',
                'instagram' => '@m.islam_agro',
                'facebook' => 'M Islam',
                'tiktok' => '@m_islam_farm',
                'email' => 'islam@example.com',
                'no_hp' => '081234567806',
                'tempat_kerja' => 'Kementerian Pertanian',
                'alamat_kerja' => 'Ragunan, Jakarta Selatan',
                'posisi' => 'Staf Ahli',
                'status_kerja' => 'PNS',
                'sosmed_perusahaan' => '@kementerianpertanian'
            ],
            [
                'nama' => 'Surjadi',
                'nim' => '05.21.32.7',
                'prodi' => 'Teknik Sipil',
                'linkedin' => 'https://linkedin.com/in/surjadi',
                'instagram' => '@surjadi_civil',
                'facebook' => 'Surjadi',
                'tiktok' => '@surjadi_const',
                'email' => 'surjadi@example.com',
                'no_hp' => '081234567807',
                'tempat_kerja' => 'Waskita Karya',
                'alamat_kerja' => 'MT Haryono, Jakarta',
                'posisi' => 'Project Manager',
                'status_kerja' => 'BUMN',
                'sosmed_perusahaan' => '@waskita_karya'
            ],
            [
                'nama' => 'Andi Moh Arpan',
                'nim' => '05.21.32.8',
                'prodi' => 'Hubungan Internasional',
                'linkedin' => 'https://linkedin.com/in/andi-moch-arpan',
                'instagram' => '@andi_arpan',
                'facebook' => 'Andi Moch Arpan',
                'tiktok' => '@andi_diplomat',
                'email' => 'andi@example.com',
                'no_hp' => '081234567808',
                'tempat_kerja' => 'Kementerian Luar Negeri',
                'alamat_kerja' => 'Pejambon, Jakarta Pusat',
                'posisi' => 'Diplomat Pertama',
                'status_kerja' => 'PNS',
                'sosmed_perusahaan' => '@kemlu_ri'
            ],
            [
                'nama' => 'Pribadi',
                'nim' => '05.21.32.9',
                'prodi' => 'Kedokteran',
                'linkedin' => 'https://linkedin.com/in/dr-pribadi',
                'instagram' => '@dr.pribadi',
                'facebook' => 'dr Pribadi',
                'tiktok' => '@dr_pribadi_health',
                'email' => 'pribadi@example.com',
                'no_hp' => '081234567809',
                'tempat_kerja' => 'RSUD dr. Soetomo',
                'alamat_kerja' => 'Jl. Mayjen Prof. Dr. Moestopo, Surabaya',
                'posisi' => 'Dokter Umum',
                'status_kerja' => 'PNS',
                'sosmed_perusahaan' => '@rsuddrsoetomo'
            ],
            [
                'nama' => 'Achmad Budiman',
                'nim' => '05.21.32.10',
                'prodi' => 'Arsitektur',
                'linkedin' => 'https://linkedin.com/in/achmad-budiman',
                'instagram' => '@budiman_arch',
                'facebook' => 'Achmad Budiman',
                'tiktok' => '@budiman_design',
                'email' => 'budiman@example.com',
                'no_hp' => '081234567810',
                'tempat_kerja' => 'Budiman Design Studio',
                'alamat_kerja' => 'Kemang, Jakarta Selatan',
                'posisi' => 'Lead Architect',
                'status_kerja' => 'Wirausaha',
                'sosmed_perusahaan' => '@budiman_studio'
            ],
        ];

        foreach ($data as $item) {
            \App\Models\AlumniUmm::updateOrCreate(['nama' => $item['nama']], $item);
        }
    }
}

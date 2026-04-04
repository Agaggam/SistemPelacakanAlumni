<?php
// Memory-efficient SQL Generator for Alumni UMM with small INSERT batches
// Usage: php database/chunks/gen_sql.php

ini_set('memory_limit', '512M');
$url = "https://docs.google.com/spreadsheets/d/1JepgHxbtFpfwAxUO3DjZd6-TOpvtCr2d/gviz/tq?tqx=out:csv&sheet=Sheet1";
$rowsPerFile = 20000;
$rowsPerInsert = 500; // Small batches to avoid max_allowed_packet error
$outputDir = __DIR__;

echo "📥 Opening CSV stream (142K+ records)...\n";
$handle = fopen($url, "r");
if (!$handle) {
    die("❌ Error opening CSV stream. Check your internet connection.\n");
}

// Skip header
fgetcsv($handle);

$part = 1;
$totalProcessed = 0;
$fileBatch = [];
$insertBatch = [];
$now = date('Y-m-d H:i:s');

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 6) continue;

    // Nama Lulusan, NIM, Tahun Masuk, Tanggal Lulus, Fakultas, Program Studi
    $nama = addslashes(trim($row[0] ?? ''));
    $nim = addslashes(trim($row[1] ?? ''));
    $tahun_masuk = addslashes(trim($row[2] ?? ''));
    $tanggal_lulus = addslashes(trim($row[3] ?? ''));
    $fakultas = addslashes(trim($row[4] ?? ''));
    $prodi = addslashes(trim($row[5] ?? ''));

    $insertBatch[] = "('$nama', '$nim', '$prodi', '$fakultas', '$tahun_masuk', '$tanggal_lulus', '$now', '$now')";
    $totalProcessed++;

    // If sub-batch is full, add to file batch as a multi-row INSERT statement
    if (count($insertBatch) >= $rowsPerInsert) {
        $fileBatch[] = generateInsertSql($insertBatch);
        $insertBatch = [];
    }

    // If file batch reaches rowsPerFile, write the file
    if ($totalProcessed % $rowsPerFile === 0) {
        if (!empty($insertBatch)) {
            $fileBatch[] = generateInsertSql($insertBatch);
            $insertBatch = [];
        }
        writePart($outputDir, $part, $fileBatch);
        $fileBatch = [];
        $part++;
    }
}

// Write remaining data
if (!empty($insertBatch)) {
    $fileBatch[] = generateInsertSql($insertBatch);
}
if (!empty($fileBatch)) {
    writePart($outputDir, $part, $fileBatch);
}

fclose($handle);

echo "\n✅ Success! Processed $totalProcessed records into $part files.\n";
echo "📍 Files are in: $outputDir\n";

function generateInsertSql($rows) {
    $sql = "INSERT INTO `alumni_umm` (`nama`, `nim`, `prodi`, `fakultas`, `tahun_masuk`, `tanggal_lulus`, `created_at`, `updated_at`) VALUES \n";
    $sql .= implode(",\n", $rows) . ";\n";
    return $sql;
}

function writePart($dir, $part, $sqlBatches) {
    $filename = "alumni_part$part.sql";
    echo "💾 Writing $filename...\n";
    file_put_contents("$dir/$filename", implode("\n", $sqlBatches));
}

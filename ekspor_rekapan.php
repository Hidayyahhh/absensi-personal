<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_GET['kelas']) || !isset($_GET['pelajaran'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$kelas = $_GET['kelas'];
$pelajaran = $_GET['pelajaran'];

$fileNilai = "data/$username/kelas/$kelas/pelajaran/$pelajaran/nilai.json";
$nilai = file_exists($fileNilai) ? json_decode(file_get_contents($fileNilai), true) : [];

// Siapkan isi file teks
$lines = [];
$lines[] = "Rekapan Pencapaian Siswa - Pelajaran: $pelajaran - Kelas: $kelas\n";
$lines[] = "No\tNama\t\tAyat\tStatus";

foreach ($nilai as $i => $n) {
    $no = $i + 1;
    $nama = $n['nama'];
    $halaman = $n['halaman'];
    $status = $n['status'] ?: '-';
    $lines[] = "$no\t$nama\t$halaman\t$status";
}

$kontenTeks = implode("\n", $lines);

// Header untuk unduh file
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="rekapan_' . $pelajaran . '_' . $kelas . '.txt"');
echo $kontenTeks;
exit;

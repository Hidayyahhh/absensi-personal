<?php
require 'PHPExcel/Classes/PHPExcel.php';

session_start();

if (!isset($_SESSION['username']) || !isset($_POST['kelas'])) {
    die("Akses ditolak");
}

$kelas = $_POST['kelas'];
$username = $_SESSION['username'];

$folderKelas = "data/$username/kelas/" . urlencode($kelas);
$fileAbsen = "$folderKelas/absensi.json";

if (!file_exists($folderKelas)) {
    mkdir($folderKelas, 0777, true);
}

if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("Gagal upload file.");
}

$tmpFile = $_FILES['file']['tmp_name'];

$excel = PHPExcel_IOFactory::load($tmpFile);
$sheet = $excel->getActiveSheet();
$data = [];

for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
    $tanggal = $sheet->getCell("D$row")->getValue();
    $nama = $sheet->getCell("B$row")->getValue();
    $status = $sheet->getCell("E$row")->getValue();

    if (!isset($data[$tanggal])) {
        $data[$tanggal] = [];
    }
    $data[$tanggal][] = [
        'nama' => $nama,
        'status' => $status
    ];
}

// Simpan ke JSON
file_put_contents($fileAbsen, json_encode($data, JSON_PRETTY_PRINT));

header("Location: rekapan.php?kelas=" . urlencode($kelas));

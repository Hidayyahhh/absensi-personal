<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$kelas = isset($_POST['kelas']) ? trim($_POST['kelas']) : '';
$pelajaran = isset($_POST['pelajaran']) ? trim($_POST['pelajaran']) : '';

// Validasi input
if (empty($kelas) || empty($pelajaran)) {
    $_SESSION['pesan_error'] = "Data tidak lengkap! Pastikan kelas dan nama pelajaran terisi.";
    header("Location: daftar_pelajaran.php?kelas=" . urlencode($kelas));
    exit;
}

// Path file pelajaran
$folderKelas = "data/$username/kelas/$kelas";
$filePelajaran = "$folderKelas/pelajaran.json";

// Cek apakah file pelajaran ada
if (!file_exists($filePelajaran)) {
    $_SESSION['pesan_error'] = "Data pelajaran tidak ditemukan.";
    header("Location: daftar_pelajaran.php?kelas=" . urlencode($kelas));
    exit;
}

// Baca file JSON pelajaran
$daftarPelajaran = json_decode(file_get_contents($filePelajaran), true) ?: [];

// Cek apakah pelajaran yang ingin dihapus benar-benar ada
if (!in_array($pelajaran, $daftarPelajaran)) {
    $_SESSION['pesan_error'] = "Pelajaran '$pelajaran' tidak ditemukan.";
    header("Location: daftar_pelajaran.php?kelas=" . urlencode($kelas));
    exit;
}

// Hapus pelajaran dari array
$daftarPelajaran = array_filter($daftarPelajaran, function($p) use ($pelajaran) {
    return $p !== $pelajaran;
});

// Simpan ulang file JSON setelah penghapusan
file_put_contents($filePelajaran, json_encode($daftarPelajaran, JSON_PRETTY_PRINT));

// Beri feedback sukses
$_SESSION['pesan_sukses'] = "Pelajaran '$pelajaran' berhasil dihapus.";
header("Location: daftar_pelajaran.php?kelas=" . urlencode($kelas));
exit;

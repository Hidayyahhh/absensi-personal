<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['kelas'])) {
    die("Kelas tidak dipilih!");
}

$username = $_SESSION['username'];
$kelas = $_GET['kelas'];

$folderKelas = "data/$username/kelas/" . urlencode($kelas);

function hapusFolder($folder) {
    if (!file_exists($folder)) return;

    $files = array_diff(scandir($folder), array('.', '..'));
    foreach ($files as $file) {
        $path = "$folder/$file";
        if (is_dir($path)) {
            hapusFolder($path);
        } else {
            unlink($path);
        }
    }
    rmdir($folder);
}

// Hapus folder kelas
if (file_exists($folderKelas)) {
    hapusFolder($folderKelas);
    header("Location: welcome.php?hapus_sukses=1");
} else {
    header("Location: welcome.php?hapus_gagal=1");
}
exit;

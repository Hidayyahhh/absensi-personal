<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$userFolder = "data/$username";
$folderKelas = "$userFolder/kelas";

if (!file_exists($folderKelas)) {
    mkdir($folderKelas, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kelas = trim($_POST['nama_kelas']);
    $wali_kelas = trim($_POST['wali_kelas']);

    // Cek jika nama kelas kosong
    if (empty($nama_kelas)) {
        header("Location: welcome.php?error=Nama kelas tidak boleh kosong");
        exit;
    }

    // Cek apakah kelas sudah ada
    foreach (scandir($folderKelas) as $folder) {
        if ($folder === '.' || $folder === '..') continue;
        $infoFile = "$folderKelas/$folder/info.json";

        if (file_exists($infoFile)) {
            $info = json_decode(file_get_contents($infoFile), true);
            if (strtolower($info['nama_kelas']) === strtolower($nama_kelas)) {
                header("Location: welcome.php?error=Kelas sudah ada");
                exit;
            }
        }
    }

    // Buat folder kelas baru
    $kelasFolder = "$folderKelas/" . preg_replace('/\s+/', '_', strtolower($nama_kelas));
    mkdir($kelasFolder, 0777, true);

    // Simpan info kelas
    $info = [
        'nama_kelas' => $nama_kelas,
        'wali_kelas' => $wali_kelas
    ];
    file_put_contents("$kelasFolder/info.json", json_encode($info, JSON_PRETTY_PRINT));

    header("Location: welcome.php?sukses=Kelas berhasil ditambahkan");
    exit;
}
?>

<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_GET['kelas'])) {
    header("Location: index.php");
    exit;
}

$kelas = $_GET['kelas'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPelajaran = trim($_POST['nama_pelajaran']);
    if ($namaPelajaran == '') {
        $_SESSION['pesan_error'] = "Nama pelajaran tidak boleh kosong!";
        header("Location: tambah_pelajaran.php?kelas=" . urlencode($kelas));
        exit;
    }

    $folderPelajaran = "data/$username/kelas/$kelas/pelajaran/$namaPelajaran";
    if (!file_exists($folderPelajaran)) {
        mkdir($folderPelajaran, 0777, true);
        file_put_contents("$folderPelajaran/siswa.json", json_encode([]));
    }

    $filePelajaran = "data/$username/kelas/$kelas/pelajaran.json";
    $pelajaran = file_exists($filePelajaran) ? json_decode(file_get_contents($filePelajaran), true) : [];
    if (!in_array($namaPelajaran, $pelajaran)) {
        $pelajaran[] = $namaPelajaran;
        file_put_contents($filePelajaran, json_encode($pelajaran));
    }

    $_SESSION['pesan_sukses'] = "Pelajaran berhasil ditambahkan.";
    header("Location: daftar_pelajaran.php?kelas=" . urlencode($kelas));
    exit;
}

// Pesan sukses dan error
$pesan_sukses = $_SESSION['pesan_sukses'] ? : '';
$pesan_error = $_SESSION['pesan_error'] ? : '';
unset($_SESSION['pesan_sukses'], $_SESSION['pesan_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Pelajaran - Kelas <?= htmlspecialchars($kelas) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-5">

<h3 class="mb-4">Tambah Pelajaran - Kelas <?= htmlspecialchars($kelas) ?></h3>

<?php if ($pesan_sukses): ?>
    <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
<?php endif; ?>

<?php if ($pesan_error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($pesan_error) ?></div>
<?php endif; ?>

<div class="card shadow-sm p-4">
    <form method="post">
        <div class="mb-3">
            <label for="nama_pelajaran" class="form-label">Nama Pelajaran</label>
            <input type="text" class="form-control" id="nama_pelajaran" name="nama_pelajaran" required>
        </div>
        <div class="d-flex justify-content-between">
            <a href="daftar_pelajaran.php?kelas=<?= urlencode($kelas) ?>" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>

</body>
</html>

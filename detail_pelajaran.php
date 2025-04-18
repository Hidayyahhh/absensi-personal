<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_GET['kelas']) || !isset($_GET['pelajaran'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$kelas = $_GET['kelas'];
$namaPelajaran = $_GET['pelajaran'];

$folderKelas = "data/$username/kelas/$kelas";
$fileSiswa = "$folderKelas/datasiswa.json";

// Ambil data siswa (cuma nama)
$siswa = file_exists($fileSiswa) ? json_decode(file_get_contents($fileSiswa), true) : [];

// Lokasi file nilai khusus per pelajaran
$folderPelajaran = "$folderKelas/pelajaran/$namaPelajaran";
if (!file_exists($folderPelajaran)) {
    mkdir($folderPelajaran, 0777, true);
}
$fileNilai = "$folderPelajaran/nilai.json";

// Load data nilai (kalau belum ada, buat kosong)
$nilai = file_exists($fileNilai) ? json_decode(file_get_contents($fileNilai), true) : [];

// Kalau file nilai kosong, inisialisasi semua siswa dengan data kosong
if (empty($nilai)) {
    foreach ($siswa as $s) {
        $nilai[] = [
            'nama' => $s['nama'],
            'tanggal' => date('Y-m-d'), // Set default tanggal hari ini
            'halaman' => '',
            'status' => ''
        ];
    }
}

// Proses Simpan Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($nilai as $index => &$n) {
        $n['tanggal'] = $_POST['tanggal'][$index] ?: date('Y-m-d'); // Jika kosong, gunakan tanggal hari ini
        $n['halaman'] = $_POST['halaman'][$index] ?: '';
        $n['status'] = $_POST['status'][$index] ?: '';
    }
    file_put_contents($fileNilai, json_encode($nilai, JSON_PRETTY_PRINT));
    $success = "Data berhasil disimpan!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Pelajaran: <?= htmlspecialchars($namaPelajaran) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-5">

<?php include 'navbar.php'; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Tanggal</th>
                <th>Halaman</th>
                <th>L/U</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($nilai)): ?>
                <tr><td colspan="5" class="text-center">Belum ada data siswa.</td></tr>
            <?php else: ?>
                <?php foreach ($nilai as $index => $n): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($n['nama']) ?></td>
                    <td><input type="date" name="tanggal[<?= $index ?>]" value="<?= htmlspecialchars($n['tanggal'] ?: date('Y-m-d')) ?>" class="form-control"></td>
                    <td><input type="text" name="halaman[<?= $index ?>]" value="<?= htmlspecialchars($n['halaman']) ?>" class="form-control"></td>
                    <td>
                        <select name="status[<?= $index ?>]" class="form-control">
                            <option value="" <?= $n['status'] === '' ? 'selected' : '' ?>>Pilih</option>
                            <option value="Lulus" <?= $n['status'] === 'Lulus' ? 'selected' : '' ?>>Lulus</option>
                            <option value="Ulang" <?= $n['status'] === 'Ulang' ? 'selected' : '' ?>>Ulang</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <button type="submit" class="btn btn-primary">Simpan</button>
<a href="daftar_pelajaran.php?kelas=<?= urlencode($kelas) ?>" class="btn btn-secondary">Kembali</a>
<a href="rekapan_pelajaran.php?kelas=<?= urlencode($kelas) ?>&pelajaran=<?= urlencode($namaPelajaran) ?>" class="btn btn-success">Lihat Rekapan</a>
    


</form>

</body>
</html>

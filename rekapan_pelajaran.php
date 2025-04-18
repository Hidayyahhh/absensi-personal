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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Rekapan Pencapaian - <?= htmlspecialchars($pelajaran) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

<h3>Rekapan Pencapaian Siswa - <?= htmlspecialchars($pelajaran) ?></h3>
<a href="detail_pelajaran.php?kelas=<?= urlencode($kelas) ?>&pelajaran=<?= urlencode($pelajaran) ?>" class="btn btn-secondary mb-3">Kembali ke Detail</a>
<a href="ekspor_rekapan.php?kelas=<?= urlencode($kelas) ?>&pelajaran=<?= urlencode($pelajaran) ?>" class="btn btn-success mb-3">Ekspor TXT</a>


<table class="table table-striped table-bordered">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Ayat</th>
            <th>Status (L/U)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($nilai)): ?>
            <tr><td colspan="4" class="text-center">Belum ada data.</td></tr>
        <?php else: ?>
            <?php foreach ($nilai as $i => $n): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($n['nama']) ?></td>
                <td><?= htmlspecialchars($n['halaman']) ?></td>
                <td><?= htmlspecialchars($n['status']) ?: '-' ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>


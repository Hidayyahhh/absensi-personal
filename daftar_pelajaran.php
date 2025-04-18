<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];

if (!isset($_GET['kelas'])) {
    $_SESSION['pesan_error'] = "Kelas tidak ditemukan!";
    header("Location: welcome.php");
    exit;
}

$kelas = $_GET['kelas'];
$folderKelas = "data/$username/kelas/$kelas";
$filePelajaran = "$folderKelas/pelajaran.json";

// Baca nama asli kelas dari info.json
$infoFile = "$folderKelas/info.json";
$infoKelas = file_exists($infoFile) ? json_decode(file_get_contents($infoFile), true) : [];
$namaKelas = isset($infoKelas['nama_kelas']) && $infoKelas['nama_kelas'] !== '' ? $infoKelas['nama_kelas'] : $kelas;

$pelajaran = file_exists($filePelajaran) ? json_decode(file_get_contents($filePelajaran), true) : [];

// Flash messages (gunakan isset agar tidak notice)
$pesan_sukses = isset($_SESSION['pesan_sukses']) ? $_SESSION['pesan_sukses'] : '';
$pesan_error = isset($_SESSION['pesan_error']) ? $_SESSION['pesan_error'] : '';
unset($_SESSION['pesan_sukses'], $_SESSION['pesan_error']);

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nama_pelajaran'])) {
        $namaBaru = trim($_POST['nama_pelajaran']);
        if ($namaBaru === '') {
            $_SESSION['pesan_error'] = "Nama pelajaran tidak boleh kosong!";
        } else {
            $folderPelajaran = "$folderKelas/pelajaran/$namaBaru";
            if (!file_exists($folderPelajaran)) {
                mkdir($folderPelajaran, 0777, true);
                file_put_contents("$folderPelajaran/siswa.json", json_encode([]));
            }
            if (!in_array($namaBaru, $pelajaran)) {
                $pelajaran[] = $namaBaru;
                file_put_contents($filePelajaran, json_encode($pelajaran));
            }
            $_SESSION['pesan_sukses'] = "Pelajaran berhasil ditambahkan.";
        }
    } elseif (isset($_POST['hapus_pelajaran'])) {
        $hapus = $_POST['hapus_pelajaran'];
        $folderPelajaran = "$folderKelas/pelajaran/$hapus";
        array_map('unlink', glob("$folderPelajaran/*.*"));
        rmdir($folderPelajaran);
        $pelajaran = array_values(array_diff($pelajaran, [$hapus]));
        file_put_contents($filePelajaran, json_encode($pelajaran));
        $_SESSION['pesan_sukses'] = "Pelajaran '$hapus' berhasil dihapus.";
    } elseif (isset($_POST['edit_pelajaran_lama']) && isset($_POST['edit_pelajaran_baru'])) {
        $lama = $_POST['edit_pelajaran_lama'];
        $baru = trim($_POST['edit_pelajaran_baru']);
        if ($baru === '') {
            $_SESSION['pesan_error'] = "Nama pelajaran baru tidak boleh kosong!";
        } elseif (in_array($baru, $pelajaran)) {
            $_SESSION['pesan_error'] = "Pelajaran dengan nama '$baru' sudah ada!";
        } else {
            rename("$folderKelas/pelajaran/$lama", "$folderKelas/pelajaran/$baru");
            $pelajaran = array_map(function ($p) use ($lama, $baru) {
                return $p === $lama ? $baru : $p;
            }, $pelajaran);
            file_put_contents($filePelajaran, json_encode($pelajaran));
            $_SESSION['pesan_sukses'] = "Pelajaran berhasil diubah.";
        }
    }
    header("Location: daftar_pelajaran.php?kelas=" . urlencode($kelas));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Pelajaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function konfirmasiHapus(pelajaran) {
            document.getElementById('hapus_pelajaran').value = pelajaran;
            document.getElementById('hapusPelajaranNama').innerText = pelajaran;
            new bootstrap.Modal(document.getElementById('modalHapus')).show();
        }

        function editPelajaran(namaLama) {
            document.getElementById('edit_pelajaran_lama').value = namaLama;
            document.getElementById('edit_pelajaran_baru').value = namaLama;
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }
    </script>
</head>
<body class="container mt-5">
<?php include 'navbar.php'; ?>

<center><h3>Daftar Pelajaran</h3></center>

<?php if (!empty($pesan_sukses)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
<?php endif; ?>
<?php if (!empty($pesan_error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($pesan_error) ?></div>
<?php endif; ?>

<div class="row mt-3">
    <?php if (empty($pelajaran)): ?>
        <div class="col-12"><div class="alert alert-warning">Belum ada pelajaran.</div></div>
    <?php else: ?>
        <?php foreach ($pelajaran as $p): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <a href="detail_pelajaran.php?kelas=<?= urlencode($kelas) ?>&pelajaran=<?= urlencode($p) ?>" class="flex-grow-1">
                            <?= htmlspecialchars($p) ?>
                        </a>
                        <div>
                            <button type="button" class="btn btn-warning btn-sm" onclick="editPelajaran('<?= htmlspecialchars($p) ?>')">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="konfirmasiHapus('<?= htmlspecialchars($p) ?>')">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<a href="welcome.php" class="btn btn-secondary mt-3">Kembali</a>
<button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Pelajaran</button>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5>Tambah Pelajaran</h5>
            </div>
            <div class="modal-body">
                <input type="text" name="nama_pelajaran" class="form-control" placeholder="Nama Pelajaran" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="hapus_pelajaran" id="hapus_pelajaran">
            <div class="modal-header">
                <h5>Konfirmasi Hapus</h5>
            </div>
            <div class="modal-body">
                Hapus pelajaran <strong id="hapusPelajaranNama"></strong>?
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="submit">Hapus</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="edit_pelajaran_lama" id="edit_pelajaran_lama">
            <div class="modal-header">
                <h5>Edit Pelajaran</h5>
            </div>

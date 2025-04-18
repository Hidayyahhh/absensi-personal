<?php
session_start();

// Fungsi auto-login pakai cookie
function autoLogin() {
    if (isset($_SESSION['username'])) {
        return; // Sudah login manual, tidak perlu auto-login
    }

    if (isset($_COOKIE['login_token'])) {
        foreach (scandir('data') as $user) {
            if ($user === '.' || $user === '..') continue;

            $tokenFile = "data/$user/token.json";
            if (file_exists($tokenFile)) {
                $savedToken = json_decode(file_get_contents($tokenFile), true)['token'] ?? '';
                if ($_COOKIE['login_token'] === $savedToken) {
                    $_SESSION['username'] = $user;
                    return;
                }
            }
        }
    }
}

// Cek login atau jalankan auto-login
autoLogin();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Ambil data user yang login
$username = $_SESSION['username'];
$userFolder = "data/$username";

// Validasi keberadaan folder user (antisipasi manipulasi session)
if (!is_dir($userFolder)) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Ambil data lembaga
$lembagaFile = "$userFolder/lembaga.json";
$lembaga = "Tidak Diketahui";

if (file_exists($lembagaFile)) {
    $lembagaData = json_decode(file_get_contents($lembagaFile), true);
    $lembaga = $lembagaData['nama'] ?? "Tidak Diketahui";
}

// Siapkan folder kelas jika belum ada
$folderKelas = "$userFolder/kelas";
if (!is_dir($folderKelas)) {
    mkdir($folderKelas, 0777, true);
}

// Ambil daftar kelas
$kelasList = [];
foreach (scandir($folderKelas) as $folder) {
    if ($folder === '.' || $folder === '..') continue;

    $infoFile = "$folderKelas/$folder/info.json";
    if (file_exists($infoFile)) {
        $info = json_decode(file_get_contents($infoFile), true);
        $kelasList[] = [
            'folder' => $folder,
            'nama_kelas' => $info['nama_kelas'] ?? $folder,
            'wali_kelas' => $info['wali_kelas'] ?? 'Tidak diketahui',
            'terdaftar' => date("d M Y", filemtime($infoFile))
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Welcome - <?= htmlspecialchars($username) ?> di <?= htmlspecialchars($lembaga) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .kelas-card { width: 100%; max-width: 500px; margin: 10px auto; }
    </style>
</head>
<body class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Selamat Datang, <?= htmlspecialchars($username) ?> di <?= htmlspecialchars($lembaga) ?></h3>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
</div>

<?php if (!empty($_GET['hapus_sukses'])): ?>
    <div class="alert alert-success">Kelas berhasil dihapus.</div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!empty($_GET['sukses'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['sukses']) ?></div>
<?php endif; ?>

<h4>Daftar Kelas</h4>

<?php if (empty($kelasList)): ?>
    <div class="alert alert-warning">Belum ada kelas di lembaga ini.</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($kelasList as $kelas): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= htmlspecialchars($kelas['nama_kelas']) ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Guru Kelas:</strong> <?= htmlspecialchars($kelas['wali_kelas']) ?>
                        </p>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="daftar_pelajaran.php?kelas=<?= urlencode($kelas['folder']) ?>" class="btn btn-outline-primary btn-sm">Lihat Pelajaran</a>
                            <a href="hapus_kelas.php?kelas=<?= urlencode($kelas['folder']) ?>" 
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('Yakin ingin menghapus kelas <?= htmlspecialchars($kelas['nama_kelas']) ?>? Semua data terkait akan hilang!')">
                                Hapus
                            </a>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        Data terakhir diperbarui: <?= $kelas['terdaftar'] ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="text-center mt-4">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahKelasModal">Tambah Kelas Baru</button>
</div>

<!-- Modal Tambah Kelas -->
<div class="modal fade" id="tambahKelasModal" tabindex="-1" aria-labelledby="tambahKelasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="tambah_kelas.php" method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahKelasModalLabel">Tambah Kelas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nama_kelas" class="form-label">Nama Kelas</label>
                    <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required>
                </div>
                <div class="mb-3">
                    <label for="wali_kelas" class="form-label">Wali Kelas</label>
                    <input type="text" class="form-control" id="wali_kelas" name="wali_kelas" 
                           value="<?= htmlspecialchars($username) ?>" placeholder="Bisa diisi nama lain">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kelas</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

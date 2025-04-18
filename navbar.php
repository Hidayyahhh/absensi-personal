<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

function isActivePage($pageName) {
    return basename($_SERVER['PHP_SELF']) === $pageName ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="welcome.php">Halo, <?= htmlspecialchars($username) ?>!</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <?php if (!empty($kelas)): ?>
                    <a class="nav-link <?= isActivePage('welcome.php') ?>" href="welcome.php?kelas=<?= urlencode($kelas) ?>">Home</a>
                    <a class="nav-link <?= isActivePage('daftar_pelajaran.php') ?>" href="daftar_pelajaran.php?kelas=<?= urlencode($kelas) ?>">Daftar Pelajaran</a>
                    <a class="nav-link <?= isActivePage('absen.php') ?>" href="absen.php?kelas=<?= urlencode($kelas) ?>">Absensi</a>
                    <a class="nav-link <?= isActivePage('datasiswa.php') ?>" href="datasiswa.php?kelas=<?= urlencode($kelas) ?>">Data Siswa</a>
                <?php endif; ?>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

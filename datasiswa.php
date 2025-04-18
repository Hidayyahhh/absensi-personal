<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];

if (!isset($_GET['kelas'])) {
    die('Kelas tidak dipilih.');
}

$kelas = $_GET['kelas'];
$folderKelas = "data/$username/kelas/" . urlencode($kelas);
$fileSiswa = "$folderKelas/datasiswa.json";

if (!file_exists($folderKelas)) {
    mkdir($folderKelas, 0777, true);
}

$siswa = file_exists($fileSiswa) ? json_decode(file_get_contents($fileSiswa), true) : [];

// === HAPUS DATA ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (isset($siswa[$id])) {
        array_splice($siswa, $id, 1);
        file_put_contents($fileSiswa, json_encode($siswa, JSON_PRETTY_PRINT));
        header("Location: ?kelas=$kelas");
        exit;
    }
}

// === EDIT DATA ===
$editIndex = null;
$editData = null;
if (isset($_GET['edit'])) {
    $editIndex = (int)$_GET['edit'];
    $editData = $siswa[$editIndex] ?? null;
}

// === TAMBAH / SIMPAN EDIT ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $jenis_kelamin = $_POST['jenis_kelamin'];

    if (!empty($nama) && !empty($jenis_kelamin)) {
        $dataBaru = [
            'nama' => $nama,
            'jenis_kelamin' => $jenis_kelamin
        ];

        if (isset($_POST['edit_index'])) {
            $idx = (int)$_POST['edit_index'];
            $siswa[$idx] = $dataBaru;
            $success = "Data siswa berhasil diperbarui.";
        } else {
            $siswa[] = $dataBaru;
            $success = "Data siswa berhasil ditambahkan.";
        }

        file_put_contents($fileSiswa, json_encode($siswa, JSON_PRETTY_PRINT));
        header("Location: ?kelas=$kelas");
        exit;
    } else {
        $error = "Nama dan jenis kelamin harus diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Data Siswa - <?= htmlspecialchars($kelas) ?></title>
</head>
<body class="container mt-5">

<?php include 'navbar.php'; ?>
<h1 class="text-center">Data Siswa</h1>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" class="card card-body">
    <input type="text" name="nama" placeholder="Nama Siswa" class="form-control" value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" required>
    <select name="jenis_kelamin" class="form-control mt-2" required>
        <option value="">Pilih Jenis Kelamin</option>
        <option value="Laki-laki" <?= (isset($editData) && $editData['jenis_kelamin'] === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
        <option value="Perempuan" <?= (isset($editData) && $editData['jenis_kelamin'] === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
    </select>

    <?php if ($editData): ?>
        <input type="hidden" name="edit_index" value="<?= $editIndex ?>">
        <button type="submit" class="btn btn-warning mt-3">Simpan Perubahan</button>
        <a href="?kelas=<?= urlencode($kelas) ?>" class="btn btn-secondary mt-3">Batal</a>
    <?php else: ?>
        <button type="submit" class="btn btn-primary mt-3">Tambah</button>
    <?php endif; ?>
</form>

<table class="table table-bordered table-striped mt-4">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Jenis Kelamin</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($siswa as $index => $data): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($data['nama']) ?></td>
            <td><?= htmlspecialchars($data['jenis_kelamin']) ?></td>
            <td>
                <a href="?kelas=<?= urlencode($kelas) ?>&edit=<?= $index ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="?kelas=<?= urlencode($kelas) ?>&delete=<?= $index ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>

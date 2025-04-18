<?php
session_start();

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Proses login manual jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $lembaga = trim($_POST['lembaga']);
    $password = trim($_POST['password']);
    $userFolder = "data/$username";

    if (!empty($username) && !empty($lembaga) && !empty($password)) {
        if (!file_exists($userFolder)) {
            // Pengguna baru: Buat folder & simpan data
            mkdir($userFolder, 0777, true);
            $data = [
                'nama' => $lembaga,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];
            file_put_contents("$userFolder/lembaga.json", json_encode($data, JSON_PRETTY_PRINT));
        } else {
            // Pengguna lama: Cek password
            $data = json_decode(file_get_contents("$userFolder/lembaga.json"), true);
            if (!password_verify($password, $data['password'])) {
                $error = "Password salah!";
            }
        }

        if (empty($error)) {
            // Login berhasil: set session dan cookie
            $_SESSION['username'] = $username;
            $token = generateToken();
            file_put_contents("$userFolder/token.json", json_encode(['token' => $token]));
            setcookie('login_token', $token, time() + (30 * 24 * 60 * 60), "/");
            header("Location: welcome.php");
            exit;
        }
    } else {
        $error = "Semua kolom wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login / Daftar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="card login-card shadow">
    <div class="card-body">
        <h3 class="text-center">Selamat Datang!</h3>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Nama Pengguna</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="lembaga" class="form-label">Nama Lembaga</label>
                <input type="text" name="lembaga" id="lembaga" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Simpan / Login</button>
        </form>
    </div>
</div>
</body>
</html>


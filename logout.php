<?php
session_start();
session_destroy();
setcookie('login_token', '', time() - 3600, '/'); // Hapus cookie login
header("Location: index.php");
exit;

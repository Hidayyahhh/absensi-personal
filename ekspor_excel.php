<?php
session_start();
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekapan-absensi.xls");

echo "<html>";
echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
echo "<body>";
include 'rekapan_absensi.php'; // panggil isi tabel HTML
echo "</body>";
echo "</html>";
?>

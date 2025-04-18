<?php
session_start();
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=rekapan-absensi.doc");

echo "<html>";
echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
echo "<body>";
include 'rekapan_absensi.php'; // panggil isi HTML tabel rekapan
echo "</body>";
echo "</html>";
?>

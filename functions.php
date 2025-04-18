<?php
function getUserFolder() {
    return 'data/' . ($_SESSION['username'] ? : 'guest');
}

function loadLembaga() {
    $path = getUserFolder() . '/lembaga.json';
    return file_exists($path) ? json_decode(file_get_contents($path), true) : '';
}

function loadSiswa() {
    $path = getUserFolder() . '/siswa.json';
    return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
}

function saveSiswa($data) {
    $path = getUserFolder() . '/siswa.json';
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

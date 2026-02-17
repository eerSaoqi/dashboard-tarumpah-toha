<?php
// includes/config.php
date_default_timezone_set('Asia/Jakarta');
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db-tarumpah-toha"; // Menggunakan nama database dari file .sql Anda

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set Timezone agar sinkron dengan PHP
mysqli_query($conn, "SET time_zone = '+07:00'");

/**
 * Helper function untuk eksekusi query
 */
function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    if ($result) {
        if (is_bool($result)) return $result;
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Helper function untuk menghitung jumlah baris
 */
function countRows($table, $where = "") {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM $table $where";
    $result = mysqli_query($conn, $sql);
    if (!$result) return 0;
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

/**
 * Helper function untuk sum kolom
 */
function sumColumn($table, $column, $where = "") {
    global $conn;
    $sql = "SELECT SUM($column) as total FROM $table $where";
    $result = mysqli_query($conn, $sql);
    if (!$result) return 0;
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}
/**
 * Helper function untuk get last inserted ID
 */
function lastInsertId() {
    global $conn;
    return mysqli_insert_id($conn);
}

/**
 * Helper function untuk escape string (keamanan)
 */
function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}
?>

<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

// Cek apakah pengguna sudah login dan memiliki role user
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Ambil username user dari session
$username = $_SESSION['username'];

// Ambil id room dari session atau input
if (isset($_SESSION['room_code'])) {
    $room_code = $_SESSION['room_code'];
} else {
    // Redirect jika room code tidak tersedia
    header("Location: user_dashboard.php");
    exit;
}

// Fungsi untuk menambahkan user ke room
function addUserToRoom($conn, $username, $room_code) {
    // Cek apakah room ada di database
    $roomQuery = $conn->prepare("SELECT id FROM rooms WHERE room_code = ?");
    $roomQuery->bind_param("s", $room_code);
    $roomQuery->execute();
    $roomResult = $roomQuery->get_result();
    if ($roomResult->num_rows > 0) {
        $room = $roomResult->fetch_assoc();
        $room_id = $room['id'];

        // Cek apakah user sudah ada di room
        $checkUserQuery = $conn->prepare("SELECT * FROM room_users WHERE username = ? AND room_id = ?");
        $checkUserQuery->bind_param("si", $username, $room_id);
        $checkUserQuery->execute();
        $checkUserResult = $checkUserQuery->get_result();
        if ($checkUserResult->num_rows == 0) {
            // Masukkan user ke room
            $insertUserQuery = $conn->prepare("INSERT INTO room_users (username, room_id) VALUES (?, ?)");
            $insertUserQuery->bind_param("si", $username, $room_id);
            if ($insertUserQuery->execute()) {
                return true;
            }
        } else {
            return false; // User sudah ada di room
        }
    }
    return false; // Room tidak ditemukan
}

// Fungsi untuk mengambil daftar pengguna di room
function getUsersInRoom($conn, $room_code) {
    $users = [];
    $roomQuery = $conn->prepare("SELECT id FROM rooms WHERE room_code = ?");
    $roomQuery->bind_param("s", $room_code);
    $roomQuery->execute();
    $roomResult = $roomQuery->get_result();
    if ($roomResult->num_rows > 0) {
        $room = $roomResult->fetch_assoc();
        $room_id = $room['id'];

        $userQuery = $conn->prepare("SELECT username FROM room_users WHERE room_id = ?");
        $userQuery->bind_param("i", $room_id);
        $userQuery->execute();
        $userResult = $userQuery->get_result();

        while ($row = $userResult->fetch_assoc()) {
            $users[] = $row['username'];
        }
    }
    return $users;
}

// Jika request method POST, tambahkan user ke room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['join'])) {
    if (addUserToRoom($conn, $username, $room_code)) {
        echo json_encode(["status" => "success", "message" => "Berhasil bergabung ke room"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal bergabung ke room atau sudah bergabung"]);
    }
    exit;
}

// Jika request method GET, ambil daftar user di room
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $users = getUsersInRoom($conn, $room_code);
    echo json_encode($users);
    exit;
}
?>

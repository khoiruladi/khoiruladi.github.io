<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

header('Content-Type: application/json');

// Fungsi untuk menghasilkan kode room acak
function generateRoomCode($length = 5) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$room_code = generateRoomCode();

// Simpan kode room di database
$stmt = $conn->prepare("INSERT INTO rooms (id_user, room_code) VALUES (?,?)");
$stmt->bind_param("ss", $_SESSION['id_user'],$room_code);
$stmt->execute();
$stmt->close();

$_SESSION['room_code'] = $room_code; 

echo json_encode(['roomCode' => $room_code]);
?>

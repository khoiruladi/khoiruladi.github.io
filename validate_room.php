<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

$response = array('success' => false, 'message' => '');

// Cek apakah form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['roomCode'])) {
    $roomCode = $_POST['roomCode'];
    $username = $_POST['username'];

    // Validasi kode room
    $stmt = $conn->prepare("SELECT id FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $roomCode);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Kode room valid, dapatkan id room
        $stmt->bind_result($roomId);
        $stmt->fetch();
        
        // Cek apakah user sudah ada dalam tabel login
        $sql = "SELECT id FROM login WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Jika username ditemukan, dapatkan ID user
            $user = $result->fetch_assoc();
            $userId = $user['id'];

            // Cek apakah user sudah ada dalam room untuk mencegah duplikasi
            $stmt = $conn->prepare("SELECT id FROM room_users WHERE id_room = ? AND id_user = ?");
            $stmt->bind_param("ss", $roomCode, $userId);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                // Jika belum ada, tambahkan user ke room
                $stmt = $conn->prepare("INSERT INTO room_users (id_room, id_user) VALUES (?, ?)");
                $stmt->bind_param("ss", $roomId, $userId);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Kode room valid. Anda telah bergabung.';
                } else {
                    $response['message'] = 'Gagal bergabung ke room.';
                }
            } else {
                $response['message'] = 'Anda sudah tergabung dalam room ini.';
            }
        } else {
            $response['message'] = 'Username tidak ditemukan.';
        }
    } else {
        $response['message'] = 'Kode room tidak valid.';
    }
    $stmt->close();
}
echo json_encode($response);
?>

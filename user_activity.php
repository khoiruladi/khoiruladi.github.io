<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

// Cek apakah pengguna sudah login dan memiliki role user
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login_user.php");
    exit;
}

$username = $_GET['username'];
$id_user = $_SESSION['id_user'];
$roomCode = $_GET['roomCode'];

// Simpan aktivitas klik ke dalam database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['click'])) {
    // Dapatkan id_room berdasarkan room_code
    $stmt = $conn->prepare("SELECT id FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $roomCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $id_room = $room['id'];

    // Dapatkan id_user berdasarkan username
    $stmt = $conn->prepare("SELECT id FROM login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    var_dump($user);
    $id_user = $user['id'];

        // Atur zona waktu yang sesuai (misalnya Asia/Jakarta untuk Waktu Indonesia Barat)
    date_default_timezone_set('Asia/Jakarta');

    // Mendapatkan waktu saat ini dengan milisecond
    $microtime = microtime(true);

    // Menampilkan timestamp dalam milisecond
    echo "Timestamp dalam milisecond: " . $microtime . "\n";

    // Mengkonversi timestamp ke format tanggal dan waktu
    $date = new DateTime();
    $date->setTimestamp(intval($microtime)); // Menggunakan intval untuk membuang bagian desimal

    // Pastikan zona waktu diatur pada objek DateTime
    $date->setTimezone(new DateTimeZone('Asia/Jakarta')); // Ubah sesuai dengan zona waktu yang diinginkan

    // Format tanggal dan waktu dengan milisecond
    $timestamp = $date->format('Y-m-d H:i:s') . sprintf('.%03d', ($microtime - intval($microtime)) * 1000);

    // Menampilkan tanggal dan waktu
    echo "Tanggal dan waktu: " . $timestamp . "\n";

    // Masukkan data ke dalam tabel history
    $stmt = $conn->prepare("INSERT INTO history (id_room, id_user, timestamp) VALUES (?, ?, ?)");

    // Variabel $waktu_klik adalah string dari timestamp yang termasuk milisecond
    $waktu_klik = (string) $timestamp;

    // Gunakan bind_param dengan tipe string ("s") untuk kolom timestamp yang berisi string waktu dengan milisecond
    $stmt->bind_param("iis", $id_room, $id_user, $waktu_klik);

    // Eksekusi query
    $stmt->execute();

    // Tutup statement
    $stmt->close();


    if ($stmt->execute()) {
        // Kirim sinyal ke admin
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity</title>
    <style>
        body {
            background-color: #2c3e50;
            color: #ecf0f1;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background-color: #34495e;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            height: 100%;
            width: 100%;
        }
        h1 {
            color: #ecf0f1;
            font-weight: 300;
            margin-bottom: 20px;
        }
        p {
            color: #bdc3c7;
            margin-bottom: 30px;
        }
        .rectangle-button {
            width: 100%;
            height: 100%;
            background-color: #e74c3c;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            transition: background-color 0.3s ease;
        }
        .rectangle-button:hover {
            background-color: #c0392b;
        }
        .rectangle-button:active {
            background-color: #e74c3c;
            transform: scale(0.95);
        }
        @media screen and (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }
            .rectangle-button {
                width: 160px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Selamat Datang di Room: <?php echo htmlspecialchars($roomCode); ?></h1>
    <p>Anda bergabung sebagai: <?php echo htmlspecialchars($username); ?></p>
    
    <!-- Tombol persegi panjang -->
    <button id="clickButton" class="rectangle-button">Klik Disini</button>

<script>
    // Fungsi untuk mengirim sinyal
    function sendSignal() {
        console.log('Sinyal dikirim...');
        fetch('user_activity.php?username=<?php echo urlencode($username); ?>&roomCode=<?php echo urlencode($roomCode); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ click: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Sinyal berhasil dikirim ke admin!');
                alert('Sinyal telah dikirim ke admin!');
            } else {
                console.log('Terjadi kesalahan saat mengirim sinyal.');
                alert('Terjadi kesalahan!');
            }
        })
        .catch(error => {
            console.log('Error:', error);
        });
    }

    // Event listener untuk klik di mana saja di halaman
    document.body.addEventListener('click', (event) => {
        console.log('Klik terdeteksi di body.');
        sendSignal();
    });
</script>
</body>
</html>

<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil nama admin dari session
$admin_username = $_SESSION['username'];

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

// Cek apakah form untuk generate kode room sudah dikirim
$room_code = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
    $room_code = generateRoomCode();

    // Simpan kode room di database
    $stmt = $conn->prepare("INSERT INTO rooms (room_code) VALUES (?)");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['room_code'] = $room_code; // Simpan kode room di session jika perlu
}

// Cek apakah form untuk reset tabel user_activity sudah dikirim
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
//     // Ambil id room berdasarkan room_code dari session
//     $stmt = $conn->prepare("SELECT id FROM rooms WHERE room_code = ?");
//     $stmt->bind_param("s", $_SESSION['room_code']);
//     $stmt->execute();
    
//     $result = $stmt->get_result();
    
//     if ($result->num_rows > 0) {
//         // Jika room ditemukan, simpan informasinya
//         $room = $result->fetch_assoc();
//         var_dump($room);
//     }
//     $stmt->close();

//     // Update status 'is_deleted' untuk user dengan id_room terkait
//     $stmt = $conn->prepare("UPDATE history SET is_deleted = 1 WHERE id_room = ? ORDER BY timestamp ASC LIMIT 1");
//     $stmt->bind_param("s", $room['id']);
//     $stmt->execute();
//     $stmt->close();
//     var_dump($room['id']);

//     // Mengatur ulang nomor urut pada kolom `No` untuk data yang tidak dihapus
//     $reset_query = "SET @num := 0; 
//                     UPDATE history SET No = (@num := @num + 1) 
//                     WHERE is_deleted = 0 
//                     ORDER BY timestamp ASC";
//     $conn->query($reset_query);
// }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #475d62;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden; /* Sembunyikan scrollbar default */
        }
        .container {
            background: #1e2833;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            color: #fff;
            max-width: 800px;
            width: 100%;
            height: 90vh; /* Atur tinggi container */
            overflow-y: auto; /* Tambahkan scroll jika konten melebihi tinggi container */
            position: relative;
            padding-bottom: 100px;
        }
        .table-custom {
            background-color: #2e3a4f;
            color: #fff;
        }
        .table-custom th {
            background-color: #242c37;
        }
        .logout-button {
            background-color: #e74c3c;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            text-align: center;
            margin-top: 1rem; /* Tambahkan jarak atas jika diperlukan */
        }
        .logout-button:hover {
            background-color: #c0392b;
        }
        .logout-button:active {
            background-color: #e74c3c;
            transform: scale(0.98);
        }
        .btn-reset {
            background-color: #e74c3c;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            text-align: center;
            margin-top: 1rem;
        }
        .btn-reset:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Selamat Datang, <?php echo htmlspecialchars($admin_username); ?>!</h1>
        
        <button id="createRoomBtn" class="btn btn-primary btn-block mb-3">Buat Room</button>
        <p id="roomCode" class="text-center mb-4"></p>

        <h3 class="d-inline">Daftar Pengguna</h3>
        <table id="joinedUsersTable" class="table table-custom">
            <thead>
                <tr>
                    <th>Nama User</th>
                </tr>
            </thead>
            <tbody>
                <!-- Baris sinyal akan ditambahkan di sini -->
            </tbody>
        </table>

        <div class="reset-container">
            <h3 class="d-inline">User Tercepat</h3>
            <button type='button' id="resetBtn" value='reset' class="btn-reset">Reset</button>

        </div>
        <table id="signalTable" class="table table-custom">
            <thead>
                <tr>
                    <th>Nama User</th>
                    <th>Waktu Klik</th>
                </tr>
            </thead>
            <tbody>
                <!-- Baris sinyal akan ditambahkan di sini -->
            </tbody>
        </table>

        <!-- Tombol keluar di pojok kanan bawah -->
        <p id="message"></p>
        <p><a href="logout.php" class="logout-button">Logout</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>


// Fungsi untuk mengambil data sinyal dan user yang bergabung dari server
function fetchSignalsAndJoinedUsers() {
    fetch('get_signals.php')
        .then(response => response.json())
        .then(data => {
            console.log(data); // Tambahkan log untuk memeriksa data yang diterima
            
            // Tampilkan user yang bergabung
            const joinedTbody = document.getElementById('joinedUsersTable').getElementsByTagName('tbody')[0];
            joinedTbody.innerHTML = ''; // Kosongkan tabel sebelumnya
            data.joined_users.forEach((user, index) => {
                const row = joinedTbody.insertRow();
                const cell = row.insertCell(0);
                cell.textContent = user.username;
            });

            // Tampilkan user tercepat
            const signalTbody = document.getElementById('signalTable').getElementsByTagName('tbody')[0];
            signalTbody.innerHTML = ''; // Kosongkan tabel sebelumnya
            data.signals.forEach((signal, index) => {
                const row = signalTbody.insertRow();
                const cell1 = row.insertCell(0); // Nama User
                const cell2 = row.insertCell(1); // Waktu Klik
                var mili = signal.timestamp.split('.')
                cell1.textContent = signal.username;
                cell2.textContent = new Date(signal.timestamp).toLocaleString() + '.' + mili[1];
            });
        })
        .catch(error => console.error('Error fetching signals:', error));
}

// Panggil fungsi untuk mengambil sinyal dan user yang bergabung secara berkala (setiap 5 detik)
setInterval(fetchSignalsAndJoinedUsers, 1000);


        // Buat room saat admin menekan tombol
        document.getElementById('createRoomBtn').addEventListener('click', () => {
            fetch('create_room.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    const roomCode = data.roomCode;
                    document.getElementById('roomCode').innerText = `Kode Room: ${roomCode}`;
                });
        });
        
         document.getElementById('resetBtn').addEventListener('click', () => {
            fetch('reset.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Data Berhasil Direset!');
                } else {
                    alert('Terjadi kesalahan!');
                }
            });
        });
    </script>
</body>
</html>

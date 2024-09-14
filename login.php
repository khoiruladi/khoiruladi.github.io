<?php
session_start();

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "dbklik");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses login ketika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah username ada di database
    $sql = "SELECT * FROM login WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika username ditemukan, verifikasi password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Jika password benar, simpan data user ke session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['id_user'] = $user['id'];

            // Alihkan pengguna ke dashboard admin
            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            }else if ($user['role'] == 'user') {
                header("Location: user.php");
            } else {
                echo "<script>alert('Anda bukan admin!'); window.location.href='login_user.php';</script>";
            }
            exit;
        } else {
            echo "<script>alert('Password salah!');</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <style>
        body {
            background-color: #354152;
            color: #7e8ba3;
            font-family: Helvetica Neue, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login {
            background-color: #242c37;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 25px #000;
            text-align: center;
            width: 300px;
        }
        h2 {
            color: #fff;
            font-weight: 100;
            margin-bottom: 1.5rem;
            font-size: 2.75rem;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            margin: 1rem 0;
            border-radius: 999px;
            border: 1px solid #242c37;
            background-color: transparent;
            color: #fff;
            text-align: center;
        }
        input[type="submit"] {
            background-image: linear-gradient(160deg, #8ceabb 0%, #378f7b 100%);
            color: #fff;
            cursor: pointer;
        }
        input::placeholder {
            color: #7e8ba3;
        }
        p {
            color: #fff;
        }
        a {
            color: #8ceabb;
        }
    </style>
</head>
<body>
    <div class="login">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <div class="form__field">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form__field">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form__field">
                <input type="submit" value="Log In">
            </div>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
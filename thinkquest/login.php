<?php
session_start();
include 'config.php';

// Inisialisasi variabel
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Proses login saat form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validasi email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Email wajib diisi.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validasi password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Password wajib diisi.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Jika tidak ada error, cek di database
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, name, email, password, avatar FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $db_email, $db_password, $avatar);
            $stmt->fetch();
            if (password_verify($password, $db_password)) {
                // Password benar, set session
                $_SESSION["user_id"] = $id;
                $_SESSION["user_name"] = $name;
                $_SESSION["user_email"] = $db_email;
                $_SESSION["user_avatar"] = $avatar;

                header("Location: index.php");
                exit;
            } else {
                $login_err = "Email atau password salah.";
            }
        } else {
            $login_err = "Email atau password salah.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - ThinkQuest</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; }
        .container { background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:400px; }
        h2 { text-align:center; margin-bottom:20px; color:#333; }
        input[type=email], input[type=password] { width: 95%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px; }
        input[type=submit] { width: 100%; padding:10px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size:16px; }
        input[type=submit]:hover { background:#0056b3; }
        .error { color:red; font-size:14px; margin-top:-10px; margin-bottom:10px; text-align:center; }
        a { text-decoration:none; color:#007bff; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Login ThinkQuest</h2>
    <?php if(!empty($login_err)) echo '<div class="error">'.$login_err.'</div>'; ?>
    <form action="" method="post">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
        <div class="error"><?= $email_err ?></div>

        <label>Password</label>
        <input type="password" name="password">
        <div class="error"><?= $password_err ?></div>

        <input type="submit" value="Login">
    </form>
    <p style="text-align:center;">Belum punya akun? <a href="register.php">Register di sini</a></p>
</div>
</body>
</html>

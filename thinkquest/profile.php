<?php
session_start();
include 'config.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Jika ada ?id = profil orang lain, kalau tidak profil sendiri
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];

// Ambil data user
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$user){
    echo "User tidak ditemukan.";
    exit;
}

$is_own_profile = ($profile_id == $_SESSION['user_id']);
$bio_err = $avatar_err = "";

// Update profil (hanya pemilik sendiri)
if($is_own_profile && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = trim($_POST['bio']);
    $avatar_path = $user['avatar'];

    if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed_ext = ['jpg','jpeg','png','gif'];
        $file_name = $_FILES['avatar']['name'];
        $file_tmp = $_FILES['avatar']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed_ext)) {
            $new_name = "uploads/".uniqid().".".$ext;

            if(move_uploaded_file($file_tmp, $new_name)) {
                if($avatar_path && file_exists($avatar_path)) unlink($avatar_path);
                $avatar_path = $new_name;
            } else {
                $avatar_err = "Gagal upload avatar.";
            }
        } else {
            $avatar_err = "Format avatar harus jpg, jpeg, png, atau gif.";
        }
    }

    if(empty($avatar_err)) {
        $sql = "UPDATE users SET bio = ?, avatar = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $bio, $avatar_path, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Profil berhasil diupdate'); window.location='profile.php';</script>";
        exit;
    }
}

// Ambil pertanyaan user
$sql_q = "SELECT * FROM questions WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql_q);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$questions = $stmt->get_result();
$stmt->close();

// Ambil jawaban user — tetapi NANTI hanya ditampilkan kalau profil sendiri
$sql_a = "SELECT a.*, q.title AS question_title 
          FROM answers a 
          JOIN questions q ON a.question_id = q.id
          WHERE a.user_id = ? 
          ORDER BY a.created_at DESC";
$stmt = $conn->prepare($sql_a);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$answers = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil - ThinkQuest</title>
<style>
    body { font-family: Arial; background:#f0f2f5; margin:0; padding:0; }

    .header { 
        background:#007bff; 
        color:#fff; 
        padding: 20px 20px;
        display:flex; 
        justify-content:space-between; 
        align-items:center;
    }
    .header h2 { margin:0; font-size:24px; font-weight:bold; }
    .header .nav a { color:#fff; margin-left:15px; text-decoration:none; font-weight:bold; }
    .header .nav a:hover { text-decoration:underline; }

    .container { max-width:900px; margin:20px auto; }

    .profile { 
        background:#fff; padding:20px; border-radius:10px; 
        box-shadow:0 0 5px rgba(0,0,0,0.1);
    }
    .profile img {
        width:100px; height:100px; border-radius:50%; object-fit:cover;
    }

    .item-card {
        background:#fff;
        padding:15px;
        border-radius:10px;
        margin-bottom:15px;
        box-shadow:0 0 5px rgba(0,0,0,0.1);
        cursor:pointer;
        transition:0.15s;
    }
    .item-card:hover { background:#f7f7f7; transform:scale(1.01); }
    .item-card img { max-width:100%; border-radius:8px; margin:10px 0; }

    .actions a { margin-right:10px; color:#007bff; text-decoration:none; }
    .actions a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="header">
    <h2>ThinkQuest</h2>
    <div class="nav">
        <span>Halo, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <a href="ask.php">Buat Pertanyaan</a>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <!-- PROFILE -->
    <div class="profile">
        <h2><?= $is_own_profile ? "Profil Saya" : "Profil: " . htmlspecialchars($user['name']) ?></h2>

        <?php if($user['avatar']): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>">
        <?php endif; ?>

        <p><strong>Nama:</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Bio:</strong><br><?= nl2br(htmlspecialchars($user['bio'])) ?></p>

        <?php if($is_own_profile): ?>
        <form method="post" enctype="multipart/form-data">
            <label>Bio</label>
            <textarea name="bio" rows="4" style="width: 99%;"><?= htmlspecialchars($user['bio']) ?></textarea><br><br>

            <label>Ganti Avatar</label>
            <input type="file" name="avatar"><br><br>

            <input type="submit" value="Update Profil"
            style="background:#28a745; padding:10px 20px; color:#fff; border:none; border-radius:5px; cursor:pointer;">
        </form>
        <?php endif; ?>
    </div>

    <!-- PERTANYAAN -->
    <h3 style="margin-top:30px;">Pertanyaan</h3>

    <?php if($questions->num_rows > 0): ?>
        <?php while($q = $questions->fetch_assoc()): ?>
            <div class="item-card" onclick="window.location='question.php?id=<?= $q['id'] ?>'">
                <h3><?= htmlspecialchars($q['title']) ?></h3>

                <?php if($q['image']): ?>
                    <img src="<?= htmlspecialchars($q['image']) ?>">
                <?php endif; ?>

                <p><?= nl2br(htmlspecialchars(substr($q['body'], 0, 200))) ?>...</p>

                <?php if($is_own_profile): ?>
                <div class="actions">
                    <a href="edit_question.php?id=<?= $q['id'] ?>">Edit</a>
                    <a href="delete_question.php?id=<?= $q['id'] ?>" onclick="return confirm('Hapus pertanyaan ini?')">Hapus</a>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Tidak ada pertanyaan.</p>
    <?php endif; ?>


    <!-- JAWABAN (HANYA UNTUK DIRI SENDIRI) -->
    <?php if($is_own_profile): ?>

    <h3 style="margin-top:30px;">Jawaban Saya</h3>

    <?php if($answers->num_rows > 0): ?>
        <?php while($a = $answers->fetch_assoc()): ?>
            <div class="item-card" onclick="window.location='question.php?id=<?= $a['question_id'] ?>'">
                <p><b>Pada pertanyaan:</b> <?= htmlspecialchars($a['question_title']) ?></p>

                <?php if($a['image']): ?>
                    <img src="<?= htmlspecialchars($a['image']) ?>">
                <?php endif; ?>

                <p><?= nl2br(htmlspecialchars(substr($a['body'], 0, 200))) ?>...</p>

                <div class="actions">
                    <a href="edit_answer.php?id=<?= $a['id'] ?>">Edit</a>
                    <a href="delete_answer.php?id=<?= $a['id'] ?>" onclick="return confirm('Hapus jawaban ini?')">Hapus</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Anda belum pernah menjawab pertanyaan.</p>
    <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>

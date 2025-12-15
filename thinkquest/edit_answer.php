<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$answer_id = intval($_GET['id']);

// Ambil jawaban
$sql = "SELECT * FROM answers WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $answer_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    echo "Jawaban tidak ditemukan atau Anda tidak memiliki hak mengedit.";
    exit;
}

$answer = $result->fetch_assoc();
$question_id = $answer['question_id'];
$old_image = $answer['image'];

$body_err = $image_err = "";
$body = isset($_POST['body']) ? trim($_POST['body']) : $answer['body'];

if($_SERVER["REQUEST_METHOD"] == "POST") {

    if(empty($body)) {
        $body_err = "Isi jawaban tidak boleh kosong.";
    }

    $image_path = $old_image;

    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

        $allowed_ext = ['jpg','jpeg','png','gif'];
        $file_name = $_FILES["image"]["name"];
        $tmp = $_FILES["image"]["tmp_name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed_ext)) {
            $new_name = "uploads/".uniqid().".".$ext;
            if(move_uploaded_file($tmp, $new_name)) {
                if($old_image && file_exists($old_image)) unlink($old_image);
                $image_path = $new_name;
            } else {
                $image_err = "Gagal upload gambar.";
            }
        } else {
            $image_err = "Format gambar harus JPG, JPEG, PNG, atau GIF.";
        }
    }

    if(empty($body_err) && empty($image_err)) {
        $sql_update = "UPDATE answers SET body = ?, image = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ssii", $body, $image_path, $answer_id, $_SESSION['user_id']);

        if($stmt->execute()) {
            header("Location: question.php?id=".$question_id);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Jawaban</title>
    <style>
        body { font-family: Arial, sans-serif; background:#fff; padding:20px; }
        .container {
            max-width:600px; margin:auto; background:#fff; padding:20px;
            border-radius:8px; box-shadow:0 0 5px rgba(0,0,0,0.1);
        }
        textarea {
            width: 96%; padding:10px; border:1px solid #ccc; border-radius:5px;
        }
        input[type=file] { margin-top:10px; }
        .btn {
            padding:10px 20px; background:#28a745; color:#fff;
            border:none; border-radius:5px; cursor:pointer; margin-top:10px;
        }
        .btn:hover { background:#28a745; }
        .error { color:red; margin-top:5px; }
        img { max-width:100%; margin-top:10px; border-radius:5px; }

        .back-home {
            color: #28a745;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Jawaban</h2>

    <form action="" method="post" enctype="multipart/form-data">

        <label>Jawaban:</label><br>
        <textarea name="body" rows="5"><?= htmlspecialchars($body) ?></textarea>
        <div class="error"><?= $body_err ?></div>

        <label>Gambar (opsional):</label><br>
        <input type="file" name="image">
        <div class="error"><?= $image_err ?></div>

        <?php if($old_image): ?>
            <p>Gambar Saat Ini:</p>
            <img src="<?= htmlspecialchars($old_image) ?>" alt="gambar lama">
        <?php endif; ?>

        <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
    <p style="text-align:center;"><a href="question.php?id=<?= $question_id ?>" class="back-home">Kembali ke Pertanyaan</a></p>
</div>

</body>
</html>

<?php
session_start();
include 'config.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Proses form submit
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = intval($_POST['question_id']);
    $body = trim($_POST['body']);

    // Validasi body
    if(empty($body)) {
        echo "<script>alert('Isi jawaban tidak boleh kosong.'); window.history.back();</script>";
        exit;
    }

    // Upload gambar jawaban (opsional)
    $image_path = NULL;
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_ext = ['jpg','jpeg','png','gif'];
        $file_name = $_FILES["image"]["name"];
        $file_tmp = $_FILES["image"]["tmp_name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if(in_array($ext, $allowed_ext)) {
            $new_name = "uploads/".uniqid().".".$ext;
            if(!move_uploaded_file($file_tmp, $new_name)) {
                echo "<script>alert('Gagal upload gambar.'); window.history.back();</script>";
                exit;
            } else {
                $image_path = $new_name;
            }
        } else {
            echo "<script>alert('Format gambar harus jpg, jpeg, png, atau gif.'); window.history.back();</script>";
            exit;
        }
    }

    // Insert jawaban ke database
    $sql = "INSERT INTO answers (question_id, user_id, body, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $question_id, $_SESSION['user_id'], $body, $image_path);
    if($stmt->execute()) {
        $stmt->close();
        header("Location: question.php?id=".$question_id);
        exit;
    } else {
        echo "Terjadi kesalahan: ".$conn->error;
    }
} else {
    header("Location: index.php");
    exit;
}
?>

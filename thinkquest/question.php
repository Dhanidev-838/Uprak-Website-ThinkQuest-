<?php
session_start();
include 'config.php';

// Cek apakah ada id pertanyaan di URL
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$question_id = intval($_GET['id']);

// Ambil data pertanyaan
$sql_q = "SELECT q.id, q.title, q.body, q.image, q.created_at, 
                 u.name AS user_name, u.avatar AS user_avatar, q.user_id
          FROM questions q
          JOIN users u ON q.user_id = u.id
          WHERE q.id = ?";
$stmt = $conn->prepare($sql_q);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result_q = $stmt->get_result();

if($result_q->num_rows == 0) {
    echo "Pertanyaan tidak ditemukan.";
    exit;
}

$question = $result_q->fetch_assoc();
$stmt->close();

// Ambil jawaban
$sql_a = "SELECT a.id, a.body, a.image, a.created_at, a.user_id,
                 u.name AS user_name, u.avatar AS user_avatar
          FROM answers a
          JOIN users u ON a.user_id = u.id
          WHERE a.question_id = ?
          ORDER BY a.created_at ASC";
$stmt = $conn->prepare($sql_a);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result_a = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($question['title']) ?> - ThinkQuest</title>
    <style>
        body { font-family: Arial, sans-serif; background:#fff; margin:0; padding:0; }

        .header {
            background:#28a745;
            color:white;
            padding:20px 20px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .header h1 {
            margin:0;
            font-size:24px;
        }
        .header .nav a {
            color:white;
            margin-left:15px;
            text-decoration:none;
            font-weight:bold;
        }
        .header .nav a:hover { text-decoration:underline; }

        .container { max-width:800px; margin:20px auto; padding:0 15px; }

        .question, .answer {
            background:#fff;
            padding:15px;
            margin-bottom:15px;
            border-radius:8px;
            box-shadow:0 0 5px rgba(0,0,0,0.1);
        }

        .meta {
            font-size:14px;
            color:#888;
            display:flex;
            align-items:center;
            margin-top:10px;
        }

        .meta img {
            width:30px;
            height:30px;
            border-radius:50%;
            margin-right:10px;
            object-fit:cover;
        }

        textarea, input[type=file] {
            width: 97%;
            margin-top:10px;
            margin-bottom:15px;
        }
        textarea {
            padding:10px;
            border:1px solid #ccc;
            border-radius:5px;
        }

        input[type=submit] {
            padding:10px 20px;
            background:#28a745;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
        }
        input[type=submit]:hover { background:#218838; }

        .answer-actions a { margin-right:15px; }

        .answer-form {
            background:white;
            padding:15px;
            border-radius:8px;
            box-shadow:0 0 5px rgba(0,0,0,0.1);
            margin-top:20px;
        }

        .meta a {
            color:black;
            display:flex;
            align-items:center;
            text-decoration:none;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>ThinkQuest</h1>

    <div class="nav">
        <?php if(isset($_SESSION['user_id'])): ?>
            <span><strong>Halo, <?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
            <a href="ask.php">Buat Pertanyaan</a>
            <a href="index.php">Home</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">

    <!-- PERTANYAAN -->
    <div class="question">
        <h2><?= htmlspecialchars($question['title']) ?></h2>

        <?php if($question['image']): ?>
            <img src="<?= htmlspecialchars($question['image']) ?>" style="max-width:100%; border-radius:5px; margin-bottom:10px;">
        <?php endif; ?>

        <p><?= nl2br(htmlspecialchars($question['body'])) ?></p>

        <div class="meta">
            <a href="profile.php?id=<?= $question['user_id'] ?>">
                
                <?php if(!empty($question['user_avatar'])): ?>
                    <img src="<?= htmlspecialchars($question['user_avatar']) ?>" alt="avatar">
                <?php endif; ?>

                <span>
                    <b><?= htmlspecialchars($question['user_name']) ?></b>
                    • <?= date('d M Y H:i', strtotime($question['created_at'])) ?>
                </span>
            </a>
        </div>

        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $question['user_id']): ?>
            <div style="margin-top:10px;">
                <a href="edit_question.php?id=<?= $question['id'] ?>" style="color:#28a745;">Edit</a>
                <a href="delete_question.php?id=<?= $question['id'] ?>" style="color:#28a745;" onclick="return confirm('Hapus pertanyaan ini?');">Hapus</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- JAWABAN -->
    <h3>Jawaban</h3>

    <?php if($result_a->num_rows > 0): ?>
        <?php while($row = $result_a->fetch_assoc()): ?>
            <div class="answer">

                <?php if($row['image']): ?>
                    <img src="<?= htmlspecialchars($row['image']) ?>" style="max-width:100%; border-radius:5px; margin-bottom:10px;">
                <?php endif; ?>

                <p><?= nl2br(htmlspecialchars($row['body'])) ?></p>

                <div class="meta">
                    <a href="profile.php?id=<?= $row['user_id'] ?>">

                        <?php if(!empty($row['user_avatar'])): ?>
                            <img src="<?= htmlspecialchars($row['user_avatar']) ?>" alt="avatar">
                        <?php endif; ?>

                        <span>
                            <b><?= htmlspecialchars($row['user_name']) ?></b>
                            • <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                        </span>
                    </a>
                </div>

                <?php if($_SESSION['user_id'] == $row['user_id']): ?>
                    <div class="answer-actions" style="margin-top:10px;">
                        <a href="edit_answer.php?id=<?= $row['id'] ?>" style="color:#28a745;">Edit</a>
                        <a href="delete_answer.php?id=<?= $row['id'] ?>" style="color:#28a745;" onclick="return confirm('Hapus jawaban ini?');">Hapus</a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Belum ada jawaban.</p>
    <?php endif; ?>

    <!-- FORM INPUT JAWABAN -->
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="answer-form">
            <h3>Berikan Jawaban</h3>
            <form action="answer.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                <textarea name="body" rows="5" placeholder="Tulis jawaban Anda..." required></textarea>
                <input type="file" name="image" accept="image/*">
                <input type="submit" value="Kirim Jawaban">
            </form>
        </div>
    <?php endif; ?>

</div>

</body>
</html>

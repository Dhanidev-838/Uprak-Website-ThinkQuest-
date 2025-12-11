<?php
session_start();
include 'config.php';

// Ambil semua pertanyaan dari database
$sql = "SELECT q.id, q.title, q.body, q.created_at, q.image, q.user_id,
        u.name AS user_name, u.avatar AS user_avatar
        FROM questions q
        JOIN users u ON q.user_id = u.id
        ORDER BY q.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ThinkQuest - Home</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin:0; padding:0; }
        .header { background:#007bff; color:#fff; padding:20px; display:flex; justify-content:space-between; align-items:center; }
        .header h1 { margin:0; font-size:24px; }
        .header .nav a { color:#fff; margin-left:15px; text-decoration:none; font-weight:bold; }
        .header .nav a:hover { text-decoration:underline; }
        .container { max-width:800px; margin:20px auto; padding:0 15px; }
        .ask-btn { display:inline-block; margin-bottom:15px; padding:10px 20px; background:#28a745; color:#fff; border-radius:5px; text-decoration:none; }
        .ask-btn:hover { background:#218838; }

        /* CARD JADI BUTTON */
        .question {
            background:#fff;
            padding:15px;
            margin-bottom:15px;
            border-radius:8px;
            box-shadow:0 0 5px rgba(0,0,0,0.1);
            cursor:pointer;
            transition:0.2s;
        }
        .question:hover { background:#f7f7f7; transform: scale(1.01); }

        .question h3 { margin:0; color:#007bff; }
        .question p { color:#555; }
        .meta { font-size:14px; color:#888; margin-top:5px; }

        .meta img { width:30px; height:30px; border-radius:50%; margin-right:10px; object-fit:cover; }

        .actions { margin-top:10px; }
        .actions a {
            display:inline-block;
            padding:6px 12px;
            background:#007bff;
            color:white;
            border-radius:5px;
            font-size:14px;
            text-decoration:none;
            margin-right:5px;
        }
        .actions a.delete { background:#dc3545; }
        .actions a.edit { background:#ffc107; color:#000; }
        .actions a:hover { opacity:0.8; }
    </style>
</head>

<body>
<div class="header">
    <h1>ThinkQuest</h1>
    <div class="nav">
        <?php if(isset($_SESSION['user_id'])): ?>
            <span>Halo, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="ask.php">Buat Pertanyaan</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">

    <?php if(isset($_SESSION['user_id'])): ?>
        <a class="ask-btn" href="ask.php">Ask Question</a>
    <?php endif; ?>

    <?php
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $body_snippet = strlen($row['body']) > 150 ? substr($row['body'], 0, 150).'...' : $row['body'];
            ?>
            
            <!-- CARD -->
            <div class="question" onclick="window.location='question.php?id=<?= $row['id'] ?>'">
                <h3><?= htmlspecialchars($row['title']) ?></h3>

                <?php if($row['image']): ?>
                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="question image" style="max-width:100%; margin:10px 0; border-radius:5px;">
                <?php endif; ?>

                <p><?= htmlspecialchars($body_snippet) ?></p>

                <!-- USER META + PROFIL LINK -->
                <div class="meta" onclick="event.stopPropagation();">
                    <a href="profile.php?id=<?= $row['user_id'] ?>"
                       style="display:flex; align-items:center; text-decoration:none; color:#000;">
                        
                        <?php if($row['user_avatar']): ?>
                            <img src="<?= htmlspecialchars($row['user_avatar']) ?>" alt="avatar">
                        <?php endif; ?>

                        <span>
                            <b><?= htmlspecialchars($row['user_name']) ?></b>
                            | <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                        </span>
                    </a>
                </div>

                <!-- EDIT & DELETE UNTUK PEMILIK -->
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                    <div class="actions">
                        <a class="edit" href="edit_question.php?id=<?= $row['id'] ?>">Edit</a>
                        <a class="delete" 
                           href="delete_question.php?id=<?= $row['id'] ?>" 
                           onclick="return confirm('Yakin ingin menghapus pertanyaan ini?')">
                           Hapus
                        </a>
                    </div>
                <?php endif; ?>

            </div>
            
        <?php
        }
    } else {
        echo "<p>Belum ada pertanyaan. Jadilah yang pertama untuk bertanya!</p>";
    }
    ?>
</div>
</body>
</html>

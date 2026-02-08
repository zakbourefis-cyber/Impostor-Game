<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration - Imposteur</title>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a href="index.php" style="text-decoration: none;">
        <img src="impostor_logo.png" alt="L'Imposteur" class="logo-img">
    </a>
    
    <?php if(isset($_SESSION['username'])): ?>
        <div class="header-right">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </div>
            <a href="logout.php" class="logout-btn-icon"><i class="fi fi-br-exit"></i></a>
        </div>
    <?php endif; ?>
</header>

    <div class="container">
        <h1>Type de partie</h1>
        <p>Comment voulez-vous jouer ?</p>

        <a href="create_game.php?private=0" class="btn-primary" style="margin-bottom: 15px;">
            🌍 PUBLIQUE
            <br><span style="font-size:0.8rem; font-weight:400; opacity:0.8;">Tout le monde peut rejoindre via "Partie Rapide"</span>
        </a>

        <a href="create_game.php?private=1" class="btn-secondary">
            🔒 PRIVÉE
            <br><span style="font-size:0.8rem; font-weight:400; opacity:0.8;">Uniquement avec le Code Secret</span>
        </a>

        <br><br>
        <a href="index.php" class="btn-danger" style="width: auto; padding: 10px 20px;">Annuler</a>
    </div>
</body>
</html>
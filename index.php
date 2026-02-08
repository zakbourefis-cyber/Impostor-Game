<?php
session_start();

// Si l'utilisateur n'est pas connecté, on le renvoie vers le login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby - Imposteur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="logo">🕵️ L'IMPOSTEUR</div>
    
    <?php if(isset($_SESSION['username'])): ?>
        <div class="header-right">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </div>
            
            <a href="logout.php" class="logout-btn-icon" title="Se déconnecter">
                <i class="fi fi-br-exit"></i>
            </a>
        </div>
    <?php endif; ?>
</header>
    <div class="container">
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</h1>
        <p>Prêt à démasquer l'imposteur ?</p>

        <button class="btn-primary" onclick="window.location.href='create_game.php'">Créer une partie</button>
        <button class="btn-secondary" onclick="window.location.href='join_game.php'">Rejoindre une partie</button>
        
    </div>
</body>
</html>
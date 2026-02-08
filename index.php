<?php
session_start();
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
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Animation spéciale pour le bouton Partie Rapide */
        .btn-pulse {
            background: linear-gradient(45deg, #00d4ff, #005bea);
            box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.7);
            animation: pulse-blue 2s infinite;
        }
        @keyframes pulse-blue {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(0, 212, 255, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 212, 255, 0); }
        }
    </style>
</head>
<body>
<header>
    <a href="index.php" style="text-decoration: none;">
        <img src="impostor_logo.png" alt="L'Imposteur" class="logo-img">
    </a>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="header-right">
            <div class="user-pill">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($_SESSION['pseudo'], 0, 1)); ?>
                </div>
                <div style="text-align:left; line-height:1.2;">
                    <span style="font-weight:bold;"><?php echo htmlspecialchars($_SESSION['pseudo']); ?></span>
                    <span style="font-size:0.7rem; opacity:0.6; display:block;">#<?php echo $_SESSION['tag']; ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn-icon" title="Se déconnecter"><i class="fi fi-br-exit"></i></a>
        </div>
    <?php endif; ?>
</header>

    <div class="container">
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['pseudo']); ?> !</h1>
        <p>Prêt à jouer ?</p>

        <button class="btn-primary btn-pulse" onclick="window.location.href='quick_join.php'">
            ⚡ PARTIE RAPIDE
        </button>

        <div style="display:flex; gap:10px; margin-top:15px;">
            <button class="btn-secondary" onclick="window.location.href='setup_game.php'">
                ➕ Créer
            </button>
            
            <button class="btn-secondary" onclick="window.location.href='join_game.php'">
                🔑 Rejoindre
            </button>
        </div>
    </div>
</body>
</html>
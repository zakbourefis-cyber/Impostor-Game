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
    <a href="index.php"><img src="impostor_logo.png" class="logo-img"></a>
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="header-right">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['pseudo'], 0, 1)); ?></div>
                <div style="text-align:left; line-height:1.2;">
                    <span style="font-weight:bold;"><?php echo htmlspecialchars($_SESSION['pseudo']); ?></span>
                    <span style="font-size:0.7rem; opacity:0.6; display:block;">#<?php echo $_SESSION['tag']; ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn-icon"><i class="fi fi-br-exit"></i></a>
        </div>
    <?php endif; ?>
</header>

    <div class="container">
        <h1>Créer une partie</h1>
        
        <form action="create_game.php" method="GET">
            
            <div style="background: rgba(255,255,255,0.05); padding:15px; border-radius:10px; margin-bottom:20px;">
                <label style="display:block; margin-bottom:10px; color:#aaa;">Nombre de joueurs max : <span id="nbValue" style="color:white; font-weight:bold;">10</span></label>
                <input type="range" name="max_players" min="3" max="15" value="10" oninput="document.getElementById('nbValue').innerText = this.value">
            </div>

            <button type="submit" name="private" value="0" class="btn-primary">
                🌍 CRÉER PUBLIQUE
            </button>
            
            <button type="submit" name="private" value="1" class="btn-secondary">
                🔒 CRÉER PRIVÉE
            </button>
        </form>

        <br>
        <a href="index.php" class="btn-danger" style="width: auto; padding: 10px 20px;">Annuler</a>
    </div>
</body>
</html>
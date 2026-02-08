<?php
// lobby.php
require 'db.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$game_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Récupérer les infos de la partie
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if ($game['status'] == 'playing') {
    header("Location: game.php?id=" . $game_id);
    exit;
}

// Récupérer la liste des joueurs
$stmt = $pdo->prepare("
    SELECT users.username, users.id 
    FROM game_players 
    JOIN users ON game_players.user_id = users.id 
    WHERE game_players.game_id = ?
");
$stmt->execute([$game_id]);
$players = $stmt->fetchAll();

$is_host = ($game['host_id'] == $user_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lobby - Code: <?php echo $game['room_code']; ?></title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Rafraîchir la page toutes les 3 secondes pour voir les nouveaux joueurs
        setTimeout(function(){
           location.reload();
        }, 3000);
    </script>
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
        <p>CODE DE LA SALLE</p>
        <h1 style="font-size: 3rem; margin: 10px 0; letter-spacing: 5px; color: var(--primary);">
            <?php echo $game['room_code']; ?>
        </h1>

        <h3>Joueurs (<?php echo count($players); ?>)</h3>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($players as $p): ?>
                    <li>
                        <div style="display:flex; align-items:center;">
                            <div class="avatar-circle">
                                <?php echo strtoupper(substr($p['username'], 0, 1)); ?>
                            </div>
                            <span style="font-weight:600; font-size:1.1rem;">
                                <?php echo htmlspecialchars($p['username']); ?>
                                </span>
                        </div>
                        
                        <?php if($game['host_id'] == $p['id']) echo "<span title='Hôte'>👑</span>"; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

        <?php if($is_host): ?>
            <p style="font-size: 0.8rem; color: #888;">Vous êtes l'hôte</p>
            <?php if(count($players) >= 3): ?>
                <a href="start_game.php?id=<?php echo $game_id; ?>" class="btn-primary">Lancer la partie</a>
            <?php else: ?>
                <button class="btn-secondary" disabled>En attente de joueurs (min 3)...</button>
            <?php endif; ?>
        <?php else: ?>
            <p>En attente de l'hôte...</p>
        <?php endif; ?>
        
        <br>
        <a href="index.php" style="color: var(--danger);">Quitter le lobby</a>
    </div>
</body>
</html>
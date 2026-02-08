<?php
require 'db.php';
session_start();
$game_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Récupérer infos partie
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();
$is_host = ($game['host_id'] == $user_id);

// 1. Qui était l'imposteur ?
$stmt = $pdo->prepare("SELECT user_id, pseudo FROM game_players JOIN users ON users.id = game_players.user_id WHERE game_id = ? AND role = 'impostor'");
$stmt->execute([$game_id]);
$impostor = $stmt->fetch();

// 2. Récupérer TOUS les votes (Qui a voté qui)
$sql = "
    SELECT u1.pseudo as voteur, u2.pseudo as cible 
    FROM game_players gp
    JOIN users u1 ON gp.user_id = u1.id
    LEFT JOIN users u2 ON gp.vote_for = u2.id
    WHERE gp.game_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$game_id]);
$votes_details = $stmt->fetchAll();

// 3. Calcul du perdant (le plus voté)
$sql = "SELECT vote_for, COUNT(*) as count FROM game_players WHERE game_id = ? AND vote_for IS NOT NULL GROUP BY vote_for ORDER BY count DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$game_id]);
$vote_result = $stmt->fetch();
$most_voted_id = $vote_result ? $vote_result['vote_for'] : null;

// Victoire ?
$impostor_caught = ($most_voted_id == $impostor['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby - Imposteur</title>
    
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    
    <link rel="stylesheet" href="style.css">
    <script>
        setInterval(function() {
            fetch('check_status.php?game_id=<?php echo $game_id; ?>')
            .then(r => r.text())
            .then(status => {
                // Si le statut repasse à "waiting", on retourne au lobby !
                if (status.trim() === 'waiting') {
                    window.location.href = 'lobby.php?id=<?php echo $game_id; ?>';
                }
            });
        }, 3000);
    </script>
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
        
        <?php if ($impostor_caught): ?>
            <h1 style="color: #00d4ff;">LES CIVILS GAGNENT ! 🎉</h1>
            <p>L'imposteur a été démasqué.</p>
        <?php else: ?>
            <h1 style="color: #ff4b5c;">L'IMPOSTEUR GAGNE ! 😈</h1>
            <p>L'imposteur a échappé au vote.</p>
        <?php endif; ?>

        <div style="background:#0f3460; padding:20px; border-radius:10px; margin:20px 0; border: 2px solid #ff4b5c;">
            <p style="margin:0; font-size:0.9rem;">L'imposteur était :</p>
            <h2 style="font-size: 2.5rem; margin:5px 0; color: #ff4b5c;">
                <?php echo htmlspecialchars($impostor['pseudo']); ?>
            </h2>
        </div>

        <h3>Détail des votes :</h3>
        <ul style="list-style:none; padding:0; text-align:left;">
            <?php foreach($votes_details as $v): ?>
                <li style="border-bottom:1px solid #444; padding:5px;">
                    <strong><?php echo htmlspecialchars($v['voteur']); ?></strong> 
                    a voté contre 
                    <span style="color:var(--danger)"><?php echo htmlspecialchars($v['cible']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <br>

        <?php if($is_host): ?>
            <a href="restart.php?id=<?php echo $game_id; ?>" class="btn-primary" style="display:block; text-decoration:none;">🔄 Rejouer (Même joueurs)</a>
        <?php else: ?>
            <p>En attente de l'hôte pour rejouer...</p>
        <?php endif; ?>
        
        <br>
        <a href="index.php" class="btn-secondary">Quitter vers l'accueil</a>
    </div>
</body>
</html>
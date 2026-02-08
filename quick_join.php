<?php
// quick_join.php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo->query("DELETE FROM games WHERE status = 'waiting' AND last_updated < (NOW() - INTERVAL 1 MINUTE)");

// 1. Chercher une partie PUBLIQUE (is_private = 0) et EN ATTENTE (waiting)
// On prend la plus ancienne créée (pour remplir les premiers serveurs)
$stmt = $pdo->prepare("SELECT * FROM games WHERE status = 'waiting' AND is_private = 0 ORDER BY created_at ASC LIMIT 1");
$stmt->execute();
$game = $stmt->fetch();

if ($game) {
    // --- UNE PARTIE TROUVÉE ! ---
    
    // Vérifier si on est déjà dedans (pour éviter les doublons/bugs)
    $check = $pdo->prepare("SELECT * FROM game_players WHERE game_id = ? AND user_id = ?");
    $check->execute([$game['id'], $_SESSION['user_id']]);
    
    if ($check->rowCount() == 0) {
        // Ajouter le joueur
        $stmt = $pdo->prepare("INSERT INTO game_players (game_id, user_id) VALUES (?, ?)");
        $stmt->execute([$game['id'], $_SESSION['user_id']]);
    }
    
    // Redirection vers le lobby
    header("Location: lobby.php?id=" . $game['id']);
    exit;

} else {
    // --- AUCUNE PARTIE TROUVÉE ---
    // On affiche une petite erreur et on propose de créer
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Partie Rapide</title>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <header>
        <a href="index.php"><img src="impostor_logo.png" class="logo-img"></a>
    </header>
        <div class="container">
            <h1>Oups ! 😕</h1>
            <p>Aucune partie publique n'est disponible pour le moment.</p>
            <p>Soyez le premier à en lancer une !</p>
            
            <a href="create_game.php?private=0" class="btn-primary btn-pulse">
                ➕ Créer une partie Publique
            </a>
            <br>
            <a href="index.php" class="btn-secondary">Retour au menu</a>
        </div>
    </body>
    </html>
    <?php
}
?>
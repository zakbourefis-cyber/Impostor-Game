<?php
// join_game.php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = strtoupper(trim($_POST['room_code']));

    // 1. Vérifier si la partie existe
    $stmt = $pdo->prepare("SELECT * FROM games WHERE room_code = ? AND status = 'waiting'");
    $stmt->execute([$code]);
    $game = $stmt->fetch();

    if ($game) {
        // 2. Vérifier si le joueur est déjà dedans pour éviter les doublons
        $check = $pdo->prepare("SELECT * FROM game_players WHERE game_id = ? AND user_id = ?");
        $check->execute([$game['id'], $_SESSION['user_id']]);
        
        if ($check->rowCount() == 0) {
            // 3. Ajouter le joueur
            $stmt = $pdo->prepare("INSERT INTO game_players (game_id, user_id) VALUES (?, ?)");
            $stmt->execute([$game['id'], $_SESSION['user_id']]);
        }
        
        // 4. Redirection
        header("Location: lobby.php?id=" . $game['id']);
        exit;
    } else {
        $error = "Partie introuvable ou déjà commencée !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rejoindre - Imposteur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Rejoindre</h1>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="room_code" placeholder="CODE (ex: ABCDE)" maxlength="5" style="text-transform:uppercase" required>
            <button type="submit" class="btn-primary">Entrer dans la salle</button>
        </form>
        <a href="index.php">Retour</a>
    </div>
</body>
</html>
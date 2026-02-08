<?php
require 'db.php';
session_start();

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$game_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Récupérer infos partie + Compter joueurs actuels
$stmt = $pdo->prepare("
    SELECT g.*, COUNT(gp.id) as current_players 
    FROM games g 
    LEFT JOIN game_players gp ON g.id = gp.game_id 
    WHERE g.id = ? 
    GROUP BY g.id
");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game || $game['status'] != 'waiting') {
    die("Partie introuvable ou déjà commencée. <a href='index.php'>Retour</a>");
}

// 2. Vérifier si c'est complet
if ($game['current_players'] >= $game['max_players']) {
    die("Cette salle est complète ! (" . $game['max_players'] . " joueurs max). <a href='index.php'>Retour</a>");
}

// 3. Vérifier si on est déjà dedans
$check = $pdo->prepare("SELECT * FROM game_players WHERE game_id = ? AND user_id = ?");
$check->execute([$game_id, $user_id]);

if ($check->rowCount() == 0) {
    // Ajouter le joueur
    $stmt = $pdo->prepare("INSERT INTO game_players (game_id, user_id) VALUES (?, ?)");
    $stmt->execute([$game_id, $user_id]);
}

header("Location: lobby.php?id=" . $game_id);
exit;
?>
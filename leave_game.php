<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Trouver dans quelle partie est le joueur
$stmt = $pdo->prepare("SELECT game_id FROM game_players WHERE user_id = ?");
$stmt->execute([$user_id]);
$game_id = $stmt->fetchColumn();

if ($game_id) {
    // 2. Vérifier si c'est l'Hôte
    $stmt = $pdo->prepare("SELECT host_id FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    $host_id = $stmt->fetchColumn();

    if ($host_id == $user_id) {
        // SI C'EST L'HÔTE -> On supprime tout le salon
        $pdo->prepare("DELETE FROM games WHERE id = ?")->execute([$game_id]);
        $pdo->prepare("DELETE FROM game_players WHERE game_id = ?")->execute([$game_id]);
        $pdo->prepare("DELETE FROM chat_messages WHERE game_id = ?")->execute([$game_id]);
    } else {
        // SI C'EST UN JOUEUR -> On le retire juste de la liste
        $pdo->prepare("DELETE FROM game_players WHERE user_id = ? AND game_id = ?")->execute([$user_id, $game_id]);
    }
}

// Retour à l'accueil
header("Location: index.php");
exit;
?>
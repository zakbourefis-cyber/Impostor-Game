<?php
// create_game.php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer le choix (Par défaut Privée si non précisé)
$is_private = isset($_GET['private']) ? (int)$_GET['private'] : 1;

// 1. Générer un Code Room unique
$room_code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

// 2. Créer la partie avec le statut PRIVÉ ou PUBLIC
$stmt = $pdo->prepare("INSERT INTO games (room_code, host_id, status, is_private) VALUES (?, ?, 'waiting', ?)");
$stmt->execute([$room_code, $_SESSION['user_id'], $is_private]);
$game_id = $pdo->lastInsertId();

// 3. Ajouter le créateur (Host)
$stmt = $pdo->prepare("INSERT INTO game_players (game_id, user_id, role) VALUES (?, ?, 'player')");
$stmt->execute([$game_id, $_SESSION['user_id']]);

// 4. Rediriger vers le lobby
header("Location: lobby.php?id=" . $game_id);
exit;
?>
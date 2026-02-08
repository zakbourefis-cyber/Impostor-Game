<?php
// create_game.php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Générer un Code Room unique (5 lettres majuscules)
$room_code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

// 2. Créer la partie dans la BDD
$stmt = $pdo->prepare("INSERT INTO games (room_code, host_id, status) VALUES (?, ?, 'waiting')");
$stmt->execute([$room_code, $_SESSION['user_id']]);
$game_id = $pdo->lastInsertId();

// 3. Ajouter le créateur (Host) comme premier joueur
$stmt = $pdo->prepare("INSERT INTO game_players (game_id, user_id, role) VALUES (?, ?, 'player')");
$stmt->execute([$game_id, $_SESSION['user_id']]);

// 4. Rediriger vers le lobby
header("Location: lobby.php?id=" . $game_id);
exit;
?>
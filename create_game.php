<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$is_private = isset($_GET['private']) ? (int)$_GET['private'] : 1;
$max_players = isset($_GET['max_players']) ? (int)$_GET['max_players'] : 10;

// Générer Code
$room_code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

// Créer la partie avec max_players
$stmt = $pdo->prepare("INSERT INTO games (room_code, host_id, status, is_private, max_players) VALUES (?, ?, 'waiting', ?, ?)");
$stmt->execute([$room_code, $_SESSION['user_id'], $is_private, $max_players]);
$game_id = $pdo->lastInsertId();

// Ajouter l'hôte
$stmt = $pdo->prepare("INSERT INTO game_players (game_id, user_id, role) VALUES (?, ?, 'player')");
$stmt->execute([$game_id, $_SESSION['user_id']]);

header("Location: lobby.php?id=" . $game_id);
exit;
?>
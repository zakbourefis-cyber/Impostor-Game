<?php
require 'db.php';
session_start();

if (!isset($_GET['id'])) die("Erreur ID");
$game_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Vérifier que c'est bien l'hôte qui demande le restart
$stmt = $pdo->prepare("SELECT host_id FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$host_id = $stmt->fetchColumn();

if ($host_id != $user_id) die("Seul l'hôte peut relancer la partie.");

// 1. Remettre le statut de la partie à "waiting"
$pdo->prepare("UPDATE games SET status = 'waiting' WHERE id = ?")->execute([$game_id]);

// 2. Nettoyer les joueurs (On garde les joueurs, on reset juste leurs infos de jeu)
$sql = "UPDATE game_players SET role = NULL, word = NULL, vote_for = NULL WHERE game_id = ?";
$pdo->prepare($sql)->execute([$game_id]);

// 3. (Optionnel) Vider le chat pour la nouvelle manche
$pdo->prepare("DELETE FROM chat_messages WHERE game_id = ?")->execute([$game_id]);

// 4. Rediriger l'hôte vers le lobby
header("Location: lobby.php?id=" . $game_id);
exit;
?>
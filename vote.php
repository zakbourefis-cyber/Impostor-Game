<?php
require 'db.php';
session_start();

if (!isset($_POST['game_id']) || !isset($_POST['target_id'])) die("Erreur données");

$game_id = $_POST['game_id'];
$target_id = $_POST['target_id']; // L'ID du joueur contre qui on vote
$user_id = $_SESSION['user_id'];

// 1. Enregistrer le vote
$stmt = $pdo->prepare("UPDATE game_players SET vote_for = ? WHERE game_id = ? AND user_id = ?");
$stmt->execute([$target_id, $game_id, $user_id]);

// 2. Vérifier si tout le monde a voté
// On compte combien de joueurs n'ont PAS encore voté (vote_for IS NULL)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM game_players WHERE game_id = ? AND vote_for IS NULL");
$stmt->execute([$game_id]);
$remaining_votes = $stmt->fetchColumn();

if ($remaining_votes == 0) {
    // Tout le monde a voté ! On ferme la partie.
    $pdo->prepare("UPDATE games SET status = 'finished' WHERE id = ?")->execute([$game_id]);
    echo "finished";
} else {
    echo "ok";
}
?>
<?php
// api_lobby.php
require 'db.php';
session_start();

if (!isset($_GET['game_id'])) exit;

$game_id = $_GET['id']; // Attention: dans ton JS on enverra ?id=...
$user_id = $_SESSION['user_id'];

// 1. Récupérer infos partie
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

// --- HEARTBEAT (Le cœur qui bat) ---
// Si c'est l'hôte qui appelle ce fichier, on met à jour la date pour ne pas supprimer le lobby
if ($game['host_id'] == $user_id) {
    $pdo->prepare("UPDATE games SET last_updated = NOW() WHERE id = ?")->execute([$game_id]);
}

// 2. Récupérer les joueurs
$stmt = $pdo->prepare("
    SELECT users.pseudo, users.id 
    FROM game_players 
    JOIN users ON game_players.user_id = users.id 
    WHERE game_players.game_id = ?
");
$stmt->execute([$game_id]);
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Renvoyer tout en JSON
header('Content-Type: application/json');
echo json_encode([
    'status' => $game['status'],
    'players' => $players,
    'host_id' => $game['host_id'],
    'current_user_id' => $user_id
]);
?>
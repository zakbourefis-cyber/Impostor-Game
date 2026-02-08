<?php
// api_lobby.php
require 'db.php';
session_start();

if (!isset($_GET['id'])) exit;

$game_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// --- 1. NETTOYAGE AUTOMATIQUE (GHOST KILLER) 👻 ---
// On supprime les joueurs inactifs depuis plus de 10 secondes
$pdo->query("DELETE FROM game_players WHERE last_seen < (NOW() - INTERVAL 4 SECOND)");

// On supprime les parties dont l'hôte est inactif (ou parti)
// On regarde si l'hôte est encore dans la liste des joueurs
$stmt = $pdo->prepare("SELECT host_id FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$host_id = $stmt->fetchColumn();

// On vérifie si l'hôte est toujours présent dans game_players
$stmt = $pdo->prepare("SELECT COUNT(*) FROM game_players WHERE game_id = ? AND user_id = ?");
$stmt->execute([$game_id, $host_id]);
$host_is_here = $stmt->fetchColumn();

if ($host_id && $host_is_here == 0) {
    // L'hôte a disparu (ex: onglet fermé), on supprime la game
    $pdo->prepare("DELETE FROM games WHERE id = ?")->execute([$game_id]);
    $pdo->prepare("DELETE FROM game_players WHERE game_id = ?")->execute([$game_id]); // Sécurité
    echo json_encode(['status' => 'deleted']); // On prévient le JS
    exit;
}
// ----------------------------------------------------


// --- 2. JE SUIS VIVANT ! (Mise à jour de mon timestamp) ---
// Chaque fois que ce script est appelé, on met à jour l'heure de "dernière vue" du joueur
$stmt = $pdo->prepare("UPDATE game_players SET last_seen = NOW() WHERE game_id = ? AND user_id = ?");
$stmt->execute([$game_id, $user_id]);


// --- 3. RÉCUPÉRATION CLASSIQUE DES DONNÉES ---
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game) {
    echo json_encode(['status' => 'deleted']);
    exit;
}

// Récupérer les joueurs
$stmt = $pdo->prepare("
    SELECT users.pseudo, users.id 
    FROM game_players 
    JOIN users ON game_players.user_id = users.id 
    WHERE game_players.game_id = ?
");
$stmt->execute([$game_id]);
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'status' => $game['status'],
    'players' => $players,
    'host_id' => $game['host_id'],
    'current_user_id' => $user_id
]);
?>
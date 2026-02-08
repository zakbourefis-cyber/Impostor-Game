<?php
// api_server_list.php
require 'db.php';
session_start();

// On vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) exit;

// On nettoie les parties fantômes avant de renvoyer la liste (pour être toujours à jour)
$pdo->query("DELETE FROM games WHERE status = 'waiting' AND last_updated < (NOW() - INTERVAL 10 SECOND)");
$pdo->query("DELETE FROM game_players WHERE last_seen < (NOW() - INTERVAL 10 SECOND)");

// --- RECUPERATION DES SALONS ---
$sql = "
    SELECT g.*, u.pseudo as host_name, 
    (SELECT COUNT(*) FROM game_players WHERE game_id = g.id) as player_count,
    (SELECT GROUP_CONCAT(users.pseudo SEPARATOR ', ') 
     FROM game_players 
     JOIN users ON game_players.user_id = users.id 
     WHERE game_players.game_id = g.id) as player_names
    FROM games g 
    JOIN users u ON g.host_id = u.id 
    WHERE g.status = 'waiting' AND g.is_private = 0 
    ORDER BY g.created_at DESC
";
$stmt = $pdo->query($sql);
$public_games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On renvoie le tout en JSON
header('Content-Type: application/json');
echo json_encode($public_games);
?>
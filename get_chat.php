<?php
// get_chat.php
require 'db.php';
session_start();

if (!isset($_GET['game_id'])) die("Manque ID");

$game_id = $_GET['game_id'];

// On récupère le message + le nom du joueur qui l'a écrit
$sql = "SELECT cm.message, u.username, cm.created_at 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        WHERE cm.game_id = ? 
        ORDER BY cm.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$game_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On renvoie le tout en JSON
header('Content-Type: application/json');
echo json_encode($messages);
?>
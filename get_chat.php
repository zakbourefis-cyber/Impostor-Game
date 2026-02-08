<?php
require 'db.php';
session_start();
if (!isset($_GET['game_id'])) die("Manque ID");

$game_id = $_GET['game_id'];

// On sélectionne le PSEUDO au lieu du USERNAME
// Modifiez 'as username' par 'as pseudo' (ou enlevez l'alias)
$sql = "SELECT cm.message, u.pseudo, cm.created_at 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        WHERE cm.game_id = ? 
        ORDER BY cm.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$game_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
?>
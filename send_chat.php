<?php
// send_chat.php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['game_id']) || !isset($_POST['message'])) {
    die("Erreur données manquantes");
}

$game_id = $_POST['game_id'];
$user_id = $_SESSION['user_id'];
$message = trim($_POST['message']);

if ($message !== "") {
    $stmt = $pdo->prepare("INSERT INTO chat_messages (game_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$game_id, $user_id, $message]);
}
?>
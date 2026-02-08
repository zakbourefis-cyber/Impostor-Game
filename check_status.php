<?php
require 'db.php';
$stmt = $pdo->prepare("SELECT status FROM games WHERE id = ?");
$stmt->execute([$_GET['game_id']]);
echo $stmt->fetchColumn();
?>
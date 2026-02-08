<?php
require 'db.php';
session_start();

if (!isset($_GET['id'])) die("Pas d'ID de partie.");
$game_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Vérifier qu'on est bien l'hôte
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if ($game['host_id'] != $user_id) die("Vous n'êtes pas l'hôte !");

// 2. Vérifier le nombre de joueurs (Minimum 3 pour que le jeu soit fun)
$stmt = $pdo->prepare("SELECT * FROM game_players WHERE game_id = ?");
$stmt->execute([$game_id]);
$players = $stmt->fetchAll();

if (count($players) < 3) {
    die("Il faut au moins 3 joueurs ! <a href='lobby.php?id=$game_id'>Retour</a>");
}

// 3. CHOISIR LE MOT SECRET (Liste en PHP pour l'instant)
$words = [
    "Pizza", "Astronaute", "Plage", "Vampire", "Internet", "Chocolat", 
    "Piano", "Tour Eiffel", "Football", "Pyramide", "Café", "Licorne", 
    "Cinéma", "Ninja", "Dinosaure", "Tigre", "Banane", "Zombie"
];
$secret_word = $words[array_rand($words)];

// 4. CHOISIR L'IMPOSTEUR
$impostor_index = array_rand($players); // Choisit un index au hasard (0, 1, 2...)
$impostor_user_id = $players[$impostor_index]['user_id'];

// 5. ENREGISTRER TOUT ÇA DANS LA BDD
// D'abord, on met tout le monde en "civilian" avec le mot secret
$sql = "UPDATE game_players SET role = 'civilian', word = ? WHERE game_id = ?";
$pdo->prepare($sql)->execute([$secret_word, $game_id]);

// Ensuite, on écrase le rôle de l'élu pour le mettre en "impostor" (sans mot ou mot différent)
$sql = "UPDATE game_players SET role = 'impostor', word = NULL WHERE game_id = ? AND user_id = ?";
$pdo->prepare($sql)->execute([$game_id, $impostor_user_id]);

// 6. CHANGER LE STATUT DE LA PARTIE (C'est le signal de départ)
$pdo->prepare("UPDATE games SET status = 'playing' WHERE id = ?")->execute([$game_id]);

// Redirection
header("Location: game.php?id=" . $game_id);
exit;
?>
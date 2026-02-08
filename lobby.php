<?php
require 'db.php';
session_start();
if (!isset($_GET['id'])) header("Location: index.php");
$game_id = $_GET['id'];

// On récupère juste le code de la salle pour l'affichage initial
$stmt = $pdo->prepare("SELECT room_code FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$room_code = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lobby - Code: <?php echo $room_code; ?></title>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a href="index.php"><img src="impostor_logo.png" alt="Logo" class="logo-img"></a>
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="header-right">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['pseudo'], 0, 1)); ?></div>
                <div style="text-align:left; line-height:1.2;">
                    <span style="font-weight:bold;"><?php echo htmlspecialchars($_SESSION['pseudo']); ?></span>
                    <span style="font-size:0.7rem; opacity:0.6; display:block;">#<?php echo $_SESSION['tag']; ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn-icon"><i class="fi fi-br-exit"></i></a>
        </div>
    <?php endif; ?>
</header>

<div class="container">
    <p>CODE DE LA SALLE</p>
    <h1 style="font-size: 3rem; margin: 10px 0; letter-spacing: 5px; color: var(--primary);">
        <?php echo $room_code; ?>
    </h1>

    <h3>Joueurs (<span id="player-count">0</span>)</h3>
    
    <ul id="player-list" style="list-style: none; padding: 0;"></ul>

    <div id="host-controls"></div>
    
    <p id="guest-message" style="display:none;">En attente de l'hôte...</p>
    
    <br>
    <a href="index.php" class="btn-danger">Quitter le lobby</a>
</div>

<script>
    const gameId = <?php echo $game_id; ?>;
    const playerListEl = document.getElementById('player-list');
    const countEl = document.getElementById('player-count');
    const hostControlsEl = document.getElementById('host-controls');
    const guestMessageEl = document.getElementById('guest-message');

    function refreshLobby() {
        // On appelle l'API au lieu de recharger la page
        fetch('api_lobby.php?id=' + gameId)
        .then(response => response.json())
        .then(data => {
            
            // 1. Si la partie est lancée -> On y va !
            if (data.status === 'playing') {
                window.location.href = 'game.php?id=' + gameId;
                return;
            }

            // 2. Mettre à jour la liste des joueurs
            countEl.innerText = data.players.length;
            playerListEl.innerHTML = ""; // On vide la liste
            
            data.players.forEach(p => {
                const isHost = (p.id == data.host_id);
                const crown = isHost ? "<span title='Hôte'>👑</span>" : "";
                
                // On recrée le HTML pour chaque joueur
                playerListEl.innerHTML += `
                    <li>
                        <div style="display:flex; align-items:center;">
                            <div class="avatar-circle">${p.pseudo.charAt(0).toUpperCase()}</div>
                            <span style="font-weight:600; font-size:1.1rem;">
                                ${p.pseudo}
                            </span>
                        </div>
                        ${crown}
                    </li>
                `;
            });

            // 3. Gestion bouton Hôte / Invité
            if (data.current_user_id == data.host_id) {
                guestMessageEl.style.display = 'none';
                if (data.players.length >= 3) {
                    hostControlsEl.innerHTML = `<a href="start_game.php?id=${gameId}" class="btn-primary">Lancer la partie</a>`;
                } else {
                    hostControlsEl.innerHTML = `<button class="btn-secondary" disabled>En attente de joueurs (min 3)...</button>`;
                }
            } else {
                hostControlsEl.innerHTML = "";
                guestMessageEl.style.display = 'block';
            }
        })
        .catch(err => console.error("Erreur chargement lobby", err));
    }

    // On rafraîchit toutes les 1 seconde (C'est fluide car ça ne clignote pas !)
    setInterval(refreshLobby, 1000);
    
    // Premier appel immédiat
    refreshLobby();
</script>

</body>
</html>
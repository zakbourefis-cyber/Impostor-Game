<?php
require 'db.php';
session_start();
if (!isset($_GET['id'])) header("Location: index.php");
$game_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Récupérer mon rôle
$stmt = $pdo->prepare("SELECT * FROM game_players WHERE game_id = ? AND user_id = ?");
$stmt->execute([$game_id, $user_id]);
$my_data = $stmt->fetch();
$role = $my_data['role'];
$word = $my_data['word'];
$has_voted = ($my_data['vote_for'] !== null); // Est-ce que j'ai déjà voté ?

// Récupérer la liste des AUTRES joueurs pour le vote
$stmt = $pdo->prepare("
    SELECT gp.user_id, u.username 
    FROM game_players gp 
    JOIN users u ON gp.user_id = u.id 
    WHERE gp.game_id = ? AND gp.user_id != ?
");
$stmt->execute([$game_id, $user_id]);
$other_players = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Jeu en cours</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Styles Chat (identiques à avant) */
        #chat-container { background: rgba(0,0,0,0.3); border-radius: 10px; padding: 10px; text-align: left; }
        #chat-box { height: 200px; overflow-y: scroll; border: 1px solid #444; padding: 10px; margin-bottom: 10px; background: #0f3460; display: flex; flex-direction: column; }
        .message { margin-bottom: 5px; font-size: 0.9rem; }
        
        /* Styles Vote */
        .vote-section { margin-top: 20px; border-top: 1px solid #444; padding-top: 20px; }
        .vote-btn { width: 100%; margin: 5px 0; background: #ff4b5c; border:none; padding:10px; color:white; border-radius:5px; cursor:pointer; }
        .vote-btn:hover { background: #d43746; }
        .disabled { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>
<header>
    <div class="logo">🕵️ L'IMPOSTEUR</div>
    
    <?php if(isset($_SESSION['username'])): ?>
        <div class="header-right">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </div>
            
            <a href="logout.php" class="logout-btn-icon" title="Se déconnecter">
                <i class="fi fi-br-exit"></i>
            </a>
        </div>
    <?php endif; ?>
</header>
    <div class="container">
        
        <div style="background: #0f3460; padding: 10px; border-radius: 10px; border: 2px solid var(--primary); margin-bottom: 10px;">
            <?php if ($role == 'impostor'): ?>
                <h2 style="color:var(--danger)">🤫 IMPOSTEUR</h2>
            <?php else: ?>
                <h2 style="color:var(--primary)">🕵️ CIVIL</h2>
                <p>Mot : <strong><?php echo htmlspecialchars($word); ?></strong></p>
            <?php endif; ?>
        </div>

        <div id="chat-container">
            <div id="chat-box"></div>
            <div style="display:flex;">
                <input type="text" id="msgInput" placeholder="Message..." style="margin:0;">
                <button onclick="sendMessage()" class="btn-primary" style="width: 80px; margin:0;">Envoyer</button>
            </div>
        </div>

        <div class="vote-section" id="vote-area">
            <h3>Qui est l'imposteur ?</h3>
            <?php if ($has_voted): ?>
                <p>Vote enregistré. En attente des autres...</p>
            <?php else: ?>
                <?php foreach($other_players as $p): ?>
                    <button class="vote-btn" onclick="castVote(<?php echo $p['user_id']; ?>)">
                        Voter contre <?php echo htmlspecialchars($p['username']); ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const gameId = <?php echo $game_id; ?>;
        
        // --- CHAT (Même logique) ---
        function sendMessage() {
            const msg = document.getElementById('msgInput').value;
            if(msg.trim() === "") return;
            const fd = new FormData(); fd.append('game_id', gameId); fd.append('message', msg);
            fetch('send_chat.php', { method: 'POST', body: fd }).then(() => {
                document.getElementById('msgInput').value = ""; loadMessages();
            });
        }
        function loadMessages() {
            fetch('get_chat.php?game_id=' + gameId).then(r => r.json()).then(data => {
                const box = document.getElementById('chat-box'); box.innerHTML = "";
                data.forEach(m => box.innerHTML += `<div class="message"><strong>${m.username}:</strong> ${m.message}</div>`);
            });
        }
        setInterval(loadMessages, 2000);

        // --- VOTE ---
        function castVote(targetId) {
            if(!confirm("Sûr de voter contre ce joueur ?")) return;

            const fd = new FormData();
            fd.append('game_id', gameId);
            fd.append('target_id', targetId);

            fetch('vote.php', { method: 'POST', body: fd })
            .then(response => response.text())
            .then(res => {
                // On cache les boutons
                document.getElementById('vote-area').innerHTML = "<p>A voté ! En attente des résultats...</p>";
            });
        }

        // --- VÉRIFICATION FIN DE PARTIE ---
        function checkGameStatus() {
            fetch('check_status.php?game_id=' + gameId)
            .then(r => r.text())
            .then(status => {
                if(status.trim() === 'finished') {
                    window.location.href = 'result.php?id=' + gameId;
                }
            });
        }
        setInterval(checkGameStatus, 3000); // Vérifie toutes les 3s si le jeu est fini

    </script>
</body>
</html>
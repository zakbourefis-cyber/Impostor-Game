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
    SELECT gp.user_id, u.pseudo 
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
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<header>
    <a href="index.php" style="text-decoration: none;">
        <img src="impostor_logo.png" alt="L'Imposteur" class="logo-img">
    </a>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="header-right">
            <div class="user-pill">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($_SESSION['pseudo'], 0, 1)); ?>
                </div>
                <div style="text-align:left; line-height:1.2;">
                    <span style="font-weight:bold;"><?php echo htmlspecialchars($_SESSION['pseudo']); ?></span>
                    <span style="font-size:0.7rem; opacity:0.6; display:block;">#<?php echo $_SESSION['tag']; ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn-icon" title="Se déconnecter"><i class="fi fi-br-exit"></i></a>
        </div>
    <?php endif; ?>
</header>

    <div class="container">
        
        <div class="role-card-modern <?php echo ($role == 'impostor') ? 'role-impostor' : 'role-civilian'; ?>">
            <?php if ($role == 'impostor'): ?>
                <h2 style="color:#ff4b5c; margin:0; text-transform:uppercase; letter-spacing:2px;">🤫 IMPOSTEUR</h2>
                <p style="margin-top:5px; opacity:0.9;">Tu n'as pas le mot secret.<br>Fonds-toi dans la masse !</p>
            <?php else: ?>
                <h2 style="color:#00d4ff; margin:0; text-transform:uppercase; letter-spacing:2px;">🕵️ CIVIL</h2>
                <p style="margin:5px 0;">Le mot secret est :</p>
                <div class="word-pill blur" title="Survolez pour voir"><?php echo htmlspecialchars($word); ?></div>
            <?php endif; ?>
        </div>

        <div class="chat-window">
            <div id="chat-box" class="chat-messages-area"></div>
            
            <div class="input-group">
                <input type="text" id="msgInput" placeholder="Écrivez votre message..." autocomplete="off">
                <button onclick="sendMessage()" class="btn-primary" style="background: var(--accent);"><i class="fi fi-br-paper-plane"></i></button>
            </div>
        </div>

        <div class="vote-section" id="vote-area" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <h3 style="margin-bottom: 15px;">Votez contre l'Imposteur</h3>
            
            <?php if ($has_voted): ?>
                <div style="background: rgba(0,255,0,0.1); padding:15px; border-radius:10px; border:1px solid #00ff00;">
                    ✅ Vote enregistré. En attente des autres...
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <?php foreach($other_players as $p): ?>
                        <button class="btn-danger" style="margin:0; font-size:0.9rem;" onclick="castVote(<?php echo $p['user_id']; ?>)">
                            <?php echo htmlspecialchars($p['pseudo']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
       const gameId = <?php echo $game_id; ?>;
    const chatBox = document.getElementById('chat-box');

    // --- 1. LA PALETTE "TWITCH" (Couleurs vives pour fond sombre) ---
    const nameColors = [
        "#FF4500", // Orange Rouge
        "#00FF7F", // Spring Green
        "#00BFFF", // Deep Sky Blue
        "#FFD700", // Gold
        "#FF69B4", // Hot Pink
        "#ADFF2F", // Green Yellow
        "#FF6347", // Tomato
        "#BA55D3", // Medium Orchid
        "#00FFFF", // Cyan
        "#F08080", // Light Coral
        "#9370DB", // Medium Purple
        "#7FFFD4"  // Aquamarine
    ];

    // --- 2. FONCTION MAGIQUE : Pseudo -> Couleur ---
    function getColorFromPseudo(pseudo) {
        let hash = 0;
        // On transforme les lettres en nombre
        for (let i = 0; i < pseudo.length; i++) {
            hash = pseudo.charCodeAt(i) + ((hash << 5) - hash);
        }
        // On s'assure que le nombre est positif et on choisit dans la liste
        const index = Math.abs(hash % nameColors.length);
        return nameColors[index];
    }

    // --- CHAT ---
    function sendMessage() {
        const msgInput = document.getElementById('msgInput');
        const msg = msgInput.value;
        if(msg.trim() === "") return;
        
        const fd = new FormData(); fd.append('game_id', gameId); fd.append('message', msg);
        fetch('send_chat.php', { method: 'POST', body: fd }).then(() => {
            msgInput.value = ""; loadMessages();
        });
    }

    function loadMessages() {
        fetch('get_chat.php?game_id=' + gameId).then(r => r.json()).then(data => {
            const currentScroll = chatBox.scrollTop;
            const isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 10;
            
            chatBox.innerHTML = "";
            data.forEach(m => {
                // On calcule la couleur unique pour ce pseudo
                const userColor = getColorFromPseudo(m.pseudo);

                // On l'applique directement dans le style du <strong>
                chatBox.innerHTML += `
                    <div class="chat-bubble">
                        <strong style="color: ${userColor}; text-shadow: 0 0 5px ${userColor}40;">${m.pseudo}</strong>
                        ${m.message}
                    </div>`;
            });

            if(isScrolledToBottom) chatBox.scrollTop = chatBox.scrollHeight;
        });
    }
    </script>
</body>
</html>
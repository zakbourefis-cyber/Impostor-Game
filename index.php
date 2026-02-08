<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Imposteur</title>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel="stylesheet" href="style.css">
    <style>
        .btn-pulse {
            background: linear-gradient(45deg, #00d4ff, #005bea);
            box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.7);
            animation: pulse-blue 2s infinite;
        }
        @keyframes pulse-blue {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(0, 212, 255, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 212, 255, 0); }
        }

        /* Styles pour la liste des serveurs */
        .server-list {
            margin-top: 30px;
            text-align: left;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 5px;
        }
        .server-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.3s;
            animation: fadeIn 0.5s ease;
        }
        .server-card:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .server-info {
            flex-grow: 1;
            margin-right: 15px;
            overflow: hidden;
        }
        .server-info h4 { margin: 0; color: var(--accent); font-size: 1.1rem; }
        
        .player-preview {
            font-size: 0.85rem; 
            color: #aaa; 
            margin-top: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .server-status {
            background: #0f0c29;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        @keyframes fadeIn { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:translateY(0); } }
    </style>
</head>
<body>
<header>
    <a href="index.php"><img src="impostor_logo.png" class="logo-img"></a>
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
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['pseudo']); ?> !</h1>
        
        <button class="btn-primary btn-pulse" onclick="window.location.href='quick_join.php'">
            ⚡ PARTIE RAPIDE
        </button>

        <div style="display:flex; gap:10px; margin-top:15px;">
            <button class="btn-secondary" onclick="window.location.href='setup_game.php'">
                ➕ Créer
            </button>
            <button class="btn-secondary" onclick="window.location.href='join_game.php'">
                🔑 Code
            </button>
        </div>

        <h3 style="margin-top: 30px; text-align: left; border-bottom: 1px solid #444; padding-bottom: 10px;">
            🌍 Salons Publics
        </h3>
        
        <div class="server-list" id="server-list-container">
            <p style="color:#888; font-style:italic;">Chargement des parties...</p>
        </div>

    </div>

<script>
    function loadServers() {
        fetch('api_server_list.php')
        .then(response => response.json())
        .then(games => {
            const container = document.getElementById('server-list-container');
            
            if (games.length === 0) {
                container.innerHTML = '<p style="color:#666; font-style:italic;">Aucune partie publique en attente...</p>';
                return;
            }

            let html = '';
            games.forEach(game => {
                // Logique d'affichage (Couleur, pourcentage...)
                const isFull = (game.player_count >= game.max_players);
                const percent = (game.player_count / game.max_players) * 100;
                const color = (percent > 80) ? '#ff4b5c' : '#00d4ff'; // Rouge si presque plein, sinon bleu
                
                // Construction du bouton Rejoindre ou Complet
                let actionButton = '';
                if (!isFull) {
                    actionButton = `
                        <a href="join_room.php?id=${game.id}" 
                           style="font-size:0.8rem; display:block; margin-top:5px; color:white; text-decoration:underline;">
                           Rejoindre ->
                        </a>`;
                } else {
                    actionButton = `<span style="font-size:0.8rem; display:block; margin-top:5px; color:#666;">Complet</span>`;
                }

                // Construction de la carte HTML
                html += `
                    <div class="server-card">
                        <div class="server-info">
                            <h4>Salon de ${game.host_name}</h4>
                            <span class="player-preview" title="${game.player_names}">
                                👥 ${game.player_names}
                            </span>
                        </div>
                        <div style="text-align:right;">
                            <div class="server-status" style="color: ${color}; border: 1px solid ${color};">
                                ${game.player_count} / ${game.max_players}
                            </div>
                            ${actionButton}
                        </div>
                    </div>
                `;
            });
            
            // On met à jour le contenu seulement si ça a changé pour éviter les micro-sauts
            // Mais ici on remplace tout pour faire simple, c'est très rapide.
            container.innerHTML = html;
        })
        .catch(err => console.error("Erreur chargement serveurs", err));
    }

    // Charger immédiatement
    loadServers();
    // Puis rafraîchir toutes les 2 secondes
    setInterval(loadServers, 2000);
</script>
</body>
</html>
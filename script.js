// --- DONNÉES DU JEU ---
// Tu peux ajouter tes propres mots ici !
const wordDatabase = [
    "Pizza", "Astronaute", "Plage", "Vampire", "Internet", 
    "Chocolat", "Piano", "Tour Eiffel", "Football", "Pyramide",
    "Café", "Licorne", "Cinéma", "Ninja", "Dinosaure"
];

let players = [];
let impostorIndex = -1;
let secretWord = "";
let currentPlayerIndex = 0;

// --- FONCTIONS DE CONFIGURATION ---

function addPlayer() {
    const input = document.getElementById('playerNameInput');
    const name = input.value.trim();

    if (name) {
        players.push(name);
        renderPlayerList();
        input.value = "";
        input.focus();
    }
}

// Permet d'ajouter avec la touche Entrée
function handleEnter(e) {
    if (e.key === "Enter") addPlayer();
}

function renderPlayerList() {
    const list = document.getElementById('playerList');
    const startBtn = document.getElementById('startBtn');
    list.innerHTML = "";

    players.forEach((player, index) => {
        list.innerHTML += `<li>${player} <span onclick="removePlayer(${index})" style="cursor:pointer; color:red;">✖</span></li>`;
    });

    // On affiche le bouton démarrer s'il y a au moins 3 joueurs
    if (players.length >= 3) {
        startBtn.classList.remove('hidden');
    } else {
        startBtn.classList.add('hidden');
    }
}

function removePlayer(index) {
    players.splice(index, 1);
    renderPlayerList();
}

// --- LOGIQUE DU JEU ---

function startGame() {
    // 1. Choisir un mot au hasard
    const randomIndex = Math.floor(Math.random() * wordDatabase.length);
    secretWord = wordDatabase[randomIndex];

    // 2. Choisir un imposteur au hasard
    impostorIndex = Math.floor(Math.random() * players.length);

    // 3. Initialiser l'affichage
    currentPlayerIndex = 0;
    switchScreen('distribution-screen');
    updateDistributionScreen();
}

function updateDistributionScreen() {
    // Affiche le nom du joueur actuel
    document.getElementById('current-player-name').innerText = players[currentPlayerIndex];
    
    // Réinitialise l'état visuel (cache le mot)
    document.getElementById('card-area').classList.remove('hidden');
    document.getElementById('secret-area').classList.add('hidden');
}

function revealCard() {
    document.getElementById('card-area').classList.add('hidden');
    document.getElementById('secret-area').classList.remove('hidden');

    const wordDisplay = document.getElementById('secret-word');
    
    if (currentPlayerIndex === impostorIndex) {
        wordDisplay.innerHTML = "Tu es l'IMPOSTEUR 🤫";
        wordDisplay.classList.add('impostor-text');
    } else {
        wordDisplay.innerHTML = secretWord;
        wordDisplay.classList.remove('impostor-text');
    }
}

function nextTurn() {
    currentPlayerIndex++;

    if (currentPlayerIndex < players.length) {
        // Il reste des joueurs
        updateDistributionScreen();
    } else {
        // Tout le monde a vu son rôle
        switchScreen('game-screen');
    }
}

function showResult() {
    document.getElementById('impostor-name').innerText = players[impostorIndex];
    document.getElementById('reveal-word').innerText = secretWord;
    switchScreen('result-screen');
}

function resetGame() {
    // On garde les joueurs, on reset juste la logique de partie
    switchScreen('setup-screen');
}

// --- UTILITAIRE ---
function switchScreen(screenId) {
    // Cache tous les écrans
    document.getElementById('setup-screen').classList.add('hidden');
    document.getElementById('distribution-screen').classList.add('hidden');
    document.getElementById('game-screen').classList.add('hidden');
    document.getElementById('result-screen').classList.add('hidden');

    // Affiche celui demandé
    document.getElementById(screenId).classList.remove('hidden');
}
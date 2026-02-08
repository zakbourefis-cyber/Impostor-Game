<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Pour la connexion (unique)
    $pseudo = trim($_POST['pseudo']);     // Pour l'affichage
    $password = $_POST['password'];

    // Vérifier si le NOM D'UTILISATEUR (Login) existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        $error = "Ce nom d'utilisateur est déjà pris (utilisé pour la connexion).";
    } else {
        // Génération du Tag (ex: 4812)
        $discriminator = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Hachage
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, pseudo, discriminator, password) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $pseudo, $discriminator, $hash])) {
            // Connexion auto
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['pseudo'] = $pseudo;
            $_SESSION['tag'] = $discriminator;
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Erreur lors de l'inscription.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Imposteur</title>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a href="index.php"><img src="impostor_logo.png" class="logo-img"></a>
</header>
    <div class="container">
        <h1>Créer un compte</h1>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <label style="text-align:left; display:block; color:#aaa; font-size:0.8rem; margin-left:5px;">Identifiant de Connexion</label>
            <input type="text" name="username" placeholder="ex: mon_mail@mail.com" required>
            
            <label style="text-align:left; display:block; color:#aaa; font-size:0.8rem; margin-left:5px;">Pseudo en Jeu</label>
            <input type="text" name="pseudo" placeholder="ex: Arracheur2Banoune" required>
            
            <label style="text-align:left; display:block; color:#aaa; font-size:0.8rem; margin-left:5px;">Mot de passe</label>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>
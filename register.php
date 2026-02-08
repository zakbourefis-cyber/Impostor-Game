<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Vérifier si le pseudo existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        $error = "Ce pseudo est déjà pris !";
    } else {
        // Hachage du mot de passe (Sécurité)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $hash])) {
            $_SESSION['username'] = $username;
            header("Location: index.php"); // Redirection vers l'accueil
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Créer un compte</h1>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Pseudo" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>
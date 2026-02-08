<?php
// db.php
$host = 'mysql-zakzik74.alwaysdata.net'; // L'hôte MySQL (voir dashboard Alwaysdata)
$db   = 'zakzik74_impostor';            // Le nom exact de ta base de données
$user = 'zakzik74';                      // Ton identifiant utilisateur MySQL
$pass = 'Agbdlcid74300?';              // Ton mot de passe MySQL (souvent le même que le compte)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
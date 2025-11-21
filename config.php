
<?php
function connecter_base_de_donnees()
{
    try {
        $mysqlClient = new PDO(
            'mysql:host=localhost;dbname=airbnb;charset=utf8',
            'root',
            ""
        );
        $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $mysqlClient;
    } catch (PDOException $e) {
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
}
 
 
$status_message = '';
$db = connecter_base_de_donnees();
?>
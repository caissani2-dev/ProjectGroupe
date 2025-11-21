<?php
 
require 'config.php';
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    if (
        !empty($_POST["name"]) &&
        !empty($_POST["price"]) &&
        !empty($_POST["host_name"]) &&
        !empty($_POST["picture_url"]) &&
        !empty($_POST["neighbourhood_group_cleansed"])
    ) {
        if (!is_numeric($_POST["price"])) {
            $status_message = '<p style="color: red; text-align: center;">Erreur : Le prix doit être un nombre.</p>';
        } else {
            try {
                $name = htmlspecialchars($_POST["name"]);
                $price = floatval($_POST["price"]);
                $host_name = htmlspecialchars($_POST["host_name"]);
                $picture_url = htmlspecialchars($_POST["picture_url"]);
 
                $neighbourhood_group_cleansed = htmlspecialchars($_POST["neighbourhood_group_cleansed"]);
               
                // Requête d'insertion avec la colonne 'neighbourhood_group_cleansed'
                $sthInsert = $db->prepare("
                    INSERT INTO listings (name, price, host_name, picture_url, neighbourhood_group_cleansed)
                    VALUES (:name, :price, :host_name, :picture_url, :neighbourhood_group_cleansed)
                ");
 
                $sthInsert->execute([
                    'name' => $name,
                    'price' => $price,
                    'host_name' => $host_name,
                    'picture_url' => $picture_url,
                    'neighbourhood_group_cleansed' => $neighbourhood_group_cleansed
                ]);
 
                $status_message = '<p style="color: green; text-align: center;">Annonce ajoutée avec succès !</p>';
 
            } catch (PDOException $e) {
                $status_message = '<p style="color: red; text-align: center;">Erreur lors de l\'insertion : ' . $e->getMessage() . '</p>';
            }
        }
    } else {
        $status_message = '<p style="color: orange; text-align: center;">Attention : Tous les champs du formulaire d\'ajout sont requis.</p>';
    }
}
 
 
 
$limit = 10;
 
 
$allowed_sorts = ['id', 'name', 'price', 'host_name', 'neighbourhood_group_cleansed'];
$default_sort = 'id';
 
 
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts) ? $_GET['sort'] : $default_sort;
 
 
$order = ($sort === 'price' || $sort === 'name') ? 'ASC' : 'DESC';
 
 
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
 
 
$countQuery = $db->prepare("SELECT COUNT(*) FROM listings");
$countQuery->execute();
$totalResults = $countQuery->fetchColumn();
$totalPages = ceil($totalResults / $limit);
 
 
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $limit;
 
 
 
 
$sql = "SELECT * FROM listings ORDER BY {$sort} {$order} LIMIT :limit OFFSET :offset";
 
$query = $db->prepare($sql);
$query->bindValue(':limit', $limit, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$data = $query->fetchAll(PDO::FETCH_ASSOC);
 
?>
 
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Listings Airbnb</title>
    <link rel="stylesheet" href="style.css">
    <style>
     
        * { margin: 0; padding: 0; box-sizing: border-box; }
       
        body {
            font-family: 'Circular', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }
       
        h1 {
            text-align: center;
            color: white;
            padding: 40px 0 20px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
       
       
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 5%;
        }
       
 
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
       
        .filter-form, .add-form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
       
        .filter-form:hover, .add-form:hover {
            transform: translateY(-5px);
        }
       
        .filter-form { width: 100%; max-width: 350px; }
       
        .add-form h2 {
            margin-top: 0;
            font-size: 1.8em;
            color: #484848;
            margin-bottom: 20px;
            border-bottom: 3px solid #FF5A5F;
            padding-bottom: 10px;
        }
       
        .add-form input, .add-form button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
            transition: all 0.3s ease;
        }
       
        .add-form input:focus {
            outline: none;
            border-color: #FF5A5F;
            box-shadow: 0 0 0 3px rgba(255,90,95,0.1);
        }
       
        .add-form button {
            background: linear-gradient(135deg, #FF5A5F 0%, #FF385C 100%);
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            font-size: 1.1em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
       
        .add-form button:hover {
            background: linear-gradient(135deg, #e04a50 0%, #e02849 100%);
            transform: scale(1.02);
        }
       
        .filter-form label {
            font-weight: 600;
            color: #484848;
            display: block;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
       
        .filter-form select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
        }
       
        .filter-form select:focus {
            outline: none;
            border-color: #FF5A5F;
        }
       
   
        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            padding: 24px 0;
        }
       
       
        .card {
            text-decoration: none;
            color: inherit;
            display: block;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
       
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
       
        .card-image {
            width: 100%;
            height: 220px;
            overflow: hidden;
            position: relative;
        }
       
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
       
        .card:hover .card-image img {
            transform: scale(1.1);
        }
       
        .card-info {
            padding: 20px;
        }
       
        .id-tag {
            color: #717171;
            font-size: 0.85em;
            margin-bottom: 8px;
            font-weight: 500;
        }
       
        .card-info h3 {
            font-size: 1.2em;
            color: #222;
            margin-bottom: 5px;
            line-height: 1.3;
        }
       
        .neighbourhood_group_cleansed-tag {
            color: #FF5A5F;
            font-size: 0.9em;
            font-weight: 600;
            margin-top: 5px;
        }
       
        .card-price {
            padding: 15px 20px;
            background: #f7f7f7;
            border-top: 1px solid #ebebeb;
            font-weight: 600;
            color: #222;
        }
       
        .card-price span {
            color: #FF5A5F;
            font-size: 1.3em;
        }
       
     
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 30px 0;
            flex-wrap: wrap;
        }
       
        .pagination a, .pagination span {
            padding: 10px 16px;
            text-decoration: none;
            color: white;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
       
        .pagination a:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
       
        .pagination .current-page {
            background: #FF5A5F;
            border-color: white;
            transform: scale(1.15);
        }
       
     
        .status-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            animation: slideDown 0.5s ease;
        }
       
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
       
       
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            color: #717171;
            font-size: 1.2em;
        }
       
       
        @media (max-width: 768px) {
            h1 { font-size: 1.8em; padding: 30px 0 15px; }
            .listings-grid { grid-template-columns: 1fr; gap: 20px; }
            .controls { flex-direction: column; }
            .filter-form { width: 100%; max-width: none; }
        }
    </style>
</head>
<body>
 
    <h1> Nos Dernières Annonces</h1>
 
    <div class="container">
        <?= $status_message ?>
       
        <div class="controls">
            <form method="GET" class="filter-form">
                <label for="sort-select">Trier par :</label>
                <select name="sort" id="sort-select" onchange="this.form.submit()">
                    <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>ID (Par défaut)</option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nom du Listing</option>
                    <option value="neighbourhood_group_cleansed" <?= $sort === 'neighbourhood_group_cleansed' ? 'selected' : '' ?>>Ville</option>
                    <option value="price" <?= $sort === 'price' ? 'selected' : '' ?>>Prix (Du moins cher au plus cher)</option>
                    <option value="host_name" <?= $sort === 'host_name' ? 'selected' : '' ?>>Propriétaire</option>
                </select>
                <?php if ($page > 1) { ?>
                    <input type="hidden" name="page" value="<?= $page ?>">
                <?php } ?>
            </form>
        </div>
       
        <div class="add-form">
            <h2>Ajouter une nouvelle annonce</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Nom du Listing" required>
                <input type="text" name="neighbourhood_group_cleansed" placeholder="Ville" required>
                <input type="text" name="price" placeholder="Prix par nuit (Ex: 150.50)" required>
                <input type="text" name="host_name" placeholder="Nom du Propriétaire" required>
                <input type="url" name="picture_url" placeholder="URL de l'image (Ex: https://...)" required>
                <button type="submit">Ajouter l'annonce</button>
            </form>
        </div>
 
        <div class="listings-grid">
            <?php
            if (!empty($data)) {
                foreach ($data as $listing) { ?>
                    <a href="#" class="card">
                        <div class="card-image">
                            <img src="<?= htmlspecialchars($listing['picture_url'] ?? 'placeholder.jpg') ?>"
                                alt="Image de la propriété : <?= htmlspecialchars($listing['name'] ?? 'Inconnu') ?>">
                        </div>
                       
                        <div class="card-info">
                            <p class="id-tag">ID: <?= htmlspecialchars($listing['id'] ?? 'N/A') ?> - Propriétaire: <?= htmlspecialchars($listing['host_name'] ?? 'N/A') ?></p>
                            <h3><?= htmlspecialchars($listing['name'] ?? 'Titre non spécifié') ?></h3>
                            <p class="neighbourhood_group_cleansed-tag">📍 <?= htmlspecialchars($listing['city'] ?? 'Ville non spécifiée') ?></p>
                        </div>
                       
                        <div class="card-price">
                            <span><?= htmlspecialchars($listing['price'] ?? '0.00') ?> €</span> / nuit
                        </div>
                    </a>
                <?php }
            } else { ?>
                <div class="no-results" style="grid-column: 1 / -1;">
                    Aucun logement trouvé sur cette page.
                </div>
            <?php } ?>
        </div>
 
        <?php if ($totalPages > 1) {
           
            ?>
            <div class="pagination">
               
                <?php if ($page > 1) { ?>
                    <a href="?page=<?= $page - 1 ?>&sort=<?= $sort ?>">Précédent</a>
                <?php } ?>
 
                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                    <?php if ($i == $page) { ?>
                        <span class="current-page"><?= $i ?></span>
                    <?php } else { ?>
                        <a href="?page=<?= $i ?>&sort=<?= $sort ?>"><?= $i ?></a>
                    <?php } ?>
                <?php } ?>
 
                <?php if ($page < $totalPages) { ?>
                    <a href="?page=<?= $page + 1 ?>&sort=<?= $sort ?>">Suivant</a>
                <?php } ?>
 
            </div>
        <?php } ?>
 
    </div>
</body>
</html>
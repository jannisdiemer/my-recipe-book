<?php
    require_once "mysql.php";

    $validCode = "YOUR-CODE";
    $givenCode = $_GET['code'] ?? '';
    if (!hash_equals($validCode, (string)$givenCode)) {
        http_response_code(403);
        exit("Kein Zugriff.");
    }
    $id = $_GET['id'] ?? '';
    $type = ($_GET['type'] ?? 'cooking') === 'backing' ? 'backing' : 'cooking';
    if (!ctype_digit((string)$id) || (int)$id < 1) exit("Ungültige ID.");
    $table = $type === 'backing' ? 'backing' : 'recepies';

    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recipe) { http_response_code(404); exit("Rezept nicht gefunden."); }

    $imagePath = !empty($recipe['image']) ? "/images/" . $recipe['image'] : null;
    $backUrl = ($type === 'backing' ? '/backing' : '/cooking') . '?code=' . urlencode($validCode);
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($recipe['Name'], ENT_QUOTES, 'UTF-8') ?></title>
        <link rel="icon" type="image/png" href="/images/logo.png">
        <link rel="stylesheet" type="text/css" href="/style.css">
    </head>
    <body>
        <main class="container">
            <?php if ($imagePath): ?><img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" class="recipe-img" alt="<?= htmlspecialchars($recipe['Name'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
            <h1><?= htmlspecialchars($recipe['Name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="meta">
                <?php if (!empty($recipe['duration'])): ?><span class="pill">⏱ Gesamt: <?= (int)$recipe['duration'] ?> Min.</span><?php endif; ?>
                <?php if (!empty($recipe['prep_time'])): ?><span class="pill">🔪 Aktiv: <?= (int)$recipe['prep_time'] ?> Min.</span><?php endif; ?>
                <?php if (!empty($recipe['difficulty'])): ?><span class="pill"><?= htmlspecialchars($recipe['difficulty'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                <?php if (!empty($recipe['servings'])): ?><span class="pill"><?= (int)$recipe['servings'] ?> Portionen</span><?php endif; ?>
                <?php foreach (['vegetarian'=>'Vegetarisch','vegan'=>'Vegan','high_protein'=>'Proteinreich','gluten_free'=>'Glutenfrei','lactose_free'=>'Laktosefrei'] as $field=>$label): ?>
                <?php if (!empty($recipe[$field])): ?><span class="pill"><?= $label ?></span><?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($recipe['ingredients'])): ?><div class="section-title">Zutaten</div><div class="text-block"><?= nl2br(htmlspecialchars($recipe['ingredients'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
            <?php if (!empty($recipe['description'])): ?><div class="section-title">Zubereitung</div><div class="text-block"><?= nl2br(htmlspecialchars($recipe['description'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="back">← Zurück zur Übersicht</a>
        </main>
    </body>
</html>
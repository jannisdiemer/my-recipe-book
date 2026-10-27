<?php
    require_once "mysql.php";

    $validCode = "YOUR-CODE";
    $givenCode = $_GET['code'] ?? '';
    if (!hash_equals($validCode, (string)$givenCode)) {
        http_response_code(403);
        exit("Kein Zugriff.");
    }

    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $segments = explode('/', $path);
    $currentPage = end($segments);

    $table = "recepies";
    $pageTitle = "Kochrezepte";
    $currentType = "cooking";
    if ($currentPage === "backing") {
        $table = "backing";
        $pageTitle = "Backrezepte";
        $currentType = "backing";
    } elseif ($currentPage === "cooking") {
        $table = "recepies";
    }

    $stmt = $pdo->query("SELECT id, Name, image, duration, prep_time, ingredients, description,
                                difficulty, servings, vegetarian, vegan, high_protein, gluten_free, lactose_free
                        FROM $table ORDER BY id DESC");
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cookingUrl = "/cooking?code=" . urlencode($validCode);
    $backingUrl = "/backing?code=" . urlencode($validCode);
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="/images/logo.png">
        <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
        <link rel="stylesheet" type="text/css" href="/style.css">
    </head>

    <body>
        <div class="topbar">
            <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="toggle">
                <a href="<?= htmlspecialchars($cookingUrl, ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentType === 'cooking' ? 'active' : '' ?>">Kochen</a>
                <a href="<?= htmlspecialchars($backingUrl, ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentType === 'backing' ? 'active' : '' ?>">Backen</a>
            </div>
        </div>

        <section class="filters" aria-label="Rezeptfilter">
            <div class="filter-grid">
                <label>Suche nach Rezept oder Zutat
                    <input type="search" id="search" placeholder="z. B. Paprika, Nudeln, Kuchen…">
                </label>
                <label>Maximale Gesamtzeit
                    <select id="timeFilter">
                        <option value="">Beliebig</option><option value="15">Bis 15 Minuten</option>
                        <option value="30">Bis 30 Minuten</option><option value="45">Bis 45 Minuten</option>
                        <option value="60">Bis 60 Minuten</option><option value="61">Mehr als 60 Minuten</option>
                    </select>
                </label>
                <label>Schwierigkeit
                    <select id="difficultyFilter">
                        <option value="">Alle Stufen</option><option value="Einfach">Einfach</option>
                        <option value="Mittel">Mittel</option><option value="Schwer">Schwer</option>
                    </select>
                </label>
                <label>Sortieren nach
                    <select id="sortFilter">
                        <option value="newest">Neueste zuerst</option><option value="fastest">Schnellste zuerst</option>
                        <option value="name">Name A–Z</option>
                    </select>
                </label>
            </div>
            <div class="checks">
                <label><input type="checkbox" class="dietFilter" value="vegetarian"> Vegetarisch</label>
                <label><input type="checkbox" class="dietFilter" value="vegan"> Vegan</label>
                <label><input type="checkbox" class="dietFilter" value="high_protein"> Proteinreich</label>
                <label><input type="checkbox" class="dietFilter" value="gluten_free"> Glutenfrei</label>
                <label><input type="checkbox" class="dietFilter" value="lactose_free"> Laktosefrei</label>
                <label><input type="checkbox" id="favoritesOnly"> ⭐ Nur Favoriten</label>
            </div>
            <div class="filter-actions">
                <span class="result-count" id="resultCount" aria-live="polite"></span>
                <button type="button" class="secondary" id="resetFilters">Filter zurücksetzen</button>
            </div>
        </section>

        <div class="recipe-list" id="recipeList">
        <?php if (!$recipes): ?>
            <p class="empty">Noch keine Rezepte vorhanden.</p>
        <?php else: ?>
            <?php foreach ($recipes as $r): ?>
                <article class="recipe-card"
                data-id="<?= (int)$r['id'] ?>"
                data-name="<?= htmlspecialchars(mb_strtolower((string)$r['Name']), ENT_QUOTES, 'UTF-8') ?>"
                data-ingredients="<?= htmlspecialchars(mb_strtolower((string)$r['ingredients']), ENT_QUOTES, 'UTF-8') ?>"
                data-description="<?= htmlspecialchars(mb_strtolower((string)$r['description']), ENT_QUOTES, 'UTF-8') ?>"
                data-duration="<?= (int)($r['duration'] ?? 0) ?>"
                data-difficulty="<?= htmlspecialchars((string)($r['difficulty'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                data-vegetarian="<?= (int)($r['vegetarian'] ?? 0) ?>"
                data-vegan="<?= (int)($r['vegan'] ?? 0) ?>"
                data-high_protein="<?= (int)($r['high_protein'] ?? 0) ?>"
                data-gluten_free="<?= (int)($r['gluten_free'] ?? 0) ?>"
                data-lactose_free="<?= (int)($r['lactose_free'] ?? 0) ?>"
                data-href="view.php?type=<?= urlencode($currentType) ?>&id=<?= (int)$r['id'] ?>&code=<?= urlencode($validCode) ?>">
                    <?php if (!empty($r['image'])): ?>
                        <img loading="lazy" src="/images/<?= htmlspecialchars($r['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($r['Name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    <div class="recipe-name-row">
                        <div class="recipe-name"><?= htmlspecialchars($r['Name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <button type="button" class="favorite" aria-label="Favorit markieren" title="Favorit" aria-pressed="false">☆</button>
                    </div>
                    <div class="recipe-meta">
                        <?php if (!empty($r['duration'])): ?><span class="pill">⏱ <?= (int)$r['duration'] ?> Min.</span><?php endif; ?>
                        <?php if (!empty($r['difficulty'])): ?><span class="pill"><?= htmlspecialchars($r['difficulty'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                        <?php if (!empty($r['servings'])): ?><span class="pill"><?= (int)$r['servings'] ?> Portionen</span><?php endif; ?>
                        <?php if (!empty($r['vegetarian'])): ?><span class="pill">Vegetarisch</span><?php endif; ?>
                        <?php if (!empty($r['vegan'])): ?><span class="pill">Vegan</span><?php endif; ?>
                        <?php if (!empty($r['high_protein'])): ?><span class="pill">Proteinreich</span><?php endif; ?>
                    </div>
                    <?php if (!empty($r['ingredients'])): ?><div class="recipe-ingredients"><strong>Zutaten:</strong> <?= htmlspecialchars($r['ingredients'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <?php if (!empty($r['description'])): ?><div class="recipe-description"><?= htmlspecialchars($r['description'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <div class="card-bottom">Rezept ansehen →</div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
        <a class="new-link" href="create_recipe.php?type=<?= urlencode($currentType) ?>&code=<?= urlencode($validCode) ?>" aria-label="Neues Rezept hinzufügen"><span aria-hidden="true">＋</span> Rezept hinzufügen</a>

        <script src="/script.js"></script>
    </body>
</html>

<?php
require_once "mysql.php";

$validCode = "9867213987";
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
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<style>
:root{font-family:system-ui,-apple-system,Segoe UI,Arial,sans-serif;color:#20251f;background:#f5f6f2}
*{box-sizing:border-box}body{margin:0;padding:24px;max-width:1500px;margin-inline:auto}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:22px}
h1{margin:0;font-size:clamp(1.7rem,3vw,2.4rem)}
.toggle{display:inline-flex;background:#e5e7e2;border-radius:999px;padding:4px;gap:4px}
.toggle a{padding:10px 18px;border-radius:999px;text-decoration:none;color:#30352f;font-weight:650}
.toggle a.active{background:#fff;box-shadow:0 2px 8px #00000012}
.filters{background:#fff;border:1px solid #e4e7df;border-radius:16px;padding:18px;margin-bottom:22px;box-shadow:0 4px 18px #17200d08}
.filter-grid{display:grid;grid-template-columns:2fr repeat(3,minmax(145px,1fr));gap:12px}
label{display:block;font-size:.88rem;font-weight:650;color:#42483f}
input[type=search],select{width:100%;margin-top:6px;padding:11px 12px;border:1px solid #d5d9d0;border-radius:9px;background:#fff;color:inherit;font:inherit}
.checks{display:flex;flex-wrap:wrap;gap:9px 16px;margin-top:15px}
.checks label{font-weight:500;display:flex;align-items:center;gap:7px}
.filter-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:16px}
button,.new-link{border:0;border-radius:9px;padding:11px 16px;background:#27372b;color:white;font:inherit;font-weight:650;text-decoration:none;cursor:pointer}
button.secondary{background:#e9ece6;color:#283027}
.result-count{color:#5d655a;font-size:.93rem}
.recipe-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px}
.recipe-card{background:#fff;border:1px solid #e8eae4;border-radius:15px;padding:14px;box-shadow:0 4px 16px #17200d08;display:flex;flex-direction:column;min-width:0;cursor:pointer;transition:transform .15s,box-shadow .15s}
.recipe-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px #17200d12}
.recipe-card img{width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:12px;background:#edf0e9}
.recipe-name-row{display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
.recipe-name{font-size:1.15rem;font-weight:750;line-height:1.3}
.favorite{font-size:1.45rem;line-height:1;border:0;background:transparent;color:#b0b5aa;padding:0 3px;flex-shrink:0}
.favorite.is-favorite{color:#e4a11b}
.recipe-meta{display:flex;gap:7px;flex-wrap:wrap;margin-top:10px}
.pill{font-size:.78rem;padding:5px 8px;background:#eef1e9;border-radius:999px;color:#485344}
.recipe-description{margin-top:11px;color:#596055;font-size:.9rem;line-height:1.45;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.recipe-ingredients{margin-top:10px;font-size:.88rem;color:#454c42;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.card-bottom{margin-top:auto;padding-top:12px;color:#65705f;font-size:.82rem}
.empty{background:white;padding:28px;border-radius:14px;color:#555}
.new-link{display:inline-flex;align-items:center;gap:9px;position:fixed;right:24px;bottom:24px;z-index:1000;margin:0;padding:15px 20px;border-radius:999px;box-shadow:0 8px 24px #17200d30;background:#27372b;color:#fff;font-size:1rem;font-weight:750;transition:transform .15s,box-shadow .15s}
.new-link:hover{transform:translateY(-2px);box-shadow:0 12px 28px #17200d3b}
.new-link:focus-visible{outline:3px solid #9caf9a;outline-offset:3px}
@media(max-width:480px){.new-link{right:16px;bottom:16px;padding:14px 18px}}
@media(max-width:800px){body{padding:14px}.filter-grid{grid-template-columns:1fr 1fr}.filter-grid label:first-child{grid-column:1/-1}}
@media(max-width:480px){.filter-grid{grid-template-columns:1fr}.filter-grid label:first-child{grid-column:auto}.recipe-list{grid-template-columns:1fr}}
</style>
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

<script>
(() => {
    const cards = [...document.querySelectorAll('.recipe-card')];
    const search = document.getElementById('search');
    const time = document.getElementById('timeFilter');
    const difficulty = document.getElementById('difficultyFilter');
    const sort = document.getElementById('sortFilter');
    const favoritesOnly = document.getElementById('favoritesOnly');
    const count = document.getElementById('resultCount');
    const list = document.getElementById('recipeList');
    const storageKey = 'recipe-favorites-v1';
    let favorites = new Set();
    try { favorites = new Set(JSON.parse(localStorage.getItem(storageKey) || '[]').map(String)); } catch(e) {}

    cards.forEach(card => {
        const button = card.querySelector('.favorite');
        const updateButton = () => {
            const active = favorites.has(card.dataset.id);
            button.classList.toggle('is-favorite', active);
            button.textContent = active ? '★' : '☆';
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.setAttribute('aria-label', active ? 'Favorit entfernen' : 'Als Favorit markieren');
        };
        updateButton();
        button.addEventListener('click', event => {
            event.stopPropagation();
            if (favorites.has(card.dataset.id)) favorites.delete(card.dataset.id);
            else favorites.add(card.dataset.id);
            localStorage.setItem(storageKey, JSON.stringify([...favorites]));
            updateButton();
            applyFilters();
        });
        card.addEventListener('click', () => { window.location.href = card.dataset.href; });
        button.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') event.stopPropagation(); });
    });

    function applyFilters() {
        const query = search.value.trim().toLocaleLowerCase('de');
        const selectedTime = time.value;
        const selectedDifficulty = difficulty.value;
        const checkedDiets = [...document.querySelectorAll('.dietFilter:checked')].map(el => el.value);
        const visible = [];
        cards.forEach(card => {
            const textMatch = !query || [card.dataset.name, card.dataset.ingredients, card.dataset.description].some(v => v.includes(query));
            const duration = Number(card.dataset.duration || 0);
            const timeMatch = !selectedTime || (selectedTime === '61' ? duration > 60 : duration > 0 && duration <= Number(selectedTime));
            const difficultyMatch = !selectedDifficulty || card.dataset.difficulty === selectedDifficulty;
            const dietMatch = checkedDiets.every(key => card.dataset[key] === '1');
            const favoriteMatch = !favoritesOnly.checked || favorites.has(card.dataset.id);
            const show = textMatch && timeMatch && difficultyMatch && dietMatch && favoriteMatch;
            card.hidden = !show;
            if (show) visible.push(card);
        });
        visible.sort((a,b) => {
            if (sort.value === 'fastest') return Number(a.dataset.duration || 999999) - Number(b.dataset.duration || 999999);
            if (sort.value === 'name') return a.dataset.name.localeCompare(b.dataset.name, 'de');
            return Number(b.dataset.id) - Number(a.dataset.id);
        });
        visible.forEach(card => list.appendChild(card));
        count.textContent = `${visible.length} von ${cards.length} Rezepten`;
    }
    [search,time,difficulty,sort,favoritesOnly,...document.querySelectorAll('.dietFilter')].forEach(el => el.addEventListener('input', applyFilters));
    document.getElementById('resetFilters').addEventListener('click', () => {
        search.value = ''; time.value = ''; difficulty.value = ''; sort.value = 'newest';
        favoritesOnly.checked = false;
        document.querySelectorAll('.dietFilter').forEach(el => el.checked = false);
        applyFilters();
    });
    applyFilters();
})();
</script>
</body>
</html>

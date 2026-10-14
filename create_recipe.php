<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
require_once "mysql.php";

$validCode = "9867213987";
$givenCode = $_GET['code'] ?? '';
if (!hash_equals($validCode, (string)$givenCode)) {
    http_response_code(403);
    exit("Kein Zugriff.");
}

$type = ($_GET['type'] ?? 'cooking') === 'backing' ? 'backing' : 'cooking';
$table = $type === 'backing' ? 'backing' : 'recepies';
$pageTitle = $type === 'backing' ? 'Neues Backrezept erstellen' : 'Neues Kochrezept erstellen';

$errors = [];
$name = trim($_POST['name'] ?? '');
$duration = trim($_POST['duration'] ?? '');
$prepTime = trim($_POST['prep_time'] ?? '');
$ingredients = trim($_POST['ingredients'] ?? '');
$description = trim($_POST['description'] ?? '');
$difficulty = $_POST['difficulty'] ?? '';
$servings = trim($_POST['servings'] ?? '');
$flags = ['vegetarian','vegan','high_protein','gluten_free','lactose_free'];
$values = [];
foreach ($flags as $flag) $values[$flag] = isset($_POST[$flag]) ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') $errors[] = "Bitte einen Rezeptnamen eingeben.";
    foreach (['duration' => $duration, 'prep_time' => $prepTime, 'servings' => $servings] as $label => $value) {
        if ($value !== '' && (!ctype_digit($value) || (int)$value < 0)) $errors[] = "Zeitangaben und Portionen müssen positive ganze Zahlen sein.";
    }
    if ($difficulty !== '' && !in_array($difficulty, ['Einfach','Mittel','Schwer'], true)) $errors[] = "Ungültiger Schwierigkeitsgrad.";
    if ($values['vegan']) $values['vegetarian'] = 1;

    $imageName = null;
    $targetPath = null;
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Bitte ein Rezeptbild hochladen.";
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'Die Datei überschreitet die PHP-Uploadgrenze. Bitte Servereinstellungen prüfen.',
            UPLOAD_ERR_FORM_SIZE => 'Die Datei ist zu groß.',
            UPLOAD_ERR_PARTIAL => 'Der Upload wurde nur teilweise übertragen.',
            UPLOAD_ERR_NO_TMP_DIR => 'Auf dem Server fehlt das temporäre Upload-Verzeichnis.',
            UPLOAD_ERR_CANT_WRITE => 'Der Server konnte die Datei nicht speichern.',
            UPLOAD_ERR_EXTENSION => 'Eine PHP-Erweiterung hat den Upload gestoppt.'
        ];
        $errors[] = $uploadErrors[$_FILES['image']['error']] ?? 'Fehler beim Bild-Upload.';
    } else {
        $tmpName = $_FILES['image']['tmp_name'];
        $fileSize = (int)$_FILES['image']['size'];
        $maxBytes = 15 * 1024 * 1024;
        if ($fileSize > $maxBytes) $errors[] = "Das Bild ist zu groß. Maximal 15 MB erlaubt.";

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false) {
            $errors[] = "Die hochgeladene Datei ist kein gültiges Bild.";
        } else {
            $allowedMimeTypes = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $mimeType = $imageInfo['mime'] ?? '';
            if (!isset($allowedMimeTypes[$mimeType])) {
                $errors[] = "Nur JPG, PNG und WEBP sind erlaubt.";
            } elseif (($imageInfo[0] ?? 0) > 6000 || ($imageInfo[1] ?? 0) > 6000) {
                $errors[] = "Die Bildabmessungen dürfen maximal 6000 × 6000 Pixel betragen.";
            } else {
                $imageName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
                $uploadDir = __DIR__ . '/images/';
                $targetPath = $uploadDir . $imageName;
                if (!is_dir($uploadDir)) $errors[] = "Der Upload-Ordner /images/ existiert nicht.";
                elseif (!is_writable($uploadDir)) $errors[] = "Der Upload-Ordner /images/ ist nicht beschreibbar.";
            }
        }
    }

    if (!$errors) {
        $durationInt = $duration === '' ? null : (int)$duration;
        $prepTimeInt = $prepTime === '' ? null : (int)$prepTime;
        $servingsInt = $servings === '' ? null : (int)$servings;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $errors[] = "Das Bild konnte nicht gespeichert werden.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO $table
                    (Name, image, duration, prep_time, ingredients, description, difficulty, servings,
                     vegetarian, vegan, high_protein, gluten_free, lactose_free)
                    VALUES (:name, :image, :duration, :prep_time, :ingredients, :description, :difficulty, :servings,
                     :vegetarian, :vegan, :high_protein, :gluten_free, :lactose_free)");
                $stmt->execute([
                    ':name'=>$name, ':image'=>$imageName, ':duration'=>$durationInt, ':prep_time'=>$prepTimeInt,
                    ':ingredients'=>$ingredients, ':description'=>$description,
                    ':difficulty'=>$difficulty === '' ? null : $difficulty, ':servings'=>$servingsInt,
                    ':vegetarian'=>$values['vegetarian'], ':vegan'=>$values['vegan'],
                    ':high_protein'=>$values['high_protein'], ':gluten_free'=>$values['gluten_free'],
                    ':lactose_free'=>$values['lactose_free']
                ]);
                $redirectUrl = ($type === 'backing' ? '/backing' : '/cooking') . '?code=' . urlencode($validCode);
                header("Location: " . $redirectUrl);
                exit;
            } catch (PDOException $e) {
                if ($targetPath && file_exists($targetPath)) unlink($targetPath);
                error_log('Recipe insert failed: ' . $e->getMessage());
                $errors[] = "Das Rezept konnte wegen eines Datenbankfehlers nicht gespeichert werden.";
            }
        }
    }
}
$backUrl = ($type === 'backing' ? '/backing' : '/cooking') . '?code=' . urlencode($validCode);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<style>
*{box-sizing:border-box}body{font-family:system-ui,Arial,sans-serif;background:#f5f6f2;margin:0;padding:22px;color:#20251f}
.container{max-width:760px;margin:0 auto;background:#fff;padding:24px;border-radius:15px;box-shadow:0 4px 18px #17200d0c}
h1{margin-top:0}label{display:block;margin-bottom:16px;font-weight:650}label span{display:block;margin-bottom:6px}
input[type=text],input[type=number],input[type=file],textarea,select{width:100%;padding:11px 12px;border:1px solid #d5d9d0;border-radius:8px;font:inherit;background:white}
textarea{resize:vertical}small,.hint{display:block;color:#646b60;font-weight:400;margin-top:5px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.checks{display:flex;gap:12px 18px;flex-wrap:wrap;margin:6px 0 18px}
.checks label{display:flex;align-items:center;gap:7px;font-weight:500;margin:0}
button{padding:12px 17px;background:#27372b;color:white;border:0;border-radius:9px;font:inherit;font-weight:700;cursor:pointer}
.back{display:inline-block;margin-top:18px;color:#344c37}
.alert{padding:12px 15px;border-radius:8px;margin-bottom:18px;background:#fee2e2;color:#991b1b}
@media(max-width:560px){body{padding:10px}.container{padding:17px}.grid{grid-template-columns:1fr}}
</style>
</head>
<body><main class="container">
<h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
<?php if ($errors): ?><div class="alert"><strong>Bitte prüfe Folgendes:</strong><ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form action="create_recipe.php?type=<?= urlencode($type) ?>&code=<?= urlencode($validCode) ?>" method="post" enctype="multipart/form-data">
<label><span>Rezeptname *</span><input type="text" name="name" maxlength="255" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"></label>
<label><span>Bild *</span><input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required><small>JPG, PNG oder WebP · maximal 15 MB · höchstens 6000 × 6000 Pixel</small></label>
<div class="grid">
<label><span>Gesamtzubereitungszeit (Minuten)</span><input type="number" name="duration" min="0" max="10000" value="<?= htmlspecialchars($duration, ENT_QUOTES, 'UTF-8') ?>"></label>
<label><span>Aktive Vorbereitungszeit (Minuten)</span><input type="number" name="prep_time" min="0" max="10000" value="<?= htmlspecialchars($prepTime, ENT_QUOTES, 'UTF-8') ?>"></label>
<label><span>Schwierigkeit</span><select name="difficulty"><option value="">Nicht angegeben</option><?php foreach (['Einfach','Mittel','Schwer'] as $level): ?><option value="<?= $level ?>" <?= $difficulty === $level ? 'selected' : '' ?>><?= $level ?></option><?php endforeach; ?></select></label>
<label><span>Portionen</span><input type="number" name="servings" min="1" max="1000" value="<?= htmlspecialchars($servings, ENT_QUOTES, 'UTF-8') ?>"></label>
</div>
<label><span>Zutaten</span><textarea name="ingredients" rows="6" placeholder="z. B. 200 g Nudeln&#10;100 ml Sahne"><?= htmlspecialchars($ingredients, ENT_QUOTES, 'UTF-8') ?></textarea></label>
<label><span>Zubereitung / Beschreibung</span><textarea name="description" rows="7"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea></label>
<strong>Eigenschaften</strong><p class="hint">Nur auswählen, wenn das Merkmal für das Rezept zutrifft.</p>
<div class="checks">
<?php $labels = ['vegetarian'=>'Vegetarisch','vegan'=>'Vegan','high_protein'=>'Proteinreich','gluten_free'=>'Glutenfrei','lactose_free'=>'Laktosefrei']; ?>
<?php foreach ($labels as $key=>$label): ?><label><input type="checkbox" name="<?= $key ?>" value="1" <?= $values[$key] ? 'checked' : '' ?>> <?= $label ?></label><?php endforeach; ?>
</div>
<button type="submit">Rezept speichern</button>
</form>
<a class="back" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>">← Zurück zur Übersicht</a>
</main></body></html>
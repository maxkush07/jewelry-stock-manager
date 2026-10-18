<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_set_cookie_params(['httponly'=>true,'secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),'samesite'=>'Lax','path'=>'/']); session_start(); }
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') header('Strict-Transport-Security: max-age=15552000; includeSubDomains');
header('Cache-Control: no-store, private');
$root = __DIR__;
$installed = is_file($root . '/storage/installed.lock');
$error = '';
if (empty($_SESSION['install_csrf'])) $_SESSION['install_csrf'] = bin2hex(random_bytes(32));
if ($installed) { http_response_code(403); exit('Application déjà installée. Supprimez install.php du serveur.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['install_csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) { http_response_code(419); exit('Jeton de sécurité invalide. Rechargez la page.'); }
    $name = trim((string)($_POST['business_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $currency = (string)($_POST['currency'] ?? 'CAD');
    if ($name === '' || strlen($name)>120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12 || !in_array($currency,['CAD','USD','EUR'],true)) $error = 'Entrez le nom de la bijouterie, un courriel valide et un mot de passe d’au moins 12 caractères.';
    elseif (!extension_loaded('pdo_sqlite')) $error = 'L’extension PHP pdo_sqlite n’est pas activée sur cet hébergement.';
    elseif (!is_writable($root . '/storage') || !is_writable($root . '/uploads')) $error = 'Les dossiers storage/ et uploads/ doivent être accessibles en écriture par PHP.';
    else {
      try {
        $db = new PDO('sqlite:' . $root . '/storage/app.sqlite', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        $db->exec('PRAGMA foreign_keys=ON');
        $sql = [
          'CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, role TEXT NOT NULL DEFAULT \'admin\', is_active INTEGER NOT NULL DEFAULT 1, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)',
          'CREATE TABLE settings (key TEXT PRIMARY KEY, value TEXT NOT NULL)',
          'CREATE TABLE items (id INTEGER PRIMARY KEY AUTOINCREMENT, sku TEXT UNIQUE, barcode TEXT UNIQUE, name TEXT NOT NULL, category TEXT NOT NULL DEFAULT \'Montre\', brand TEXT, model TEXT, serial_number TEXT, description TEXT, photo TEXT, purchase_date TEXT NOT NULL, supplier TEXT, purchase_price REAL NOT NULL DEFAULT 0, currency TEXT NOT NULL DEFAULT \'CAD\', gold_grams REAL NOT NULL DEFAULT 0, gold_purity INTEGER NOT NULL DEFAULT 0, gold_price_per_gram REAL, gold_price_currency TEXT NOT NULL DEFAULT \'CAD\', gold_price_unit TEXT NOT NULL DEFAULT \'gram\', retail_price REAL NOT NULL DEFAULT 0, status TEXT NOT NULL DEFAULT \'stock\', sale_date TEXT, sale_price REAL, sale_notes TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)',
          'CREATE TABLE gold_prices (id INTEGER PRIMARY KEY AUTOINCREMENT, price_date TEXT NOT NULL, price_per_gram REAL NOT NULL, currency TEXT NOT NULL DEFAULT \'CAD\', source TEXT NOT NULL DEFAULT \'Saisie manuelle\', notes TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(price_date,currency))',
          'CREATE TABLE item_events (id INTEGER PRIMARY KEY AUTOINCREMENT, item_id INTEGER NOT NULL, event_type TEXT NOT NULL, event_date TEXT NOT NULL, note TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(item_id) REFERENCES items(id) ON DELETE CASCADE)',
          'CREATE INDEX idx_items_status ON items(status)', 'CREATE INDEX idx_items_purchase_date ON items(purchase_date)', 'CREATE INDEX idx_gold_date ON gold_prices(price_date)'
        ];
        $db->beginTransaction();
        foreach ($sql as $q) $db->exec($q);
        $stmt=$db->prepare('INSERT INTO users(email,password_hash) VALUES(?,?)'); $stmt->execute([$email,password_hash($password,PASSWORD_DEFAULT)]);
        $stmt=$db->prepare('INSERT INTO settings(key,value) VALUES(?,?)');
        foreach(['business_name'=>$name,'currency'=>$currency,'gold_currency'=>$currency,'gold_unit'=>'gram','installed_at'=>date('c')] as $k=>$v) $stmt->execute([$k,$v]);
        if (file_put_contents($root . '/storage/installed.lock', 'installed ' . date('c'), LOCK_EX) === false) { throw new RuntimeException('Impossible de créer storage/installed.lock. Vérifiez les permissions.'); }
        $db->commit();
        @chmod($root . '/storage/app.sqlite', 0600);
        header('Location: index.php?installed=1'); exit;
      } catch(Throwable $e) { if (isset($db) && $db instanceof PDO && $db->inTransaction()) $db->rollBack(); if (is_file($root . '/storage/installed.lock') && isset($db) && $db instanceof PDO && !$db->inTransaction()) @unlink($root . '/storage/installed.lock'); $error = 'Installation échouée. Vérifiez les permissions et la configuration PHP/SQLite, puis réessayez.'; }
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Installation — Bijouterie Manager</title><link rel="stylesheet" href="assets/style.css"></head><body class="install-page"><main class="install-card"><div class="brand-mark">✦</div><p class="eyebrow">INSTALLATION AUTOMATIQUE</p><h1>Bijouterie <span>Manager</span></h1><p class="muted">Configurez votre espace d’inventaire sécurisé.</p><?php if($error): ?><div class="alert danger"><?= $error ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['install_csrf'], ENT_QUOTES, 'UTF-8') ?>"><label>Nom de la bijouterie<input name="business_name" required value="<?= htmlspecialchars($_POST['business_name']??'',ENT_QUOTES) ?>" placeholder="Ma bijouterie"></label><label>Courriel administrateur<input type="email" name="email" required value="<?= htmlspecialchars($_POST['email']??'',ENT_QUOTES) ?>" placeholder="vous@exemple.ca"></label><label>Mot de passe administrateur<input type="password" name="password" minlength="12" required autocomplete="new-password" placeholder="12 caractères minimum"></label><label>Devise principale<select name="currency"><option value="CAD">CAD — Dollar canadien</option><option value="USD">USD — Dollar américain</option><option value="EUR">EUR — Euro</option></select></label><button class="btn primary full" type="submit">Installer l’application</button></form><div class="install-requirements">Prérequis : PHP 8.1+, PDO SQLite, dossiers <code>storage/</code> et <code>uploads/</code> accessibles en écriture. HTTPS recommandé.</div></main></body></html>

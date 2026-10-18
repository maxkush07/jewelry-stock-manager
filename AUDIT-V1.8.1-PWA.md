# Rapport de livraison — Bijouterie Manager V1.8.1 Design & PWA

## Modifications livrées

- Refonte CSS de l’interface : contraste, espaces, typographie système, cartes, boutons, tableaux, formulaires, états de focus, animations réduites si demandées par l’OS.
- Navigation mobile avec barre inférieure, prise en compte des safe areas iOS/Android, tableaux défilants horizontalement et formulaires adaptés aux petits écrans.
- Manifeste PWA enrichi : identifiant, démarrage, scope, modes d’affichage, catégories, raccourcis, icônes PNG 192/512 et icône maskable.
- Installation exposée sur l’écran de connexion et dans l’application quand le navigateur prend en charge `beforeinstallprompt`; aide manuelle pour Safari iOS.
- Indicateur réseau en ligne/hors ligne.
- Service worker révisé : cache versionné, liste blanche de fichiers statiques, fallback hors ligne. Les routes PHP et données privées ne sont pas mises en cache.
- Retrait de la dépendance distante Google Fonts.

## Sécurité et limite fonctionnelle PWA

Le cache ne comprend pas les pages PHP, les requêtes métier, les exports, la base de données ni les photos. Cela évite de laisser des données privées persistantes dans Cache Storage. Les opérations de gestion exigent donc toujours une connexion au serveur. La PWA est installable, mais ne permet pas encore d’ajouter/modifier des articles hors ligne avec synchronisation ultérieure.

## Vérifications exécutées

Les contrôles de livraison doivent être interprétés comme des vérifications statiques, et non comme une certification en production.

- `php -l install.php` : réussi.
- `php -l index.php` : réussi.
- `node --check assets/app.js` : réussi.
- `node --check sw.js` : réussi.
- Parsing JSON de `manifest.json` : réussi; 4 icônes déclarées et 2 raccourcis.
- Présence et dimensions vérifiées : PNG 192×192, PNG 512×512 et PNG maskable 512×512.
- Vérification statique de la liste blanche du service worker et du traitement réseau uniquement des routes PHP : réussie.
- `python tests/test_v18_sqlite.py` : réussi (migration additive/idempotente, ligne et chemin photo existants, frais article, validation et intégrité SQLite).
- `unzip -t` sur l’archive finale : doit être exécuté après création de l’archive.

## Limites de validation

Un test navigateur réel (Chrome Android, Safari iOS, Lighthouse, installation depuis HTTPS, scénario hors ligne et serveur PHP/SQLite) dépend de l’hébergement et du navigateur de destination; il ne peut pas être garanti par les contrôles statiques locaux. Il faut déployer sous HTTPS, vérifier que `sw.js` et `manifest.json` sont servis avec les bons types MIME, puis tester installation et reconnexion.

## Mise à jour recommandée

1. Sauvegarder `storage/app.sqlite` et le dossier `uploads/`.
2. Téléverser les fichiers de l’archive sans écraser la base ni les photos existantes.
3. Garder `storage/` protégé par le `.htaccess` inclus.
4. Ouvrir l’application sous HTTPS, recharger une fois puis accepter l’installation.
5. Si une ancienne PWA reste installée, fermer l’application et recharger le site pour activer le nouveau service worker.

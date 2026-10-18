# Audit — Bijouterie Manager V1.9

Fonctionnalités ajoutées à la V1.8.2 : rotation du stock, historique des prix, alerte sur le cours de l'or, seuils de stock dormant par catégorie, valeur du métal par devise, photos multiples, QR code imprimable.

## Vérifications effectuées (PHP 8.3 CLI, serveur intégré, SQLite)
| Vérification | Résultat |
|---|---|
| `php -l` sur `index.php`, `install.php`, `includes/qrcode.php` | OK |
| Migration d'une vraie base V1.8.2 (4 articles, photo, vente, cours) vers V1.9 : tables créées, photos et prix repris, aucune ligne perdue, idempotence (2 chargements), `PRAGMA integrity_check`, copie `pre-v19-migration.sqlite` | OK |
| Installation neuve V1.9, pages vides sans erreur | OK |
| Scénarios fonctionnels HTTP (113 contrôles) : historique des prix, photos (ajout, plafond, principale, suppression, fichiers invalides ou trop gros, doublon de SKU, nettoyage des fichiers orphelins), alerte or (seuils, devises séparées), seuils dormants, rotation (comparée à un calcul Python indépendant), effet des baisses, QR, inventaire par QR, rôles vendeur/admin, cascade, XSS, CSRF, limite `post_max_size` | OK |
| QR codes : 20 contenus (versions 1 à 10) relus à l'identique par le décodeur ZXing, depuis la matrice et depuis le SVG réellement généré | OK |
| Aucun avertissement/notice PHP (`display_errors=1`, `E_ALL`) sur les pages testées | OK |

## Problèmes trouvés pendant les tests et corrigés
- Un envoi de photos dépassant `post_max_size` produisait « Jeton de sécurité invalide » : message dédié (HTTP 413) ajouté.
- Le prix initial reconstitué à la migration pouvait être daté après la vente de l'article : plafonné à la date de vente.
- URL du QR : le port non standard était perdu en repli, puis dupliqué après une première correction ; corrigé et couvert par un test.

## Limites connues (non vérifiées ou non modifiées)
- **Rendu visuel non testé dans un navigateur** (bandeau, galerie, tableaux, impression des étiquettes) : le HTML et le CSS sont validés par leur contenu, pas à l'écran. À contrôler sur mobile et à l'impression.
- La lecture réelle des QR par un téléphone et une imprimante n'a pas été testée (décodage logiciel uniquement).
- Test sur l'hébergement de production à faire : `pdo_sqlite`, droits d'écriture de `storage/` et `uploads/`, `post_max_size`/`upload_max_filesize` pour plusieurs photos.
- La synchronisation du cours spot (réseau sortant) n'a pas pu être exercée ici ; l'alerte ne repose que sur les cours enregistrés.
- Hérité de V1.8.2 : en session non connectée, un jeton CSRF vide est accepté sur le formulaire de connexion (sans effet sur les sessions connectées).
- Hérité : photos servies sans authentification ; compteur « articles dormants » du tableau de bord toutes devises alors que la valeur est filtrée par devise.

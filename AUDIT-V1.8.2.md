# Audit — Bijouterie Manager V1.8.2

Revue manuelle de tous les fichiers de l'archive V1.8.1 (index.php, install.php, app.js, sw.js, manifest.json, offline.html, style.css, .htaccess, tests).

## Corrigé
| Gravité | Problème | Correctif |
|---|---|---|
| Haute | Courriel de l'installation conservé tel quel alors que la connexion le met en minuscules : un administrateur créé avec une majuscule ne pouvait plus se connecter | Installation en minuscules + comparaison `lower(email)` |
| Haute | Un employé désactivé gardait l'accès jusqu'à l'expiration de sa session | Vérification de `is_active` à chaque requête |
| Haute | `PDOException` étendant `RuntimeException`, le message SQL brut était affiché à l'utilisateur | Message clair, détail dans `error_log` |
| Moyenne | `.htaccess` ne bloquait pas `AUDIT-*.md`, `tests/` | Règles ajoutées |
| Moyenne | Littéraux SQL entre guillemets doubles (échec sur SQLite compilé sans DQS) | Apostrophes |
| Moyenne | Remise en stock : les notes de vente restaient | `sale_notes=NULL` |
| Moyenne | Code-barres sans zone de silence droite | viewBox élargi |
| Basse | BACKUP-INFO.txt : `\n` littéraux et mention V1.7 | Corrigé |
| Basse | Import CSV et cours spot absents du journal d'audit | Journalisés |
| Basse | Catégorie non validée, nom non borné, index POST non protégés | Validations ajoutées |
| Basse | Bouton Supprimer visible pour les vendeurs (échec à l'usage) | Réservé aux admins |
| Basse | Erreur JS sur Échap hors inventaire; cache statique jamais rafraîchi | Corrigés |

## Limites connues (non modifiées)
- PHP n'était pas disponible dans l'environnement de revue : `php -l` n'a pas pu être exécuté sur les fichiers modifiés. Seuls `node --check` (JS) et la validation JSON ont été faits. Lancez `php -l index.php install.php` avant déploiement.
- Les photos sont servies directement par `uploads/` (noms aléatoires, mais sans authentification).
- Sur la page Cours de l'or, les formulaires d'ajout, d'import et de synchronisation sont visibles par les vendeurs mais refusés côté serveur (administrateur requis).
- Le compteur « articles dormants » du tableau de bord compte toutes les devises alors que la valeur est filtrée par devise.

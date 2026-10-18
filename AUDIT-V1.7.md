# Rapport d’audit et de tests — Bijouterie Manager V1.7

Date : 2026-10-16

## Résumé

V1.7 ajoute les codes-barres Code 39, le calculateur d’or, les sessions d’inventaire physique, les rapports mensuels par devise, les comptes employés et la sauvegarde complète ZIP optionnelle. Les changements de schéma sont additifs et une copie de sécurité SQLite est tentée avant la première migration V1.7 lorsque l’inventaire contient des articles et que les colonnes V1.7 manquent.

## Fonctionnalités livrées

- **Codes-barres** : champ de code-barres, index unique partiel, attribution automatique d’un code interne `BM-########` aux anciennes fiches sans code, export CSV incluant le code et impression d’étiquettes Code 39. La compatibilité avec un lecteur et une imprimante doit être confirmée par un essai physique.
- **Calculateur d’or** : poids, pureté, cours par gramme et marge paramétrables; affiche poids d’or fin, valeur théorique du métal et prix indicatif. Aucun cours ni taux de change fictif n’est injecté.
- **Inventaire avancé** : sessions de comptage, scan par code-barres/SKU, journal du comptage, état ouvert/fermé et liste des articles non vérifiés. Le modèle suit des fiches individuelles; il ne s’agit pas d’une gestion de quantité par SKU.
- **Rapports financiers** : ventes mensuelles, nombre de ventes, chiffre d’affaires et marge brute par devise sélectionnée. Pas d’addition interdevises.
- **Sauvegardes** : copie SQLite cohérente via `VACUUM INTO`; sauvegarde complète ZIP contenant une copie SQLite et `uploads/` si `ZipArchive` est installé; journal des métadonnées de sauvegarde complète. La sauvegarde téléchargée doit être conservée hors du serveur. La restauration reste manuelle.
- **Employés** : création de comptes, rôles `admin` et `seller`, activation/désactivation; protections d’administrateur sur la gestion des comptes, paramètres, cours de l’or et sauvegardes. Les comptes préexistants sont migrés en administrateurs pour éviter le verrouillage de l’accès.

## Résultats des tests effectués

| Contrôle | Résultat |
|---|---|
| `php -l index.php` | Réussi |
| `php -l install.php` | Réussi |
| `node --check assets/app.js` | Réussi |
| `node --check sw.js` | Réussi |
| Validation JSON du manifeste PWA | Réussie |
| Correspondance formulaires/actions POST | Réussie : chaque action de formulaire a un gestionnaire |
| Migration SQLite simulée depuis le schéma de l’installateur | Réussie |
| Conservation d’une fiche et du chemin photo existants | Réussie dans le test SQLite indépendant |
| Rôle et activation par défaut pour les comptes existants | Réussis dans le test SQLite indépendant |
| Unicité des codes-barres | Réussie dans le test SQLite indépendant |
| Session de comptage et re-scan idempotent | Réussis dans le test SQLite indépendant |
| Requête du rapport mensuel | Réussie dans le test SQLite indépendant |
| Intégrité de l’archive ZIP de livraison | Réussie avec `unzip -t` |

## Limites importantes

- L’environnement de construction ne dispose pas de l’extension PHP `pdo_sqlite`; l’application complète n’a donc pas été exécutée par HTTP contre PDO SQLite dans cet environnement. Les tests SQL ont été réalisés avec SQLite via Python, séparément du runtime PHP.
- L’extension PHP `ZipArchive` n’est pas détectée ici. La sauvegarde complète ZIP est conditionnelle à sa présence sur l’hébergement; la sauvegarde SQLite seule reste disponible.
- Aucun test réel de lecteur code-barres, d’imprimante d’étiquettes, de navigateur mobile ou de restauration de sauvegarde sur l’hébergement n’a été réalisé.
- La copie pré-migration `storage/pre-v17-migration.sqlite` ne contient que la base de données; les photos restent intactes car la migration ne modifie pas `uploads/`. Faites toujours une copie séparée de `uploads/` avant déploiement.
- Les comptes existants reçoivent le rôle administrateur par défaut. Après mise à jour, vérifier la liste des comptes et créer des vendeurs au besoin.
- Le cours automatique de l’or dépend d’une source HTTPS externe disponible et n’est pas garanti par les tests hors ligne.

## Déploiement prudent

1. Télécharger une copie de `storage/app.sqlite` et de `uploads/`.
2. Déployer les fichiers sans remplacer la base, le fichier `storage/installed.lock` ni les photos.
3. Vérifier les permissions de `storage/` et `uploads/`, HTTPS et `pdo_sqlite`.
4. Tester connexion admin, création vendeur, ajout/modification d’article, impression/scannage d’une étiquette, inventaire physique, rapports et téléchargement des deux formats de sauvegarde.
5. Conserver les sauvegardes hors du serveur et tester la restauration sur une copie avant toute restauration réelle.

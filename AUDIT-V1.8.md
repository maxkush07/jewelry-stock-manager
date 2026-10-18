# Rapport de livraison et de tests — Bijouterie Manager V1.8

**Date :** 2026-10-16  
**Base :** archive `Bijouterie-Manager-v1.7-Complet-final.zip` disponible dans l’espace de travail.  
**Type de livraison :** mise à jour installable PHP/SQLite; l’archive ne contient pas de base de production et ne doit pas remplacer `storage/app.sqlite`.

## Fonctionnalités V1.8

- **Sécurité renforcée :** délai d’inactivité de 30 minutes pour les sessions authentifiées; journalisation de l’expiration; en-tête `Permissions-Policy`; HSTS conditionnel seulement si HTTPS est déjà actif; politique de mot de passe de 12 caractères minimum pour l’installation et le changement depuis les paramètres. Les en-têtes existants contre le MIME sniffing, le framing et les caches privés sont conservés.
- **Rentabilité avancée :** la page Rapports affiche la marge brute, les frais d’articles enregistrés dans la même devise et une estimation après frais. Les montants de devises différentes ne sont pas convertis ni additionnés.
- **PWA mobile :** version de cache statique renouvelée, bouton d’installation quand le navigateur émet `beforeinstallprompt`, styles adaptés aux petits écrans, cache limité aux ressources statiques. Les navigations PHP et les réponses contenant les données privées ne sont pas mises en cache.
- **Assistant intelligent :** recommandations locales à partir du stock, des prix sous le coût et du stock dormant. Il s’agit d’un moteur de règles, sans appel à une IA externe; il ne modifie aucune donnée métier. Les évaluations sont stockées dans SQLite.
- **Migration SQLite additive :** création idempotente de `schema_migrations`, `assistant_feedback` et d’un index d’audit. Aucune instruction `DROP` n’a été ajoutée et aucun fichier photo n’est supprimé ou déplacé.

## Tests exécutés dans l’environnement de construction

| Contrôle | Résultat |
|---|---|
| `php -l` sur tous les fichiers PHP du projet | Réussi |
| `node --check assets/app.js` | Réussi |
| `node --check sw.js` | Réussi |
| Validation JSON de `manifest.json` | Réussie |
| Tests SQLite indépendants (`tests/test_v18_sqlite.py`) | Réussis : migration additive, répétabilité, préservation d’un article et de son chemin photo, conservation d’un frais, contrainte d’évaluation, `PRAGMA integrity_check` |
| Recherche de suppressions de tables/colonnes et de nettoyage de `uploads/` ajouté en V1.8 | Aucun trouvé |
| Intégrité de l’archive V1.7 source | Réussie avant modification |

## Limites et tests non exécutés

- **PDO SQLite n’est pas installé dans l’environnement de construction** (`php -m` expose `PDO`, mais pas `pdo_sqlite`); l’application n’a donc pas été démarrée par PHP contre une vraie base via PDO. Le test SQLite est exécuté par Python et ne remplace pas un test d’intégration sur l’hébergement.
- Aucun test HTTP authentifié, navigateur réel, installation PWA sur téléphone, test hors ligne sur appareil, test de charge ou restauration de sauvegarde sur un serveur réel n’a été exécuté.
- Les sessions expirent à la prochaine requête après 30 minutes d’inactivité; cela ne remplace pas une configuration serveur sécurisée.
- L’assistant est volontairement heuristique et local, pas un assistant génératif. La rentabilité ajustée n’est pas un état comptable : les frais sont agrégés selon la devise et peuvent inclure des articles encore en stock; taxes, frais généraux et conversions ne sont pas inclus.
- HSTS n’est utile que si HTTPS est configuré correctement. La PWA installable nécessite HTTPS (hors localhost) et le support du navigateur.

## Procédure prudente de mise à jour depuis V1.7

1. Téléchargez une sauvegarde SQLite depuis Sécurité et sauvegardez **séparément** le dossier `uploads/`, ou téléchargez une sauvegarde complète ZIP si `ZipArchive` est disponible.
2. Décompressez le ZIP V1.8 et téléversez les fichiers de l’application sans écraser `storage/app.sqlite`, `storage/installed.lock` ni les fichiers de `uploads/`.
3. Vérifiez les permissions de `storage/` et `uploads/`, activez HTTPS et confirmez que l’extension `pdo_sqlite` est installée sur l’hébergement.
4. Ouvrez l’application et connectez-vous : la migration additive s’applique au démarrage. Vérifiez l’inventaire, les photos, les ventes, les rapports, l’assistant et la sauvegarde.
5. Conservez les sauvegardes hors du serveur et testez toute restauration sur une copie avant de toucher à la production.

**Conclusion :** contrôles statiques et tests SQL indépendants réussis. La compatibilité de bout en bout avec l’hébergement de production reste à confirmer en raison de l’absence de `pdo_sqlite` dans l’environnement de construction.

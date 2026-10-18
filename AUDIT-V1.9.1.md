# Audit — V1.9.1 (baisse en lot, filtres, exports)

Vérifié avec PHP 8.3 CLI (serveur intégré, SQLite), base V1.8.2 migrée puis scénarios HTTP : 140 contrôles au total (113 V1.9 sans régression + 27 nouveaux).

- Aperçu sans effet de bord ; plancher au coût d'achat ; exclusion des articles non dormants, des articles récents et des autres devises ; seuils par catégorie respectés (ex. Montre à 1000 j).
- Application : prix mis à jour, ligne d'historique datée avec note, garde anti-modification concurrente (transaction), rejet des pourcentages invalides (0, 60, texte, vide) et des catégories inconnues.
- Droits : action et page réservées à l'administrateur (vendeur refusé, prix intacts, bouton masqué).
- Filtres catégorie/fournisseur (seuls, combinés, valeur invalide ignorée) ; liste de fournisseurs dans la fiche.
- Exports CSV (en-têtes, lignes catégorie/fournisseur, baisses en lot présentes).
- Aucun avertissement PHP (`E_ALL`) sur les pages testées ; intégrité SQLite OK.

Limites : rendu visuel non testé dans un navigateur ; test sur l'hébergement de production à faire ; les filtres catégorie/fournisseur ne sont pas conservés quand on change de pastille (Tout / En stock / Dormant / Vendu).

# Rapport d’audit — Bijouterie Manager V1.6.1

## Résultat

Relecture statique de tous les fichiers livrés et revue des parcours PHP/SQLite, formulaires, exports CSV, téléversements, authentification, historique de l’or, sauvegardes et service worker. Des défauts fonctionnels et de sécurité ont été corrigés dans cette version.

## Correctifs appliqués

1. **Paramètres à zéro** — la fonction de lecture des paramètres ne remplace plus la valeur valide `0` par la valeur par défaut. La marge cible peut donc être réglée à 0 %.
2. **Prix de détail manquant** — le tableau d’alertes affiche maintenant les articles sans prix et les articles sous leur coût, avec une suggestion de prix.
3. **Devises historiques** — modifier une fiche conserve la devise d’achat et la devise du cours d’or déjà associées à l’article, même si les paramètres globaux ont changé.
4. **Graphique de l’or** — une seule devise est tracée à la fois; CAD, USD et EUR ne sont plus mélangés sur une même échelle.
5. **Ajout d’article** — le lien `page=new` redirige côté serveur, sans dépendre de JavaScript.
6. **Sécurité JavaScript** — le nom d’un article injecté dans l’action d’ouverture du formulaire de vente est encodé pour éviter une rupture d’attribut HTML et une injection de script.
7. **Tentatives de connexion** — ajout d’une limite de 10 tentatives par adresse IP sur 15 minutes et nettoyage des tentatives anciennes.
8. **Suppression d’article** — l’action existante est désormais accessible depuis la fiche historique; la photo associée est supprimée si elle est dans `uploads/`.
9. **Téléversement de photos** — contrôle des erreurs d’upload et nettoyage des fichiers qui n’ont pas été rattachés à une fiche après un échec.
10. **Validation des nombres** — rejet des valeurs non finies (par exemple un nombre qui déborde vers l’infini) pour les montants importants.
11. **Cours d’or** — les cours manuels et les lignes CSV futures sont rejetés; le cours manuel exige un prix positif fini.
12. **Paramètres** — un mot de passe trop court est vérifié avant l’écriture des paramètres, évitant une sauvegarde partielle inattendue.
13. **Installation** — création du schéma et du compte dans une transaction; un échec ne doit plus laisser volontairement un schéma partiellement installé.
14. **PWA** — le service worker propose une page hors connexion tout en évitant de mettre en cache les pages PHP et les données privées authentifiées.
15. **Actions POST** — les actions inconnues renvoient désormais un message explicite plutôt que de continuer silencieusement.

## Contrôles exécutés

- `php -l` sur les fichiers PHP : aucune erreur de syntaxe.
- `node --check` sur les scripts JavaScript : aucune erreur de syntaxe.
- Validation JSON du manifeste PWA : valide.
- Vérification de la structure de l’archive ZIP et des chemins attendus.
- Tests SQLite indépendants des migrations additives, des contraintes de clés étrangères, du journal d’audit, des frais et de la sauvegarde.

## Limite du test

L’environnement de contrôle dispose de PHP mais **pas de l’extension `pdo_sqlite`**. Il n’a donc pas été possible d’exécuter l’application complète derrière un serveur PHP réel avec une base SQLite, ni de simuler une session HTTP complète. Avant la production, testez l’installation et les opérations principales sur l’hébergement cible avec `pdo_sqlite` activé. Sauvegardez `storage/app.sqlite` et `uploads/` avant toute mise à jour.

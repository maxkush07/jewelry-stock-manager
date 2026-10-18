# Bijouterie Manager V1.9.1 — Baisse en lot, filtres et exports

Application PHP 8.1+ / SQLite pour inventaire de bijouterie, stock dormant, ventes, historique du cours de l’or et estimation théorique du métal en stock.

## Installation
1. Téléversez le contenu du dossier `bijouterie-manager` dans votre hébergement.
2. Activez PHP PDO SQLite (`pdo_sqlite`) et autorisez l'écriture dans `storage/` et `uploads/`.
3. Ouvrez `/install.php` et créez le compte administrateur.
4. Après l'installation, supprimez `install.php` du serveur.
5. Faites des sauvegardes régulières de `storage/app.sqlite` et `uploads/`.

## Cours de l'or
- Les cours historiques peuvent être importés en CSV (`price_date`, `price_per_gram`, `currency`, avec `source` et `notes` facultatifs).
- Le bouton « Synchroniser le cours spot actuel (USD) » récupère le cours XAU/USD via `https://api.gold-api.com/price/XAU`, convertit l'once troy en grammes (31,1034768 g) et enregistre le prix du jour en USD.
- Cette synchronisation nécessite un accès HTTPS sortant depuis l'hébergement. Elle ne remplace pas un historique historique pluriannuel; pour cela, importez un CSV fiable.
- Attention : les comparaisons sont valides seulement si les cours et l'article sont dans la même devise. La valeur théorique du métal est une estimation, pas une offre de rachat.

## Sécurité / exploitation
- Utilisez HTTPS et un mot de passe administrateur unique d'au moins 12 caractères.
- Ne rendez pas le dossier `storage/` accessible publiquement; le `.htaccess` fourni bloque son accès sur Apache.
- Conservez les sauvegardes hors du serveur web.
- L'archive doit être testée sur l'hébergement réel, notamment l'accès réseau sortant et `pdo_sqlite`.


## V1.3 — Valeur théorique du métal
Dans Rapports, un tableau estime la valeur théorique de l’or contenu dans chaque article en stock : poids saisi × pureté (carats/24) × dernier cours de l’or fin par gramme enregistré dans la devise du cours de l’article. Cette estimation concerne uniquement le métal et ne constitue pas une évaluation commerciale ou une offre de rachat. Les devises différentes ne sont pas additionnées.


## V1.4 — Historique individuel
- Cliquez sur le nom d’un article dans l’inventaire pour ouvrir son historique.
- Affiche la durée de détention, l’évolution du cours de l’or, la valeur théorique du métal et la marge brute si l’article est vendu.
- Courbe individuelle construite à partir des cours réellement enregistrés en SQLite; aucune donnée historique n’est inventée.

## V1.5 — Priorités du stock dormant
- Le rapport classe les articles encore en stock selon quatre tranches : moins d’un an, 1 à 3 ans, 3 à 5 ans et 5 ans et plus.
- Chaque ligne présente l’ancienneté, le capital immobilisé, un niveau de priorité et une action suggérée (suivi, vérification du prix, promotion ou réévaluation).
- Le bouton « Exporter le rapport CSV » génère un rapport du stock non vendu avec les cours d’or disponibles et une estimation de la valeur théorique du métal.
- L’écart entre valeur théorique du métal et prix d’achat est exporté uniquement lorsque les deux montants utilisent la même devise. Les conversions de devises ne sont pas estimées.
- Les seuils servent d’aide à la décision et ne déclenchent aucun changement automatique de prix.


## V1.6 — Rentabilité, sécurité et alertes

- Migration SQLite additive automatique : les données existantes sont conservées.
- Frais par article (réparation, nettoyage, commission, transport, évaluation, autre) et calcul de marge après frais dans la même devise uniquement.
- Totaux du tableau de bord filtrés par devise de référence afin de ne pas additionner des devises différentes.
- Page Sécurité : sauvegarde SQLite téléchargeable et journal d’audit des opérations principales.
- Contrôles de date plus stricts pour les ventes.
- Les photos restent dans `uploads/` et doivent être sauvegardées séparément.

### Mise à jour depuis V1.5.1
1. Sauvegardez `storage/app.sqlite` et `uploads/`.
2. Remplacez les fichiers applicatifs sans écraser `storage/app.sqlite`, `storage/installed.lock` ni les photos.
3. Connectez-vous : les tables supplémentaires sont créées automatiquement.
4. Ouvrez **Sécurité & sauvegardes** pour télécharger une copie SQLite.

La sauvegarde intégrée contient la base SQLite, pas les photos. La validation locale ne remplace pas un test complet sur l’hébergement de production.


## V1.6.1 — Audit de code et correctifs

- Correction de la lecture des paramètres dont la valeur est `0` (notamment une marge cible de 0 %).
- Protection contre les tentatives de connexion répétées par adresse IP.
- Correction du contexte de devise lors de la modification d’un article : les devises historiques de l’achat et du cours de l’or sont conservées.
- Correction de l’alerte de prix : les articles sans prix de détail sont maintenant listés avec leur prix conseillé.
- Graphique de cours filtré sur une seule devise pour éviter de mélanger CAD, USD et EUR.
- Validation renforcée des montants, des photos et des articles ciblés; suppression de la photo associée quand une fiche est supprimée ou remplacée.
- Protection de l’attribut JavaScript utilisé pour ouvrir le formulaire de vente contre l’injection via le nom d’un article.
- Action de suppression rendue accessible depuis l’historique individuel.
- Installation rendue transactionnelle et ajout d’un écran hors connexion PWA sans mise en cache des pages PHP authentifiées.
- La limitation de tentatives de connexion est stockée dans SQLite et les anciennes tentatives sont nettoyées automatiquement.

## V1.7 — inventaire, or, employés et sauvegardes

### Mise à jour depuis V1.6.1
1. Faites une sauvegarde de `storage/app.sqlite` et du dossier `uploads/` avant de remplacer les fichiers.
2. Téléversez les fichiers V1.7 en conservant `storage/app.sqlite`, `storage/installed.lock` et tous les fichiers de `uploads/`.
3. À la première ouverture, des migrations additives créent les colonnes `items.barcode`, `users.role`, `users.is_active` et les tables d’inventaire physique / historique de sauvegarde. Les articles existants reçoivent un code interne `BM-00000001` si aucun code n’est présent.
4. Les comptes existants deviennent administrateurs afin d’éviter une perte d’accès. Créez ensuite des comptes vendeurs depuis « Employés & accès » et testez les permissions.
5. Testez l’impression d’une étiquette et scannez-la avec votre lecteur avant d’imprimer en série.

### Nouveautés V1.7
- Codes-barres Code 39 imprimables à partir de la fiche d’inventaire; le SKU est repris si compatible, sinon un identifiant interne est généré.
- Calculateur du métal fin et prix indicatif selon le cours saisi / dernier cours disponible, la pureté et la marge.
- Sessions d’inventaire physique avec scan du code-barres/SKU, historique du comptage et liste des articles non vérifiés.
- Rapport des ventes par mois dans la devise choisie, sans additionner les devises entre elles.
- Sauvegarde complète ZIP de `app.sqlite` et de `uploads/`, si l’extension PHP `ZipArchive` est installée; la sauvegarde SQLite seule reste disponible séparément.
- Comptes employés, rôles administrateur/vendeur, activation/désactivation et restriction des actions sensibles.

### Dépendances et limites
- PHP 8.1+ et PDO SQLite sont requis; l’extension `ZipArchive` est nécessaire uniquement pour la sauvegarde complète ZIP.
- Le code-barres imprimé utilise le format Code 39. La compatibilité dépend du lecteur configuré et du support d’impression; effectuer un essai réel avant usage opérationnel.
- Le calculateur d’or est une estimation du métal fin et ne remplace pas une évaluation professionnelle ou comptable.
- Une sauvegarde téléchargée doit être stockée dans un emplacement protégé, hors du serveur. La restauration est manuelle et doit être faite après sauvegarde de l’état courant.


## V1.8 — Sécurité, rentabilité avancée, PWA et assistant

- **Sécurité** : expiration après 30 minutes d’inactivité, en-têtes HTTP de sécurité complémentaires et audit de l’expiration de session. HSTS est envoyé uniquement si la requête est déjà en HTTPS. Les mots de passe définis via Paramètres doivent maintenant faire au moins 12 caractères. Configurez également HTTPS au niveau de l’hébergement.
- **Migrations SQLite** : création additive et répétable de `schema_migrations` et `assistant_feedback`; aucun `DROP`, aucune suppression ou renommage des tables métier, et aucun déplacement des photos. Faites néanmoins une sauvegarde de `storage/app.sqlite` et de `uploads/` avant la mise à jour.
- **Rentabilité avancée** : le rapport inclut une estimation après les frais d’articles enregistrés dans la même devise. Les frais de devise différente ne sont ni convertis ni additionnés. Ce n’est pas un état comptable/fiscal.
- **PWA mobile** : invite d’installation lorsque le navigateur le permet, cache statique versionné et écran hors ligne. Les réponses PHP et les données privées ne sont pas mises en cache. L’installation exige HTTPS (sauf localhost) et le support du navigateur.
- **Assistant intelligent local** : recommandations heuristiques à partir des données de stock, prix et marge. Aucun service d’IA externe n’est appelé; aucune modification automatique des articles/prix/ventes n’est faite. Les évaluations sont enregistrées dans SQLite.

### Mise à jour depuis V1.7
1. Téléchargez une sauvegarde SQLite via Sécurité et sauvegardez le dossier `uploads/` séparément (ou utilisez l’archive complète si ZipArchive est disponible).
2. Décompressez le ZIP et téléversez les fichiers en préservant impérativement `storage/app.sqlite`, `storage/installed.lock` et tout le contenu de `uploads/`. Ne remplacez pas votre base par celle d’une archive vierge.
3. Vérifiez que `storage/` et `uploads/` restent accessibles en écriture par PHP, ouvrez l’application et connectez-vous. La migration V1.8 se fait à l’ouverture.
4. Testez connexion, ajout/consultation d’article, photo existante, rapports, assistant et sauvegarde. Gardez une copie de sauvegarde hors serveur.

### Limites connues
L’assistant est un système de règles local, pas un LLM. Le mode hors ligne ne rend pas les opérations de gestion disponibles hors ligne : seules les ressources statiques et la page hors ligne peuvent être affichées. Le test d’intégration PHP-PDO-SQLite doit être exécuté sur un hébergement qui possède `pdo_sqlite`; les tests de cette livraison distinguent clairement les validations statiques et les tests SQL indépendants.


## V1.8.1 — Édition design & PWA

- Interface visuelle modernisée : hiérarchie plus nette, cartes et tableaux affinés, états de focus accessibles et navigation mobile adaptée aux zones sûres iOS/Android.
- Manifeste PWA complet avec icônes PNG 192×192 et 512×512, icône maskable, raccourcis Inventaire et Calculateur d’or, mode autonome et prise en charge du bouton d’installation.
- Installation proposée depuis la connexion et depuis l’application lorsqu’elle est prise en charge. Safari iOS reçoit une indication d’installation manuelle.
- Indicateur de connectivité et écran hors ligne amélioré.
- Service worker limité à une liste explicite de ressources publiques statiques. Les pages PHP, la base SQLite, les exports, les photos et les autres fichiers privés ne sont pas mis en cache.
- Polices système locales : aucune dépendance Google Fonts pour le rendu hors ligne.

### Limite importante du mode hors ligne

Cette version est une PWA installable et fournit un écran hors ligne fiable, mais les opérations métier nécessitent toujours une connexion au serveur PHP/SQLite. Il ne s’agit pas d’une application de saisie hors ligne synchronisée : aucune modification d’inventaire n’est stockée localement. Cette limite est volontaire pour éviter les conflits et l’exposition des données privées. L’installation PWA nécessite HTTPS (ou localhost en développement) et un navigateur compatible.


## V1.8.2 — Audit de code et correctifs

- **Connexion** : le courriel est maintenant enregistré et comparé en minuscules (un administrateur installé avec une majuscule ne pouvait plus se connecter). Un compte désactivé perd l'accès dès sa prochaine requête, sans attendre l'expiration de session.
- **Messages d'erreur** : les erreurs SQL (doublon de SKU, de code-barres ou de courriel) affichent un message clair au lieu du texte technique SQLite.
- **Compatibilité SQLite** : les littéraux `"stock"` / `"sold"` et les `DEFAULT "..."` de l'installation utilisent des apostrophes (certaines builds SQLite refusent les guillemets doubles comme chaînes).
- **Remise en stock** : les notes de vente sont effacées avec la vente annulée.
- **Code-barres Code 39** : zone de silence ajoutée à droite de l'étiquette (lecture plus fiable), caractère `*` neutralisé dans la valeur encodée.
- **Sauvegarde ZIP** : `BACKUP-INFO.txt` avait des `\n` littéraux et indiquait V1.7; corrigé.
- **Journal d'audit** : l'import CSV et la synchronisation du cours spot sont maintenant journalisés.
- **Validation** : catégorie limitée à la liste connue, nom de la bijouterie limité à 120 caractères, index POST manquants traités sans avertissement PHP, étiquettes limitées à 200 articles par impression.
- **Inventaire** : la recherche couvre aussi le code-barres. Le bouton « Supprimer » de la fiche n'est plus affiché aux vendeurs (action réservée aux administrateurs).
- **PWA** : le service worker sert style.css / app.js en « réseau d'abord » (les mises à jour s'affichent sans changer le numéro de cache); la touche Échap ne génère plus d'erreur JavaScript hors de l'inventaire; l'attribut `hidden` n'est plus écrasé par les styles de boutons.
- **Sécurité des fichiers** : `.htaccess` bloque maintenant `tests/`, les fichiers `.md` (rapports d'audit) et `.py`.

### Mise à jour depuis V1.8.1
Remplacez les fichiers applicatifs sans écraser `storage/app.sqlite`, `storage/installed.lock` ni `uploads/`. Aucune migration de base n'est nécessaire. Rechargez deux fois l'application installée pour activer le nouveau service worker.

- **Synchronisation du cours spot** : le cours USD est aussi converti dans la devise des cours de l'or configurée (CAD/EUR) via open.er-api.com, de sorte que l'accueil et les calculs se mettent à jour. Sans taux de change, seul l'USD est enregistré et un message l'indique. L'accueil se rabat sur le dernier cours toutes devises si aucun cours n'existe dans la devise configurée.


## V1.9 — Rotation du stock, historique des prix, alerte or, photos et QR

### Mise à jour depuis V1.8.2
1. Sauvegardez `storage/app.sqlite` et `uploads/` (Sécurité & sauvegardes → sauvegarde complète).
2. Remplacez les fichiers applicatifs **et ajoutez le nouveau dossier `includes/`** (générateur de QR code). Ne remplacez pas `storage/app.sqlite`, `storage/installed.lock` ni `uploads/`.
3. Ouvrez l'application : la migration est additive et automatique. Une copie de sécurité `storage/pre-v19-migration.sqlite` est créée avant la première migration. Les photos existantes deviennent la photo principale de leur article ; le prix de détail actuel de chaque article est repris comme « prix initial » de l'historique.
4. Rechargez deux fois l'application installée pour activer le nouveau service worker.

### Nouveautés
- **Rotation du stock** (Rapports) : par catégorie et par fournisseur, délai moyen de vente, nombre en stock, capital immobilisé, âge moyen, marge réelle (prix de vente − prix d'achat − frais de même devise) et rotation annuelle (coût des ventes sur 12 mois ÷ capital en stock). Un signal rouge « Capital immobilisé » s'affiche pour une catégorie qui a du stock mais aucune vente en 12 mois. Une devise à la fois (sélecteur), sans conversion.
- **Historique des changements de prix** : chaque modification du prix de détail est datée (fiche de l'article). Le rapport « Effet des baisses de prix » indique quelles baisses ont été suivies d'une vente dans les 30 jours (sans autre changement de prix entre-temps), par ampleur de baisse. C'est une corrélation, pas une preuve de causalité. Les prix antérieurs à V1.9 sont inconnus.
- **Alerte sur le cours de l'or** : bandeau sur toutes les pages quand le dernier cours (devise des cours de l'or) varie de plus de X % par rapport au cours enregistré 7 à 10 jours plus tôt, avec l'impact sur la valeur théorique du métal en stock (même devise uniquement). Seuil dans Paramètres (défaut 3 %, 0 = désactivé). Aucune alerte si le dernier cours a plus de 7 jours ou s'il manque un cours de référence : les cours doivent être synchronisés ou saisis régulièrement.
- **Seuils de stock dormant par catégorie** (Paramètres) : vide = 365 jours. Ils pilotent le filtre « Dormant », les compteurs du tableau de bord, l'assistant et l'export du stock dormant (colonnes `seuil_dormant_jours`, `dormant`). Les tranches d'ancienneté du rapport (1/3/5 ans) restent fixes.
- **Valeur du métal par devise** : totaux par devise du cours sur le tableau de bord et dans les Rapports (jamais additionnés entre devises ; articles sans cours ou sans pureté comptés à part).
- **Photos multiples** : jusqu'à 8 photos par article (5 Mo chacune), galerie dans la fiche, photo principale modifiable, suppression individuelle. Le total envoyé en une fois est limité par `post_max_size` du serveur : un message clair l'indique s'il est dépassé.
- **QR code imprimable** : étiquettes depuis l'inventaire (par ligne ou liste affichée) et la fiche. Deux contenus au choix : lien vers la fiche (connexion requise, adresse déduite de la requête) ou code de l'article. L'inventaire physique accepte aussi le scan du QR « lien ».

### Limites connues
- Le QR « lien » contient l'adresse du site : si le domaine change, réimprimez les étiquettes. Testez la lecture avec un téléphone avant une impression en série.
- Le générateur de QR est interne (niveau de correction M, jusqu'à 213 octets, versions 1 à 10).
- Les photos restent servies directement par `uploads/` (noms aléatoires, sans authentification), comme avant.


## V1.9.1 — Baisse en lot, filtres et exports

Mise à jour depuis V1.9 : remplacez `index.php` et `README.md` ; aucune migration.

- **Baisse de prix en lot** (administrateur, bouton « ↘ Baisse en lot » dans l'inventaire) : aperçu puis application d'une baisse de 1 à 50 % aux articles **dormants** (selon les seuils par catégorie), par catégorie ou pour toutes. Option « ne pas descendre sous le prix d'achat » activée par défaut (ces articles sont ignorés et comptés). Seul le prix de détail des articles en stock dans la devise de l'inventaire est modifié ; chaque changement est daté dans l'historique avec la note « Baisse en lot X % ». Pas d'annulation automatique : l'ancien prix reste visible dans la fiche. Les prix sont arrondis au cent.
- **Filtres de l'inventaire** par catégorie et par fournisseur (combinables avec la recherche).
- **Suggestion des fournisseurs existants** dans la fiche article, pour éviter les variantes d'orthographe qui fragmentent les statistiques de rotation.
- **Exports CSV** : rotation du stock (catégories et fournisseurs, par devise) et historique complet des prix.

Note : au chargement, tout article ayant un prix de détail mais aucun historique reçoit une ligne « prix initial » (comportement de la migration V1.9, relancée à chaque chargement).

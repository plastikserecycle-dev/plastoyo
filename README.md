# Plastoyo — site dynamique d'aide aux opérateurs d'injection

Site PHP / MySQL à faire tourner **en local avec XAMPP**.

## Installation (XAMPP + MySQL)

1. Copier le dossier `plastoyo` dans `C:\xampp\htdocs\` (Windows) ou `/opt/lampp/htdocs/` (Linux).
2. Démarrer **Apache** et **MySQL** depuis le panneau XAMPP.
3. Ouvrir phpMyAdmin : [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   → onglet **Importer** → sélectionner `plastoyo/sql/plastoyo.sql` → Exécuter.
   (La base `plastoyo`, toutes les tables et les données de départ sont créées.)
4. Si vos identifiants MySQL ne sont pas `root` sans mot de passe, modifier
   `plastoyo/config/config.php` (`DB_USER`, `DB_PASS`).
5. Ouvrir le site : [http://localhost/plastoyo/](http://localhost/plastoyo/)

## Comptes par défaut

- **Administrateur** : [http://localhost/plastoyo/admin/connexion.php](http://localhost/plastoyo/admin/connexion.php)
  → `admin@plastoyo.fr` / `admin123` (créé automatiquement au premier accès — à changer ensuite).
- **Opérateur** : créer un compte via « Créer un compte » (ouvre automatiquement une défauthèque personnelle).

## Structure

```
plastoyo/
├── index.php                  Page d'accueil (3 choix : process / défauthèque / enregistrer un défaut)
├── pages/                     Pages d'information du process (matière, familles, moule, machine,
│                              matériel, montage, mode, processus, défaut, cause, remède)
├── defautheque/               Défauthèque générale plastoyo (liste, recherche, fiches)
├── compte/                    Inscription / connexion / mon espace (défauthèque personnelle)
├── defauts/                   Questionnaire d'enregistrement d'un défaut + commentaires nominatifs
├── admin/                     Administration : CRUD de toutes les tables, défauthèque plastoyo,
│                              demandes de transfert, abonnements 100 €/an
├── config/config.php          Connexion MySQL + fonctions utilitaires
├── includes/                  Gabarit, rendu des fiches, liste des champs du défaut
├── assets/                    CSS + JS (fenêtre explicative des causes avant coche)
├── sql/plastoyo.sql           Schéma complet + données de départ (à importer dans phpMyAdmin)
└── uploads/                   Photos, vidéos et fichiers de transfert (images/, videos/, transferts/)
```

## Règles métier implémentées

- **Défauthèque générale plastoyo** : renseignée uniquement par l'administrateur (toutes les valeurs
  sont optionnelles et modifiables à tout moment). Fiche = toutes les lignes descriptives (même vides),
  puis uniquement les causes cochées, puis le commentaire et les réponses nominatives.
- **Essai gratuit 6 mois puis 100 €/an** : à la création du compte, l'accès à la défauthèque générale
  et à l'espace de création est gratuit pendant 6 mois ; ensuite l'administrateur valide le paiement
  de 100 € (1 an) dans `admin/utilisateurs.php`. Visiteurs non inscrits : accès libre à la défauthèque publique.
- **Défaut opérateur** : enregistré via le questionnaire (identification : nom, fonctions cochées,
  date, équipe, heure, puis toutes les valeurs de la table). **Non modifiable ensuite** : on y ajoute
  seulement des commentaires/réponses, signés nominativement. Chaque cause cochable ouvre une fenêtre
  explicative (texte + vidéo) **avant** la coche.
- **Transfert** : l'admin peut demander à l'entreprise de l'opérateur l'accord de transférer son défaut
  dans la défauthèque plastoyo : un fichier temporaire avec lien ACCEPTE/REFUSE est généré
  (`admin/defauts.php` → « Demander l'accord »).

## Envoi de photos / vidéos

Dans l'admin, chaque table accepte une image/photo et une vidéo de formation (mp4, webm, ogg),
stockées dans `uploads/images` et `uploads/videos`.

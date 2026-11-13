-- =====================================================================
-- PLASTOYO - Base de données MySQL
-- À importer dans phpMyAdmin (XAMPP) : fichier sql/plastoyo.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS plastoyo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plastoyo;

-- ---------------------------------------------------------------------
-- Administrateur du site (le 1er admin est créé automatiquement au
-- premier accès à admin/connexion.php : admin@plastoyo.fr / admin123)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS administrateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Comptes opérateurs (création de compte = création d'une défauthèque
-- personnelle). Essai gratuit 6 mois, puis abonnement 100 EUR/an.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  equipe VARCHAR(100) DEFAULT NULL,
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  statut_abonnement ENUM('essai','paye','expire') NOT NULL DEFAULT 'essai',
  date_paiement DATETIME DEFAULT NULL,
  date_expiration DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fonctions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS utilisateur_fonctions (
  utilisateur_id INT NOT NULL,
  fonction_id INT NOT NULL,
  PRIMARY KEY (utilisateur_id, fonction_id),
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (fonction_id) REFERENCES fonctions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO fonctions (nom) VALUES
('Responsable fabrication'),('Méthode'),('Mise au point'),('Responsable atelier'),
('Préparation matière'),('Maintenance moule'),('Maintenance atelier'),('Chef d''équipe'),
('Opérateur'),('Qualité');

-- ---------------------------------------------------------------------
-- Pages d'information du process
-- (valeur + explication + image + vidéo de formation)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS matiere (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS moule (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS machine (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS materiel (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS montage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reglage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  identifiant VARCHAR(150) NOT NULL,
  valeur VARCHAR(255) NOT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mode (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  photo VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS processus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phase VARCHAR(100) NOT NULL,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS defaut_info (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS remede (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categorie ENUM('matiere','moule','machine','materiel','montage','mode') NOT NULL,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Familles de matières et conditions de transformation
CREATE TABLE IF NOT EXISTS famille (
  id INT AUTO_INCREMENT PRIMARY KEY,
  famille VARCHAR(100) NOT NULL,
  abreviation VARCHAR(20) DEFAULT NULL,
  fusion VARCHAR(100) DEFAULT NULL,
  temperature_moule VARCHAR(100) DEFAULT NULL,
  etuvage VARCHAR(150) DEFAULT NULL,
  pourcentage_recyclage VARCHAR(50) DEFAULT NULL,
  fluidite_chaude VARCHAR(100) DEFAULT NULL,
  retrait_moulage VARCHAR(100) DEFAULT NULL,
  explication TEXT,
  image VARCHAR(255) DEFAULT NULL,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Causes cochables dans le questionnaire (fenêtre explicative avant coche)
CREATE TABLE IF NOT EXISTS cause (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categorie ENUM('matiere','moule','machine','materiel','montage','mode') NOT NULL,
  valeur VARCHAR(200) NOT NULL,
  explication TEXT,
  video VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- DÉFAUTHÈQUE (générale plastoyo renseignée par l'admin + défauthèques
-- personnelles des opérateurs). Un enregistrement opérateur n'est plus
-- modifiable ensuite : on y ajoute seulement des commentaires/réponses.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS defauts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  origine ENUM('plastoyo','operateur') NOT NULL DEFAULT 'plastoyo',
  utilisateur_id INT DEFAULT NULL,
  nom_personne VARCHAR(150) DEFAULT NULL,
  equipe VARCHAR(100) DEFAULT NULL,
  heure_enregistrement TIME DEFAULT NULL,
  date_enregistrement DATE DEFAULT NULL,

  defaut_constate TEXT,
  photo1 VARCHAR(255) DEFAULT NULL,
  photo2 VARCHAR(255) DEFAULT NULL,
  photo3 VARCHAR(255) DEFAULT NULL,

  ref_matiere VARCHAR(100) DEFAULT NULL,
  famille_abbr VARCHAR(50) DEFAULT NULL,
  densite_matiere VARCHAR(50) DEFAULT NULL,
  retrait_moulage_fabricant VARCHAR(50) DEFAULT NULL,
  temp_fusion_reglage VARCHAR(50) DEFAULT NULL,
  etuvage_duree VARCHAR(50) DEFAULT NULL,
  etuvage_temperature VARCHAR(50) DEFAULT NULL,
  temp_moule_reglage VARCHAR(50) DEFAULT NULL,
  pourcentage_recyclage VARCHAR(50) DEFAULT NULL,

  nb_empreintes VARCHAR(50) DEFAULT NULL,
  poids_matiere_injecter VARCHAR(50) DEFAULT NULL,
  mode_alimentation VARCHAR(100) DEFAULT NULL,
  epaisseur_min VARCHAR(50) DEFAULT NULL,
  epaisseur_max VARCHAR(50) DEFAULT NULL,

  tonnage_presse VARCHAR(50) DEFAULT NULL,
  volume_injectable VARCHAR(50) DEFAULT NULL,
  diametre_vis VARCHAR(50) DEFAULT NULL,
  course_vis VARCHAR(50) DEFAULT NULL,

  type_etuve VARCHAR(100) DEFAULT NULL,
  volume_etuvage VARCHAR(50) DEFAULT NULL,
  type_regulateur VARCHAR(100) DEFAULT NULL,
  temperature_fusion VARCHAR(50) DEFAULT NULL,

  temps_dosage VARCHAR(50) DEFAULT NULL,
  contrepression_dosage VARCHAR(50) DEFAULT NULL,
  conditions_etuvage VARCHAR(150) DEFAULT NULL,
  temperature_empreintes VARCHAR(50) DEFAULT NULL,
  temps_remplissage VARCHAR(50) DEFAULT NULL,
  pression_commutation VARCHAR(50) DEFAULT NULL,
  pression_maintien VARCHAR(50) DEFAULT NULL,
  duree_maintien VARCHAR(50) DEFAULT NULL,
  temps_cycle VARCHAR(50) DEFAULT NULL,
  precision_valeur VARCHAR(50) DEFAULT NULL,
  poids_grappe VARCHAR(50) DEFAULT NULL,
  equilibrage_remplissage VARCHAR(100) DEFAULT NULL,

  commentaire TEXT,

  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Causes cochées d'un défaut (les seules affichées sur la fiche)
CREATE TABLE IF NOT EXISTS defaut_cause (
  defaut_id INT NOT NULL,
  cause_id INT NOT NULL,
  PRIMARY KEY (defaut_id, cause_id),
  FOREIGN KEY (defaut_id) REFERENCES defauts(id) ON DELETE CASCADE,
  FOREIGN KEY (cause_id) REFERENCES cause(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commentaires et réponses nominatives associés à un défaut
CREATE TABLE IF NOT EXISTS commentaires_defaut (
  id INT AUTO_INCREMENT PRIMARY KEY,
  defaut_id INT NOT NULL,
  auteur VARCHAR(150) NOT NULL,
  est_admin TINYINT(1) NOT NULL DEFAULT 0,
  texte TEXT NOT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  parent_id INT DEFAULT NULL,
  FOREIGN KEY (defaut_id) REFERENCES defauts(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES commentaires_defaut(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demandes de transfert d'un défaut opérateur vers la défauthèque plastoyo
-- (l'admin envoie un fichier temporaire à l'entreprise pour accord)
CREATE TABLE IF NOT EXISTS transferts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  defaut_id INT NOT NULL,
  entreprise VARCHAR(150) DEFAULT NULL,
  email_entreprise VARCHAR(150) DEFAULT NULL,
  token VARCHAR(64) NOT NULL,
  fichier VARCHAR(255) DEFAULT NULL,
  statut ENUM('en_attente','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  date_demande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_reponse DATETIME DEFAULT NULL,
  FOREIGN KEY (defaut_id) REFERENCES defauts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- DONNÉES DE DÉPART
-- =====================================================================

INSERT INTO matiere (valeur, explication) VALUES
('La molécule','Une matière plastique est constituée de molécules organiques de grande taille obtenues par polymérisation.'),
('Les chaînes','Les molécules sont formées de longues chaînes d''atomes de carbone reliés entre eux.'),
('Homopolymère ou copolymère','Homopolymère : un seul monomère. Copolymère : plusieurs monomères associés, propriétés modifiées.'),
('Structure des chaînes (amorphe / semi-cristallin)','Amorphe : chaînes enchevêtrées (PS, PMMA). Semi-cristallin : zones ordonnées (PE, PP, PA), plus résistant.'),
('Liens et mobilité dans les chaînes','La mobilité des chaînes conditionne la fluidité à chaud et le retrait au refroidissement.'),
('Fluidité à chaud','Capacité de la matière fondue à s''écouler ; mesurée par l''indice de fluidité (MFI).'),
('Retrait de moulage','Retrait dimensionnel de la pièce entre la sortie du moule et le refroidissement complet.'),
('Conditions de fabrication','Température de fusion, étuvage, taux de recyclage et conditions de transformation du fabricant.');

INSERT INTO moule (valeur, explication) VALUES
('Le volume et les contre-dépouilles','Le volume de l''empreinte et les contre-dépouilles facilitent le démoulage de la pièce.'),
('Le plan de joint','Surface de jonction des deux parties du moule ; son étanchéité évite les bavures.'),
('L''alimentation par carotte','Injection par une carotte froide ; laisse une trace à découper sur la pièce.'),
('L''alimentation par bloc chaud','Canaux chauffés maintenant la matière fondue : pas de carotte, moins de déchets.'),
('La mise en forme','Les empreintes donnent la forme dimensionnelle finale de la pièce.'),
('L''éjection','Système d''éjecteurs qui pousse la pièce hors du moule en fin de cycle.'),
('La durée de vie','Nombre de cycles garantis ; dépend de l''acier, du refroidissement et de l''entretien.');

INSERT INTO machine (valeur, explication) VALUES
('L''emplacement du moule','Le moule est installé entre les plateaux de la presse, centré et bien calé.'),
('Le verrouillage','Force de fermeture qui maintient le moule fermé contre la pression d''injection.'),
('La plastification','La vis fait fondre et homogénéiser la matière dans le fourreau chauffé.'),
('La transmission de pression','La pression hydraulique est transmise à la matière via l''injection et le maintien.'),
('La répétition du cycle','Régularité des mouvements de fermeture, injection, refroidissement et éjection.'),
('L''environnement','Température ambiante, alimentation électrique et air comprimé autour de la presse.'),
('L''assistance','Périphériques : étuve, régulateur de température, robot, convoyeur.');

INSERT INTO materiel (valeur, explication) VALUES
('Le matériel fonctionnel','Étuve, régulateur de température moule, broyeur, mélangeur : participe directement à la qualité.'),
('Le matériel de productivité','Alimentateur, colorateur, convoyeur, robot : améliore le débit et réduit les manutentions.'),
('Le matériel de surveillance','Capteurs moule, pesage, contrôle dimensionnel : sécurise la production.');

INSERT INTO montage (valeur, explication) VALUES
('Préparation matière','Vérification de la référence, de l''étuvage et du taux de recyclage avant montage.'),
('Montage du moule','Installation, centrage, réglage des sécurités et raccordement des alimentations.'),
('Réglage de la presse','Mise au point des courses, pressions, températures et temps selon la fiche de réglage.'),
('Démarrage','Premiers cycles en mode semi-automatique avec surveillance renforcée.'),
('Stabilisation du cycle','Passage en automatique une fois la pièce conforme et le cycle stable.');

INSERT INTO reglage (identifiant, valeur, explication) VALUES
('Fiche de réglage','Document de référence : températures, pressions, courses et temps validés pour la pièce.'),
('Installation','Mise en place de la matière étuvée, du moule et des périphériques avant réglage.'),
('La stabilisation','Réglage fin jusqu''à obtenir des pièces conformes et un cycle répétable.'),
('Les alarmes','Seuils de surveillance : sécurité moule, fusion, étuvage, températures, temps, pressions.');

INSERT INTO mode (valeur, explication) VALUES
('La procédure matière','Manutention, étuvage, stockage et contrôle de la matière.'),
('La procédure moule','Entretien, montage, réglage et suivi d''usure du moule.'),
('La procédure machine','Vérifications quotidiennes et réglages de la presse.'),
('La procédure matérielle','Contrôle des périphériques (étuve, régulateur, alimentateur...).'),
('La procédure réglage','Application et suivi de la fiche de réglage.'),
('La procédure qualité','Contrôles en cours de production et traçabilité des défauts.');

INSERT INTO processus (phase, valeur, explication) VALUES
('Préparation','Matière','Vérifier référence, étuvage et taux de recyclage.'),
('Préparation','Moule','Contrôler l''état, le plan de joint et les éjecteurs.'),
('Préparation','Machine','Vérifier les niveaux, pressions et températures de la presse.'),
('Préparation','Matériel','Contrôler étuve, régulateur, alimentateur et périphériques.'),
('Préparation','Montage','Préparer les outillages et les branchements.'),
('Préparation','Mode','Appliquer les procédures matière, moule, machine et qualité.'),
('Montage moule','Montage du moule','Installer, centrer et verrouiller le moule en sécurité.'),
('Réglages','Étuvage','Régler durée et température d''étuvage de la matière.'),
('Réglages','Température moule','Régler le régulateur sur la température de consigne.'),
('Réglages','Perte de charge buse','Vérifier la buse et la perte de charge à l''injection.'),
('Réglages','Fermeture sécurité','Régler la sécurité de fermeture du moule.'),
('Réglages','Verrouillage','Régler la force de verrouillage nécessaire.'),
('Réglages','Fusion','Régler les températures de plastification du fourreau.'),
('Réglages','Dosage et décompression','Régler la course de dosage et la décompression.'),
('Réglages','Appui du ponton','Régler la position d''appui de l''unité d''injection.'),
('Réglages','Remplissage','Régler vitesse et course de remplissage.'),
('Réglages','Commutation','Régler le point de commutation remplissage / maintien.'),
('Réglages','Maintien','Régler pression et durée de maintien.'),
('Réglages','Refroidissement','Régler le temps de refroidissement.'),
('Réglages','Décollage et ouverture','Régler les mouvements d''ouverture et de décollage.'),
('Démarrage','Démarrage','Lancer les premiers cycles en semi-automatique.'),
('Stabilisation cycle','Stabilisation','Valider la conformité et stabiliser le cycle automatique.'),
('Modification','Étuvage','Modifier durée/température d''étuvage si défaut matière.'),
('Modification','Température moule','Modifier la consigne du régulateur.'),
('Modification','Perte de charge buse','Intervenir sur la buse si perte de charge anormale.'),
('Modification','Fusion','Modifier les températures du fourreau.'),
('Modification','Remplissage','Modifier vitesses et courses de remplissage.'),
('Modification','Commutation','Modifier le point de commutation.'),
('Modification','Maintien','Modifier pression et durée de maintien.'),
('Modification','Refroidissement','Modifier le temps de refroidissement.');

INSERT INTO defaut_info (valeur, explication) VALUES
('Qu''est-ce qu''un défaut d''injection','Toute non-conformité de la pièce : aspect, cote, résistance ou fonctionnement.'),
('Comment le constater','Contrôle visuel et dimensionnel en début de production puis en cours de cycle.'),
('Comment l''enregistrer','Renseigner la fiche de la défauthèque avec photos, réglages et causes cochées.');

INSERT INTO remede (categorie, valeur, explication) VALUES
('matiere','Vérifier la qualité et la conformité de la matière','Contrôler la référence, le certificat et l''état des sacs.'),
('matiere','Contrôler le taux de recyclage','Respecter le pourcentage maxi préconisé par le fabricant.'),
('moule','Vérifier l''alimentation du moule','Carotte ou bloc chaud : contrôler buses, seuils et obturation.'),
('moule','Contrôler la mise en forme dimensionnelle','Vérifier l''usure des empreintes et les cotes critiques.'),
('machine','Vérifier le verrouillage','La force de fermeture doit être suffisante.'),
('machine','Contrôler la plastification','Températures de fourreau et vitesse de vis conformes.'),
('materiel','Vérifier l''étuve','Durée, température et débit d''air conformes.'),
('materiel','Vérifier le régulateur','Température moule stable et conforme à la consigne.'),
('montage','Respecter les alarmes de sécurité','Ne jamais shunter une alarme de sécurité moule.'),
('mode','Appliquer la procédure qualité','Contrôles réguliers et enregistrement des défauts.');

INSERT INTO famille (famille, abreviation, fusion, temperature_moule, etuvage, pourcentage_recyclage, fluidite_chaude, retrait_moulage, explication) VALUES
('Polypropylène','PP','200-260 °C','30-60 °C','70-80 °C / 2-3 h','20-30 %','MFI 10-30','1,5-2,5 %','Matière souple, bon rapport qualité/prix, très utilisée en automobile.'),
('Polyéthylène haute densité','PEHD','220-280 °C','40-70 °C','70-80 °C / 2-3 h','20-30 %','MFI 4-20','1,5-3 %','Bonne résistance chimique, utilisé pour bouchons et flacons.'),
('Acrylonitrile butadiène styrène','ABS','220-260 °C','50-80 °C','80 °C / 2-4 h','20-30 %','MFI 15-30','0,4-0,7 %','Bonne tenue aux chocs et bel aspect de surface.'),
('Polyamide','PA','260-300 °C','60-90 °C','80 °C / 4-8 h','20 % maxi','MFI 20-40','1-2 %','Très résistant, mais très sensible à l''humidité : étuvage indispensable.'),
('Polystyrène','PS','180-240 °C','20-50 °C','70 °C / 2 h','20-30 %','MFI 5-15','0,4-0,7 %','Rigide et cassant, bon marché, aspect brillant.');

-- Causes cochables (avec texte explicatif affiché avant la coche)
INSERT INTO cause (categorie, valeur, explication) VALUES
('matiere','Qualité matière','Mauvaise référence, matière humide, dégradée ou non conforme au certificat fournisseur.'),
('matiere','Composition matière','Taux de recyclage trop élevé, mélange de matières incompatibles, colorant inadapté.'),
('matiere','Conditions d''utilisation matière','Matière non étuvée ou reprise d''humidité avant injection.'),
('matiere','Conditions de plastification et pollution','Températures de fourreau inadaptées, matière polluée ou dégradée dans la vis.'),
('moule','Volume du moule','Volume de l''empreinte inadapté au poids de pièce et à la presse.'),
('moule','Alimentation du moule','Seuil trop petit, obturé ou mal positionné ; perte de charge à l''alimentation.'),
('moule','Mise en forme dimensionnelle','Cotes d''empreinte hors tolérance ou usure des empreintes.'),
('moule','Éjection','Éjecteurs mal réglés, grippés ou insuffisants : la pièce reste collée.'),
('moule','Résistance à l''usure et à l''encrassement','Moule usé ou encrassé (dépôts de gaz) modifiant l''aspect de la pièce.'),
('machine','Emplacement moule et verrouillage','Moule mal centré ou force de verrouillage insuffisante : bavures.'),
('machine','Plastification','Mauvais réglage des températures de fourreau ou de la vitesse de vis.'),
('machine','Transmission de pression','Pression d''injection ou de maintien mal transmise (fuites, clapets).'),
('machine','Répétition du cycle','Cycle irrégulier : temps ou courses non reproductibles.'),
('machine','Environnement et assistance','Alimentation électrique, air comprimé ou périphériques défaillants.'),
('materiel','L''étuve','Mauvais étuvage : durée, température ou débit d''air non conformes.'),
('materiel','Le régulateur','Température du moule instable ou hors consigne.'),
('materiel','Les obturations','Buse ou seuil partiellement obturé.'),
('materiel','Les positionnements','Mauvais positionnement du moule, des capteurs ou des éjecteurs.'),
('materiel','La salle blanche','Pollution de l''environnement de production.'),
('materiel','Le broyeur','Carotte broyée polluée ou taux de rebroyé trop élevé.'),
('materiel','Le mélangeur','Mélange matière / rebroyé / colorant non homogène.'),
('materiel','Le colorateur','Dosage de colorant incorrect.'),
('materiel','L''alimentateur','Alimentation matière irrégulière vers la trémie.'),
('materiel','La sécurité matière','Défaut de protection de la matière contre l''humidité et les polluants.'),
('materiel','L''évacuation finition','Mauvaise évacuation des pièces ou de la carotte.'),
('materiel','Les capteurs moule','Capteurs de présence pièce ou de contrôle mal réglés.'),
('montage','Sécurité moule','Alarme de fermeture moule mal réglée ou shuntée.'),
('montage','Fusion','Alarme de température de fusion (fourreau) déclenchée.'),
('montage','Étuvage','Alarme de durée ou de température d''étuvage.'),
('montage','Température moule','Alarme de température du moule (régulateur).'),
('montage','Temps de dosage','Alarme de non-atteinte de la course de dosage.'),
('montage','Temps d''injection','Alarme de temps de remplissage hors limites.'),
('montage','Pression de commutation','Alarme de pression au point de commutation.'),
('montage','Matelas (maintien)','Alarme de matelas matière insuffisant en fin de maintien.'),
('montage','Temps d''éjection','Alarme de défaut d''éjection de la pièce.'),
('montage','Temps de cycle','Alarme de dépassement du temps de cycle maxi.'),
('mode','Maintien de l''homogénéité et de la qualité matière','Procédure matière non appliquée.'),
('mode','Maintien du fonctionnement du moule','Entretien et contrôles du moule non réalisés.'),
('mode','Maintien de la répétition du cycle','Cycle non surveillé, dérives non détectées.'),
('mode','Maintien des résultats des matériels','Contrôles des périphériques non faits.'),
('mode','Maintien de la stabilité du temps de cycle','Temps de cycle non stabilisé ni surveillé.'),
('mode','Maintien des consignes','Fiche de réglage non respectée.'),
('mode','Maintien des contrôles de qualité','Contrôles qualité insuffisants ou non tracés.');

-- Exemple de défaut dans la défauthèque générale plastoyo
INSERT INTO defauts (origine, date_enregistrement, defaut_constate,
  ref_matiere, famille_abbr, densite_matiere, retrait_moulage_fabricant,
  temp_fusion_reglage, etuvage_duree, etuvage_temperature, temp_moule_reglage,
  pourcentage_recyclage, nb_empreintes, poids_matiere_injecter, mode_alimentation,
  epaisseur_min, epaisseur_max, tonnage_presse, volume_injectable, diametre_vis,
  course_vis, type_etuve, volume_etuvage, type_regulateur, temperature_fusion,
  temps_dosage, contrepression_dosage, conditions_etuvage, temperature_empreintes,
  temps_remplissage, pression_commutation, pression_maintien, duree_maintien,
  temps_cycle, precision_valeur, poids_grappe, equilibrage_remplissage, commentaire)
VALUES ('plastoyo', CURDATE(), 'Retassures (retraits localisés) sur pièce de capot PP',
  'PP copolymère choc', 'PP', '0,905', '1,8 %', '230 °C', '3 h', '80 °C', '45 °C',
  '20 %', '4', '85 g', 'Bloc chaud', '1,5 mm', '3 mm', '150 T', '180 cm3', '45 mm',
  '120 mm', 'Étuvage 60 L', '60 L', 'Régulateur eau 12 circuits', '230 °C',
  '6,5 s', '8 bar', '80 °C / 3 h', '45 °C', '1,8 s', '650 bar', '350 bar',
  '8 s', '24 s', '± 0,05 mm', '12 g', 'Équilibré 4 empreintes',
  'Défaut constaté après changement de lot matière. Étuvage prolongé et pression de maintien augmentée : défaut disparu.');

INSERT INTO defaut_cause (defaut_id, cause_id)
SELECT 1, id FROM cause WHERE categorie='matiere' AND valeur='Qualité matière';
INSERT INTO defaut_cause (defaut_id, cause_id)
SELECT 1, id FROM cause WHERE categorie='montage' AND valeur='Matelas (maintien)';

INSERT INTO commentaires_defaut (defaut_id, auteur, est_admin, texte) VALUES
(1, 'Administrateur plastoyo', 1, 'Vérifier le certificat du lot matière et allonger l''étuvage à 4 h si le défaut persiste.');

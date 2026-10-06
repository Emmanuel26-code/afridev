-- AFRIDEV : schéma de base de données (tâche E2)
CREATE DATABASE IF NOT EXISTS afridev
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE afridev;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  pseudo VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe_hash VARCHAR(255) NOT NULL,
  bio VARCHAR(500) NULL,
  pays_ville VARCHAR(100) NULL,
  disponibilite ENUM('disponible','occupe') NOT NULL DEFAULT 'disponible',
  role ENUM('membre','admin') NOT NULL DEFAULT 'membre',
  est_bloque TINYINT(1) NOT NULL DEFAULT 0,
  avatar VARCHAR(255) NULL,
  github_url VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE skills (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(80) NOT NULL UNIQUE,
  categorie VARCHAR(50) NULL,
  technologies_proches VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE user_skills (
  user_id INT UNSIGNED NOT NULL,
  skill_id INT UNSIGNED NOT NULL,
  niveau TINYINT UNSIGNED NOT NULL,
  annees_experience TINYINT UNSIGNED NULL,
  PRIMARY KEY (user_id, skill_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  titre VARCHAR(255) NOT NULL,
  contenu TEXT NOT NULL,
  statut ENUM('ouverte','resolue') NOT NULL DEFAULT 'ouverte',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE question_tags (
  question_id INT UNSIGNED NOT NULL,
  skill_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (question_id, skill_id),
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE answers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  contenu TEXT NOT NULL,
  est_acceptee TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE answer_votes (
  answer_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (answer_id, user_id),
  FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  team_id INT UNSIGNED NULL,
  titre VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  statut ENUM('idee','en_cours','termine') NOT NULL DEFAULT 'idee',
  lien_git VARCHAR(255) NULL,
  lien_demo VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_skills (
  project_id INT UNSIGNED NOT NULL,
  skill_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (project_id, skill_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE teams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  description TEXT NULL,
  project_id INT UNSIGNED NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE team_members (
  team_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('chef','membre') NOT NULL DEFAULT 'membre',
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (team_id, user_id),
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE projects
  ADD FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL;

CREATE TABLE collab_needs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NULL,
  titre VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  role_recherche VARCHAR(100) NOT NULL,
  niveau_min TINYINT UNSIGNED NOT NULL DEFAULT 1,
  disponibilite ENUM('disponible','indifferent') NOT NULL DEFAULT 'disponible',
  statut ENUM('ouvert','en_cours','pourvu','clos') NOT NULL DEFAULT 'ouvert',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE need_skills (
  need_id INT UNSIGNED NOT NULL,
  skill_id INT UNSIGNED NOT NULL,
  type ENUM('requise','souhaitee') NOT NULL DEFAULT 'requise',
  niveau_min TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (need_id, skill_id),
  FOREIGN KEY (need_id) REFERENCES collab_needs(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE collab_proposals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  need_id INT UNSIGNED NOT NULL,
  developer_id INT UNSIGNED NOT NULL,
  message TEXT NULL,
  score_final TINYINT UNSIGNED NULL,
  statut ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (need_id, developer_id),
  FOREIGN KEY (need_id) REFERENCES collab_needs(id) ON DELETE CASCADE,
  FOREIGN KEY (developer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ai_analyses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('extraction_besoin','matching') NOT NULL,
  sujet_id INT UNSIGNED NULL,
  input_hash CHAR(64) NOT NULL,
  resultat_json LONGTEXT NOT NULL,
  modele VARCHAR(100) NULL,
  duree_ms INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cache (type, sujet_id, input_hash)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('nouvelle_reponse','proposition_recue','proposition_acceptee','proposition_refusee') NOT NULL,
  contenu VARCHAR(255) NOT NULL,
  lien VARCHAR(255) NULL,
  lu TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_non_lues (user_id, lu),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
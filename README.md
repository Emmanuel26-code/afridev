# AFRIDEV

Plateforme d'échange et de collaboration pour développeurs, avec **CODEV** :
le matching de collaborateurs assisté par l'IA.

Équipe **SHINOBI.3** : Emmanuel Kablan (chef d'équipe), Michée Koffi, Enock Kakou.

## Technologies
PHP (MVC léger), MySQL (PDO), HTML/CSS/JavaScript, Bootstrap 5.
Hébergement : DataCloud de Systalink.

## Installation en local
1. Cloner le dépôt : `git clone <url-du-depot>`
2. Copier `.env.example` en `.env` et renseigner les valeurs
3. Créer la base `afridev` et importer `database/schema.sql` puis `database/seed.sql`
4. Lancer le serveur : `php -S localhost:8000 -t public`

## Structure du projet
- `public/` : point d'entrée, CSS, JS, images
- `src/Controllers/`, `src/Services/`, `src/Models/` : code PHP
- `templates/` : vues HTML
- `config/` : configuration (sans secrets)
- `database/` : schéma et données de démonstration
- `docs/` : documentation
- `tests/` : scénarios de test

## Conventions Git
- `main` : version stable et déployée, personne n'y pousse directement
- `develop` : branche d'intégration
- `feature/<ID>-<nom>` : une branche par fonctionnalité (ex. `feature/F-403-matching`)
- Chaque fusion se fait par pull request relue par un autre membre
- Commits avec l'ID de la fonctionnalité : `F-403 : ajoute le score algorithmique`
- Jamais de secret dans le dépôt : seul `.env.example` est commité
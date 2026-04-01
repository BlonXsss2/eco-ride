# EcoRide - Plateforme de Covoiturage Ecologique

Projet de covoiturage ecologique developpe en PHP.

## Technologies utilisees

- PHP 8+ avec PDO
- MySQL
- Bootstrap 5
- Chart.js (dashboard admin)

## Structure du projet

```
eco-ride/
├── public/           # Fichiers accessibles (pages PHP)
│   ├── css/          # Feuilles de style
│   ├── admin/        # Dashboard administrateur
│   └── employee/     # Dashboard employe
├── src/              # Code source PHP
│   ├── config/       # Configuration (BDD, session)
│   └── models/       # Modeles (User, Carpool, Booking, Vehicle)
└── database/
    └── schema.sql    # Schema de la base de donnees
```

## Installation

1. Importer `database/schema.sql` dans MySQL
2. Configurer la connexion dans `src/config/database.php`
3. Lancer le serveur PHP :
   ```
   php -S localhost:8000 -t public
   ```
4. Ouvrir `http://localhost:8000`

## Comptes de test

| Role     | Email              | Mot de passe |
|----------|--------------------|--------------|
| Admin    | admin@ecoride.com  | password     |
| Employe  | emp@ecoride.com    | password     |
| Utilisateur | alice@example.com | password  |

## Fonctionnalites

- Inscription / Connexion avec 20 credits
- Recherche de covoiturages avec filtres
- Reservation (1 credit par place)
- Gestion du profil et des vehicules
- Dashboard employe (validation des avis)
- Dashboard admin (graphiques, gestion utilisateurs)

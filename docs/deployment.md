# Documentation de Deploiement - EcoRide

## Architecture de deploiement

- **Application** : Fly.io (PHP 8.2 + Apache)
- **Base de donnees** : Railway (MySQL)
- **Region** : Frankfurt (fra)

---

## Pre-requis

1. Un compte [Fly.io](https://fly.io)
2. Un compte [Railway](https://railway.app)
3. Fly CLI installe : `powershell -Command "iwr https://fly.io/install.ps1 -useb | iex"`
4. Git installe

---

## Etape 1 : Configurer la base de donnees (Railway)

### 1.1 Creer le projet
1. Se connecter a Railway (https://railway.app)
2. Cliquer sur « New Project »
3. Choisir « Provision MySQL »
4. Attendre que l'instance soit creee

### 1.2 Recuperer les identifiants
Dans l'onglet « Variables » du service MySQL, noter :
- **MYSQL_HOST** : ex. `metro.proxy.rlwy.net`
- **MYSQL_PORT** : ex. `56691`
- **MYSQL_DATABASE** : ex. `railway`
- **MYSQL_USER** : ex. `root`
- **MYSQL_PASSWORD** : ex. `votre_mot_de_passe`

### 1.3 Importer le schema
Se connecter avec un client MySQL (phpMyAdmin, DBeaver, ou ligne de commande) :

```bash
mysql -h metro.proxy.rlwy.net -P 56691 -u root -p railway < database/schema.sql
```

Puis importer les donnees de test :
```bash
mysql -h metro.proxy.rlwy.net -P 56691 -u root -p railway < database/seeds.sql
```

---

## Etape 2 : Configurer Fly.io

### 2.1 Se connecter
```bash
fly auth login
```

### 2.2 Creer l'application
```bash
fly apps create eco-ride
```

### 2.3 Configurer les variables d'environnement
```bash
fly secrets set DB_HOST=metro.proxy.rlwy.net -a eco-ride
fly secrets set DB_PORT=56691 -a eco-ride
fly secrets set DB_NAME=railway -a eco-ride
fly secrets set DB_USER=root -a eco-ride
fly secrets set DB_PASS=votre_mot_de_passe -a eco-ride
```

---

## Etape 3 : Deployer

### 3.1 Fichier fly.toml
Le fichier `fly.toml` a la racine configure le deploiement :

```toml
app = 'eco-ride'
primary_region = 'fra'

[build]
  dockerfile = 'Dockerfile'

[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = 'stop'
  auto_start_machines = true
  min_machines_running = 0
  processes = ['app']

[[vm]]
  memory = '1gb'
  cpu_kind = 'shared'
  cpus = 1
```

### 3.2 Dockerfile
Le `Dockerfile` utilise l'image PHP 8.2 avec Apache :

```dockerfile
FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql
RUN a2enmod rewrite

# Fly.io utilise le port 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80>/:8080>/g' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/
WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 8080
```

### 3.3 Lancer le deploiement
```bash
cd eco-ride
fly deploy
```

Le deploiement prend environ 2 minutes. A la fin, l'URL sera affichee : `https://eco-ride.fly.dev`

---

## Etape 4 : Verifier le deploiement

1. Ouvrir https://eco-ride.fly.dev dans le navigateur
2. Tester la connexion avec un compte de test :
   - Email : `alice@example.com`
   - Mot de passe : `password`
3. Tester la recherche de covoiturages
4. Tester la reservation

---

## Variables d'environnement

| Variable | Description | Exemple |
|----------|-------------|---------|
| DB_HOST | Hote de la base de donnees | metro.proxy.rlwy.net |
| DB_PORT | Port MySQL | 56691 |
| DB_NAME | Nom de la base de donnees | railway |
| DB_USER | Utilisateur MySQL | root |
| DB_PASS | Mot de passe MySQL | (secret) |

---

## Commandes utiles

### Voir les logs
```bash
fly logs -a eco-ride
```

### Acceder au serveur en SSH
```bash
fly ssh console -a eco-ride
```

### Redeployer apres modifications
```bash
fly deploy
```

### Voir le statut
```bash
fly status -a eco-ride
```

---

## Developpement local avec Docker

Pour lancer le projet en local avec Docker Compose :

```bash
docker-compose up -d
```

L'application sera disponible sur http://localhost:8080

Voir le fichier `docker-compose.yml` pour la configuration complete.

---

## Workflow de deploiement

1. Modifier le code en local
2. Tester en local (XAMPP ou Docker)
3. Commiter les changements : `git add . && git commit -m "description"`
4. Pousser sur GitHub : `git push origin main`
5. Deployer sur Fly.io : `fly deploy`

---

## Problemes courants

### Erreur de connexion a la base de donnees
- Verifier que les variables d'environnement sont bien definies (`fly secrets list`)
- Verifier que l'instance Railway est active

### Page blanche ou erreur 500
- Consulter les logs : `fly logs -a eco-ride`
- Verifier les erreurs PHP dans les logs Apache

### Base de donnees vide apres expiration Railway
- Reimporter le schema : `mysql -h HOST -P PORT -u root -p DB < database/schema.sql`
- Reimporter les donnees : `mysql -h HOST -P PORT -u root -p DB < database/seeds.sql`

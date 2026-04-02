# Documentation Securite - EcoRide

## Introduction

Ce document decrit les mesures de securite mises en place dans l'application EcoRide pour proteger les utilisateurs et leurs donnees.

---

## 1. Prevention des injections SQL (SQLi)

### Probleme
Une injection SQL permet a un attaquant d'executer des requetes SQL malveillantes via les champs de saisie utilisateur.

### Solution : PDO avec requetes preparees
Toutes les requetes vers la base de donnees utilisent PDO avec des parametres lies (prepared statements).

**Exemple dans `src/models/User.php` :**
```php
$sql = "SELECT * FROM users WHERE email = :email";
$stmt = $this->db->prepare($sql);
$stmt->execute([':email' => $email]);
```

On ne concatene **jamais** les variables directement dans les requetes SQL.

---

## 2. Prevention des attaques XSS (Cross-Site Scripting)

### Probleme
Une attaque XSS injecte du code JavaScript malveillant dans les pages web vues par d'autres utilisateurs.

### Solution : htmlspecialchars()
Toutes les donnees affichees dans les pages HTML sont echappees avec `htmlspecialchars()`.

**Exemple dans les vues :**
```php
<p><?php echo htmlspecialchars($user['pseudo']); ?></p>
<input value="<?php echo htmlspecialchars($user['email']); ?>">
```

Cela convertit les caracteres speciaux (`<`, `>`, `"`, `&`) en entites HTML inoffensives.

---

## 3. Protection CSRF (Cross-Site Request Forgery)

### Probleme
Une attaque CSRF force un utilisateur connecte a executer des actions a son insu (ex: supprimer un vehicule).

### Solution : Jetons CSRF
Chaque formulaire inclut un jeton CSRF unique lie a la session de l'utilisateur.

**Generation du jeton (dans les pages avec formulaires) :**
```php
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

**Inclusion dans le formulaire :**
```html
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
```

**Verification cote serveur :**
```php
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Requete invalide.';
    header('Location: profile.php');
    exit;
}
```

---

## 4. Hachage des mots de passe

### Probleme
Si la base de donnees est compromise, les mots de passe en clair seraient visibles.

### Solution : password_hash() et password_verify()
Les mots de passe sont haches avec l'algorithme bcrypt avant le stockage.

**A l'inscription :**
```php
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
```

**A la connexion :**
```php
if (password_verify($password, $user['password'])) {
    // connexion reussie
}
```

`PASSWORD_DEFAULT` utilise bcrypt avec un cout adaptatif. Le sel est genere automatiquement.

---

## 5. Securite des sessions

### Configuration des sessions
Le fichier `src/config/session.php` configure les sessions de maniere securisee :

- **session.cookie_httponly** : Empeche JavaScript d'acceder au cookie de session
- **session.cookie_secure** : Le cookie est envoye uniquement en HTTPS (en production)
- **session.use_strict_mode** : Refuse les ID de session non generes par le serveur
- **session.cookie_samesite** : Protege contre les attaques CSRF sur les cookies

### Gestion des sessions
- Verification d'authentification sur chaque page protegee
- Fonction `isLoggedIn()` pour verifier l'etat de connexion
- Destruction complete de la session a la deconnexion

**Exemple de protection d'une page :**
```php
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Veuillez vous connecter.';
    header('Location: login.php');
    exit;
}
```

---

## 6. Validation des entrees

### Cote serveur
- Verification du type de donnees (int, string, email)
- Limitation de la longueur des champs (`maxlength`, `strlen()`)
- Verification des valeurs autorisees (listes blanches)
- Utilisation de `trim()` pour nettoyer les espaces

**Exemple :**
```php
$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email invalide';
}
```

### Cote client
- Attributs HTML5 : `required`, `minlength`, `maxlength`, `type="email"`
- Mais la validation cote serveur est **toujours** presente (le client peut etre contourne)

---

## 7. Controle d'acces

### Roles utilisateurs
- **user** : Acces aux fonctionnalites de base (profil, recherche, reservation)
- **employee** : Acces a la moderation des avis
- **admin** : Acces a l'administration complete

### Verification des permissions
Chaque page verouille verifie le role de l'utilisateur :

```php
if (getUserRole() !== 'admin') {
    $_SESSION['error'] = 'Acces refuse.';
    header('Location: ../index.php');
    exit;
}
```

### Verification de propriete
Pour les vehicules et reservations, on verifie que l'utilisateur est bien le proprietaire :
```php
$vehicle = $vehicleModel->getVehicleById($vehicleId);
if (!$vehicle || $vehicle['user_id'] != $userId) {
    $_SESSION['error'] = 'Vehicule introuvable.';
    header('Location: profile.php');
    exit;
}
```

---

## 8. Gestion des erreurs

- Les erreurs techniques sont ecrites dans les logs serveur avec `error_log()`
- Les messages d'erreur affiches a l'utilisateur ne revelent pas de details techniques
- Les exceptions PDO sont attrapees et traitees proprement

**Exemple :**
```php
try {
    // operation en base de donnees
} catch (PDOException $e) {
    error_log('Erreur: ' . $e->getMessage());
    $_SESSION['error'] = 'Une erreur est survenue.';
    header('Location: profile.php');
    exit;
}
```

---

## Resume des mesures

| Menace | Solution | Implementation |
|--------|----------|---------------|
| Injection SQL | PDO prepared statements | Tous les modeles |
| XSS | htmlspecialchars() | Toutes les vues |
| CSRF | Jetons aleatoires | Tous les formulaires POST |
| Mots de passe | bcrypt (password_hash) | Inscription / Connexion |
| Sessions | Configuration securisee | session.php |
| Acces non autorise | Verification des roles | Pages admin/employe |

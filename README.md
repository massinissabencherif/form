# Formulaire Sécurisé

Un formulaire web sécurisé avec authentification HTTP Basic et design Apple-inspired.

## 🚀 Installation

### Prérequis
- PHP 8.0+ avec extensions PDO et MySQL
- Serveur web (Apache/Nginx)
- Base de données MySQL/MariaDB

### Configuration

1. **Cloner le projet**
   ```bash
   git clone <votre-repo>
   cd formulaire-docker
   ```

2. **Configurer la base de données**
   - Créer une base de données MySQL
   - Modifier `config.php` avec vos paramètres :
     ```php
     define('DB_HOST', 'localhost');     // Votre host MySQL
     define('DB_NAME', 'mydatabase');    // Nom de votre base
     define('DB_USER', 'user');          // Votre utilisateur MySQL
     define('DB_PASS', 'userpassword');  // Votre mot de passe MySQL
     ```

3. **Configurer l'authentification**
   - Modifier `config.php` avec vos identifiants :
     ```php
     define('BASIC_USER', 'massi');              // Votre nom d'utilisateur
     define('BASIC_PASS', 'randomizerpassword'); // Votre mot de passe
     ```

4. **Déployer sur votre serveur web**
   - Copier le dossier `web/` dans votre répertoire web
   - Copier `config.php` dans le répertoire parent de `web/`
   - S'assurer que `config.php` n'est pas accessible publiquement

## 🔐 Authentification

- **URL** : Votre domaine
- **Utilisateur** : `massi` (configurable dans `config.php`)
- **Mot de passe** : `randomizerpassword` (configurable dans `config.php`)

## ✨ Fonctionnalités

- ✅ **Authentification HTTP Basic** sécurisée
- ✅ **Formulaire** avec validation des données
- ✅ **Validation email unique** (pas de doublons)
- ✅ **Design Apple-inspired** moderne et responsive
- ✅ **Sécurité** : en-têtes de sécurité, validation des entrées
- ✅ **Liste des utilisateurs** récents
- ✅ **Responsive design** pour tous les appareils

## 🎨 Design

Le projet utilise un design inspiré d'Apple avec :
- Typographie système moderne
- Effets de transparence et de flou
- Animations fluides et micro-interactions
- Palette de couleurs Apple (#007AFF, #1D1D1F, #F5F5F7)
- Responsive design mobile-first

## 📁 Structure des fichiers

```
formulaire-docker/
├── config.php          # Configuration (hors web)
├── web/
│   ├── index.php       # Application principale
│   └── style.css       # Styles CSS
└── README.md           # Ce fichier
```

## 🔧 Configuration avancée

### Environnement
- `APP_ENV = 'dev'` : Mode développement (affiche les erreurs)
- `APP_ENV = 'prod'` : Mode production (masque les erreurs)

### Base de données
La table `utilisateurs` est créée automatiquement avec :
- `id` : Identifiant unique auto-incrémenté
- `nom` : Nom de l'utilisateur (VARCHAR 100)
- `email` : Email unique (VARCHAR 190)
- `created_at` : Date de création

## 🚨 Sécurité

- Authentification HTTP Basic
- Validation des entrées utilisateur
- Protection contre les injections SQL (PDO prepared statements)
- En-têtes de sécurité HTTP
- Validation email unique
- Échappement des sorties HTML

## 📱 Responsive

Le design s'adapte automatiquement à :
- Mobile (320px+)
- Tablette (768px+)
- Desktop (1024px+)

## 🐛 Dépannage

### Erreur de connexion à la base
- Vérifier les paramètres dans `config.php`
- S'assurer que MySQL est démarré
- Vérifier les permissions utilisateur

### Erreur d'authentification
- Vérifier les identifiants dans `config.php`
- S'assurer que l'authentification HTTP Basic est activée

### Problème de style
- Vérifier que `style.css` est accessible
- Vérifier les permissions des fichiers

## 📄 Licence

Ce projet est fourni à des fins éducatives et de démonstration.

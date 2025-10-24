# Laravel Release Manager - Esempi Pratici

Questa guida mostra esempi pratici di utilizzo del package in vari scenari.

## Scenario 1: Nuova Laravel Application

Hai appena creato una nuova applicazione Laravel e vuoi configurare tutto da zero.

```bash
# 1. Creare nuova Laravel app
laravel new my-awesome-app
cd my-awesome-app

# 2. Installare il package
composer require --dev alegiac/laravel-release-manager

# 3. Setup completo con GitHub
php artisan release:setup \
    --version=0.1.0 \
    --create-repo \
    --private \
    --branches \
    --push

# Output:
# 🔧 Setting up Release Manager
# Git repository not initialized
# Initialize git repository? (yes/no) [yes]: yes
# ✓ Git repository initialized
# ✓ CHANGELOG.md created
# ✓ Git tag created
# ✓ Release scripts published
# ✓ Documentation published
# 📋 Setting up standard branches...
# ✓ Branch 'main' created
# ✓ Branch 'develop' created
# ✓ Branch 'stage' created
# 🔗 Creating repository...
# Repository name [my-awesome-app]: 
# Platform [GitHub]:
# ✓ GitHub repository created: git@github.com:username/my-awesome-app.git
# 📤 Pushing to remote...
# ✓ Branch 'main' pushed
# ✓ Branch 'develop' pushed
# ✓ Branch 'stage' pushed
# ✓ Tags pushed
# 🎉 Release System initialized successfully!
```

## Scenario 2: Progetto Esistente con Git già Inizializzato

Hai un progetto esistente con git già configurato.

```bash
# 1. Installare il package
composer require --dev alegiac/laravel-release-manager

# 2. Setup semplice
php artisan release:setup --version=1.0.0

# 3. Link a repository esistente (se non già configurato)
git remote add origin git@github.com:username/existing-project.git

# 4. Setup branches
php artisan release:setup --branches --force

# 5. Push
git push -u origin main
git push origin develop stage
git push --tags
```

## Scenario 3: Progetto con Repository Bitbucket

```bash
# 1. Installare il package
composer require --dev alegiac/laravel-release-manager

# 2. Setup con Bitbucket
php artisan release:setup \
    --version=1.0.0 \
    --repo=git@bitbucket.org:myworkspace/my-project.git \
    --branches

# 3. Push manuale
git push -u origin main
git push origin develop stage
git push --tags
```

## Scenario 4: Package Laravel (Come i Tuoi Package)

```bash
# 1. Creare directory package
mkdir packages/my-awesome-package
cd packages/my-awesome-package

# 2. Creare composer.json
cat > composer.json << 'EOF'
{
    "name": "alegiac/my-awesome-package",
    "require": {
        "php": ">=8.2",
        "illuminate/support": "^10.0|^11.0|^12.0"
    }
}
EOF

# 3. Installare release manager
composer require --dev alegiac/laravel-release-manager

# 4. Setup completo
php artisan release:setup \
    --version=0.1.0 \
    --create-repo \
    --branches

# 5. Sviluppare
git commit -m "feat: initial package structure"
git commit -m "feat: add main functionality"
git commit -m "test: add comprehensive tests"

# 6. Creare release
php artisan release

# 7. Push
git push --follow-tags
```

## Scenario 5: Workflow Completo di Sviluppo

### Setup Iniziale

```bash
composer require --dev alegiac/laravel-release-manager
php artisan release:setup --version=0.1.0 --branches
```

### Feature Development

```bash
# Switch a develop
git checkout develop

# Crea feature
git commit -m "feat: add user authentication"
git commit -m "feat: add password reset"
git commit -m "test: add auth tests"
git commit -m "docs: update README"

# Merge in main
git checkout main
git merge develop

# Release (auto-detect: minor bump per le features)
php artisan release
# Output: New version: v0.2.0

# Push
git push --follow-tags
```

### Hotfix

```bash
# Su main
git checkout main

# Fix urgente
git commit -m "fix: resolve critical security issue"

# Release (auto-detect: patch bump)
php artisan release
# Output: New version: v0.2.1

# Push
git push --follow-tags

# Merge back in develop
git checkout develop
git merge main
git push
```

### Breaking Change

```bash
git checkout develop

# Breaking change
git commit -m "feat!: change API authentication method

BREAKING CHANGE: Old API keys will no longer work"

# Merge in main
git checkout main
git merge develop

# Release (auto-detect: major bump)
php artisan release
# Output: New version: v1.0.0

# Push
git push --follow-tags
```

## Scenario 6: Multi-Repository Setup

Hai più package e vuoi configurarli tutti:

```bash
# Script per setup di tutti i package
for package in packages/*; do
    if [ -d "$package" ]; then
        echo "Setting up $package..."
        cd "$package"
        
        # Installa release manager
        composer require --dev alegiac/laravel-release-manager
        
        # Setup con repo specifico
        PACKAGE_NAME=$(basename "$package")
        php artisan release:setup \
            --version=0.1.0 \
            --repo=git@github.com:username/$PACKAGE_NAME.git \
            --branches
        
        cd ../..
    fi
done
```

## Scenario 7: CI/CD Integration

### GitHub Actions

```yaml
name: Release

on:
  workflow_dispatch:
    inputs:
      release_type:
        description: 'Release type'
        required: true
        type: choice
        options:
          - auto
          - patch
          - minor
          - major

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
          
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          
      - name: Install dependencies
        run: composer install
        
      - name: Create release
        run: |
          php artisan release \
            --${{ github.event.inputs.release_type }} \
            --no-interaction
            
      - name: Push changes
        run: git push --follow-tags
```

## Scenario 8: Dry Run per Testing

Prima di creare una release vera, verifica cosa succederà:

```bash
# Dry run
php artisan release --dry-run

# Output:
# 🚀 Laravel Auto Release
# Latest tag: v1.0.0
# Detected MINOR changes (new features)
# New version: v1.1.0
# 
# ## [v1.1.0] - 2024-01-24
# ### Features
# - add payment gateway
# ### Bug Fixes
# - resolve timeout
# 
# DRY RUN - No changes were made
```

## Scenario 9: Configurazione Personalizzata

Pubblica e personalizza la configurazione:

```bash
# 1. Pubblica config
php artisan vendor:publish --tag=release-manager-config

# 2. Modifica config/release-manager.php
```

```php
return [
    'default_version' => '1.0.0',  // Cambia versione default
    'tag_prefix' => '',            // Rimuovi 'v' dai tag
    'commit_message' => 'release: {version}',  // Custom commit message
    
    'commit_types' => [
        'feat' => '🚀 Features',   // Aggiungi emoji (se vuoi)
        'fix' => '🐛 Bug Fixes',
        // ... altri tipi
    ],
];
```

## Tips & Tricks

### 1. Setup Rapido per Package

```bash
alias pkg-setup='php artisan release:setup --version=0.1.0 --branches'
pkg-setup
```

### 2. Verifica Prima di Pushare

```bash
# Dry run
php artisan release --dry-run

# Se ok, fai la release vera
php artisan release
```

### 3. Uso con Git Flow

```bash
# Develop
git checkout develop
git commit -m "feat: add feature"

# Merge in main per release
git checkout main
git merge develop --no-ff
php artisan release

# Push
git push --follow-tags
git checkout develop
git merge main
git push
```

### 4. Automazione Setup Multi-Package

```bash
#!/bin/bash
# setup-all-packages.sh

PACKAGES=(
    "hub-toolkit-connector-qapla"
    "hub-toolkit-connector-pgw-paypal"
    "laravel-release-manager"
)

for pkg in "${PACKAGES[@]}"; do
    echo "Setting up $pkg..."
    cd "packages/$pkg"
    
    composer require --dev alegiac/laravel-release-manager
    
    php artisan release:setup \
        --version=0.1.0 \
        --repo=git@github.com:yourname/$pkg.git \
        --branches \
        --push
    
    cd ../..
done
```

## Common Issues

### GitHub CLI not installed

```bash
# Install GitHub CLI
brew install gh  # macOS
# or visit https://cli.github.com/
```

### Repository already exists

```bash
# Use --repo invece di --create-repo
php artisan release:setup --repo=<existing-url>
```

### Need to change remote URL

```bash
git remote set-url origin <new-url>
```

## Links Utili

- [GitHub CLI](https://cli.github.com/)
- [Git Flow](https://nvie.com/posts/a-successful-git-branching-model/)
- [Conventional Commits](https://www.conventionalcommits.org/)


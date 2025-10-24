# Guida al Release Management

Questa guida spiega come creare release automatiche con changelog generato dai commit.

## Setup Iniziale

Prima di usare il sistema di release, devi inizializzarlo:

```bash
# Con Artisan (raccomandato)
php artisan release:setup

# Con script bash
./release-setup.sh

# Con versione custom
php artisan release:setup --version=1.0.0
./release-setup.sh 1.0.0
```

Questo creerà:
- CHANGELOG.md con versione iniziale
- Tag git iniziale (v0.0.1 di default)
- Scripts di release

## Script Disponibili

### 1. `release.sh` - Release Semplice

Script base che genera il changelog da tutti i commit.

**Uso:**
```bash
# Release patch (incrementa 1.0.0 -> 1.0.1)
./release.sh patch

# Release minor (incrementa 1.0.0 -> 1.1.0)
./release.sh minor

# Release major (incrementa 1.0.0 -> 2.0.0)
./release.sh major

# Default: patch
./release.sh
```

### 2. `release-conventional.sh` - Release con Conventional Commits

Script avanzato che supporta i Conventional Commits e auto-versioning.

**Uso:**
```bash
# Auto-detect del tipo di release dai commit
./release-conventional.sh auto

# Forza un tipo specifico
./release-conventional.sh patch
./release-conventional.sh minor
./release-conventional.sh major
```

## Conventional Commits

Per usare lo script avanzato, scrivi i commit seguendo questo formato:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Tipi di Commit

- **feat**: Nuova feature (incrementa MINOR)
- **fix**: Bug fix (incrementa PATCH)
- **docs**: Documentazione
- **style**: Formattazione, punto e virgola mancanti, ecc.
- **refactor**: Refactoring del codice
- **perf**: Miglioramenti di performance
- **test**: Aggiunta o modifica di test
- **build**: Modifiche al sistema di build
- **ci**: Modifiche alla CI/CD
- **chore**: Altre modifiche (aggiornamenti dipendenze, ecc.)

### Breaking Changes

Aggiungi `!` dopo il tipo per indicare un breaking change:

```bash
git commit -m "feat!: remove deprecated API endpoints"
```

I breaking changes incrementano la versione MAJOR.

## Esempi di Commit

### Feature
```bash
git commit -m "feat: add webhook signature verification"
git commit -m "feat(payments): add refund support"
```

### Bug Fix
```bash
git commit -m "fix: resolve null pointer in payment capture"
git commit -m "fix(orders): correct order status update"
```

### Breaking Change
```bash
git commit -m "feat!: change payment response structure"
```

### Documentation
```bash
git commit -m "docs: update README with new examples"
```

### Chore
```bash
git commit -m "chore: update dependencies"
git commit -m "chore(release): v1.2.3"
```

## Workflow Completo

### 1. Sviluppo

```bash
# Fai le tue modifiche
git add .
git commit -m "feat: add subscription support"

# Altri commit
git commit -m "fix: resolve capture timeout"
git commit -m "docs: update API documentation"
```

### 2. Creare una Release

**Opzione A: Auto-detect (Consigliato)**
```bash
./release-conventional.sh auto
```

Lo script analizzerà i commit e deciderà automaticamente:
- **MAJOR** se ci sono breaking changes (feat!, fix!, ecc.)
- **MINOR** se ci sono nuove feature (feat)
- **PATCH** se ci sono solo fix o altro

**Opzione B: Manuale**
```bash
./release-conventional.sh minor
```

### 3. Pubblicare

```bash
# Push del branch e del tag
git push origin develop
git push origin v1.2.3

# Oppure tutto insieme
git push --follow-tags
```

## Esempio di Output

Quando esegui `./release-conventional.sh auto`:

```
[INFO] Ultimo tag: v1.0.0
[INFO] Rilevate nuove feature - incremento MINOR
[INFO] Nuova versione: v1.1.0
[SECTION] Generazione changelog...
[INFO] Changelog aggiornato
[SECTION] Nuovo entry nel changelog:

## [v1.1.0] - 2024-01-15

### Features

- add webhook signature verification
- add subscription support

### Bug Fixes

- resolve null pointer in payment capture
- resolve capture timeout

### Documentation

- update README with new examples

Vuoi procedere con la release v1.1.0? (y/n)
```

## Changelog Generato

Il changelog viene automaticamente aggiornato in `CHANGELOG.md`:

```markdown
# Changelog

All notable changes to this project will be documented in this file.

## [v1.1.0] - 2024-01-15

### Features

- add webhook signature verification
- add subscription support

### Bug Fixes

- resolve null pointer in payment capture
- resolve capture timeout

### Documentation

- update README with new examples

## [v1.0.0] - 2024-01-01

### Features

- initial release
```

## Best Practices

### 1. Commit Piccoli e Frequenti

```bash
# Buono: commit atomici
git commit -m "feat: add payment capture"
git commit -m "test: add capture tests"
git commit -m "docs: document capture API"

# Evita: commit troppo grandi
git commit -m "feat: add all payment features and tests and docs"
```

### 2. Messaggi Descrittivi

```bash
# Buono
git commit -m "fix: resolve timeout in payment capture for large amounts"

# Evita
git commit -m "fix stuff"
```

### 3. Usa gli Scope

```bash
git commit -m "feat(payments): add capture method"
git commit -m "fix(orders): resolve status update"
git commit -m "docs(api): update endpoint documentation"
```

### 4. Breaking Changes Espliciti

```bash
# Se cambi un'API esistente
git commit -m "feat!: change payment response structure

BREAKING CHANGE: Payment response now returns 'data' instead of 'result'"
```

## Troubleshooting

### Script non eseguibile
```bash
chmod +x release.sh
chmod +x release-conventional.sh
```

### Repository non pulito
```bash
# Committa le modifiche
git add .
git commit -m "chore: prepare for release"

# Oppure stash
git stash
```

### Nessun commit dall'ultimo tag
Assicurati di aver fatto dei commit dopo l'ultimo tag:
```bash
git log v1.0.0..HEAD
```

### Tag già esistente
```bash
# Elimina il tag locale
git tag -d v1.0.0

# Elimina il tag remoto (se già pushato)
git push origin :refs/tags/v1.0.0
```

## Alias Git Utili

Aggiungi al tuo `~/.gitconfig`:

```ini
[alias]
    # Conventional commits
    feat = "!f() { git commit -m \"feat: $*\"; }; f"
    fix = "!f() { git commit -m \"fix: $*\"; }; f"
    docs = "!f() { git commit -m \"docs: $*\"; }; f"
    
    # Release
    release = "!bash release-conventional.sh auto"
    release-patch = "!bash release-conventional.sh patch"
    release-minor = "!bash release-conventional.sh minor"
    release-major = "!bash release-conventional.sh major"
```

Ora puoi usare:
```bash
git feat "add webhook support"
git fix "resolve capture issue"
git release
```

## Integrazione con Composer

Puoi anche aggiungere script composer:

```json
{
    "scripts": {
        "release": "bash release-conventional.sh auto",
        "release:patch": "bash release-conventional.sh patch",
        "release:minor": "bash release-conventional.sh minor",
        "release:major": "bash release-conventional.sh major"
    }
}
```

Uso:
```bash
composer release
composer release:minor
```

## Links Utili

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)


# Laravel Release Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alegiac/laravel-release-manager.svg?style=flat-square)](https://packagist.org/packages/alegiac/laravel-release-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/alegiac/laravel-release-manager.svg?style=flat-square)](https://packagist.org/packages/alegiac/laravel-release-manager)
[![Tests](https://github.com/alegiac/laravel-release-manager/actions/workflows/tests.yml/badge.svg)](https://github.com/alegiac/laravel-release-manager/actions/workflows/tests.yml)
[![License](https://img.shields.io/packagist/l/alegiac/laravel-release-manager.svg?style=flat-square)](https://packagist.org/packages/alegiac/laravel-release-manager)
[![PHP Version](https://img.shields.io/packagist/php-v/alegiac/laravel-release-manager.svg?style=flat-square)](https://packagist.org/packages/alegiac/laravel-release-manager)

Automated release management for Laravel packages with Conventional Commits support and automatic changelog generation.

## Features

- **Automatic Version Bumping** - Analyzes commits and determines version increment (major/minor/patch)
- **Conventional Commits** - Full support for conventional commit format
- **Changelog Generation** - Automatically generates and updates CHANGELOG.md
- **Breaking Changes Detection** - Automatically detects breaking changes
- **Git Tag Creation** - Creates annotated git tags
- **Release Notifications** - Send notifications to Telegram, Slack, Discord
- **AI Descriptions** - Generate human-readable descriptions of changes
- **Laravel Artisan Command** - Easy to use artisan command
- **Standalone Script** - Can also be used as a standalone bash script

## Quick Start

### Basic Setup

```bash
# 1. Install
composer require --dev alegiac/laravel-release-manager

# 2. Interactive setup (recommended for first time)
php artisan release:setup --interactive

# 3. Make changes and commit
git commit -m "feat: add awesome feature"

# 4. Create release
php artisan release

# 5. Push
git push --follow-tags
```

**Note:** Use `--interactive` for a guided setup that asks for:
- Initial version
- Branch setup
- Repository creation/linking
- Remote push preferences

### Complete Setup with Repository

```bash
# 1. Install
composer require --dev alegiac/laravel-release-manager

# 2. Full setup with GitHub repository creation
php artisan release:setup \
    --initial-version=1.0.0 \
    --create-repo \
    --branches \
    --push

# 3. Or link to existing repository
php artisan release:setup \
    --initial-version=1.0.0 \
    --repo=git@github.com:username/repo.git \
    --branches \
    --push
```

## Installation

Install via Composer:

```bash
composer require --dev alegiac/laravel-release-manager
```

Initialize the release system:

```bash
php artisan release:setup
```

This will:
- Create initial CHANGELOG.md
- Create initial git tag (v0.0.1 by default)
- Publish the release script
- Publish documentation

You can specify a custom initial version:

```bash
php artisan release:setup --initial-version=1.0.0
```

### Publishing Assets

You can publish assets individually:

```bash
# Publish configuration
php artisan vendor:publish --tag=release-manager-config

# Publish scripts
php artisan vendor:publish --tag=release-manager-scripts

# Publish documentation
php artisan vendor:publish --tag=release-manager-docs
```

Or publish everything:

```bash
php artisan vendor:publish --provider="Alegiac\\ReleaseManager\\ReleaseManagerServiceProvider"
```

## Usage

### Option 1: Laravel Artisan Command

```bash
# Auto-detect version bump from commits
php artisan release

# Force a specific version type
php artisan release --patch
php artisan release --minor
php artisan release --major

# Dry run (show what would happen without making changes)
php artisan release --dry-run
```

### Option 2: Standalone Script

```bash
# Auto-detect version bump
./release-conventional.sh auto

# Specific version bump
./release-conventional.sh patch
./release-conventional.sh minor
./release-conventional.sh major
```

## Conventional Commits

Write commits following this format:

```
<type>(<scope>): <subject>
```

### Commit Types

- **feat**: New feature (bumps MINOR version)
- **fix**: Bug fix (bumps PATCH version)
- **docs**: Documentation changes
- **style**: Code style changes
- **refactor**: Code refactoring
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **build**: Build system changes
- **ci**: CI/CD changes
- **chore**: Other changes

### Breaking Changes

Add `!` after the type to indicate a breaking change (bumps MAJOR version):

```bash
git commit -m "feat!: remove deprecated API endpoints"
```

## Examples

### Development Workflow

```bash
# Make changes and commit
git add .
git commit -m "feat: add payment gateway support"
git commit -m "fix: resolve timeout issue"
git commit -m "docs: update README"

# Create release
php artisan release

# Or use the script
./release-conventional.sh auto

# Push to repository
git push --follow-tags
```

### Example Output

```
[INFO] Last tag: v1.0.0
[INFO] Detected new features - bumping MINOR version
[INFO] New version: v1.1.0
[SECTION] Generating changelog...

## [v1.1.0] - 2024-01-24

### Features
- add payment gateway support

### Bug Fixes
- resolve timeout issue

### Documentation
- update README

Proceed with release v1.1.0? (y/n)
```

## Generated Changelog

The package automatically updates `CHANGELOG.md`:

```markdown
# Changelog

All notable changes to this project will be documented in this file.

## [v1.1.0] - 2024-01-24

### Features
- add payment gateway support

### Bug Fixes
- resolve timeout issue

### Documentation
- update README

## [v1.0.0] - 2024-01-01

### Features
- initial release
```

## Notifications

The package supports sending release notifications to external services like Telegram, Slack, and Discord.

### Setup Notifications

1. **Enable notifications** in your `.env`:
```bash
RELEASE_MANAGER_NOTIFICATIONS_ENABLED=true
RELEASE_MANAGER_NOTIFICATION_DRIVER=telegram
```

2. **Configure your preferred service**:

#### Telegram
```bash
RELEASE_MANAGER_TELEGRAM_ENABLED=true
RELEASE_MANAGER_TELEGRAM_BOT_TOKEN=your_bot_token
RELEASE_MANAGER_TELEGRAM_CHAT_ID=your_chat_id
```

#### Slack
```bash
RELEASE_MANAGER_SLACK_ENABLED=true
RELEASE_MANAGER_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
RELEASE_MANAGER_SLACK_CHANNEL=#releases
RELEASE_MANAGER_SLACK_USERNAME=Release Bot
```

#### Discord
```bash
RELEASE_MANAGER_DISCORD_ENABLED=true
RELEASE_MANAGER_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...
RELEASE_MANAGER_DISCORD_USERNAME=Release Bot
```

### Usage

Notifications are sent automatically after each release:

```bash
php artisan release
```

To skip notifications:
```bash
php artisan release --no-notify
```

### Notification Content

Notifications include:
- Release version and type (major/minor/patch)
- Number of commits
- Changelog summary
- Timestamp

## AI Descriptions

Generate human-readable descriptions of changes using AI to explain what changed, technical impacts, and user impacts.

### Setup AI Descriptions

1. **Enable AI descriptions** in your `.env`:
```bash
RELEASE_MANAGER_AI_DESCRIPTION_ENABLED=true
RELEASE_MANAGER_AI_PROVIDER=openai
```

2. **Configure your AI provider**:

#### OpenAI
```bash
OPENAI_API_KEY=your_openai_api_key
RELEASE_MANAGER_OPENAI_MODEL=gpt-3.5-turbo
RELEASE_MANAGER_OPENAI_MAX_TOKENS=1000
```

#### Anthropic (Claude)
```bash
ANTHROPIC_API_KEY=your_anthropic_api_key
RELEASE_MANAGER_ANTHROPIC_MODEL=claude-3-sonnet-20240229
RELEASE_MANAGER_ANTHROPIC_MAX_TOKENS=1000
```

#### Ollama (Local)
```bash
RELEASE_MANAGER_AI_PROVIDER=ollama
RELEASE_MANAGER_OLLAMA_BASE_URL=http://localhost:11434
RELEASE_MANAGER_OLLAMA_MODEL=llama2
```

### Usage

Generate AI descriptions with your release:

```bash
php artisan release --ai-description
```

**Example output:**
```
🤖 Generating AI description...
✓ AI description generated and added to changelog

## [v1.2.0] - 2024-01-24

### Features
- add two-factor authentication system
- add real-time dashboard updates

### Bug Fixes
- resolve session timeout on mobile
- fix payment processing error

### Performance
- optimize database queries
- add Redis caching

### AI Description

## Cosa è Cambiato

Questa release introduce un sistema di autenticazione a due fattori (2FA) per migliorare la sicurezza degli account utente, insieme a ottimizzazioni delle performance per le query del database.

## Impatto Lato Codice

- Nuovo modello `TwoFactorAuth` per gestire i codici 2FA
- Modifiche al controller `AuthController` per supportare la verifica 2FA
- Aggiunta di middleware per proteggere le rotte sensibili
- Nuove migrazioni per le tabelle 2FA

## Impatto Lato Sistema

- Miglioramento della sicurezza con autenticazione a due fattori
- Ottimizzazione delle query N+1 nel dashboard utenti
- Riduzione del tempo di caricamento del 40%
- Aggiunta di logging per eventi di sicurezza

## Impatto Lato Cliente

- Gli utenti potranno abilitare 2FA nelle impostazioni account
- Dashboard più veloce e reattivo
- Maggiore sicurezza per i dati personali
- Notifiche email per nuovi accessi sospetti
```

### Templates

Choose between two description templates:

- **Default**: Concise description (300 words max)
- **Detailed**: Comprehensive analysis (500 words max)

```bash
RELEASE_MANAGER_AI_TEMPLATE=detailed
```

## Configuration

The package works out of the box, but you can customize behavior by modifying the published script.

## Best Practices

### 1. Use Descriptive Commit Messages

```bash
# Good
git commit -m "feat(auth): add two-factor authentication"
git commit -m "fix(api): resolve null pointer in payment handler"

# Avoid
git commit -m "fix stuff"
git commit -m "updates"
```

### 2. Use Scopes for Better Organization

```bash
git commit -m "feat(api): add new endpoint"
git commit -m "fix(ui): correct button alignment"
git commit -m "docs(readme): add installation guide"
```

### 3. Mark Breaking Changes Explicitly

```bash
git commit -m "feat!: change response structure

BREAKING CHANGE: API now returns 'data' object instead of direct response"
```

## Artisan Commands

### Setup Command

```bash
php artisan release:setup [options]
```

**Options:**
- `--initial-version=X.Y.Z` - Specify initial version (default: 0.0.1)
- `--interactive` - Interactive setup mode (asks for all options)
- `--repo=URL` - Link to existing repository
- `--create-repo` - Create new repository on GitHub (requires GitHub CLI)
- `--workspace=NAME` - Bitbucket workspace for repo creation
- `--private` - Make repository private (default: public)
- `--branches` - Setup standard branches (main, develop, stage)
- `--push` - Push branches and tags to remote
- `--force` - Overwrite existing setup

**Examples:**

```bash
# Basic setup with default version (v0.0.1)
php artisan release:setup

# Interactive setup (recommended for first-time users)
php artisan release:setup --interactive

# Setup with custom version
php artisan release:setup --initial-version=1.0.0

# Setup and link to existing GitHub repository
php artisan release:setup \
    --initial-version=1.0.0 \
    --repo=git@github.com:username/my-app.git

# Complete setup: create GitHub repo + branches + push
php artisan release:setup \
    --initial-version=1.0.0 \
    --create-repo \
    --branches \
    --push

# Setup with Bitbucket
php artisan release:setup \
    --initial-version=1.0.0 \
    --repo=git@bitbucket.org:workspace/repo.git \
    --branches

# Setup private repository
php artisan release:setup \
    --create-repo \
    --private \
    --branches

# Reinitialize (force overwrite)
php artisan release:setup --force --initial-version=2.0.0
```

**What it does:**

1. Initializes git repository (if not already initialized)
2. Creates CHANGELOG.md with initial version
3. Creates initial git tag (e.g., v0.0.1)
4. Optionally creates repository on GitHub/Bitbucket
5. Optionally links to existing repository
6. Optionally creates standard branches (main, develop, stage)
7. Optionally pushes everything to remote
8. Publishes release scripts and documentation

### Release Command

```bash
php artisan release [--patch] [--minor] [--major] [--dry-run] [--no-confirm] [--no-notify] [--ai-description]
```

Options:
- `--patch` - Force patch version bump (1.0.0 -> 1.0.1)
- `--minor` - Force minor version bump (1.0.0 -> 1.1.0)
- `--major` - Force major version bump (1.0.0 -> 2.0.0)
- `--dry-run` - Show what would happen without making changes
- `--no-confirm` - Run without confirmation prompts
- `--no-notify` - Skip sending notifications
- `--ai-description` - Generate human-readable AI description of changes

## Package Structure

The package follows Laravel's standard package structure:

```
laravel-release-manager/
├── .github/
│   └── workflows/
│       └── tests.yml              # CI/CD workflow
├── config/
│   └── release-manager.php        # Configuration file
├── scripts/
│   ├── release-conventional.sh    # Main release script
│   └── release-setup.sh           # Setup script
├── src/
│   ├── Commands/
│   │   ├── ReleaseCommand.php     # php artisan release
│   │   └── ReleaseSetupCommand.php # php artisan release:setup
│   ├── Services/
│   │   └── ReleaseManager.php     # Core logic
│   └── ReleaseManagerServiceProvider.php
├── stubs/
│   ├── CHANGELOG.md               # CHANGELOG template
│   └── RELEASING.md               # Documentation template
├── tests/
│   ├── Feature/                   # Feature tests
│   ├── Unit/                      # Unit tests
│   ├── Pest.php                   # Pest configuration
│   └── TestCase.php               # Base test case
├── .gitignore
├── CHANGELOG.md                   # Package changelog
├── composer.json                  # Package definition
├── CONTRIBUTING.md                # Contribution guidelines
├── LICENSE.md                     # MIT License
├── phpstan.neon                   # Static analysis config
├── phpunit.xml                    # PHPUnit configuration
└── README.md                      # This file
```

## Requirements

- PHP >= 8.0
- Laravel >= 8.0
- Git

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

- [Alessandro Giacomella](https://github.com/alegiac)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Links

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)


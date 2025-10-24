# Laravel Release Manager

Automated release management for Laravel packages with Conventional Commits support and automatic changelog generation.

## Features

- **Automatic Version Bumping** - Analyzes commits and determines version increment (major/minor/patch)
- **Conventional Commits** - Full support for conventional commit format
- **Changelog Generation** - Automatically generates and updates CHANGELOG.md
- **Breaking Changes Detection** - Automatically detects breaking changes
- **Git Tag Creation** - Creates annotated git tags
- **Laravel Artisan Command** - Easy to use artisan command
- **Standalone Script** - Can also be used as a standalone bash script

## Quick Start

### Basic Setup

```bash
# 1. Install
composer require --dev alegiac/laravel-release-manager

# 2. Simple setup
php artisan release:setup

# 3. Make changes and commit
git commit -m "feat: add awesome feature"

# 4. Create release
php artisan release

# 5. Push
git push --follow-tags
```

### Complete Setup with Repository

```bash
# 1. Install
composer require --dev alegiac/laravel-release-manager

# 2. Full setup with GitHub repository creation
php artisan release:setup \
    --version=1.0.0 \
    --create-repo \
    --branches \
    --push

# 3. Or link to existing repository
php artisan release:setup \
    --version=1.0.0 \
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
php artisan release:setup --version=1.0.0
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
- `--version=X.Y.Z` - Specify initial version (default: 0.0.1)
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

# Setup with custom version
php artisan release:setup --version=1.0.0

# Setup and link to existing GitHub repository
php artisan release:setup \
    --version=1.0.0 \
    --repo=git@github.com:username/my-app.git

# Complete setup: create GitHub repo + branches + push
php artisan release:setup \
    --version=1.0.0 \
    --create-repo \
    --branches \
    --push

# Setup with Bitbucket
php artisan release:setup \
    --version=1.0.0 \
    --repo=git@bitbucket.org:workspace/repo.git \
    --branches

# Setup private repository
php artisan release:setup \
    --create-repo \
    --private \
    --branches

# Reinitialize (force overwrite)
php artisan release:setup --force --version=2.0.0
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
php artisan release [--patch] [--minor] [--major] [--dry-run] [--no-interaction]
```

Options:
- `--patch` - Force patch version bump (1.0.0 -> 1.0.1)
- `--minor` - Force minor version bump (1.0.0 -> 1.1.0)
- `--major` - Force major version bump (1.0.0 -> 2.0.0)
- `--dry-run` - Show what would happen without making changes
- `--no-interaction` - Run without confirmation prompts

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

- PHP >= 8.1
- Laravel >= 10.0
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


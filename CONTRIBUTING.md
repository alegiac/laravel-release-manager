# Contributing

Contributions are welcome! Please follow these guidelines:

## Development Setup

```bash
# Clone the repository
git clone https://github.com/alegiac/laravel-release-manager.git
cd laravel-release-manager

# Install dependencies
composer install

# Run tests
composer test
```

## Pull Request Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Write or update tests as needed
5. Ensure tests pass (`composer test`)
6. Commit using Conventional Commits format
7. Push to your fork
8. Open a Pull Request

## Conventional Commits

This project uses Conventional Commits for consistent commit messages and automatic changelog generation.

### Commit Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat**: New feature
- **fix**: Bug fix
- **docs**: Documentation changes
- **style**: Code style changes (formatting, etc.)
- **refactor**: Code refactoring
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **build**: Build system changes
- **ci**: CI/CD changes
- **chore**: Other changes

### Examples

```bash
git commit -m "feat: add auto-tagging support"
git commit -m "fix: resolve changelog parsing issue"
git commit -m "docs: update installation guide"
git commit -m "feat(commands): add dry-run option"
```

### Breaking Changes

Use `!` after the type to indicate breaking changes:

```bash
git commit -m "feat!: change command signature"
```

## Coding Standards

- Follow PSR-12 coding standards
- Add PHPDoc blocks for all classes and public methods
- Write tests for new features
- Keep backward compatibility when possible

## Testing

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/pest tests/Unit/
./vendor/bin/pest tests/Feature/

# Run with coverage (if Xdebug is installed)
./vendor/bin/pest --coverage
```

## Code Quality

```bash
# Static analysis
composer phpstan
```

## Questions?

Feel free to open an issue for questions or discussions.


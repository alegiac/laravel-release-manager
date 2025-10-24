# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v1.0.0] - 2024-01-24

### Features

- Initial release
- Laravel Artisan commands (`php artisan release` and `php artisan release:setup`)
- Automatic version bumping based on Conventional Commits
- Automatic CHANGELOG.md generation
- Breaking changes detection
- Support for all conventional commit types (feat, fix, docs, etc.)
- Dry-run mode for testing
- Standalone bash scripts option
- Git repository initialization
- GitHub repository creation (with gh CLI)
- Bitbucket repository linking
- Standard branches setup (main, develop, stage)
- Automatic push to remote
- Configurable initial version (default: 0.0.1)
- Full documentation with examples
- Complete test suite


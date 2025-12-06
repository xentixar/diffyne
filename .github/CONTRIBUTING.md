# Contributing to Diffyne

Thank you for your interest in contributing to Diffyne! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md).

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

- **Clear title and description**
- **Steps to reproduce** the issue
- **Expected behavior** vs **actual behavior**
- **Environment details** (PHP version, Laravel version, Diffyne version, browser)
- **Code examples** or minimal reproduction case
- **Screenshots** if applicable

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **Clear title and description**
- **Use case**: Why is this enhancement useful?
- **Proposed solution**: How should it work?
- **Alternatives**: What alternatives have you considered?

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Make your changes** following our coding standards
3. **Add tests** for new functionality
4. **Ensure all tests pass** (`composer test`)
5. **Run PHPStan** (`composer analyse`) - must pass with 0 errors
6. **Check code formatting** (`composer format:test`) - must pass
7. **Update documentation** if needed
8. **Create a pull request** using our PR template

## Development Setup

### Prerequisites

- PHP 8.3 or higher
- Composer
- Node.js and npm (for frontend assets)

### Installation

```bash
# Clone the repository
git clone https://github.com/diffyne/diffyne.git
cd diffyne

# Install dependencies
composer install

# Install dev dependencies (already included)
```

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test -- --coverage

# Run specific test file
vendor/bin/pest tests/Feature/YourTest.php
```

### Code Quality Checks

```bash
# Run PHPStan (static analysis)
composer analyse

# Check code formatting
composer format:test

# Auto-fix formatting issues
composer format
```

## Coding Standards

### PHP Code Style

We use PHP CS Fixer with PSR-12 standards. The configuration is in `.php-cs-fixer.php`.

**Key guidelines:**
- Follow PSR-12 coding standard
- Use type hints wherever possible
- Add PHPDoc comments for public methods
- Keep methods focused and small
- Use meaningful variable and method names

### Type Safety

- **PHPStan Level 7**: All code must pass PHPStan level 7 with 0 errors
- **Type hints**: Use strict type hints for parameters and return types
- **Array types**: Always specify array value types in PHPDoc (`@var array<string, mixed>`)

### Testing

- **Write tests** for all new features and bug fixes
- **Use Pest** for testing (already configured)
- **Test coverage**: Aim for high test coverage, especially for critical paths
- **Test names**: Use descriptive test names that explain what is being tested

### Documentation

- **Update README.md** if adding new features
- **Add examples** in the [docs repository](https://github.com/diffyne/docs/tree/main/examples) for new features
- **Update feature docs** in the [docs repository](https://github.com/diffyne/docs/tree/main/features) if applicable
- **Add PHPDoc** comments for all public methods

## Commit Messages

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(component): add lazy loading support

Add #[Lazy] attribute to enable lazy component loading
with placeholder support.

Closes #123
```

```
fix(renderer): fix state normalization for nested arrays

The state normalization was not handling nested arrays
correctly, causing signature verification failures.

Fixes #456
```

## Pull Request Process

1. **Update your branch** with the latest `main` branch
2. **Ensure all checks pass**:
   - PHPStan analysis
   - Code formatting
   - All tests
3. **Fill out the PR template** completely
4. **Request review** from maintainers
5. **Address review feedback** promptly
6. **Wait for approval** before merging

## Project Structure

```
packages/diffyne/
├── src/              # Source code
├── tests/            # Test files
├── config/           # Configuration files
├── resources/        # Views and assets
└── stubs/            # Code generation stubs
```

## Questions?

- Open a [GitHub Discussion](https://github.com/diffyne/diffyne/discussions)
- Check existing [Issues](https://github.com/diffyne/diffyne/issues)
- Email: xentixar@gmail.com

Thank you for contributing to Diffyne! 🎉


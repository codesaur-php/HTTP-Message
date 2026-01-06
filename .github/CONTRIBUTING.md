# Contributing to codesaur/http-message

First of all, thank you for taking the time to contribute ❤️  
Contributions of any kind are welcome and greatly appreciated.

---

## Ways to Contribute

You can contribute to this project in several ways:

- 🐛 **Reporting bugs** - Help us identify and fix issues
- 💡 **Suggesting features** - Propose new features or improvements
- 📚 **Improving documentation** - Enhance docs, examples, or comments
- 🔧 **Submitting code** - Fix bugs or implement new features via pull requests
- ✅ **Writing tests** - Improve test coverage and quality

---

## Getting Started

### Prerequisites

- PHP **8.2.1** or higher
- Composer
- Git

### Setup

1. **Fork and clone the repository:**

```bash
git clone https://github.com/codesaur-php/HTTP-Message.git
cd HTTP-Message
```

2. **Install dependencies:**

```bash
composer install
```

3. **Verify the setup:**

```bash
composer test
```

All tests should pass before you start making changes.

---

## Development Workflow

1. **Create a branch** for your changes:

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

2. **Make your changes** following the coding guidelines below

3. **Run tests** to ensure everything works:

```bash
composer test
```

4. **Check code quality** and ensure all tests pass

5. **Commit your changes** with a clear message

6. **Push to your fork** and create a pull request

---

## Coding Guidelines

### Code Style

- Follow existing coding style and conventions in the codebase
- Maintain consistency with PSR standards (PSR-7, PSR-12)
- Use meaningful variable and method names
- Add comments for complex logic

### Code Quality

- Keep changes focused - **one feature or fix per PR**
- Write or update tests when applicable
- Ensure all tests pass before submitting
- Update documentation if behavior changes

### Testing

- Write tests for new features
- Update tests when fixing bugs
- Aim for high test coverage
- Ensure tests are clear and maintainable

---

## Pull Request Guidelines

### Before Submitting

- ✅ All tests pass locally
- ✅ Code follows project conventions
- ✅ Documentation is updated (if needed)
- ✅ Commit messages are clear and descriptive

### PR Requirements

- **One logical change per pull request** - Keep PRs focused and reviewable
- **Clear description** - Explain what and why, not just how
- **Reference issues** - Link to related issues if applicable
- **Test coverage** - Include tests for new features or bug fixes

### PR Title Format

Use clear, descriptive titles:
- `fix: resolve HTTP header parsing issue`
- `feat: add support for custom status codes`
- `docs: update API documentation`
- `test: add tests for Stream class`

---

## Commit Message Convention

Use clear and descriptive commit messages following conventional commits:

### Format

```
<type>: <subject>

[optional body]

[optional footer]
```

### Types

- `fix:` - Bug fixes
- `feat:` - New features
- `docs:` - Documentation changes
- `test:` - Test additions or modifications
- `refactor:` - Code refactoring
- `style:` - Code style changes (formatting, etc.)
- `chore:` - Maintenance tasks

### Examples

```
fix: resolve header case sensitivity issue
feat: add support for chunked transfer encoding
docs: update README with usage examples
test: add edge case tests for Uri class
refactor: simplify message body handling
```

---

## Documentation

If your change affects usage or the public API:

- 📝 Update `README.md` with new features or changed behavior
- 📚 Update files under `docs/` if they exist
- 📋 Update `CHANGELOG.md` for notable changes
- 💬 Add or update code comments for complex logic

---

## Code of Conduct

### Our Standards

- Be respectful and constructive in all interactions
- Welcome newcomers and help them get started
- Focus on what is best for the community
- Show empathy towards other community members

This project follows a friendly, inclusive open-source culture.  
We are committed to providing a welcoming and inspiring environment for all.

---

## Security Issues

**Please do not report security vulnerabilities through public GitHub issues.**

For security-related issues, please follow the instructions in  
[SECURITY.md](SECURITY.md).

---

## Questions?

If you have questions or need help:

- Open an issue for discussion
- Check existing issues and pull requests
- Review the documentation in `docs/` directory

---

Thank you for helping improve the **codesaur ecosystem** 🦖

Your contributions make this project better for everyone!

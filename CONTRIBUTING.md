# Contributing to Veldora

Thank you for your interest in contributing to **Veldora**! We welcome contributions of all kinds: bug fixes, new features, documentation improvements, UI components, and feedback.

---

## ✦ Code of Conduct

By participating in this project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md).

---

## ✦ Development Workflow

1. **Fork the Repository** to your own GitHub account.
2. **Clone your fork locally**:
   ```bash
   git clone https://github.com/<your-username>/veldora.git
   cd veldora
   ```
3. **Create a Feature Branch**:
   ```bash
   git checkout -b feat/your-feature-name
   ```
4. **Make Your Changes**:
   - Write clean, modern PHP (PHP 8.2+).
   - Follow PSR-12 coding standards.
   - Keep architectural simplicity in mind (see [ARCHITECTURE.md](ARCHITECTURE.md)).
5. **Run Static Analysis & Tests**:
   ```bash
   vendor/bin/phpstan analyse
   vendor/bin/phpunit
   ```
6. **Commit & Push**:
   ```bash
   git commit -m "feat: description of your feature"
   git push origin feat/your-feature-name
   ```
7. **Open a Pull Request**: Submit your PR targeting the `main` branch.

---

## ✦ Reporting Bugs

When filing an issue, please include:
- A clear, descriptive title.
- Steps to reproduce the behavior.
- Expected vs. actual results.
- Your PHP version and OS environment.

---

## ✦ License

Any contributions you submit will be licensed under the [MIT License](LICENSE).

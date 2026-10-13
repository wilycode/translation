# Contributing to WilyCode Translation

Thank you for helping improve WilyCode Translation. Please open an issue to discuss behavioral or public API changes before submitting a pull request.

## Requirements

- PHP 8.4 or newer
- Composer 2

## Development

```bash
composer install
composer validate --strict
composer test
```

Use `declare(strict_types=1);` in PHP sources. Keep public contracts framework-independent and avoid adding concrete languages, translation storage, or formatting resources to this package. Add PHPUnit coverage for behavior changes and update `README.md` and `CHANGELOG.md` when appropriate. Documentation is written in English.

## Pull requests

Keep changes focused, add a clear description, and confirm the tests pass. Avoid unrelated formatting changes or breaking existing interface signatures without a documented release plan.

By contributing, you agree that your contribution is licensed under the project's [MIT License](LICENSE).

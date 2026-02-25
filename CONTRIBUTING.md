# Contributing

Thanks for contributing to **waha-laravel-sdk**!

## Quick start

```bash
composer install
composer pint:test
composer stan
composer test
```

## Requirements

- PHP 8.1+ (CI currently uses PHP 8.4)
- Composer

## Project structure

- `src/` package source
- `tests/` PHPUnit tests (via Orchestra Testbench)
- `.github/workflows/` CI and automation workflows

## Development workflow

1. Fork the repository
2. Create a feature branch from `main`

Suggested branch naming:
- `feat/<short-description>`
- `fix/<short-description>`
- `chore/<short-description>`

3. Make your changes
4. Ensure everything passes locally:

```bash
composer pint:test
composer stan
composer test
```

5. Open a Pull Request (PR)

## Code style

- We use **Laravel Pint**. CI will fail if formatting is off.
- Prefer small, focused commits and PRs.

## Static analysis

- We use **PHPStan** (and Larastan). Keep types accurate and avoid suppressing issues unless absolutely necessary.

## Tests

- Add or update tests when behavior changes.
- Keep tests deterministic (no real network calls unless explicitly mocked).

## PR guidelines

A good PR includes:
- What changed
- Why it changed
- Any breaking changes (if applicable)
- How it was tested

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

# qiick Excubia

Health checks for Laravel apps.

qiick Excubia is the Laravel Health Check CLI of the [qiick](https://qiick.io) suite. Package: [`qiick/excubia-core`](https://github.com/qiick-io/excubia-core). The v0.1.0 scope is defined in `docs/SPEC.md` (approved 2026-10-31).

## Requirements

- PHP `^8.2`
- Optional: Laravel 11 / 12 / 13 for `php artisan excubia:check`

## Install

```bash
composer require qiick/excubia-core
```

CLI binary: `vendor/bin/excubia`.

```bash
vendor/bin/excubia check
# or, in a Laravel app with the package discovered:
php artisan excubia:check
```

## Links

- Source: https://github.com/qiick-io/excubia-core
- Issues: https://github.com/qiick-io/excubia-core/issues
- Spec: [`docs/SPEC.md`](docs/SPEC.md)

## License

MIT

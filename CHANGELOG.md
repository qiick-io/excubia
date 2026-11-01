# Changelog

All notable changes to **Excubia** (`qiick/excubia-core`) are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Complete public README with badges, install and usage, nine-check table, config pointer, and links (docs release targeting `0.1.1`).

### Changed

- Display name is **Excubia** alone (not “qiick Excubia”) in README, CLI banner/`--version`, docs headings, and user-facing strings. Package remains `qiick/excubia-core`.
- README GitHub header assets removed until Externa-style Excubia headers (product wordmark + tagline, no qiick wordmark in hero) are commissioned.

## [0.1.0] - 2026-11-01

### Added

- Initial `qiick/excubia-core` release: standalone `vendor/bin/excubia check` CLI, optional Artisan alias `excubia:check`, nine health checks, `table` / `json` / `markdown` formats, `excubia.json` config, and local response cache.

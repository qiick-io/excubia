# /code-comments

Rewrite documentation in professional English. Remove comments that are not English. Keep logic and user-facing strings unchanged.

## PHP

- Prefer PHPDoc blocks over inline comments.
- Document classes and public methods. Document protected or private methods only when they are non-trivial.
- Include the intent, array shapes, side effects, invariants, and `@throws` when the method throws.
- Do not restate a scalar type that the signature already has, unless PHPStan needs the extra shape.
- Keep one blank line at most between the docblock and the symbol.

## Shared

- Do not change runtime behavior.
- `docs/SPEC.md` and `docs/ARCHITECTURE.md` stay the source of truth for product behavior.

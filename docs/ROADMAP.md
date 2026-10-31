# qiick Excubia — Roadmap

After v0.1.0, through v1.0.0. Source of truth: [`docs/SPEC.md`](SPEC.md) and [`docs/ARCHITECTURE.md`](ARCHITECTURE.md), approved on 2026-10-31. Decisions D1–D13 and A1–A2 are final. Anything marked **[PROPOSAL]** in the spec is not approved.

v0.1.0 (CLI, checks, config, CI, Packagist) is specified there and is not repeated as implementation work in this document.

Versions below are the ones the spec assigns, plus v1.0.0 (stable / API freeze). Later work with no version number is listed with an empty milestone.

## v0.2.0 — web scanner

SPEC.md section 9. Decision D2: free zero-signup scanner at `qiick.io/excubia`, on the same core. Domain verification is not part of this milestone (SPEC section 9: Phase B only).

| Issue | Title |
| --- | --- |
| #7 | Add zero-signup web scanner page at qiick.io/excubia |
| #8 | Add web scanner backend on the shared core |
| #9 | Add web scanner abuse protections and legal pages |

#7 covers the page and in-browser parse (only package names and versions are sent). #8 is the Laravel endpoint (same core, queues, cache, rate limits). #9 is the abuse-protection and legal-page sentence in section 9 (per-IP rate limits, payload cap, cache TTL, no storage beyond that TTL, Impressum and privacy policy).

## v0.3.0 — SARIF + GitHub Action

Decision D6. JSON and Markdown ship in v0.1.0; SARIF is this version, with the GitHub Action for code scanning. This is not the v0.1.0 CI workflow or the `qiick-io/.github` reusable workflow.

| Issue | Title |
| --- | --- |
| #10 | Add SARIF report output |
| #11 | Add GitHub Action for code scanning |

The spec does not define the SARIF version, rule mapping, or the action's triggers, inputs, and permissions.

## v1.0.0 — stable / API freeze

SPEC.md and ARCHITECTURE.md assign no implementation tasks to this version. No issues.

## No milestone

The spec names the following and assigns no version. Each issue says so and is a `needs-decision` proposal, not a plan and not acceptance criteria.

| Issue | Title | Spec |
| --- | --- | --- |
| #12 | Opt-in AI summary using the user's own key | D4. User's own key. No metered qiick service. After the v0.1.0 `upgrade-plan` check. |
| #13 | Proposal: env-example-sync check | D8. Section 3. |
| #14 | Proposal: qiick-recommendations check | D8. Section 3. |
| #15 | Proposal: --strict for data-source failures | Section 4. Marked [PROPOSAL]. |
| #16 | Later: GitHub App | Section 2 non-goals. "Later, Pro/CI mode". Not D6. |
| #17 | Later: pull request comments | Section 2 non-goals. "Later, Pro/CI mode". |
| #18 | Later: scheduled multi-repo monitoring | Section 2 non-goals. "Later, Pro/CI mode". Not Phase B. |
| #19 | Later: badges | Section 2 non-goals. "Later, Pro/CI mode". |
| #20 | Phase B: uptime, connectors, and runtime probe | Section 1 and section 2. Roadmap Step 4. Not part of this spec. Includes multi-region probes and `qiick/excubia-probe`. Domain verification belongs here, not in v0.2.0. |
| #21 | yarn.lock and pnpm-lock.yaml support | Section 6. Non-goals for v0.1.0 only. No later version is assigned. |

## Not in this roadmap

- v0.1.0 implementation, including the Packagist `qiick` claim (D5) and the trademark search before the first tag (D13).
- Permanent non-goals in SPEC section 2: paid features, qiick Cloud, accounts, login, telemetry, writes or auto-fix in the scanned project, source-code analysis.
- ARCHITECTURE section 6 Dependabot entry, and later Externa packages reusing the shared CI workflow. The workflow itself is v0.1.0. Externa is not this repository.

## Duplicate

#6 has the same title as #7 and a placeholder body. #7 is the section 9 page issue. #6 is not roadmap work.

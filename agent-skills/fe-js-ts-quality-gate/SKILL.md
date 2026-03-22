---
name: fe-js-ts-quality-gate
description: Enforces ESLint, Prettier, and TypeScript compiler checks from the frontend package.json for JavaScript and TypeScript source. Use when creating or editing .js, .jsx, .ts, or .tsx files under fe/, when generating React or Vite frontend code in this repo, or when the user asks for lint-, format-, or tsc-compliant frontend changes.
---

# Frontend JS/TS Quality Gate

## Scope

Apply this skill whenever you **create or modify** any of:

- `.js`, `.jsx`, `.ts`, `.tsx`

under the frontend package that owns the tooling scripts. In this repository, that is **`fe/`** (see `fe/package.json`).

If the repo later adds other Node packages with their own `package.json`, use the **nearest** `package.json` to the changed files and the scripts defined there instead.

## Source Of Truth

Compliance means whatever is configured for:

| Concern    | How it is enforced (this repo)              | Defined in / via                          |
| ---------- | ------------------------------------------- | ----------------------------------------- |
| ESLint     | `pnpm run lint` → `eslint . --max-warnings=0` | `fe/package.json`, `fe/eslint.config.js` |
| Prettier   | `pnpm run format` → `prettier --check .`    | `fe/package.json`, Prettier config in `fe/` |
| TypeScript | `pnpm run typecheck` → `tsc -b --pretty false` | `fe/package.json`, `fe/tsconfig*.json`  |

Do **not** invent style or type rules that conflict with these tools. Prefer fixes in code over disabling rules, unless the user explicitly asks otherwise.

## Required Workflow

After implementing changes to `.js`, `.jsx`, `.ts`, or `.tsx` under `fe/src/` (or other frontend sources under `fe/`):

1. Run **typecheck** (catches `tsc` errors for TS and project references).
2. Run **lint** (ESLint must pass with zero warnings).
3. Run **format check** (Prettier must pass).

Run from **`fe/`**:

```bash
pnpm run typecheck
pnpm run lint
pnpm run format
```

4. If any command fails, fix the code (or config, only if the user requested it) and **rerun until all three pass**.
5. In your final response, state which commands you ran and that they passed (or what failed and the exact error if you could not run them).

For larger edits, you may use `pnpm run check` once the above are green to also run tests (`typecheck` + `lint` + `format` + `test`).

## Optional Autofix

If ESLint/Prettier report fixable issues, you may run:

```bash
pnpm run lint:fix
pnpm run format:fix
```

Then rerun **`pnpm run lint`** and **`pnpm run format`** (check mode) to confirm a clean state.

## Failure Handling

- If `pnpm` or dependencies are missing, say so and give the exact commands for the user to run locally after `pnpm install` in `fe/`.
- Do **not** claim ESLint/Prettier/tsc compliance unless the relevant scripts were executed successfully (or you clearly state they were not run).

## Relationship To Other Skills

- For PHP/Symfony code under `api/`, use the **`php-quality-gate`** skill instead.
- The older **`ts-tsx-eslint-prettier`** skill is style-focused; this skill **adds mandatory verification** via `fe/package.json` scripts for all listed extensions.

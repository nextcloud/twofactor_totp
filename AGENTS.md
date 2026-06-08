<!--
  - SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# AGENTS.md

This file provides guidance to AI coding assistants working with code in this repository.

## Commands

### Setup
```bash
composer install
npm ci
```

### JavaScript
See `package.json` scripts for all available commands (build, dev, watch, lint, stylelint, test, test:e2e, etc.).

### PHP
Available composer commands:
```bash
composer cs:check                # Check code style
composer cs:fix                  # Fix code style
composer lint                    # PHP syntax check
composer psalm                   # Run static analysis
composer test:unit               # Run unit tests
composer test:acceptance         # Run acceptance tests
```
See `composer.json` for all available commands.

## Architecture

### Stack
- **Backend**: PHP (see `appinfo/info.xml` for version requirements), Nextcloud app framework, `rullzer/easytotp` and `christian-riesen/base32` for TOTP (RFC 6238) implementation. Namespace: `OCA\TwoFactorTOTP\`.
- **Frontend**: Vue 2, Vuex, bundled with webpack into two separate entry points (settings, login-setup).

### PHP Backend (`lib/`)
Layered: Controllers → Services → DB Mappers.

- **`Provider/`** — `TotpProvider` implements the Nextcloud two-factor provider interfaces (`IProvider`, `IProvidesPersonalSettings`, `IDeactivatableByAdmin`). `AtLoginProvider` handles activation at login.
- **`Service/`** — Core TOTP logic (`Totp`, implementing `ITotp`): secret generation, storage, and OTP verification.
- **`Db/`** — Nextcloud `QBMapper`-based mapper and entity (`TotpSecret`, `TotpSecretMapper`) for per-user TOTP secrets.
- **`Controller/`** — Thin HTTP handlers for the settings API (enable/disable TOTP).
- **`Listener/`** — Event listeners for activity logging, two-factor registry updates, and user deletion cleanup.
- **`Activity/`** — Activity provider and setting for Nextcloud activity app integration.
- **`Command/`** — OCC console command (`CleanUp`).
- **`Event/`** — Custom domain events (`StateChanged`, `DisabledByAdmin`).
- **`Settings/`** — Personal settings section rendering.
- **`Migration/`** — Database migrations.

### JavaScript Frontend (`src/`)
Two independent webpack bundles, each mounted into a Nextcloud template.

- **`store.js`** — Vuex store for TOTP state; seeded via Nextcloud `InitialState`.
- **`state.js`** — State shape definitions.
- **`services/`** — JS services calling the PHP REST API.
- **`components/`** — Vue components for the personal settings page and login-time setup flow.

### Key Conventions
- **Registration**: `appinfo/info.xml` declares app metadata. `AppInfo/Application.php` registers event listeners and services via the Nextcloud bootstrap API (`IBootstrap`).
- **InitialState**: PHP controllers push data to the frontend via `IInitialStateService`; Vue components read it with `loadState()`.
- **Events**: State changes dispatch events from `lib/Event/`; listeners in `lib/Listener/` react to them for activity logging and registry updates.
- **REUSE & SPDX**: Every file requires an SPDX license header. **New files must use `AGPL-3.0-or-later`, never `AGPL-3.0-only`**. Header format:
  ```php
  /**
   * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
   * SPDX-License-Identifier: AGPL-3.0-or-later
   */
  ```

## Testing

### Unit Tests
Located in `tests/Unit/` with structure mirroring `lib/`.

#### Pattern
- Use **arrange-act-assert** structure with blank lines separating each phase (no literal comments)
- Mock dependencies via `$this->createMock(Interface::class)`
- Setup mocks in `setUp()` for common fixtures

#### Running Tests
```bash
composer test:unit                                                 # Run all unit tests
composer test:unit -- tests/Unit/Service/TotpTest.php             # Run specific file
composer test:unit -- --filter="TestClassName"                     # Run tests matching filter
```

### Acceptance Tests
Located in `tests/Acceptance/`.

```bash
composer test:acceptance                                           # Run all acceptance tests
composer test:acceptance -- --filter="TestClassName"               # Run tests matching filter
```

### JavaScript Tests
```bash
npm test                             # Jest unit tests
npm run test:e2e                     # Playwright end-to-end tests
```

## Git Workflow

Do NOT commit changes unless explicitly asked to do so.

After completing code changes:
1. Verify your work is complete and tests pass
2. Never push directly to `master` — always create a feature branch with a descriptive name (e.g. `fix/secret-cleanup`, `feat/backup-codes`).
3. Worktree branches must use descriptive feature-branch names, not generated names like `agent-xxxx`.
4. Make sure there is no trailing whitespace
5. Leave changes in working directory or staged (do not commit)
6. Provide a summary of what was changed and why
7. Suggest a commit message using Conventional Commits format

### Commit Message Format

All commits must include two trailers at the end:
1. Agent/model attribution: `Assisted-by: <AgentName>:<model-id>`
2. DCO sign-off: Use `git commit -s` to add automatically

When committing, use: `git commit -m "message" -s`

This ensures the sign-off includes your configured Git user email.

Example:
```
fix(totp): handle null return from preg_replace

- add explicit null check before string operations
- prevents type error on edge-case input

Assisted-by: Claude:claude-sonnet-4-6
Signed-off-by: Name <email>
```

### Styling

For all CSS colors, spacing, and dimensions, use the standard Nextcloud CSS variables. Do not leave magic numbers; use `calc(x * var(...))` when more specific control is needed.

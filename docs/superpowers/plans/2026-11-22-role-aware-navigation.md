# Role-aware navigation and workspace polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Align GrowPOS navigation with owner, cashier, and platform work while reducing duplicate actions and mobile navigation clutter.

**Architecture:** Keep the existing Blade/Tailwind workspace shell and shared visual tokens. Render a compact cashier shell, an owner/manager sidebar organized by work area, and a visually separate platform section. Keep all links aligned to existing route permissions; no transaction behavior or database model changes.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS 3, Alpine.js, PHPUnit, Node test runner.

**Spec:** docs/product-audit/05-information-architecture.md, docs/product-audit/07-interaction-specifications.md, and docs/product-audit/08-design-system-review.md.

## Global Constraints

- Use one primary POS entry point per owner viewport.
- Cashier menu links must use routes/panels that cashier role can access.
- Only exact current-page links receive aria-current="page"; parent/group links may use visual active styling.
- Preserve existing permission gates and tenant scoping.
- Keep outlet/tenant context accurate: current model switches between separate businesses, not a consolidated parent outlet.
- No new dependency and no payment, stock, or billing business-logic change.

## Review Focus

- Manager-only pages must not be hidden behind inaccessible links; verify role-visible navigation.
- Platform operators must see withdrawal review outside business settings, without exposing it to ordinary users.
- Cashier history/shift shortcuts must stay inside the POS shell.
- Long nav labels and six-item contextual menus must remain usable below 400 px.
- Current page semantics must not identify broad parent destinations as the exact page.

## Files and responsibilities

- resources/views/layouts/sidebar.blade.php: owner/manager IA, workspace context, POS primary action, platform section, active semantics.
- resources/views/layouts/app.blade.php: render the role-appropriate shell and remove duplicated role decisions where necessary.
- resources/views/layouts/navbar.blade.php: retain account/billing context, remove duplicate POS and onboarding quick actions if sidebar owns the action.
- resources/views/components/workspace-tabs.blade.php: responsive one-row scroll behavior and exact active-page semantics.
- resources/views/workspace/settings.blade.php: business settings only, grouped as compact list sections; help/account/platform entry points move out.
- resources/views/dashboard/index.blade.php: remove duplicated POS CTA while preserving dashboard content hierarchy.
- Tenant/profile/billing/public copy views and Auth/VerifyEmailController.php: align customer-facing copy to separate businesses/accounts, not implied consolidated outlet management.
- tests/Feature/Pos/WorkspaceNavigationTest.php, CashierWorkspaceTest.php, and existing copy-focused feature tests: regression coverage.

### Task 1: Role-aware owner and cashier navigation

**Files:**
- Modify resources/views/layouts/sidebar.blade.php
- Modify resources/views/layouts/app.blade.php
- Test tests/Feature/Pos/WorkspaceNavigationTest.php
- Test tests/Feature/Pos/CashierWorkspaceTest.php

**Interfaces:** Preserve named routes. Owner POS entry is one primary sidebar link. Cashier uses existing POS buttons for sale, history panel, and shift modal; do not link cashier to the owner shift index.

- [x] Write feature tests for owner group labels/links, exact current-page semantics, and platform-gate visibility; write cashier workspace assertions for accessible POS actions only.
- [x] Run the focused feature tests and confirm expected failures before changing Blade.
- [x] Implement owner group labels: Ringkasan; Penjualan (Transaksi, Promo, Shift, permitted QRIS review); Kelola usaha (Katalog, Persediaan, Pelanggan, conditional Dapur/Jasa); Analisis (Laporan); Pengaturan usaha; Bantuan; Akun; separate Platform section only for platform admins.
- [x] Preserve a visible current business label and link it to business switching for owner/admin users.
- [x] Ensure cashier shell remains a focused POS workspace; use existing Riwayat and Shift controls within POS, and keep only permitted contextual module links on service/kitchen pages.
- [x] Run the focused feature tests and verify permission boundaries.

### Task 2: Settings, duplicate CTA, and contextual navigation

**Files:**
- Modify resources/views/workspace/settings.blade.php
- Modify resources/views/layouts/navbar.blade.php
- Modify resources/views/dashboard/index.blade.php
- Modify resources/views/components/workspace-tabs.blade.php
- Test tests/Feature/Pos/WorkspaceNavigationTest.php

**Interfaces:** Keep existing settings routes. Help/account are navigated from the workspace footer; platform withdrawal is not rendered as a business setting.

- [x] Add tests asserting one persistent POS action, no duplicate dashboard/header POS CTA, settings contains only business configuration, and contextual nav is mobile-scrollable.
- [x] Run focused feature tests and observe expected failures.
- [x] Remove duplicated header/dashboard quick actions; preserve the primary POS action in sidebar.
- [x] Group actual business settings by task; move Help, Account and Platform operations to their appropriate navigation areas.
- [x] Make contextual navigation a single scrollable row on compact screens; only exact route targets get aria-current="page".
- [x] Run focused feature tests.

### Task 3: Clarify tenant/outlet copy

**Files:**
- Modify tenant/profile/billing/public setting and verification views/controllers.
- Test the existing tenant quota/copy feature tests.

**Interfaces:** Do not change max_outlets, tenant ownership, switching, entitlements, or database schema. Copy must explain the current separate-business model without promising merged data.

- [x] Add assertions for clear separate-business context and remove misleading “kelola outlet/cabang” claims from relevant screens.
- [x] Run focused feature tests and observe expected failures.
- [x] Update labels/help/copy consistently; keep package quota values and route behavior unchanged.
- [x] Run focused feature tests.

### Task 4: Full verification and final visual pass

**Files:** no additional production files expected.

- [x] Run all PHP feature/unit tests.
- [x] Run JavaScript tests and production asset build.
- [x] Run Pint/format checks only on modified files where available.
- [x] Review rendered output or test DOM for owner, manager, cashier, platform admin, desktop and narrow viewport states.
- [x] Review diff for unrelated changes and document any environment limitations.

## Expected verification commands

- php artisan test tests/Feature/Pos/WorkspaceNavigationTest.php tests/Feature/Pos/CashierWorkspaceTest.php
- Focused tenant/profile/settings feature tests identified during implementation.
- php artisan test
- npm test
- npm run build
- git diff --check

### Verification results

- Focused navigation/cashier/copy tests: 19 passed, 389 assertions.
- JavaScript tests: 23 passed. Production asset build: passed. Pint and `git diff --check`: passed.
- Full PHP suite: 143 passed, 1 failed. `EmailVerificationTest::test_email_can_be_verified` fails on the base `HEAD` controller as well, so it is an existing baseline failure unrelated to this work.
- Role/permission and compact-nav markup were checked through rendered-response DOM assertions; no interactive browser screenshot review was performed.

No commit, push, production deploy, or database schema change is included.

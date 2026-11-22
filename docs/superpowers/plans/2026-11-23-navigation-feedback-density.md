# Navigation, feedback, and density implementation plan

**Design:** `docs/superpowers/specs/2026-11-23-navigation-feedback-density-design.md`
**Audit:** `docs/audits/2026-11-23-global-navigation-feedback-layout-audit.md`

## Constraints

- Publish only to GitHub `main`, following the user's prior explicit instruction; do not push a Codex branch.
- Preserve permission/plan gates, field validation, payment recovery guidance, and the immersive POS layout.
- Do not add or run automated tests; verify with production build, format, static searches, and diff checks.
- Use existing Laravel Blade, Alpine, Tailwind, and Font Awesome assets; add no dependency.

## Task 1 — One operational sidebar tree

**Files:** `resources/views/layouts/sidebar.blade.php`, `resources/views/layouts/cashier-sidebar.blade.php`, `resources/views/layouts/app.blade.php`, `resources/views/components/workspace-tabs.blade.php`.

- Replace duplicated sidebar/tab navigation with task groups and expandable Katalog/Persediaan children.
- Keep Kasir and Ringkasan prominent; move cash/QRIS tasks to a clear Keuangan group; keep report exports and AI under Analisis.
- Retain existing admin, manager, role, plan, and business-module checks.
- Automatically expand the current route's parent; add focus, accessible disclosure state, and short motion.
- Remove the workspace-tab component from app pages once equivalent routes are in the sidebar; retain role-specific cashier navigation.

## Task 2 — Shared transient notification system

**Files:** create `resources/js/notifications.js`, create `resources/views/components/toast-stack.blade.php`; modify `resources/js/app.js`, app/guest/landing shells, auth controller, auth/profile/operational views, finance and billing client-side feedback.

- Render one shared toast stack in authenticated, guest, and landing shells.
- Read success, error, warning, info, and status flash messages, mapping symbolic auth status keys to Indonesian copy.
- Add explicit login and logout success messages.
- Add a reusable browser event for asynchronous actions and replace native `alert()` calls with app toasts.
- Add close button, 5–9 second type-specific timeout, pause on hover/focus, bounded stack, reduced-motion-compatible transitions, and accessible status roles.
- Remove duplicate inline flash banners; retain persistent validation summary/field errors and inline payment safety/recovery states.
- [x] Implement shared notices in authenticated, guest, landing, and standalone POS shells; add auth flashes and replace browser alerts in finance/payment views.

## Task 3 — Compact, consistent page and form spacing

**Files:** `resources/views/components/page-header.blade.php`, app page wrappers and form views under `resources/views/`.

- Inventory all authenticated page wrappers and form card/field/action spacing.
- Align page gutters and form group gaps to the existing 4/5/6 Tailwind spacing scale; remove compounded page-header margin plus parent stack gap.
- Reduce oversized section gaps only where source structure confirms duplication or excessive scale; preserve table empty-state breathing room, profile hero, auth cards, landing sections, and POS workspace.
- Ensure form cards and action rows remain responsive and maintain existing labels, field errors, button affordances, and visual tokens.
- [x] Reduce redundant header/wrapper gaps and selected oversized form/card spacing while preserving POS and intentional empty-state space.

## Task 4 — Verification and publish

- Run `npm run build:icons`, `npm run build`, and `npm run format:check`.
- Run `git diff --check` and static searches for duplicate workspace tabs, native alerts in app flows, missing notification close controls, and forgotten sidebar route definitions.
- Inspect final diff and working tree; do not run automated tests.
- [x] Run icon generation, production build, Prettier check, static alert search, and `git diff --check`.
- Commit the finished work, fast-forward local `main`, and push only `origin/main`; verify the remote `main` SHA after pushing.

## Review focus

- Every route remains linked under the correct role and business-module gate.
- Active route opens its sidebar parent; collapsed/mobile sidebars preserve accessible names and keyboard focus.
- Auth success flashes survive redirects, including logout to the landing page.
- Toasts do not duplicate notices or auto-dismiss field-validation and payment recovery guidance.
- Page-header spacing does not compound with parent stacks; page and form content does not gain nested/redundant surfaces.
- Reduced-motion preference remains respected and POS interactions are not delayed by decorative animation.

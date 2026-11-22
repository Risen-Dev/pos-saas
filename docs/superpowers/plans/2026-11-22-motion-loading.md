# GrowPOS motion and loading polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add smooth, restrained page and card motion, real async skeleton loading, and consistent local Font Awesome icons across GrowPOS.

**Architecture:** Keep Laravel Blade multi-page navigation and existing Alpine/Tailwind patterns. Add cross-document view transitions as progressive enhancement, an opt-in card reveal utility, and a reusable Blade skeleton for actual async waits. Preserve the local Font Awesome SVG registry and avoid new dependencies.

**Tech Stack:** Laravel Blade, Tailwind CSS 3, Alpine.js, vanilla JavaScript, CSS View Transitions.

**Spec:** `docs/superpowers/specs/2026-11-22-motion-loading-design.md`

## Global Constraints

- Do not convert the application to an SPA or intercept form submissions.
- Do not show skeletons on server-rendered content that has already arrived.
- Motion must not block POS scan, typing, payment, or recovery.
- Honor `prefers-reduced-motion` for nonessential transitions and entrances.
- Continue using `resources/icons/fontawesome.json` through `<x-icon>`; no CDN or new package.
- Keep empty and error states distinct from loading.

## Review Focus

- Slow or failed navigation: unsupported browsers retain normal navigation; skeletons do not become stuck.
- Reduced-motion preference: page and card animations are absent while loading/status feedback stays visible.
- Long card grids: stagger remains brief and items below the fold animate when they enter view.
- POS latency: loading feedback stays local to the pending action and leaves checkout usable.
- Failed chart, AI, and export requests: error/empty messages replace loading presentation.

---

### Task 1: Cross-page motion and shared card reveal

**Files:**
- Modify `resources/css/app.css`
- Create `resources/js/motion.js`
- Modify `resources/js/app.js`
- Modify `resources/views/layouts/app.blade.php`
- Modify major card-group Blade views across dashboard, catalog, inventory, sales, reports, billing, settings, employees, kitchen, services, and finance.

**Interfaces:** `motion.js` exports `initPageMotion(document = window.document)`; card groups opt in using `data-motion-stagger`, and their direct children are revealed in order unless marked `data-motion-ignore`.

- [x] Add a compact cross-document transition under `@view-transition { navigation: auto; }` with a quick fade and minimal vertical movement.
- [x] Add `prefers-reduced-motion: reduce` CSS that disables view-transition and card entrance animation.
- [x] Implement `initPageMotion` with `IntersectionObserver`, reading-order stagger capped to a short total delay; fall back to visible content when the API is unavailable.
- [x] Mark major card grids in core business views with `data-motion-stagger`; do not mark nested decorative panels or POS product tiles that update continuously.
- [x] Add consistent Font Awesome `<x-icon>` usage for remaining interface symbols in touched views; preserve accessible labels for icon-only actions.
- [x] Run `npm run build`; source and asset output reviewed. Browser-rendered state review is unavailable in this worktree because it has no `.env` or `vendor/autoload.php`.

### Task 2: Reusable skeleton primitives

**Files:**
- Create `resources/views/components/skeleton.blade.php`
- Modify `resources/css/app.css`
- Modify `tests/Feature/TailwindAssetsTest.php` only if a template rendering regression needs a stable assertion.

**Interfaces:** `<x-skeleton variant="text|circle|block" />` renders an aria-hidden visual placeholder. The async parent owns `aria-busy="true"` and an announced loading label.

- [x] Implement `x-skeleton` with text, circle, and block variants and size classes that Tailwind can statically discover.
- [x] Add a restrained shimmer/pulse animation that is disabled under reduced motion.
- [x] Confirm skeleton markup is decorative and loading state is announced by its container.

### Task 3: Apply skeletons to real asynchronous states

**Files:**
- Modify `resources/views/reports/index.blade.php`
- Modify `resources/js/app.js`
- Modify `resources/views/reports/ai-analysis.blade.php`
- Modify `resources/views/reports/exports-list.blade.php`
- Modify `resources/views/pos/index.blade.php` or `resources/js/grow-pos.js` only for an existing state that waits on remote data.

- [x] Show a chart-shaped skeleton until Chart.js signals ready; keep numeric report data visible and show a distinct fallback if chart import fails.
- [x] Replace the AI chat's generic pending dots with a compact response skeleton/status while a request is active; preserve its error handling.
- [x] Give pending exports a skeleton/status in the export result region until the existing status poll resolves; keep timeout/failure text distinct.
- [x] Keep the POS checkout stable: source review found no empty server-rendered data region awaiting a remote fetch; existing action-level busy states remain local.
- [x] Run `npm run build`; source and asset output reviewed. Browser-rendered state review is unavailable in this worktree because it has no `.env` or `vendor/autoload.php`.

### Task 4: Final integration review

**Files:** no additional production files expected.

- [x] Review the diff for accidental motion on forms, menus, dialogs, dense tables, and frequently updating POS content.
- [x] Review same-origin transition progressive enhancement, reduced-motion CSS, and native navigation behavior in source.
- [x] Verify the Font Awesome icons are drawn from the local registry; no remote Font Awesome asset was introduced.
- [x] Run `npm run build`, `npm run format:check`, and `git diff --check`.
- [ ] Review at narrow and desktop viewports; unavailable because this worktree lacks `.env` and `vendor/autoload.php`.

## Notes

- This project is server-rendered. Page-wide route skeletons are intentionally excluded because the destination HTML already contains its server data; showing a skeleton there would require a SPA or artificial delay.
- No automated tests are added or run unless separately requested; implementation is checked through asset build and source review.
- Existing view files do not share a consistent card component, so opt-in groups animate direct children to avoid brittle CSS selectors.
- Automated tests were not added or run per developer instruction. Build, format, and diff checks provide the available verification; this leaves lower regression coverage.
- No live browser screenshot review was possible because the isolated worktree lacks `.env` and `vendor/autoload.php`.

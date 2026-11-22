# GrowPOS Page Containment Audit

## Scope and evidence

This is a targeted visual hierarchy and design consistency audit of authenticated GrowPOS pages, focused on page headings and primary task content that appear to float outside a visual surface. Evidence includes the user-provided screenshot of `orders.index` and static inspection of the 46 Blade views using `<x-app-layout>` plus the legacy admin layout. The screenshot is observed evidence for one desktop state; code findings describe source structure, not verified rendering. Mobile, print output, auth, and marketing pages were not visually exercised.

## Findings

### VIS-01 — Page title and primary action float above the transaction table

- **Dimension:** Visual hierarchy & layout; design system consistency
- **Severity:** Medium
- **Confidence:** Observed (screenshot) / Observed (source)
- **Location:** `/orders`, `resources/views/orders/index.blade.php:7-23`
- **Evidence:** The screenshot shows “Riwayat Transaksi”, its explanatory sentence, and “Transaksi Baru (POS)” directly on the page background. The source wrapper uses spacing and `border-b` only; the table below is separately wrapped in a white bordered card.
- **Why it matters:** The page identity and its main action read as detached from the data they control, weakening the scan hierarchy and making the content surface feel unfinished.
- **Recommendation:** Group the title, description, and related primary action in a responsive, padded surface using the existing surface, border, radius, and shadow tokens.
- **Effort:** S

### VIS-02 — The same uncontained heading pattern recurs across operational pages

- **Dimension:** Visual hierarchy & layout; design system consistency
- **Severity:** Medium
- **Confidence:** Observed (source)
- **Location:** Examples include `categories/index.blade.php:6-14`, `customers/index.blade.php:7-17`, `discounts/index.blade.php:7-17`, `employees/index.blade.php:59-60`, `shifts/index.blade.php:7-9`, `material/index.blade.php:7-9`, `finance/index.blade.php:6-15`, `reports/exports-list.blade.php:6-20`, and `settings/index.blade.php:23-32`.
- **Evidence:** These heading wrappers use a bottom border, margin, and/or spacing, while later tables or forms use separate white cards. Other pages use an unboxed title immediately before card content, such as `inventory/history.blade.php:3-8`, `admin/withdrawals/index.blade.php:8-13`, and `finance/withdrawals.blade.php:8-17`.
- **Why it matters:** Users encounter different visual grouping rules for the same page pattern, which makes the product feel assembled from unrelated screens and reduces the clarity of the page-level hierarchy.
- **Recommendation:** Apply one shared page-heading surface to authenticated operational screens, preserving responsive action placement and current page spacing.
- **Effort:** M

### VIS-03 — Some detail and configuration pages have ungrouped top-level context

- **Dimension:** Visual hierarchy & layout; content hierarchy
- **Severity:** Low
- **Confidence:** Observed (source)
- **Location:** Examples include `customers/show.blade.php:3-5`, `business/edit.blade.php:3-7`, and `help/index.blade.php:3-11`.
- **Evidence:** The customer title and return link sit directly above metric cards; business configuration and help pages place their main heading and description on the page background before the first form or content group.
- **Why it matters:** The page title and its first action/content group do not read as one coherent starting point, especially on secondary routes reached from another task.
- **Recommendation:** Add a restrained page-heading container to standard app detail/configuration/help screens; keep the form, metrics, and task-specific panels in their own existing containers.
- **Effort:** S

## Decisions and boundaries

- Use shared visual tokens for standard authenticated page headings and group their adjacent actions with the title.
- Audit primary content blocks as well as headings; add a card only when a standalone functional block lacks a boundary. Do not put an extra white card around already-carded forms, tables, or metric groups.
- Preserve the immersive POS workspace, auth cards, marketing/landing sections, email, and print-oriented invoice/receipt layouts; their surfaces serve different tasks.
- Keep tabs and workspace navigation distinct from content cards so navigation does not read as page data.

## Not observed

- No live runtime or responsive browser pass was available during this source audit. The codebase worktree lacks the Laravel runtime configuration and PHP dependencies, so current findings outside the supplied screenshot are source-level evidence.

## Implementation and verification

- Added the shared `<x-page-header>` surface to standard authenticated index, detail, configuration, and task pages where their title/action blocks floated above content. The stock list heading and history action were also contained after the final page sweep.
- The four authenticated views without this shared header retain intentional surfaces: the legacy dashboard uses a content card, inventory's first task block is already a card, the account profile starts with its own profile hero card, and the billing invoice is a document-style card.
- Standard page headings now share surface, border, radius, spacing, and shadow tokens. Existing forms, tables, metrics, workspace tabs, POS, auth, marketing, and invoice content keep their task-specific grouping.
- Source scan found no account/settings/help/network/logout items in either sidebar. Browser viewport review was unavailable because the worktree lacks `.env` and `vendor/autoload.php`.

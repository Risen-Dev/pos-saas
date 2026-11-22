# Navigation, notification, and layout density design

## Intent

Make GrowPOS easier to navigate and feel consistent and responsive across operational screens. Admins and managers should find features from one predictable sidebar; users should see clear, dismissible feedback after actions; forms and cards should use a compact, consistent spacing rhythm.

## Goals

- Make the sidebar the single navigation source for authenticated operational features, with expandable subfeature groups and role/plan-aware visibility.
- Remove duplicate page-level workspace tabs once their destinations are available in the sidebar.
- Provide consistent success, error, warning, and status notifications after login, logout, CRUD operations, and client-side actions. Each toast has a close control, auto-dismisses, pauses while hovered or focused, and is announced accessibly.
- Keep field validation feedback persistent beside the form so it does not disappear before a user can correct input.
- Tighten redundant header/card and form spacing while preserving the premium surface hierarchy, purposeful empty states, auth, marketing, and POS layouts.
- Reuse existing Alpine, Tailwind, Font Awesome subset, and motion system without introducing dependencies or continuous animation work.

## Non-goals

- Do not add every feature as a separate dashboard tile.
- Do not bypass feature entitlements or role restrictions.
- Do not turn critical payment guidance into a toast alone; keep the related inline state visible while also offering transient toast feedback.
- Do not broadly animate every DOM node or make validation errors auto-dismiss.

## Approaches considered

1. **Keep sidebar and workspace tabs together.** Smallest code change, but preserves duplicated destinations and competing navigation levels.
2. **Make the sidebar the primary tree and remove the tab row.** Subfeatures live in expandable, route-aware groups; active groups open automatically. Chosen because it matches the user's request for a complete operational sidebar and provides one source of navigation.
3. **Move all links into a permanently flat sidebar.** Maximizes visibility, but makes the sidebar long and hard to scan on short screens and collapsed layouts.

For feedback, keep page-specific banners versus centralize all session/client messages. Chosen: one shared toast stack in app, auth, and landing shells; leave inline field errors and consequential payment recovery guidance in place.

For density, use global overrides on every form versus standardize shared page-header/gutter/stack spacing and fix observed outliers. Chosen: shared spacing primitives and targeted page adjustments to avoid damaging specialized POS, auth, and marketing layouts.

## Interaction decisions

- Sidebar groups are labeled by task: Penjualan, Operasional, Keuangan, and Analisis. Katalog and Persediaan expose their task-specific destinations as children. Active route opens its parent automatically. Groups support keyboard activation and visible focus.
- Common routes stay one click away; subfeatures are at most one additional group-open plus one destination click. The cashier sidebar remains role-specific.
- Toast stack caps visible notices to a small number, uses distinct color/icon treatment for status, and supports close, timeout, and pause-on-hover/focus. Authentication feedback appears on the destination page after the redirect.
- Server validation errors remain in the form summary and field-level messages. They are not timed out.
- Page headings, section stacks, form cards, and field groups follow a compact spacing scale; POS and document/receipt surfaces keep their existing density rules.
- Motion uses short opacity/translate transitions and respects `prefers-reduced-motion`.

## Constraints and open implementation calls

- Laravel session flash keys and existing controller messages are the source of truth for server feedback; add explicit success flashes for login/logout.
- Existing success/error/warning/status banners must not render twice after shared toast inclusion.
- Some native browser alerts occur in payment/banking flows. Replace them with app toasts while retaining persistent inline recovery copy for payment safety.
- No automated tests will be added or run; verification is limited to source checks, production asset build, formatter, and diff checks.
- Browser QA is limited by runtime access available in the worktree; report the exact boundary if a live pass cannot be performed.

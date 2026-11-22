# App-wide performance and navigation design

## Intent

Improve GrowPOS loading and navigation across the website. Fix the reported first-load case where metric cards can remain invisible until a reload, make authenticated page changes keep the sidebar and navbar mounted, and reduce other confirmed avoidable work without changing business behavior.

## Goals

- Server-rendered content, especially cards, remains visible if JavaScript, motion, or an observer is delayed or unavailable.
- Authenticated operational navigation replaces the content region while preserving the shared sidebar and navbar.
- Initial page cost and repeated page work are reduced where source or build evidence confirms a safe opportunity.
- Existing Blade routes, authorization, forms, and role-specific navigation remain the source of truth.

## Non-goals and anti-goals

- No rewrite to a client-rendered SPA and no broad API redesign.
- No changes to business rules, role access, or visual design unrelated to loading feedback.
- No speculative database or hosting changes without measurements identifying a bottleneck.
- POS, print, download, external, and other flows that need full-document behavior may keep a full-page navigation path.

## Constraints

- The application is Laravel 13 with Blade, Alpine, and Vite.
- Keep dependencies minimal and reuse existing server-rendered routes.
- Native form behavior and direct URL access must continue to work.
- The repository has an existing source-level frontend performance audit; deployed runtime and backend timings are not available in this workspace.

## Approaches considered

1. **Keep normal document navigation and make motion non-blocking.** Lowest risk and directly fixes invisible content, but recreates the sidebar/navbar on every route change and does not meet the persistent-shell goal.
2. **Use Turbo Frames around the authenticated content.** Keeps Blade and normal URLs while replacing the content frame, leaving the sidebar/navbar mounted. It requires lifecycle handling for Alpine, dialogs, charts, and page-specific scripts; some routes should explicitly opt out to full-document navigation. This is the recommended approach because it meets both the navigation and maintenance goals without a SPA rewrite.
3. **Rewrite the authenticated application as a SPA.** Could give full control of transitions and data loading, but adds a large migration surface and duplicates existing Laravel view behavior. It is out of proportion to the reported issue and is rejected.

## Proposed work

1. Make reveal motion progressive enhancement: server-rendered cards are visible by default, while any animation runs only after the element is known to be ready. Avoid leaving content hidden when IntersectionObserver does not report an intersection.
2. The latest project commit already adds a Turbo Frame boundary and routes eligible internal navigation through it. Review and harden that implementation rather than introducing a second navigation system. Keep POS, print/download, external, and any incompatible interactions on full-document navigation based on source review.
3. Reinitialize page-scoped JavaScript on frame navigation without double-registering global listeners. Ensure Alpine components, dialogs, currency inputs, chart loading, error accessibility, and focus/scroll behavior continue to work.
4. Continue the existing frontend performance pass across shared/page-specific frontend work and high-cost controller query patterns. Implement only low-risk, evidence-backed improvements that do not change output or authorization; record larger or measurement-dependent items as follow-up findings.
5. Verify with a production asset build and static checks, plus targeted interaction review when a local runtime is available. Do not claim deployed performance improvements without deployed measurements.

## Decisions left to implementation

- Identify the exact full-page navigation exceptions by reviewing route and page behavior.
- Review the existing Turbo package/version and lifecycle integration against the current Laravel/Vite setup.
- Keep the audit bounded to avoid behavior-changing refactors without measurements.

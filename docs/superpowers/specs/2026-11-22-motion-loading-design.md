# GrowPOS motion, icons, and loading design

## Intent

Make navigation and content arrival feel deliberate across GrowPOS while keeping checkout fast to scan and operate. The user approved the recommended controlled-motion approach: smooth page transitions, restrained sequential card entrances, real loading skeletons, and the existing Font Awesome icon system.

## Goals

- Add a subtle same-origin page transition with a graceful instant-navigation fallback.
- Reveal major card groups with a short stagger; do not animate every nested element or delay interaction.
- Add reusable skeleton primitives and use them for genuine asynchronous waits such as lazy charts, AI responses, and export status/data requests.
- Keep icons on the existing local Font Awesome subset rendered as inline SVG; replace remaining product icons that do not follow this system where practical.
- Respect `prefers-reduced-motion`; keep meaningful loading state announcements accessible.

## Non-goals

- Convert the Laravel Blade multi-page application into an SPA.
- Show fake loading skeletons for server-rendered pages whose data is already present in the response.
- Add a remote icon font, CDN dependency, or animation library.
- Animate POS checkout actions in a way that blocks scanning, typing, payment, or recovery.

## Approaches

1. **Motion on every element** — maximum visible movement, but adds noise, delays scanning, and can make high-frequency POS work feel slower. Rejected.
2. **Controlled shared motion (chosen)** — progressive-enhancement page transitions, opt-in stagger on major card groups, reusable skeletons on real asynchronous states, and local Font Awesome SVG. Fits the existing Blade/Alpine/Tailwind stack and preserves normal navigation and form behavior.
3. **SPA navigation and route-wide skeletons** — makes cross-page loading uniform but requires interception and state management across forms, query strings, back/forward, permissions, and POS recovery. Too much behavioral risk for the current goal.

## Behavior and visual direction

- Cross-document transitions apply only to same-origin navigations and use a quick, low-distance fade/translate. Browsers without support use ordinary navigation.
- Card entrance motion is opt-in at major content groups; direct children are staggered in reading order with a short total delay. No nested animation cascade and no animation that waits for user interaction.
- Skeletons match the dimensions of the pending content, carry `aria-busy`/status semantics, and are replaced when the real result arrives. Empty and error states remain distinct from loading.
- POS remains visually stable while opening, scanning, calculating, and submitting; use a compact inline loading indicator/skeleton only where data is actually pending.
- Use existing `resources/icons/fontawesome.json` and `<x-icon>` mapping. Keep decorative icons hidden from assistive technology and label icon-only controls.
- Under reduced motion, remove entrance/transition movement while preserving state changes and loading feedback.

## Constraints and open implementation details

- Existing runtime: Laravel Blade multi-page rendering, Alpine.js, Tailwind CSS, locally subsetted Font Awesome SVGs.
- Apply to shared workspace surfaces and the main public/product screens where the same components are used. Explicitly exclude the loyalty-card animation system unless a conflict requires a minimal reduced-motion alignment.
- Keep CSS/JS additions small and dependency-free; verify HTML/DOM output, JS behavior, production build, reduced-motion CSS, and responsive layout.
- Skeleton coverage is based on asynchronous waits found in source; do not claim every server-rendered route needs a skeleton.

## Acceptance

- Same-origin navigation has a subtle transition where supported and still works normally where unsupported.
- Major card grids enter in visual reading order without large cumulative delays; POS checkout remains immediately operable.
- Async chart/report/AI/export waits have a dimension-matched skeleton or equivalent loading state, accessible status, and distinct empty/error outcomes.
- Existing Font Awesome subset remains local and icon-only controls retain accessible names.
- Reduced-motion users receive no nonessential motion.
- Focused tests, JS tests, production asset build, Pint/format checks, and diff checks pass; any unrelated baseline failure is documented.

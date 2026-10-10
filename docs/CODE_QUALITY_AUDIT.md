# Code quality audit — 10 October 2026

## Changes

- Discount create/update share one validated request. Product IDs must belong to the current tenant; values, dates, times, and weekdays are validated. Explicit `is_active=false` is respected. Discount and product relations are written atomically.
- Removed the unused discount show route, which targeted a missing controller action. Added a route integrity regression test, including invokable controllers.
- Purchase order lines reject extra input keys, including forged received quantities. Quantities must fit the database's three decimal places. Only active stock-managed goods can be purchased.
- Purchase orders and stocktakes use ULID-based document numbers, avoiding collisions from simultaneous creation or deleted records.
- Receiving locks and reloads the purchase order, rejects terminal states and empty receipts, and retains the tenant lock used by stock writers.
- Stocktake completion checks the current locked database record instead of the potentially stale route-bound record. Creation inserts item snapshots in batches of 500 and reads only product IDs and stock quantities.
- Customer API accepts an omitted phone and returns normal validation errors. Customer creation reports internal exceptions without returning their details to users.
- Applied the existing Laravel Pint conventions across PHP source files.

## Verification

- PHP suite: 105 tests, 948 assertions, passing (9 new regression tests).
- JavaScript suite: 17 tests, passing.
- Laravel Pint and Prettier checks: passing.
- Production Vite and mail CSS builds: passing.
- Git whitespace check: passing.

An initial registration test failed once, then passed in isolation and subsequent full runs. A session validation assertion was added for better diagnostics; no production registration change was made without a reproducible cause.

## Limits

The PHP suite runs against in-memory SQLite. The stale-object stocktake regression is deterministic, but it does not replace a concurrent MySQL integration test. No production migration, deployment, live payment call, or load test was performed. Passing automated checks does not establish that every possible bug is eliminated or that production capacity has been measured. The build still reports an outdated Browserslist dataset warning.

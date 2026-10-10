# GrowPOS — local setup and verification (2026-10-10)

## Contents
- Email notifications for reset password and verification now use GrowPOS HTML + compiled Tailwind CSS inliner, plus plain-text alternative.
- Tailwind mail config scans custom template, plus direct-content fallback; mail build is required before sending email.
- Legacy plans migration avoids creating an already-existing plans table and avoids destructive rollback; the following upgrade migration adds missing columns without deleting data.
- Deployment script remains fail-fast, inherited from the previous patched source.

## Apply safely on Mac
1. Back up your existing local project and **database**. Keep `.env`, `storage/app/public`, and any user files; never overlay the whole ZIP onto an unknown working tree without reviewing `git diff`.
2. Recommended: PHP 8.4.x (not 8.5 for the current locked PhpSpreadsheet), Node compatible with Vite 8, Composer, MySQL.
3. `composer install` then `composer validate` and `composer check-platform-reqs`.
4. `npm ci && npm run build` (includes `build:mail`).
5. Verify `test -s public/build/mail.css` and `test -s public/build/manifest.json`.
6. `php artisan optimize:clear` then `php artisan test --filter=TailwindAssetsTest`, `php artisan test`, and `npm test`.
7. Check migration status and back up DB. Run `php artisan migrate` only against your intended **local/test** DB and only after checking compatibility. Never use `migrate:fresh` on existing records.
8. Test local registration/verification/reset password with `MAIL_MAILER=log` or a sandbox SMTP. Do not send test messages to real users.
9. Only after local checks pass: commit code changes and deploy to production after independently verified backup/restore plan.

## Note
- Production data, SMTP provider, payment gateway and real hardware cannot be validated inside this ZIP.
- Composer lock alignment and npm audit advisories are separate checks; avoid `npm audit fix --force` without testing.
- CSS inlining depends on `tijsverkoyen/css-to-inline-styles`, already a dependency of Laravel Mail in the supplied composer lock.

# Student Voting Management System (SVMS)

Status: Draft v0.1
Owner: Product/Systems Analysis
Tech Lead: Laravel Team
Date: 2025-09-14

## 1) Overview
The Student Voting Management System (SVMS) is a multi-tenant, configurable election platform for universities and their student organizations. Each organization operates in isolation with its own database while sharing a common application codebase. The system supports annual elections (configurable), flexible voter scheduling, self-service registration, and role-based administration with auditability.

- Primary audience: University-wide Student Council (General org) + Course/Academic organizations.
- Scale target: Up to ~20,000 students on free/low-cost infrastructure.
- Technology: Laravel 12, Inertia + Vue 3, Tailwind v4 (existing), PostgreSQL, Redis, Docker, Laravel Reverb for realtime.
- Cost focus: All components must be free-tier friendly.

Goals
- Simple to operate, safe to scale later (split services only when needed).
- Strong tenant isolation (per-organization databases) for portability/export.
- API-first with secure, minimal frontend props.

Non-goals (initial)
- Full microservices. We keep a monolith-with-modules approach (may follow microservices later).
- GraphQL by default (may be added later).
- Advanced accessibility (may be added later).

Assumptions
- One shared codebase for central portal, organization sites, and admin panels.
- Postgres cluster hosts: one central DB + N tenant DBs.
- Existing repo uses Tailwind v4.

## 2) Roles & Personas
- Student (Voter): Registers, uploads COR(s), receives access code, votes once per election and per organization.
- Organization Admin (Committee): Validates registrations, configures elections/positions/candidates/schedules/themes, imports users, views live results, manages FAQs/ToS.
- Super Admin: Provisions organizations, sets defaults (SMTP fallback, global FAQ/ToS), oversees health, but cannot change per-org configs.

## 3) Multitenancy Model
Approach
- Use multi-database isolation per org (e.g., stancl/tenancy). Central registry in a single “central” DB.

Domains (example)
- Central landing and registration: `portal.example.edu`
- Admin shell: `admin.example.edu` (routes scoped by tenant selection)
- Organization sites: `org-code.example.edu` (or path-based for development)

Tenant Provisioning Flow
1) Super Admin creates Organization in central DB (short_name, full_name, category, flags).
2) System creates a new Postgres database for the tenant using naming convention.
3) Run tenant migrations + seed defaults (roles, permissions, settings, theme, FAQ/ToS placeholders).
4) Attach domain(s) to tenant and activate.

Database Naming Convention
- Pattern: `pup_sr_0_elec_ms_{org_slug}` (prefix configurable). Example: `pup_sr_0_elec_ms_sco`.

Storage Isolation
- File paths namespaced per tenant: `storage/app/tenants/{tenant_id}/cor/{ulid}.pdf`.

## 4) Data Model (High-level)
Central Database (shared)
- organizations: id, short_name, full_name, category_id, is_general, should_copy_from_other_org, allow_cross_membership, active, theme_defaults, created_at, updated_at
- organization_domains: id, organization_id, domain
- categories: id, name (General, Academic, …)
- super_admins: id, email, password_hash, two_factor columns, status
- smtp_profiles: id, organization_id (nullable for global), host, port, encryption, username, password_encrypted, from_name, from_email, active
- global_content: id, type (faq|tos), slug, content_markdown, is_default

Tenant Database (per organization)
- users (students): id (UUID/ULID), email, password_hash, voting_status, account_status, year_level, section, created_at, updated_at, deleted_at
- user_cors: id, user_id, file_path, verified_at, uploaded_at
- roles, permissions, model_has_roles, model_has_permissions, role_has_permissions (Spatie)
- elections: id, year, name, sandbox_mode, status (draft|scheduled|running|closed|archived), published_results_at
- positions: id, election_id, name, slots
- candidates: id, election_id, position_id, code, profile_json, photo_path, status
- schedules: id, election_id, partition_type (year_level|section|surname_range), partition_value (JSON), start_at, end_at
- ballot_access: id (UUID), election_id, user_id (nullable until claim), short_key (unique per election), issued_at, expires_at, used_at
- ballots/votes:
  - ballots: id (UUID), election_id, user_id, cast_at
  - vote_lines: id, ballot_id, position_id, candidate_id
- faq, terms: id, slug, content_markdown, is_active
- themes: id, variables_json (CSS variables), logo_path, alias_name, social_links_json
- audit_log: id, action, subject_type, subject_id, causer_id, properties_json, created_at
- imports: id, type (users), file_path, stats_json, completed_at
- settings: key, value_json

Notes
- Unique checks: users.email unique per tenant; enforce uniqueness during imports/registration.
- Account statuses: for_validation|active|rejected|suspended.
- Voting status: not_eligible|eligible|voted (per election derived or cached map).

## 5) Key Features & Flows
Registration (Self-service)
- Fields: Email, Organization, Password, COR upload(s). Name is omitted for anonymity.
- Flow: Central page posts to central API → creates pending record in the selected tenant DB (account_status=for_validation). If org flag `should_copy_from_other_org` for main org is true, mirror pending record there too.
- Duplicate checks: email (and optional student_id if used) flagged; admin decides accept/reject.

Organization Selection & Login
- Landing page lists organizations and links to org-specific login. For students voting with short codes during the schedule window, a dedicated “Enter Access Code” page exists per org.

Bulk Upload
- Tenant admin uploads CSV/XLSX. File is queued for processing, duplicates reported (email/student_id). New records set to `for_validation`.

Per-Org Configuration
- Roles/permissions (Spatie) per tenant.
- Theme (colors via CSS variables, logo, alias, social links).
- FAQ and Terms (tenant overrides).
- SMTP (override global SMTP).

Elections, Positions, Candidates
- Create election (year/name), configure positions (with slots), add candidates.
- Sandbox mode allows test runs without affecting production stats.

Schedules & Partitioning
- Partition types: year_level, section, surname_range. Multiple windows supported.
- A student can vote exactly once per election; enforced by ballot existence and schedule eligibility.

Access Codes (Load Smoothing)
- Pre-generate `ballot_access` records once schedules are finalized.
- Send short_key codes gradually via queued emails. Code + election_id identify the voter during window.
- Collision avoidance: 8-digit numeric key space with unique index `(election_id, short_key)` and retry-on-conflict.

Live Results Broadcast
- Fullscreen dashboard with toggle: anonymized aggregate vs. detailed aggregate (no PII).
- Realtime via Laravel Reverb; fallback to polling with backoff.

Audit & Activity Log
- Use spatie/laravel-activitylog in each tenant DB to record admin actions and critical events.

Student Organization Migration
- Policy controls whether an org permits cross-membership within a category (Academic vs General).
- Workflow: Student requests migration → current org approval → receiving org revalidation → status for_validation. Block if current org has an active election.

## 6) Security & Authorization
- Authentication: Fortify (already present) + Sanctum for SPA/API auth; cookie domain set for subdomain SSO.
- Authorization: Spatie Permission per tenant (roles: Committee, Auditor, Viewer; extendable) + Laravel Policies.
- Rate limiting: strict limits on access code submission, login, registration, imports.
- CAPTCHA: Cloudflare Turnstile for registration and code verification.
- 2FA (Fortify) optional for admins.
- Data separation: Different DB connections per tenant; storage paths namespaced.

## 7) API Design (High-level)
Guidelines
- API-first. Inertia pages consume JSON APIs; no sensitive props passed directly.
- Use Eloquent API Resources and pagination.
- Protect with Sanctum + Policies.

Central API (examples)
- POST `/api/register` → { email, organization_id, password, cor_files[] }
- GET `/api/organizations` (public list for selection)
- POST `/api/organizations` (super-admin) → provision tenant (creates DB, runs migrations)

Tenant API (examples)
- GET `/api/elections`
- POST `/api/elections` (committee)
- POST `/api/elections/{election}/schedules`
- POST `/api/elections/{election}/access-codes/generate`
- POST `/api/imports/users`
- POST `/api/vote/access` → { short_key } (returns session token restricted to election window)
- POST `/api/vote/ballot` → { selections[] }
- GET `/api/results/live` (read-only aggregate)

Error Modes
- 401/403 for auth/permission failures; 422 for validation; 409 for schedule/eligibility conflicts.

## 8) Theming & UI
- Tailwind v4 with CSS variables set per tenant; primary/secondary colors applied at :root for the tenant.
- Theme JSON stored per tenant; logo path and alias used across layouts.

## 9) Packages & Integrations (Free)
- Tenancy: `stancl/tenancy`
- Roles: `spatie/laravel-permission`
- Audit: `spatie/laravel-activitylog`
- Realtime: Laravel Reverb (self-hosted)
- Imports: `maatwebsite/excel` (optional)
- CAPTCHA: Cloudflare Turnstile
- Error monitoring: Sentry (free tier, optional)

## 10) Infrastructure & Deployment (Free-first)
Baseline (single VPS)
- Reverse proxy: Traefik (TLS via Cloudflare)
- App: Laravel (PHP-FPM) + queue worker + scheduler
- DB: PostgreSQL (1 cluster, N tenant DBs)
- Cache/Queue: Redis
- Realtime: Reverb server
- Dev mail: Mailpit

Docker Compose (logical services)
- `traefik`, `app`, `nginx`, `postgres`, `redis`, `reverb`, `mailpit`.

Environments
- Dev: `.env` with local DB; run `php artisan migrate` (central) and tenant provisioning commands.
- Staging/Prod: Cloudflare DNS; backups via restic to R2/B2 (free tiers).

## 11) Repository & Code Organization
Recommended: Monorepo
- `apps/laravel` (current app)
- `packages/shared-domain` (optional later: shared DTOs, policies, tenant migrations if splitting deployments)

Pattern (MVVP-C mapping)
- Domain: Models, Services, Policies, Actions
- ViewModels: API Resources/Transformers
- Presenters/Controllers: Controllers + Request validation
- Pages: Inertia/Vue components and pages

## 12) Testing Strategy
- PHPUnit feature tests for:
  - Tenant provisioning and migrations
  - Registration + validation workflow (central → tenant)
  - Access code generation uniqueness and redemption window
  - One-vote-per-election enforcement
  - RBAC (committee-only actions)
- Factories per model, using ULIDs.
- Rate-limited endpoints: include abuse tests.

## 13) Internationalization
- JSON and PHP translation files supported; default English content with option to extend later.

## 14) Observability & Logging
- Application logs per environment.
- Activity log per tenant (admin actions).
- Aggregate metrics for election throughput (queued jobs, code sends, ballots per minute).

## 15) Open Questions / Decisions
- Central vs split deployments: Start monolith; split voting/admin later only if required.
- SSO across subdomains when split: Keep Sanctum with cookie domain; if separate origins, consider OIDC.
- SMS/Email for codes: Start email-only; evaluate free SMS gateway feasibility if required.

## 16) Short Key Generation (Example)
```php
<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Database\ConnectionInterface;

final class ShortKeyService
{
    public function __construct(public ConnectionInterface $db) { }

    /**
     * Generates a unique 8-digit short key for a given election.
     */
    public function generateUniqueShortKey(int $electionId): string
    {
        for ($i = 0; $i < 10; $i++) {
            $num = random_int(0, 99_999_999);
            $key = str_pad((string) $num, 8, '0', STR_PAD_LEFT);

            $exists = $this->db->table('ballot_access')
                ->where('election_id', $electionId)
                ->where('short_key', $key)
                ->exists();

            if (! $exists) {
                return $key;
            }
        }

        return substr(Str::ulid()->toBase32(), -8);
    }
}
```

## 17) Requirements Coverage Checklist
- Students fields: email, organization, password, many COR, voting status, account status, year level, section → Tenant `users` + `user_cors` tables.
- 8+1 orgs, editable → Central `organizations` with `categories` and dynamic provisioning.
- Separate DB per org → Multi-database tenancy; naming convention defined.
- Central DB to store org list, warn on schedule conflicts → Central `organizations`; scheduling conflict checks at create/update.
- Self-service registration (central) validated by org admins → Flow defined; no name stored.
- Landing page to choose org and login → Included.
- Per-org theme and shared layout → Theme variables per tenant.
- FAQ/ToS global + tenant-specific → Central defaults + tenant overrides.
- Validation workflows + bulk upload with duplicate flags → Included; imports queued.
- Org admin cannot modify other orgs → Enforced via tenancy boundaries.
- Super admin adds orgs; org admins customize → Separation clarified; auth strategy noted.
- Auto-copy registrations to main org (toggle) → `should_copy_from_other_org` flag.
- Per-org positions/candidates/guidelines → Election model set.
- Roles with Spatie Permission → Planned per tenant.
- Optional extra fields per org → Store in `users` additional JSON or a custom_fields table; enforce on registration if mirroring.
- Audit log per org → Activity log per tenant.
- Flexible schedules (year/section/surname) spanning days, one chance to vote → Schedules + ballots model.
- 6–8 digit access codes, gradual send, collision mitigation → Short key service, unique index, queued dispatch.
- Split apps idea (portal/voting/admin); super admin server? → Start single app; optional split later; SSO guidance.
- Student migration between orgs (category rules) and freeze during elections → Workflow covered.
- Mailing: default SMTP + per-org SMTP override → Included.
- Sandbox/mock election → `sandbox_mode` flag.
- Live results broadcast with toggle and websocket/polling → Included (Reverb + polling fallback).
- Free third-party services → Listed.
- API-first; avoid heavy Inertia props → Included.
- DB name prefix example → Included.
- Postgres suitability → Adopted.
- Localization optional → Included.
- MQTT/Kafka not required → Not needed initially; Reverb is sufficient.
- Docker for dev/deploy → Included.

## 18) Next Steps (Concrete)
1) Tenancy bootstrap
   - Install `stancl/tenancy` and configure central + tenant migrations folders.
   - Central migrations: `organizations`, `organization_domains`, `categories`, `smtp_profiles`, `global_content`.
   - Tenant migrations: `users`, `user_cors`, `elections`, `positions`, `candidates`, `schedules`, `ballot_access`, `ballots`, `vote_lines`, `themes`, `faq`, `terms`, `settings`, Spatie tables, activity log.
2) Auth & RBAC
   - Fortify + Sanctum configured for subdomain SSO; Spatie Permission in tenant connection.
3) Core APIs
   - Central: org provisioning, registration endpoint.
   - Tenant: elections, schedules, access-code issue/verify, ballot cast, results.
4) UI skeleton (Inertia)
   - Landing (org selector), Registration, Tenant Admin Dashboard, Voting (access code → ballot → confirmation), Live Results.
5) Infra
   - Docker Compose services (app, postgres, redis, reverb, mailpit, traefik).
6) Tests
   - Provisioning, registration flow, schedule eligibility, one-vote rule, short key uniqueness.

---
This draft aligns with the existing Laravel 12 + Inertia + Tailwind stack and keeps costs minimal, while leaving a clear path to split components and add complexity only when scale requires it.

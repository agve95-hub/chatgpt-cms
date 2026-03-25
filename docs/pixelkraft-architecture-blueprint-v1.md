# Pixelkraft Architecture Blueprint

**Version:** 1.0  
**Date:** March 2026

A self-hosted site operations platform for managing, editing, deploying, and monitoring AI-generated websites from a single dashboard.

## Contents
1. Final Tech Stack
2. System Architecture
3. Database Schema
4. Multi-Strategy Parser
5. Visual Page Editor
6. GitHub Sync Engine
7. Deployment Pipeline
8. Domain & SSL Management
9. Content Management
10. SEO Suite
11. Analytics & Monitoring
12. Email System
13. Media Management
14. Performance Optimization
15. Operations & Logging
16. Public API
17. Auth & Security
18. Project Structure
19. Build Phases
20. Infrastructure Requirements

---

## 1) Final Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Backend Framework | Laravel (PHP 8.3+) | 11.x |
| Frontend / Reactivity | Livewire 3 + Alpine.js | 3.x |
| CSS Framework | Tailwind CSS | 3.x |
| Database | MariaDB | 10.11+ |
| Cache / Queue / Sessions | Redis | 7.x |
| Queue Dashboard | Laravel Horizon | 5.x |
| Media Storage | Cloudflare R2 (S3-compatible) | — |
| Email Provider | Resend | — |
| HTML Parsing | Symfony DomCrawler + DOMDocument | — |
| Headless Browser | Spatie Browsershot (Puppeteer) | 4.x |
| Git Operations | CzProject/GitPHP + Octokit (webhooks) | — |
| Task Scheduling | Laravel Scheduler (cron) | — |
| Web Server | Nginx | — |
| OS | AlmaLinux 10 | — |
| Process Manager | Supervisor (queue workers) | — |
| SSL | Certbot (Let's Encrypt) | — |
| Notifications | Discord webhook + in-app | — |

**Why this stack?** Laravel + Livewire keeps the dashboard and business logic in one PHP stack while still enabling reactive UX. MariaDB + Redis support concurrent jobs (builds, audits, uptime checks). Browsershot enables real rendered DOM capture for visual editing and parsing fallback.

---

## 2) System Architecture

Pixelkraft is a **monolithic Laravel application** with a heavy background-job model.

### Core layers

- **Dashboard layer (Livewire + Alpine.js):** Site listing, editor UI, SEO tools, analytics, media, settings.
- **Application layer (Laravel services):** Domain services such as `GitSyncService`, `ParserService`, `DeployService`, `SeoAnalyzer`, `AnalyticsAggregator`.
- **Worker layer (Horizon + queues):** All heavy work runs async (git operations, builds, audits, crawls, optimization, backups, email).
- **Data layer (MariaDB + Redis + R2):** Structured data, cache/queues/sessions, and media objects.

### Request flow

`Nginx -> Laravel -> Services -> Horizon/Redis -> MariaDB + R2`

---

## 3) Database Schema

All core tables use **UUID primary keys**.

### Key entities

- `sites`: repo, build, deploy, domain, SSL, and integration config.
- `pages`: discovered page metadata + SEO + screenshot + score fields.
- `editable_regions`: region detection metadata, selector mapping, confidence, source location.
- `content_revisions`: region diff history + commit/user attribution.
- `blog_posts`, `product_listings`, `content_templates`: structured content subsystem.
- `redirects`: managed 301/302 routing.
- `form_submissions`, `newsletter_subscribers`, `newsletter_campaigns`: form + email subsystem.
- `deploy_logs`: full deployment lifecycle logs.
- `uptime_checks`, `analytics_snapshots`: monitoring + analytics history.
- `notifications`: in-app alert stream.
- `users`: auth, 2FA, roles, notification preferences.

---

## 4) Multi-Strategy Parser

This is the most complex subsystem due to mixed tech stacks across managed sites.

### Step 1: Project type detection

Detect by signature files (ordered):

1. `package.json` contains `astro` -> `astro`
2. `package.json` contains `next` -> `react` (Next.js)
3. `package.json` contains `nuxt` -> `vue` (Nuxt)
4. `package.json` contains `svelte` -> `svelte`
5. `package.json` contains `react` -> `react`
6. `package.json` contains `vue` -> `vue`
7. `hugo.toml` or `config.toml` -> `hugo`
8. `.eleventy.js` or `eleventy.config.js` -> `11ty`
9. root `*.html` files -> `static_html`
10. fallback -> `custom` (manual config)

### Step 2: Parse strategy by type

- **Static HTML (easy):** Parse `.html` directly via DomCrawler.
- **SSG outputs (medium):** Build, parse generated HTML, map edits back to source.
- **SPA stacks (hard):** Parse component sources + render via Browsershot.
- **Universal fallback:** Build + local serve + headless render for true DOM.

### Step 3: Region detection heuristics

Scoring model (0-1) with semantic, content, context, and repetition penalties.

- Score `>= 0.5` => auto dynamic/editable.
- Score `< 0.5` => auto static/locked.
- User confirmation remains required.

### Step 4: Marker injection

Confirmed regions are wrapped with markers:

```html
<!-- cms:editable id="hero-title" type="text" -->
<h1 class="hero-title">Welcome to My Site</h1>
<!-- /cms:editable -->
```

Subsequent parses prefer markers for deterministic identification.

---

## 5) Visual Page Editor

### Parent frame
Livewire/Alpine shell with toolbar, region panel, metadata sidebar.

### Child frame
Rendered site in iframe with injected script for selection/highlighting + `postMessage` bridge.

### Modes
- **Visual mode:** click-to-edit for text/image/link regions.
- **Code mode:** full source editing (CodeMirror), with region-source mapping.
- **Static regions:** locked by default with optional manual override.

### Save flow
1. Edit region
2. Patch source (`ContentPatcher`)
3. Stage preview
4. Commit + push
5. Build + deploy

---

## 6) GitHub Sync Engine

- Clone repos to `/var/www/pixelkraft/repos/{site-slug}/`
- Pull before edit sessions and on webhook pushes
- Commit with generated or custom messages
- Push with auto pull/rebase retry
- Handle conflicts via diff/choice workflow in dashboard

---

## 7) Deployment Pipeline

Pipeline steps:
1. Git pull
2. Install dependencies
3. Build
4. Optimize
5. Deploy + reload

Per-site config includes `build_command`, `build_output_dir`, `node_version`, env vars, hooks, and `deploy_path`.

### Staging + rollback
- Temporary staging subdomain for approval
- Timestamped deploy snapshots with last-10 retention

---

## 8) Domain & SSL Management

- Generate Nginx vhost from templates
- Write to `sites-available` and symlink to `sites-enabled`
- Provision SSL via Certbot queue job
- Weekly expiry checks with Discord + in-app alerts
- Enforce narrowly scoped sudoers for privileged ops

---

## 9) Content Management

Two coexisting models:

- **Region-based editing:** direct patching of existing pages/templates.
- **Structured collections:** blog/products generated from templates.

Includes template system with placeholders (e.g., `{{title}}`, `{{body}}`, `{{image}}`) and optional global components.

---

## 10) SEO Suite

Per-page controls for:
- Meta title/description/keywords
- Open Graph
- JSON-LD schema
- Canonicals
- `robots.txt`
- Redirect rules
- Auto sitemap generation/submission
- Computed 0-100 SEO score

---

## 11) Analytics & Monitoring

- Unified GA4 + Cloudflare dashboards with daily sync
- Per-page metrics in listing views
- Custom event reporting
- Search Console integration
- Uptime checks every 5 minutes with failure threshold alerts
- Weekly Lighthouse audits + trend tracking
- Weekly broken-link crawler

---

## 12) Email System

### Form submission API
`POST /api/forms/{site-slug}` with CORS, rate limiting, and spam controls.

### Newsletter
- Subscriber management + segmentation
- Campaign composer + scheduling
- Resend delivery via queued jobs
- Bounce/unsubscribe webhook handling
- Campaign stats tracking

---

## 13) Media Management

- Per-site R2 namespace: `sites/{site-slug}/media/`
- Upload, optimize, browse, search/filter
- Usage tracking before delete
- In-editor media picker modal

---

## 14) Performance Optimization

Runs post-build, pre-deploy against generated output only.

- Image optimization (jpegoptim/pngquant/svgo + WebP variants)
- Lazy-loading + async decoding attributes
- HTML/CSS/JS minification
- Keep source files unmodified for readability

---

## 15) Operations & Logging

- Full deploy logs (status, duration, output, commit, trigger)
- Centralized issues panel (Nginx, Laravel, jobs, link checks)
- Daily DB backups to R2 (30-day retention)
- In-app + Discord notification channels

---

## 16) Public API

Sanctum-authenticated REST API, including endpoints for:

- Sites list/detail
- Deploy + sync triggers
- Pages + region updates
- Deploy history + analytics
- Notifications feed
- Rollback triggers
- Public forms endpoint

---

## 17) Auth & Security

- Fortify-based login + bcrypt
- Built-in TOTP 2FA + recovery codes
- Role model (`admin`, `editor`) for future team support
- Sanctum API tokens
- Redis sessions + CSRF + rate limits
- Encrypted GitHub tokens at rest

---

## 18) Project Structure

Targeted Laravel structure with clear separation of:
- Livewire dashboard modules
- Domain services + parsers
- Queue jobs
- API/web/webhook controllers
- Nginx template views
- migrations/routes/config

(See full tree in original blueprint input.)

---

## 19) Build Phases

1. **Foundation:** Laravel + DB/Redis/Horizon + Fortify + Git sync + site onboarding
2. **Parsing/Discovery:** multi-strategy parser + region detector + confirmation UI
3. **Editor/Content:** visual editor + patching + code view + structured content
4. **Deploy/Domains:** pipeline + optimization + Nginx + SSL + rollback
5. **SEO/Analytics/Monitoring:** full optimization + observability suite
6. **Email/Ops/Polish:** forms, newsletter, public API, backups, dashboard polish

---

## 20) Infrastructure Requirements

### VPS sizing

| Resource | Minimum | Recommended |
|---|---:|---:|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 4 GB | 8 GB |
| Storage | 80 GB SSD | 160 GB NVMe |
| Bandwidth | 2 TB/mo | 4 TB/mo |

8 GB RAM is recommended due to concurrent headless-browser workloads.

### Software dependencies

- PHP 8.3+ + required extensions
- Composer
- MariaDB 10.11+
- Redis 7.x
- Nginx
- Node.js 20 LTS
- Git + Supervisor
- Chromium + Puppeteer
- Optimization tooling (`jpegoptim`, `pngquant`, `svgo`, `cwebp`)
- Certbot + nginx plugin
- Optional per-site SSG binaries (e.g., Hugo)

### External services (starter cost profile)

- GitHub, Cloudflare, Cloudflare R2, Resend, Google Analytics, Search Console
- Expected initial external cost: **$0/month** within free-tier limits for early scale.

---

**Status:** Blueprint v1.0 is consolidated and ready to execute as an implementation roadmap.

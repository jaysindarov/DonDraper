# DonDraper — AI Image & Video Generation Platform

DonDraper is a full-stack AI creative platform built with **Laravel 12**, **Vue 3 (Inertia.js)**, and **Tailwind CSS**. It lets authenticated users generate images using OpenAI's DALL-E models (with Stable Diffusion and Flux support planned), with fine-grained control over style, quality, and advanced generation parameters — all powered by a credit-based system.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP) |
| Frontend | Vue 3 + Inertia.js (SPA-style, no separate API) |
| Styling | Tailwind CSS |
| Auth | Laravel Breeze (session-based, email verification ready) |
| Queue | Laravel Jobs (`ProcessImageGeneration`) |
| Image AI | OpenAI DALL-E 3 / DALL-E 2 (via HTTP, no SDK dependency) |
| Database | SQLite (local dev) — swappable to MySQL/Postgres |
| SSR | Inertia SSR (`ssr.js`) configured |

---

## What's Been Built

### Authentication
- Full auth flow: Register, Login, Logout, Password Reset, Email Verification, Password Confirm
- Profile management: update name/email, change password, delete account
- Standard Laravel Breeze setup with Vue 3 components

### Credit System
- Every user starts with **10 free credits** on registration (`default: 10` in migration)
- Each image generation costs **1 credit**
- Credits are deducted atomically via `User::deductCredits()` before the job is dispatched
- If a user has 0 credits, generation is blocked with a validation error
- Credit balance is visible on the Dashboard and on the Create Generation page
- Three plan tiers defined in the landing page (not yet enforced by a billing system):
  - **Starter** — Free, 10 credits/month
  - **Pro** — $19/month, 500 credits/month
  - **Enterprise** — $79/month, Unlimited

### Image Generation
- **`GenerationController`** handles CRUD: `index`, `create`, `store`, `show`, `destroy`
- **`ProcessImageGeneration`** job (async queue): calls OpenAI Images API, updates generation status (`pending → processing → completed/failed`)
- Job retries: 3 attempts, 120s timeout
- Supported models (seeded, selectable by user): `dall-e-3`, `dall-e-2`, `stable-diffusion-xl`, `stable-diffusion-3`, `flux-pro`, `flux-dev`
- Provider field tracks source: `openai`, `stability`, `replicate` (only OpenAI wired up currently)
- Soft deletes on generations

### Generation Attributes (Dynamic Parameters)
Stored in the `generation_attributes` table and seeded via `GenerationAttributeSeeder`. These are loaded dynamically into the Create form, grouped by category:

| Category | Attributes |
|---|---|
| **Basic** | Aspect Ratio, Resolution (256×256 → 1792×1024) |
| **Style** | Art Style (14 options), Lighting (8 options), Color Palette (8 options), Mood & Atmosphere (9 options), Camera Angle (7 options) |
| **Quality** | Quality (Standard / HD / Ultra HD), Detail Level (1–10), Sharpness (1–10) |
| **Advanced** | Diffusion Steps (10–150), Guidance Scale/CFG (1–20), Seed (reproducibility), AI Model selector |

Attributes support field types: `select`, `range`, `text`, `toggle`, `color`.

### Database Schema
- **`users`** — standard auth fields + `credits` (int, default 10), `avatar` (nullable), `plan` (string, default `free`)
- **`generations`** — `user_id`, `type` (image/video), `status`, `prompt`, `negative_prompt`, `model`, `provider`, `attributes` (JSON), `result_url`, `thumbnail_url`, `width`, `height`, `steps`, `guidance_scale`, `seed`, `credits_used`, `error_message`, `metadata` (JSON), soft deletes
- **`generation_attributes`** — `key`, `label`, `type`, `category`, `options` (JSON), `default_value`, `min`, `max`, `step`, `description`, `applicable_to`, `sort_order`, `is_active`

### Pages (Vue/Inertia)
- **`Welcome.vue`** — Public landing page: hero, features, style showcase, how-it-works, testimonials, pricing, CTA, footer
- **`Dashboard.vue`** — Stats (total generations, completed, credit balance) + recent generations grid
- **`Generations/Create.vue`** — Prompt input, negative prompt, attribute panel (grouped by category), credit cost indicator
- **`Generations/Index.vue`** — Paginated gallery of user's generations with status badges
- **`Generations/Show.vue`** — Single generation view with result image and metadata
- **`Profile/Edit.vue`** — Profile info, password change, account deletion

### Authorization
- `GenerationPolicy` — users can only view and delete their own generations

### Routes
```
GET  /                         → Welcome (public)
GET  /dashboard                → Dashboard (auth + verified)
GET  /generations              → Gallery index
GET  /generations/create       → Create form
POST /generations              → Store + dispatch job
GET  /generations/{id}         → Show result
DELETE /generations/{id}       → Delete
GET|PATCH|DELETE /profile      → Profile management
+ Full auth routes (login, register, password reset, email verify)
```

---

## Credit System — How It Works

| Action | Credit Cost |
|---|---|
| Generate 1 image | 1 credit |
| Generate 1 video *(planned)* | 5 credits (TBD) |

**Examples:**
- 10 credits → 10 image generations (Starter / Free tier)
- 20 credits → 20 image generations
- 100 credits → 100 image generations
- 500 credits → 500 image generations (Pro tier)

Credits are deducted at the point of submission. If the generation job fails, credits are **not** automatically refunded (refund logic is a planned enhancement).

---

## Local Setup

```bash
git clone <repo>
cd DonDraper
composer install
npm install
cp .env.example .env
php artisan key:generate

# Set OPENAI_API_KEY in .env
# services.openai.key is read in ProcessImageGeneration job

php artisan migrate --seed
php artisan queue:work      # required for async generation
npm run dev
php artisan serve
```

---

## What's Not Built Yet (Planned)

- Video generation (schema + routes exist, job not wired)
- Billing / payment integration (Stripe) for plan upgrades
- Credit top-up / purchase flow
- Credit refund on failed generations
- API access (token-based, for Pro plan)
- Admin panel
- Public/community gallery
- Team workspace (Enterprise)
- Stable Diffusion / Flux provider wiring (only DALL-E is active)
- AI prompt enhancer

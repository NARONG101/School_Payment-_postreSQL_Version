# Deployment Guide — Render

## Prerequisites
- A [Render](https://render.com) account
- This repo pushed to GitHub or GitLab

---

## 1. Generate an APP_KEY locally

```bash
php artisan key:generate --show
```

Copy the output (e.g. `base64:abc123...`). You'll paste it into Render.

---

## 2. Connect repo to Render

1. Go to **Render Dashboard → New → Web Service**
2. Connect your GitHub/GitLab repo
3. Render will detect `render.yaml` automatically

---

## 3. Set secret environment variables

In the Render dashboard for your service → **Environment**:

| Key              | Value                          |
|------------------|--------------------------------|
| `APP_KEY`        | `base64:...` (from step 1)     |
| `ADMIN_EMAIL`    | your admin email               |
| `ADMIN_PASSWORD` | a strong password              |

> **Never commit these to git.**

---

## 4. Add a Persistent Disk

Render free tier supports 1 GB disks.

- **Mount path:** `/var/www/html/storage/app`
- **Size:** 1 GB (increase as needed)

This persists:
- The SQLite database (`storage/app/database.sqlite`)
- Uploaded student/payment photos

---

## 5. Deploy

Push to your main branch. Render will:
1. Build the Docker image (multi-stage: Node → Composer → PHP-FPM + Nginx)
2. Run the entrypoint which:
   - Creates the SQLite file if missing
   - Runs `php artisan migrate --force`
   - Seeds the admin user (first run only)
   - Caches config/routes/views
3. Start Nginx + PHP-FPM + Queue worker via Supervisord

---

## 6. First login

| Field    | Value                          |
|----------|--------------------------------|
| Email    | value of `ADMIN_EMAIL`         |
| Password | value of `ADMIN_PASSWORD`      |

**Change the password immediately after first login.**

---

## Local Docker development

```bash
# Copy env
cp .env.example .env
# Edit .env and set APP_KEY, ADMIN_EMAIL, ADMIN_PASSWORD

# Build and run
docker compose up --build

# App available at http://localhost:8080
```

---

## Useful artisan commands (inside container)

```bash
docker compose exec app php artisan tinker
docker compose exec app php artisan migrate:status
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan cache:clear
```

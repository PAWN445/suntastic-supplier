# ☀ Suntastic Supplier — Setup Guide

## Mga Kailangan
- PHP 7.4 pataas (na may cURL enabled)
- Supabase account (libre sa supabase.com)

---

##testing ko i live. hahaha

## Hakbang 1: Gumawa ng Supabase Project

1. Pumunta sa https://supabase.com at mag-sign in
2. I-click ang **"New Project"**
3. Lagyan ng pangalan (hal. `suntastic-supplier`)
4. Piliin ang region na pinakamalapit (hal. Southeast Asia)
5. Hintayin na ma-setup (1-2 minuto)

---

## Hakbang 2: Gumawa ng Database Table

1. Sa Supabase dashboard, pumunta sa **SQL Editor**
2. I-click ang **"New Query"**
3. I-copy ang buong laman ng `SUPABASE_SETUP.sql`
4. I-paste at i-click **"Run"**

---

## Hakbang 3: Kunin ang API Keys

1. Sa Supabase dashboard, pumunta sa **Settings → API**
2. Kopyahin ang:
   - **Project URL** (hal. `https://abcdefghij.supabase.co`)
   - **anon/public key** (mahabang string na nagsisimula sa `eyJ...`)

---

## Hakbang 4: I-configure ang PHP App

1. Buksan ang `config.php`
2. Palitan ang mga value:

```php
define('SUPABASE_URL', 'https://YOUR_PROJECT_ID.supabase.co');  // ← dito
define('SUPABASE_ANON_KEY', 'eyJhbGc...');                       // ← dito
```

---

## Hakbang 5: I-run ang Website

**Option A — Gamit ang PHP built-in server:**
```bash
cd suntastic-supplier
php -S localhost:8000
```
Buksan ang browser: http://localhost:8000

**Option B — XAMPP/MAMP:**
- I-copy ang folder sa `htdocs` (XAMPP) o `www` (WAMP)
- Buksan ang: http://localhost/suntastic-supplier

---

## Istraktura ng Mga Files

```
suntastic-supplier/
├── index.php          ← Main page (listahan ng items)
├── add.php            ← Magdagdag ng bagong item
├── edit.php           ← I-edit ang item
├── delete.php         ← I-delete ang item
├── config.php         ← ⚠ Dito ilagay ang Supabase credentials
├── SUPABASE_SETUP.sql ← SQL para gumawa ng table
├── assets/
│   ├── css/style.css  ← Styling
│   └── js/main.js     ← JavaScript
└── README.md          ← Ito
```

---

## Mga Features

| Feature | Description |
|---------|-------------|
| ➕ Magdagdag | Idagdag ang bagong item + supplier info |
| ✎ I-edit | Baguhin ang existing na item |
| 🗑 I-delete | Tanggalin ang item (may confirmation) |
| 🔍 Maghanap | Hanapin sa pamamagitan ng item name o supplier |
| 📊 Stats | Tingnan ang total items, quantity, at suppliers |
| 📱 Responsive | Gumagana sa mobile at desktop |

---

## Mga Kulay ng Quantity Badge
- 🔴 **Pula** — Mababa (< 10 units) 
- 🟡 **Dilaw** — Katamtaman (10–49 units)
- 🟢 **Berde** — Sapat (50+ units)

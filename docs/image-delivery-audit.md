# Image delivery audit — 4 November 2026

Why images disappeared in the admin panel on 3–4 November, what was found, what was changed, and what
could still go wrong.

## 1. How images are stored and served

| Piece | Where | Who serves it |
|---|---|---|
| Original uploads (products, categories, branding, media library) | Cloudflare R2 bucket `images` | `https://media.bachastylo.com` — a Cloudflare-proxied custom domain (IPs 104.21.x / 172.67.x) |
| Website and admin | Cloudways server 178.128.46.9 | `https://www.bachastylo.com` — DNS only, **not** behind Cloudflare |
| Resized copies (`/img/{width}/…`) | `public/img` on the Cloudways server, generated on demand | `www.bachastylo.com`, as static files with a one-year cache header |
| Local copies of originals (new) | `storage/app/media-originals` on the server | not served; used to make copies |

Two facts shape everything below:

- The R2 API key in `.env` can **only upload** (PutObject). It cannot list, read or copy objects. So the
  server itself can only obtain an original by downloading it from `media.bachastylo.com` like any browser.
- The bucket's objects are stored as **AVIF** data even when named `.png` / `.jpg` / `.jfif` (an earlier
  upload pipeline converted them). Some have no content type at all (served as `application/octet-stream`).

## 2. What actually happened

1. **Media Library and picker empty (3 Nov).** The page listed the bucket with the API key, which returns
   401 for listing. The list was caught silently and shown as "no files". Fixed: the library is built from
   the media table plus every image referenced by products, categories, blog posts and settings (1,055
   images), with the bucket listing merged in only when the key allows it.
2. **Every picture broken on the admin pages, including the admin logo (4 Nov).** All of those were loaded
   directly from `media.bachastylo.com`. The same URLs returned HTTP 200 from this machine and from the
   Cloudways server at the same time, and still do with browser, empty, `python-requests` and `GuzzleHttp`
   user agents (no Cloudflare challenge is returned to any of them). The 240 px copies served by
   `www.bachastylo.com` displayed for you immediately. Conclusion: **your connection could not load
   `media.bachastylo.com` while it could load `www.bachastylo.com`**. The media domain is the only part of
   the site that goes through Cloudflare, so the likely causes are, in order:
   - a Cloudflare security setting on the bachastylo.com zone that challenges or blocks your IP/ISP/country
     (Security level, "I'm Under Attack" mode, Bot Fight Mode, a WAF custom rule, or a rate limit). An `<img>`
     cannot answer a challenge, so it shows the broken icon;
   - the ISP or network you were on failing to reach Cloudflare's 104.21/172.67 ranges (intermittent in
     Pakistan on some providers);
   - something on that PC: the malware that produced the fake "Cloudflare verification" popup on 2 Nov, a
     browser extension or an antivirus web filter. The admin logo failing too rules out the admin code.
   I cannot see the Cloudflare dashboard, so I cannot say which. The direct image URL opening (or not) in
   your browser at the moment of failure is the quickest way to tell: a Cloudflare page means a zone setting;
   a bare connection error means network or PC.
3. **Header logo on a black background (3 Nov).** Unrelated to the above: ImageMagick's HEIF decoder drops
   AVIF transparency. Logos now decode with GD, which keeps it. Verified on the live copy (alpha channel
   present, transparent corner pixel).

## 3. Can it happen again?

| Risk | Before | Now |
|---|---|---|
| Visitor cannot reach `media.bachastylo.com` | Every image on the storefront and admin came from it | Every `<img>` on the home, listing, product, cart, checkout, about and journal pages carries a `srcset` of copies served by `www` (checked: 0 media-only image tags on those pages). Admin lists, previews, settings, team, blog and the picker use `www` copies. |
| The **server** cannot reach `media.bachastylo.com` (Cloudflare blocks the datacenter IP, outage) | Copies could not be generated; requests fell back to the media URL | Originals are kept under `storage/app/media-originals` after the first fetch; `media:warm` pre-generates every width for every catalogue image nightly (03:30) and ran once today. Copies already on disk are served without any fetch. |
| A newly uploaded image | Fetched on first view | Same, but its original is kept from then on; the nightly job fills in the other widths. |
| Upload stored without a content type | Rejected as "not an image" → fallback to media URL | Accepted; the bytes are verified by decoding instead. |
| AVIF with transparency | Black background | Decoded with GD, transparency kept. |
| Cloudflare returning a challenge page as an "original" | Would have been cached as an image | Only bytes that decode as an image are kept. |
| `public/img` deleted or server rebuilt | — | Regenerated on demand and by the nightly job; originals cache survives independently. |
| Scheduled jobs | No cron existed; nothing scheduled ran | `schedule:run` cron installed for the app user (every minute). |

What still depends on `media.bachastylo.com`:

- `og:image` meta tags (used by Facebook/WhatsApp link previews, fetched by their crawlers, not by visitors).
- The plain `src` attribute next to each `srcset` (browsers ignore it when a `srcset` candidate matches).
- The product JSON from `/api/v1` (no longer used by any live frontend).
- The first fetch of any image uploaded after today, once, by the server.

## 4. Broken references in the data (not a delivery problem)

These URLs point at files that do not exist in the bucket (404), so they show the grey placeholder
everywhere, and every generation attempt is logged as a failure:

| Record | Missing file |
|---|---|
| Category "Special Deal Offers" (id 76) | `categories/60a86f08-e086-41c2-9cdb-17fef2c566d1.png` |
| Product BS-00406 "White Swati Shawl & Chitrali Cap Deal" | `products/381ba2bb-6147-4c31-abad-af4b34d3bf56.png` |
| Product BS-00407 "Off White Swati Shawl & Chitrali Cap Deal" | `products/6542d4a0-39c0-44d6-96e4-c315f36403b0.webp` |
| One more product image | `products/fa80862e-…` (see `media:warm` output for the record) |

Re-upload the images on those records in the admin panel.

## 5. Recommendations

1. **Cloudflare (you):** on the bachastylo.com zone, Security → Settings: security level Medium or lower,
   "I'm Under Attack" off, Bot Fight Mode off; check WAF custom rules and rate limiting for anything matching
   `media.bachastylo.com`. If you want, also check Security → Events for blocked requests from your IP on
   the day it failed — that is the definitive answer.
2. **R2 API key (you, optional):** issue a key with Object Read & Write and update `AWS_ACCESS_KEY_ID` /
   `AWS_SECRET_ACCESS_KEY` in the server `.env`. The server would then read originals straight from the
   bucket, bypassing Cloudflare entirely, and the Media Library could list the bucket itself.
3. **Monitoring:** `grep "\[img\] variant failed" storage/logs/laravel.log` on the server shows generation
   failures; a sudden rise in "source responded 403/503" would mean the server is being blocked by Cloudflare.
4. Fix the four broken references above.

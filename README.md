# Who's In – Sign In App PHP Display

A simple PHP page that shows **who is currently signed in** at a given site using the [Sign In App Client API]. It fetches today's sign‑ins for a site, groups them, and renders each signed‑in person as a tile with their photo and name.

> **What this does**: Calls `GET /client-api/v1/sites/{siteId}/today` with Basic Auth, then displays any visitors with `status == "signed_in"`.

---

## 📊 Status & Info

![Last Commit](https://img.shields.io/github/last-commit/BlindTrevor/SignInAppWallboard)
![Issues](https://img.shields.io/github/issues/BlindTrevor/SignInAppWallboard)
![Repo Size](https://img.shields.io/github/repo-size/BlindTrevor/SignInAppWallboard)

---

## Table of Contents
- [Features](#features)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Environment Variables (recommended)](#environment-variables-recommended)
- [Security Notes](#security-notes)
- [Deployment](#deployment)
- [Customising the UI](#customising-the-ui)
- [Error Handling & Troubleshooting](#error-handling--troubleshooting)
- [Rate Limiting & Caching](#rate-limiting--caching)
- [Local Development](#local-development)
- [Directory Layout](#directory-layout)
- [License](#license)

---

## Features
- Fetches current day sign‑ins for a specific site from Sign In App.
- Groups by visitor groups (`$item['name']`) and shows only those **signed in**.
- Sorts names alphabetically within each group.
- Optional **IP allow‑list** to restrict page visibility.
- Simple, readable tile layout with circular avatar images.

## Prerequisites
- PHP **7.4+** (tested with 7.4/8.x)
- `curl` extension enabled in PHP
- A web server (Apache, Nginx, IIS) capable of running PHP
- Sign In App **Client API** credentials:
  - API Key
  - API Secret
  - Site ID

## Quick Start
1. Copy the provided PHP file to your web server (e.g. `whos-in.php`).
2. Edit the configuration at the top of the file:
   ```php
   $baseUrl = 'https://backend.signinapp.com/client-api/v1';
   $key = 'YOUR_API_KEY';
   $secret = 'YOUR_API_SECRET';
   $siteId = 'YOUR_SITE_ID';

   // Optional IP restriction
   $limitIpAddress = true; // set to false to disable
   $allowedIPs = array('1.2.3.4','5.6.7.8');
   ```
3. Ensure your server can make outbound HTTPS requests.
4. Browse to the page (e.g. `https://yourdomain/whos-in.php`).

If credentials and site ID are valid, you’ll see groups with tiles for each signed‑in person.

## Configuration
The top of the script contains these settings:
- `baseUrl`: API base URL (keep as default unless Sign In App advises otherwise).
- `key` and `secret`: Client API credentials from Sign In App.
- `siteId`: Your specific site’s ID.
- `limitIpAddress` (bool): If `true`, only requests from `allowedIPs` will be served.
- `allowedIPs` (array): List of IPv4 addresses allowed to access the page.

## Environment Variables (recommended)
To avoid committing secrets in source code, load credentials from environment variables.

Example using PHP’s `getenv()`:
```php
$key = getenv('SIA_CLIENT_KEY');
$secret = getenv('SIA_CLIENT_SECRET');
$siteId = getenv('SIA_SITE_ID');
```

On Linux with Apache, set in your vhost or `.htaccess`:
```apacheconf
SetEnv SIA_CLIENT_KEY "xxxx"
SetEnv SIA_CLIENT_SECRET "yyyy"
SetEnv SIA_SITE_ID "12345"
```

On Nginx with PHP‑FPM, set in the `php-fpm.conf` pool or systemd unit:
```ini
; php-fpm pool
env[SIA_CLIENT_KEY] = xxxx
env[SIA_CLIENT_SECRET] = yyyy
env[SIA_SITE_ID] = 12345
```

Or use a `.env` loader library if your framework supports it.

## Security Notes
- **Never** commit API keys/secrets to source control.
- Prefer environment variables or a secrets manager (e.g., 1Password, Azure Key Vault).
- If exposing on a public URL, consider additional protections:
  - IP allow‑list (already included)
  - Basic Auth at the web server level
  - Network restrictions (VPN, internal only)
- Validate/escape output if you expand the template (current data fields are assumed safe from the API, but sanitise as good practice).

## Error Handling & Troubleshooting
- **Empty page or no groups shown**: Verify site ID is correct and there are sign‑ins today.
- **Auth errors**: Check API key/secret values; ensure Basic Auth header is built as `base64(key:secret)`.
- **cURL not installed/enabled**: Enable `php-curl` extension and restart web server.
- **IP blocked message**: Disable `limitIpAddress` or add your client IP to `allowedIPs`.
- **Slow image loads**: The `photo_url` is loaded from the API response; consider caching or a placeholder.
- **JSON decode issues**: Log `$response` to diagnose API or network errors.

---

## License
This example is provided "as is" without warranty. Adapt and integrate into your own project as needed. Check your Sign In App agreement and API terms before production use.


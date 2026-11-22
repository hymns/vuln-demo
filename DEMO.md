# Answer key

Everything planted in StayEasy, where it is, and what in Vuln Scanner should
report it. "Verified" means the flaw was exploited against this code with a
real payload, not just reasoned about. The "AI found" column is from a real
Claude Sonnet 5 review of these files on 22 November 2026 (9 files, $0.22).
Semgrep's PHP rule packs found none of the code flaws.

## Code — looks secure, isn't

| # | Where | Looks like | Actually | Category | AI found |
|---|---|---|---|---|---|
| 1 | `app/Support/BookingSort.php`, `BookingController@index` | Sort column allow-list | `Str::startsWith` accepts `created_at, (CASE WHEN … END)` → SQL injection in `orderByRaw` (verified: subquery executed) | injection | ✅ |
| 2 | `BookingController@update` | `authorize('update', $booking)` | Authorizes the booking in the URL, then updates `booking_id` from the form → edit anyone's booking (IDOR) | authorization | ✅ |
| 3 | `BookingController@invoice` | "Reference isn't the id" | Sequential `BK-000123` references, no owner check → download anyone's invoice (IDOR) | authorization | ✅ |
| 4 | `BookingController@attachment` | `str_replace('../', '')` | `....//....//.env` becomes `../../.env` (verified) → path traversal | path-traversal | ✅ |
| 5 | `BookingController@guestLookup` | 6-digit code + email | `mt_rand` code, no rate limit → brute-forceable in ≤1M tries | authentication / cryptography | ✅ |
| 6 | `BookingController@store` | Coupon `isAvailable()` check | Check-then-increment without a lock → single-use coupon reused concurrently | business-logic | ✅ |
| 7 | `StoreBookingRequest` | `integer\|max:10` on guests | No `min:1` → negative guests, negative total (verified: `-3` passes) | business-logic | ❌ missed |
| 8 | `PaymentController@callback` | HMAC + `hash_equals` | Never compares the paid amount or the gateway's order with the booking → pay RM1, booking marked paid | business-logic | ❌ missed |
| 9 | `PaymentController@return` | Only `/` or `APP_URL` allowed | `//evil.example` starts with `/` (verified) → open redirect | open-redirect | ✅ |
| 10 | `ProfileController@update` | Validated, and `role` is guarded | `$request->except(['password','role'])` still passes `is_admin` (verified) → self-promotion to admin | mass-assignment | ✅ |
| 11 | `RoomImageController@preview` | URL validation + blocked hosts | Blocks `localhost`/`127.0.0.1` only → `169.254.169.254`, `0.0.0.0`, decimal IPs, redirects → SSRF | ssrf | ✅ |
| 12 | `CartController` | HMAC-signed cookie, `hash_equals` | Key is `APP_NAME . '-cart'` ("StayEasy-cart") → forge cookie → `unserialize` → object injection | deserialization | ✅ |
| 13 | `Api/MobileAuthController` | JWT with RS256 | Also accepts HS256 with the *public* key as secret → forge any user's token (php-jwt 5.x, CVE-2021-46743) | authentication | ✅ |
| 14 | `resources/views/bookings/show.blade.php` | `{{ }}` everywhere | `{!! Str::markdown($notes) !!}` passes raw HTML (verified: `<img onerror>` survives) → stored XSS | xss | ✅ |
| 15 | `resources/views/invoices/pdf.blade.php` | Same markdown | User HTML rendered by mPDF → `<img src>` fetches internal URLs while generating the PDF | ssrf | not reviewed |
| 16 | `routes/web.php` | `can:admin` group | `/admin/reports/export` sits outside the group → any user exports every guest's email, phone and lookup code | authorization | ✅ |
| 17 | `config/services.php` | `env()` lookup | Hardcoded `sk_live_…` fallback | secrets | Gitleaks |
| 18 | `VerifyCsrfToken` | Gateway needs an exemption | `payments/*` exempts every payment route, not just the callback | csrf | — |

## Dependencies (OSV-Scanner)

80 advisories across 12 packages at the time of writing:

| Lockfile | Package | Version | Advisories |
|---|---|---|---|
| composer.lock | laravel/framework | 10.48.0 | 7 |
| composer.lock | guzzlehttp/guzzle | 7.4.4 | 11 |
| composer.lock | guzzlehttp/psr7 | 2.4.4 | 5 |
| composer.lock | firebase/php-jwt | 5.5.1 | 2 |
| composer.lock | phpmailer/phpmailer | 6.4.0 | 3 |
| composer.lock | phpseclib/phpseclib | 3.0.14 | 10 |
| package-lock.json | axios | 0.21.1 | 24 |
| package-lock.json | lodash | 4.17.15 | 5 |
| package-lock.json | moment | 2.29.1 | 2 |
| package-lock.json | jquery | 3.4.1 | 2 |
| package-lock.json | vite / esbuild | 5.4.21 / 0.21.5 | 8 / 1 |

php-jwt is the one used vulnerably in code (#13). mPDF 8.1 is GPL-2.0, which
Trivy's license check reports.

## Configuration

- **Dockerfile** (Hadolint, Trivy): runs as root, `curl | php` installer, unpinned
  apt packages, apt lists left behind.
- **deploy/k8s/deployment.yaml** (Trivy, Semgrep): mounts the Docker socket,
  `allowPrivilegeEscalation: true`, `:latest` image, no resource limits.
- **.github/actions/preview/action.yml** (zizmor, 8 findings): a composite action
  that interpolates the PR title, branch and a token straight into `run:`
  (template injection), checks out the PR head, and pins actions to tags instead
  of commits. No workflow calls it, so it never runs on GitHub; zizmor reads it
  all the same.

# Route Crawl v3 Results — 2025-02-07

## Summary

| Metric            | Value                               |
| ----------------- | ----------------------------------- |
| Total routes      | 835                                 |
| Crawl time        | 582 s                               |
| Auth method       | Session cookie (super admin)        |
| Delay per request | 250 ms (exponential backoff on 429) |
| Re-auth interval  | Every 100 routes                    |

## HTTP Status Distribution

| Status | Count | % of Total |
| ------ | ----- | ---------- |
| 200    | 297   | 35.6%      |
| 302    | 340   | 40.7%      |
| 404    | 157   | 18.8%      |
| 500    | 20    | 2.4%       |
| 429    | 10    | 1.2%       |
| Other  | 11    | 1.3%       |

## Body Quality Analysis

| Quality  | Count | Notes                                                                           |
| -------- | ----- | ------------------------------------------------------------------------------- |
| good     | 57    | Full HTML with no PHP errors                                                    |
| warn     | 127   | Missing `<html>` / `<!DOCTYPE>`                                                 |
| err      | 310   | **Mostly false positives** — regex `Warning:\|Notice:` matches normal page text |
| empty    | 1     | Empty body                                                                      |
| redirect | 340   | 302 redirects (body not checked)                                                |

> ⚠️ The 310 "err" body results are almost all false positives. The regex
> `/Fatal error|Parse error|Warning:|Notice:/i` matches UI labels like
> "Warning" in navbar text. Only 2 pages had actual Whoops/Ignition screens.

## 20 HTTP 500 URLs

| #   | URL                                       | Likely Cause                                                      |
| --- | ----------------------------------------- | ----------------------------------------------------------------- |
| 1   | `/_debugbars/clockworks/1`                | Debugbar infrastructure route, not a real page                    |
| 2   | `/deals/1/tasks`                          | Missing Deal #1 or task relationship issue                        |
| 3   | `/deals/1/tasks/1/edit`                   | Same as above                                                     |
| 4   | `/deals/1/tasks/1/show`                   | Same as above                                                     |
| 5   | `/email_template_stores/1`                | EmailTemplate store route, missing resource                       |
| 6   | `/fortify-login`                          | Fortify auth route — not fully configured                         |
| 7   | `/fortify-register`                       | Fortify auth route — not fully configured                         |
| 8   | `/invoices/benefits/1/1`                  | Benefit callback with bad params                                  |
| 9   | `/journal_entries/create`                 | **Real Whoops page** — likely missing ChartOfAccount relationship |
| 10  | `/projects.timesheets/projects/updates/1` | Malformed route path (`.` instead of `/`)                         |
| 11  | `/projects/copy-links/1`                  | Copy-link controller view naming mismatch                         |
| 12  | `/projects/1/users/1/permission`          | Permission route, missing user/project                            |
| 13  | `/register`                               | Registration disabled or misconfigured                            |
| 14  | `/reset-passwords/1`                      | Password reset with invalid token                                 |
| 15  | `/share-projects/en`                      | Project sharing with locale param                                 |
| 16  | `/store-language`                         | Language store POST route hit with GET                            |
| 17  | `/users/confirm-password`                 | Confirm password route (Fortify)                                  |
| 18  | `/users/profile`                          | Profile route, possible auth issue                                |
| 19  | `/webhook-settings/create`                | Webhook creation view error                                       |
| 20  | `/1/notifications/seen`                   | Notification route with bad company prefix                        |

## Whoops/Ignition Pages (Real Errors)

Only **1 confirmed real Whoops page**: `/journal_entries/create`

The other detection (`/_debugbars/clockworks/1`) is a debugbar CSS route — false positive.

## Log Files

- `/tmp/crawl_v3_results.log` — Full per-route results
- `/tmp/crawl_v3_500s.log` — HTTP 500 details
- `/tmp/crawl_v3_bodies.log` — Body quality issues
- `/tmp/crawl_v3_console.log` — Console output / summary

## Improvement vs Previous Crawls

| Crawl        | 500 Errors | Routes |
| ------------ | ---------- | ------ |
| v1 (cycle 1) | 205        | 803    |
| v1 (cycle 9) | 13         | 803    |
| v3           | 20         | 835    |

> The increase from 13 → 20 is due to 32 additional routes being discovered
> in v3 (835 vs 803), plus rate-limited retries revealing previously-skipped
> routes. The core application routes are stable.

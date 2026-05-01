# ERP Brand New Ideas Company — Complete Route Map

> Generated from `routes/web.php` (1985 lines), `routes/api.php`, and `routes/auth.php`.
> All constants resolved from `ViewsConstants`, `DatabaseConstants`, `MiddlewaresConstants`, `RoutesResourcesConstants`.

---

## Legend

| Abbrev         | Meaning                                                           |
| -------------- | ----------------------------------------------------------------- |
| **MW**         | Middleware                                                        |
| `auth`         | Authenticated users                                               |
| `XSS`          | XSS sanitization                                                  |
| `revalidate`   | Cache revalidation                                                |
| `verified`     | Email verified                                                    |
| `guest`        | Unauthenticated only                                              |
| `throttle:N,M` | Rate limit N requests per M minutes                               |
| `signed`       | Signed URL required                                               |
| `set`          | Settings middleware                                               |
| **R**          | Resource (expands to index/create/store/show/edit/update/destroy) |
| `{id}`         | Dynamic param (type noted in Params column)                       |

### Resource Route Expansion

A `R::resource('name', Controller::class)` expands to:

| Method    | URI                 | Action    |
| --------- | ------------------- | --------- |
| GET       | `/name`             | `index`   |
| GET       | `/name/create`      | `create`  |
| POST      | `/name`             | `store`   |
| GET       | `/name/{name}`      | `show`    |
| GET       | `/name/{name}/edit` | `edit`    |
| PUT/PATCH | `/name/{name}`      | `update`  |
| DELETE    | `/name/{name}`      | `destroy` |

---

## A. AUTH / AUTHENTICATION ROUTES

**File: `routes/auth.php`**

| #   | Method | URI                                | Controller                              | Action     | Route Name            | Middleware                      | Params                       |
| --- | ------ | ---------------------------------- | --------------------------------------- | ---------- | --------------------- | ------------------------------- | ---------------------------- |
| A1  | GET    | `/login/{lang?}`                   | AuthenticatedSessionController          | SHW_LG_FM  | `login`               | web, guest                      | `lang`: string, optional     |
| A2  | GET    | `/register/{lang?}`                | RegisteredUserController                | SHW_RG_FM  | `register`            | web, guest                      | `lang`: string, optional     |
| A3  | GET    | `/forgot-password/{lang?}`         | AuthenticatedSessionController          | SHW_LG_RQ  | `password.request`    | web, guest                      | `lang`: string, optional     |
| A4  | GET    | `/reset-password/{token}`          | NewPasswordController                   | create     | `password.reset`      | web                             | `token`: string (UUID/token) |
| A5  | POST   | `/login`                           | AuthenticatedSessionController          | store      | `login.store`         | web, XSS, throttle:10,1         | —                            |
| A6  | POST   | `/register`                        | RegisteredUserController                | store      | `register.store`      | web, XSS, throttle:10,1         | —                            |
| A7  | POST   | `/forgot-password`                 | PasswordResetLinkController             | store      | `password.email`      | web, XSS, throttle:10,1         | —                            |
| A8  | POST   | `/reset-password`                  | NewPasswordController                   | store      | `password.update`     | web, XSS, throttle:10,1         | —                            |
| A9  | GET    | `/verify/{lang?}`                  | EmailVerificationPromptController       | \_\_invoke | `verification.notice` | web, auth                       | `lang`: string, optional     |
| A10 | GET    | `/confirm-password`                | ConfirmablePasswordController           | show       | `password.confirm`    | web, auth                       | —                            |
| A11 | POST   | `/logout`                          | AuthenticatedSessionController          | destroy    | `logout`              | web, auth, throttle:6,1         | —                            |
| A12 | POST   | `/email/verification-notification` | EmailVerificationNotificationController | store      | `verification.send`   | web, auth, throttle:6,1         | —                            |
| A13 | GET    | `/verify/{id}/{hash}`              | VerifyEmailController                   | \_\_invoke | `verification.verify` | web, auth, throttle:6,1, signed | `id`: int; `hash`: string    |
| A14 | POST   | `/confirm-password`                | ConfirmablePasswordController           | store      | —                     | web, auth, throttle:6,1         | —                            |

---

## B. ROUTE OVERRIDES (Chatify + 2FA)

| #   | Method | URI                           | Controller                             | Action   | Route Name                   | Middleware | Params             |
| --- | ------ | ----------------------------- | -------------------------------------- | -------- | ---------------------------- | ---------- | ------------------ |
| B1  | GET    | `chats/downloads/{fileName}`  | Closure (MessagesController)           | download | `chatify.download.safe`      | web, auth  | `fileName`: string |
| B2  | GET    | `/user/two-factor-secret-key` | Closure (TwoFactorSecretKeyController) | show     | `two-factor.secret-key.safe` | web, auth  | —                  |

---

## C. HOME / DASHBOARD

| #   | Method | URI                  | Controller          | Action      | Route Name               | Middleware                            | Params |
| --- | ------ | -------------------- | ------------------- | ----------- | ------------------------ | ------------------------------------- | ------ |
| C1  | GET    | `/`                  | DashboardController | ACC_DSB_IDX | `home_section.index`     | web, auth                             | —      |
| C2  | GET    | `/home` (VW::HM)     | DashboardController | ACC_DSB_IDX | `home_section.index.alt` | web, auth                             | —      |
| C3  | GET    | `/dashboard`         | DashboardController | ACC_DSB_IDX | `dashboard`              | XSS, revalidate                       | —      |
| C4  | GET    | `/account-dashboard` | DashboardController | ACC_DSB_IDX | `dashboard`              | auth, verified, XSS, revalidate       | —      |
| C5  | GET    | `/project-dashboard` | DashboardController | PRJ_DSB_IDX | `projects.dashboard`     | auth, verified, auth, XSS, revalidate | —      |
| C6  | GET    | `/hrm-dashboard`     | DashboardController | HRM_DSB_IDX | `hrm.dashboard`          | auth, verified, auth, XSS, revalidate | —      |
| C7  | GET    | `/crm-dashboard`     | DashboardController | CRM_DSB_IDX | `crm.dashboard`          | auth, verified, auth, XSS, revalidate | —      |
| C8  | GET    | `/pos-dashboard`     | DashboardController | POS_DSB_IDX | `pos.dashboard`          | auth, verified, auth, XSS, revalidate | —      |
| C9  | GET    | `dashboard-view`     | DashboardController | FT_VW       | `dashboard.view`         | auth, XSS                             | —      |
| C10 | GET    | `dashboard`          | DashboardController | CL_VW       | `client.dashboard.view`  | auth, XSS                             | —      |
| C11 | POST   | `stop-tracker`       | DashboardController | STP_TRK     | `stop.tracker`           | auth, XSS                             | —      |
| C12 | GET    | `/change/mode`       | UserController      | CHG_MD      | `change.mode`            | auth, XSS                             | —      |

---

## D. ADMIN / AUTH (Users, Roles, Permissions, Settings)

### D1. Users

| #   | Method                       | URI                         | Controller     | Action           | Route Name              | Middleware                            | Params                    |
| --- | ---------------------------- | --------------------------- | -------------- | ---------------- | ----------------------- | ------------------------------------- | ------------------------- |
| D1a | GET                          | `profile`                   | UserController | PRF              | `user.profile`          | auth, verified, auth, XSS, revalidate | —                         |
| D1b | GET/POST                     | `edit-profile`              | UserController | EDT_PRF          | `users.account.update`  | auth, verified, auth, XSS, revalidate | —                         |
| D1c | POST                         | `change-password`           | UserController | UPD_PSW          | `users.password.update` | auth, verified                        | —                         |
| D1d | GET/PUT/PATCH/DELETE/OPTIONS | `user-reset-password/{id}`  | UserController | USR_PSW          | `users.reset`           | auth, verified                        | `id`: int/UUID            |
| D1e | POST                         | `user-reset-password/{id}`  | UserController | USR_PSW_RST      | `users.password.update` | auth, verified                        | `id`: int/UUID            |
| D1f | **RESOURCE**                 | `users`                     | UserController | R                | `users.*`               | auth, XSS, revalidate                 | `{user}`: int             |
| D1g | GET                          | `users/{view?}`             | UserController | index            | `users`                 | auth, XSS                             | `view`: string, optional  |
| D1h | GET                          | `users-view`                | UserController | FLT_USR_VW       | `filter.user.view`      | auth, XSS                             | —                         |
| D1i | GET                          | `checkuserexists`           | UserController | CHK_USR_EXT      | `users.exists`          | auth, XSS                             | —                         |
| D1j | POST                         | `/profile`                  | UserController | updateProfile    | `update.profile`        | auth, XSS                             | —                         |
| D1k | GET                          | `users/info/{id}`           | UserController | userInfo         | `users.info`            | auth, XSS                             | `id`: int                 |
| D1l | GET                          | `users/{id}/info/{type}`    | UserController | getProjectTask   | `users.info.popup`      | auth, XSS                             | `id`: int; `type`: string |
| D1m | DELETE                       | `users/{id}`                | UserController | destroy          | `users.destroy`         | auth, XSS                             | `id`: int                 |
| D1n | GET                          | `/{uid}/notifications/seen` | UserController | notificationSeen | `notifications.seen`    | auth, verified                        | `uid`: int                |

### D2. User Logs

| #   | Method | URI               | Controller     | Action       | Route Name          | Middleware | Params    |
| --- | ------ | ----------------- | -------------- | ------------ | ------------------- | ---------- | --------- |
| D2a | GET    | `users/logs`      | UserController | USR_LOG      | `users.log`         | auth, XSS  | —         |
| D2b | GET    | `users/logs/{id}` | UserController | USR_LOG_VIEW | `users.log.view`    | auth, XSS  | `id`: int |
| D2c | DELETE | `users/logs/{id}` | UserController | USR_LOG_DSTR | `users.log.destroy` | auth, XSS  | `id`: int |

### D3. Roles & Permissions

| #   | Method       | URI           | Controller           | Action | Route Name      | Middleware                            | Params              |
| --- | ------------ | ------------- | -------------------- | ------ | --------------- | ------------------------------------- | ------------------- |
| D3a | **RESOURCE** | `roles`       | RoleController       | R      | `roles.*`       | auth, verified, auth, XSS, revalidate | `{role}`: int       |
| D3b | **RESOURCE** | `permissions` | PermissionController | R      | `permissions.*` | auth, verified, auth, XSS, revalidate | `{permission}`: int |

### D4. Plans (SaaS)

| #   | Method       | URI                     | Controller     | Action  | Route Name      | Middleware            | Params                |
| --- | ------------ | ----------------------- | -------------- | ------- | --------------- | --------------------- | --------------------- |
| D4a | GET          | `users/{id}/plan`       | UserController | UPG_PLN | `plans.upgrade` | auth, XSS             | `id`: int             |
| D4b | GET          | `users/{id}/plan/{pid}` | UserController | ACT_PLN | `plans.active`  | auth, XSS             | `id`: int; `pid`: int |
| D4c | **RESOURCE** | `plans`                 | PlanController | R       | `plans.*`       | auth, XSS, revalidate | `{plan}`: int         |

### D5. Plan Requests

| #   | Method | URI                                | Controller            | Action | Route Name                       | Middleware | Params                                              |
| --- | ------ | ---------------------------------- | --------------------- | ------ | -------------------------------- | ---------- | --------------------------------------------------- |
| D5a | GET    | `plan_requests`                    | PlanRequestController | index  | `plan_requests.index`            | auth, XSS  | —                                                   |
| D5b | GET    | `request_frequency/{id}`           | PlanRequestController | RQ_VW  | `plan_requests.request.view`     | auth, XSS  | `id`: int                                           |
| D5c | GET    | `request_send/{id}`                | PlanRequestController | USR_RQ | `plan_requests.request.send`     | auth, XSS  | `id`: int                                           |
| D5d | GET    | `request_response/{id}/{response}` | PlanRequestController | AC_RQ  | `plan_requests.request.response` | auth, XSS  | `id`: int; `response`: string (enum: accept/reject) |
| D5e | GET    | `request_cancel/{id}`              | PlanRequestController | CC_RQ  | `plan_requests.request.cancel`   | auth, XSS  | `id`: int                                           |

### D6. System Settings

| #    | Method       | URI                           | Controller       | Action                     | Route Name                    | Middleware                       | Params          |
| ---- | ------------ | ----------------------------- | ---------------- | -------------------------- | ----------------------------- | -------------------------------- | --------------- |
| D6a  | POST         | `email-settings`              | SystemController | SV_EM_ST                   | `email.settings`              | auth, XSS, revalidate            | —               |
| D6b  | POST         | `company-email-settings`      | SystemController | SV_CP_EM_ST                | `companies.email.settings`    | auth, XSS, revalidate            | —               |
| D6c  | POST         | `company-settings`            | SystemController | SV_CP_ST                   | `companies.settings`          | auth, XSS, revalidate            | —               |
| D6d  | POST         | `system-settings`             | SystemController | SV_SYS_ST                  | `systems.settings`            | auth, XSS, revalidate            | —               |
| D6e  | POST         | `zoom-settings`               | SystemController | SV_ZM_ST                   | `zoom.settings`               | auth, XSS, revalidate            | —               |
| D6f  | POST         | `tracker-settings`            | SystemController | SV_TK_ST                   | `time_trackers.settings`      | auth, XSS, revalidate            | —               |
| D6g  | POST         | `slack-settings`              | SystemController | SV_SLK_ST                  | `slack.settings`              | auth, XSS, revalidate            | —               |
| D6h  | POST         | `telegram-settings`           | SystemController | SV_TLG_ST                  | `telegram.settings`           | auth, XSS, revalidate            | —               |
| D6i  | POST         | `twilio-settings`             | SystemController | SV_TWL_ST                  | `twilio.setting`              | auth, XSS, revalidate            | —               |
| D6j  | GET          | `print-setting`               | SystemController | PRT                        | `print.setting`               | auth, XSS, revalidate            | —               |
| D6k  | GET          | `settings`                    | SystemController | CP                         | `settings`                    | auth, XSS, revalidate            | —               |
| D6l  | POST         | `business-setting`            | SystemController | SV_BS_ST                   | `business.setting`            | auth, XSS, revalidate            | —               |
| D6m  | POST         | `company-payment-setting`     | SystemController | SV_CP_PAY_ST               | `companies.payment.settings`  | auth, XSS, revalidate            | —               |
| D6n  | GET          | `test-mail`                   | SystemController | TT_MAIL                    | `tests.mail`                  | auth, XSS, revalidate            | —               |
| D6o  | POST         | `test-mail`                   | SystemController | TT_MAIL                    | `tests.mail`                  | auth, XSS, revalidate            | —               |
| D6p  | POST         | `test-mail/send`              | SystemController | TT_SMAIL                   | `tests.send.mail`             | auth, XSS, revalidate            | —               |
| D6q  | POST         | `stripe-settings`             | SystemController | SV_PAY_ST                  | `payments.settings`           | auth, XSS, revalidate            | —               |
| D6r  | POST         | `pusher-setting`              | SystemController | SV_PSR_ST                  | `settings.pusher`             | auth, XSS, revalidate            | —               |
| D6s  | POST         | `recaptcha-settings`          | SystemController | RCP_ST_STR                 | `settings.recaptcha.store`    | auth, XSS                        | —               |
| D6t  | POST         | `seo-settings`                | SystemController | SEO_ST                     | `settings.seo.store`          | auth, XSS                        | —               |
| D6u  | ANY          | `webhook-settings`            | SystemController | webhook                    | `webhooks.settings`           | auth, XSS                        | —               |
| D6v  | GET          | `webhook-settings/create`     | SystemController | WHK_CRT                    | `webhooks.create`             | auth, XSS                        | —               |
| D6w  | POST         | `webhook-settings/store`      | SystemController | WHK_STR                    | `webhooks.store`              | auth, XSS, revalidate            | —               |
| D6x  | GET          | `webhook-settings/{wid}/edit` | SystemController | WHK_EDT                    | `webhooks.edit`               | auth, XSS                        | `wid`: int      |
| D6y  | POST         | `webhook-settings/{wid}/edit` | SystemController | WHK_UPD                    | `webhooks.update`             | auth, XSS                        | `wid`: int      |
| D6z  | DELETE       | `webhook-settings/{wid}`      | SystemController | WHK_DST                    | `webhooks.destroy`            | auth, XSS                        | `wid`: int      |
| D6aa | POST         | `cookie-setting`              | SystemController | SV_CK_ST                   | `settings.cookies.store`      | auth, XSS, revalidate            | —               |
| D6ab | POST         | `cache-settings`              | SystemController | CC_ST_STR                  | `cache.settings.store`        | auth, XSS                        | —               |
| D6ac | **RESOURCE** | `systems`                     | SystemController | R                          | `systems.*`                   | auth, XSS, revalidate            | `{system}`: int |
| D6ad | POST         | `storage-settings`            | SystemController | storageSettingStore        | `settings.storage.store`      | auth, XSS                        | —               |
| D6ae | POST         | `system-settings/note`        | SystemController | FT_NT_STR                  | `systems.settings.footernote` | auth, XSS                        | —               |
| D6af | POST         | `chatgpt-settings`            | SystemController | CHTGPT_ST                  | `settings.chatgpt.settings`   | —                                | —               |
| D6ag | POST         | `settings/google-calendar`    | SystemController | saveGooglecalendarSettings | `settingsgoogle.calendar`     | auth, XSS, revalidate (in group) | —               |
| D6ah | GET          | `pos-print-setting`           | SystemController | posPrintIndex              | `pos.print.setting`           | auth, XSS                        | —               |

### D7. Offer/Joining/Experience/NOC Letters (Settings)

| #   | Method | URI                               | Controller       | Action      | Route Name                                 | Middleware | Params                   |
| --- | ------ | --------------------------------- | ---------------- | ----------- | ------------------------------------------ | ---------- | ------------------------ |
| D7a | POST   | `settings/offer-letter/{lang?}`   | SystemController | OFR_LTR_UPD | `offer_letter.update`                      | auth, XSS  | `lang`: string, optional |
| D7b | GET    | `settings/offer-letter`           | SystemController | CP          | `settings.offer_letter.language`           | auth, XSS  | —                        |
| D7c | POST   | `settings/joining-letter/{lang?}` | SystemController | JN_LTR_UPD  | `joining_letter.update`                    | auth, XSS  | `lang`: string, optional |
| D7d | GET    | `settings/joining-letter`         | SystemController | CP          | `settingsjoining_letter.language`          | auth, XSS  | —                        |
| D7e | POST   | `settings/exp/{lang?}`            | SystemController | EXP_CRT_UPD | `experience_certificate.update`            | auth, XSS  | `lang`: string, optional |
| D7f | GET    | `settings/exp`                    | SystemController | CP          | `settings.experience_certificate.language` | auth, XSS  | —                        |
| D7g | POST   | `settings/noc/{lang?}`            | SystemController | NOC_UPD     | `noc.update`                               | auth, XSS  | `lang`: string, optional |
| D7h | GET    | `settings/noc`                    | SystemController | CP          | `settings.noc.language`                    | auth, XSS  | —                        |

### D8. IP Controls

| #   | Method | URI                       | Controller       | Action  | Route Name           | Middleware | Params    |
| --- | ------ | ------------------------- | ---------------- | ------- | -------------------- | ---------- | --------- |
| D8a | GET    | `systems/create/ip`       | SystemController | CR_IP   | `systems.ip.create`  | auth, XSS  | —         |
| D8b | POST   | `systems/create/ip`       | SystemController | STR_IP  | `systems.ip.store`   | auth, XSS  | —         |
| D8c | GET    | `systems/edit/ip/{id}`    | SystemController | EDT_IP  | `systems.ip.edit`    | auth, XSS  | `id`: int |
| D8d | POST   | `systems/edit/ip/{id}`    | SystemController | UPD_IP  | `systems.ip.update`  | auth, XSS  | `id`: int |
| D8e | DELETE | `systems/destroy/ip/{id}` | SystemController | DSTR_IP | `systems.ip.destroy` | auth, XSS  | `id`: int |

### D9. Languages

| #   | Method | URI                          | Controller         | Action     | Route Name             | Middleware            | Params         |
| --- | ------ | ---------------------------- | ------------------ | ---------- | ---------------------- | --------------------- | -------------- |
| D9a | GET    | `change-language/{lang}`     | LanguageController | CHG_LNG    | `languages.change`     | auth, XSS, revalidate | `lang`: string |
| D9b | GET    | `manage-language/{lang}`     | LanguageController | MNG_LNG    | `languages.manage`     | auth, XSS, revalidate | `lang`: string |
| D9c | POST   | `store-language-data/{lang}` | LanguageController | STR_LNG_DT | `languages.store.data` | auth, XSS, revalidate | `lang`: string |
| D9d | GET    | `create-language`            | LanguageController | CR_LNG     | `languages.create`     | auth, XSS, revalidate | —              |
| D9e | ANY    | `store-language`             | LanguageController | STR_LNG    | `languages.store`      | auth, XSS, revalidate | —              |
| D9f | DELETE | `/lang/{lang}`               | LanguageController | DEL_LNG    | `languages.destroy`    | auth, XSS, revalidate | `lang`: string |
| D9g | POST   | `disable-language`           | LanguageController | DSB_LNG    | `language.disable`     | auth, XSS             | —              |

### D10. Coupons

| #    | Method       | URI             | Controller       | Action | Route Name      | Middleware            | Params          |
| ---- | ------------ | --------------- | ---------------- | ------ | --------------- | --------------------- | --------------- |
| D10a | GET          | `coupons/apply` | CouponController | AP_CPN | `coupons.apply` | auth, XSS, revalidate | —               |
| D10b | **RESOURCE** | `coupons`       | CouponController | R      | `coupons.*`     | auth, XSS, revalidate | `{coupon}`: int |

### D11. Email Templates

| #    | Method       | URI                                | Controller              | Action     | Route Name               | Middleware | Params                              |
| ---- | ------------ | ---------------------------------- | ----------------------- | ---------- | ------------------------ | ---------- | ----------------------------------- |
| D11a | GET          | `email_template_lang/{id}/{lang?}` | EmailTemplateController | MNG_EM_LNG | `emails.manage.language` | auth, XSS  | `id`: int; `lang`: string, optional |
| D11b | ANY          | `email_template_store`             | EmailTemplateController | UPD_STT    | `emails.status.language` | auth       | —                                   |
| D11c | ANY          | `email_template_store/{pid}`       | EmailTemplateController | STR_EM_LNG | `emails.store.language`  | auth       | `pid`: int                          |
| D11d | **RESOURCE** | `email_template`                   | EmailTemplateController | R          | `email_template.*`       | auth, XSS  | `{email_template}`: int             |

### D12. Notification Templates

| #    | Method       | URI                                    | Controller                     | Action | Route Name                     | Middleware | Params                                        |
| ---- | ------------ | -------------------------------------- | ------------------------------ | ------ | ------------------------------ | ---------- | --------------------------------------------- |
| D12a | GET          | `notification_templates/{id?}/{lang?}` | NotificationTemplateController | IDX    | `notification_templates.index` | auth, XSS  | `id`: int, optional; `lang`: string, optional |
| D12b | **RESOURCE** | `notification_templates`               | NotificationTemplateController | R      | `notification_templates.*`     | auth, XSS  | `{notification_template}`: int                |

### D13. Todos

| #    | Method | URI                 | Controller     | Action | Route Name      | Middleware | Params    |
| ---- | ------ | ------------------- | -------------- | ------ | --------------- | ---------- | --------- |
| D13a | POST   | `todos/create`      | UserController | TD_STR | `todos.store`   | auth, XSS  | —         |
| D13b | POST   | `todos/{id}/update` | UserController | TD_UPD | `todos.update`  | auth, XSS  | `id`: int |
| D13c | DELETE | `todos/{id}/delete` | UserController | TD_DEL | `todos.destroy` | auth, XSS  | `id`: int |

### D14. Search

| #    | Method | URI       | Controller     | Action | Route Name    | Middleware     | Params |
| ---- | ------ | --------- | -------------- | ------ | ------------- | -------------- | ------ |
| D14a | GET    | `/search` | UserController | search | `search.json` | auth, verified | —      |

### D15. Cookie Consent

| #    | Method | URI               | Controller       | Action   | Route Name       | Middleware | Params |
| ---- | ------ | ----------------- | ---------------- | -------- | ---------------- | ---------- | ------ |
| D15a | ANY    | `/cookie-consent` | SystemController | CK_CNSNT | `cookie-consent` | — (public) | —      |

---

## E. FINANCIAL ROUTES

### E1. Invoices

| #   | Method       | URI                                   | Controller        | Action    | Route Name                    | Middleware            | Params                              |
| --- | ------------ | ------------------------------------- | ----------------- | --------- | ----------------------------- | --------------------- | ----------------------------------- |
| E1a | GET          | `invoices/{id}/duplicate`             | InvoiceController | duplicate | `invoices.duplicate`          | auth, XSS, revalidate | `id`: UUID                          |
| E1b | GET          | `invoices/{id}/shipping/print`        | InvoiceController | SHP_DSP   | `invoices.shipping.print`     | auth, XSS, revalidate | `id`: UUID                          |
| E1c | GET          | `invoices/{id}/payment/reminder`      | InvoiceController | PAY_RMD   | `invoices.payment.reminder`   | auth, XSS, revalidate | `id`: UUID                          |
| E1d | GET          | `invoices/index`                      | InvoiceController | index     | `invoices.index`              | auth, XSS, revalidate | —                                   |
| E1e | POST         | `invoices/product/destroy`            | InvoiceController | PRD_DST   | `invoices.product.destroy`    | auth, XSS, revalidate | —                                   |
| E1f | POST         | `invoices/product`                    | InvoiceController | product   | `invoices.product`            | auth, XSS, revalidate | —                                   |
| E1g | POST         | `invoices/customer`                   | InvoiceController | customer  | `invoices.customer`           | auth, XSS, revalidate | —                                   |
| E1h | GET          | `invoices/{id}/sent`                  | InvoiceController | sent      | `invoices.sent`               | auth, XSS, revalidate | `id`: UUID                          |
| E1i | GET          | `invoices/{id}/resent`                | InvoiceController | resent    | `invoices.resent`             | auth, XSS, revalidate | `id`: UUID                          |
| E1j | GET          | `invoices/{id}/payment`               | InvoiceController | payment   | `invoices.payment`            | auth, XSS, revalidate | `id`: UUID                          |
| E1k | POST         | `invoices/{id}/payment`               | InvoiceController | PAY_CRT   | `invoices.payment`            | auth, XSS, revalidate | `id`: UUID                          |
| E1l | POST         | `invoices/{id}/payment/{pid}/destroy` | InvoiceController | PAY_DST   | `invoices.payment.destroy`    | auth, XSS, revalidate | `id`: UUID; `pid`: int              |
| E1m | GET          | `invoices/items`                      | InvoiceController | items     | `invoices.items`              | auth, XSS, revalidate | —                                   |
| E1n | GET          | `invoices/create/{cid}`               | InvoiceController | create    | `invoices.create`             | auth, XSS, revalidate | `cid`: int (customer ID)            |
| E1o | GET          | `invoices/export`                     | InvoiceController | export    | `invoices.export`             | auth, XSS, revalidate | —                                   |
| E1p | **RESOURCE** | `invoices`                            | InvoiceController | R         | `invoices.*`                  | auth, XSS, revalidate | `{invoice}`: UUID                   |
| E1q | GET          | `invoices/preview/{template}/{color}` | InvoiceController | INV_PRV   | `invoices.preview`            | auth, verified        | `template`: string; `color`: string |
| E1r | POST         | `invoices/template/setting`           | InvoiceController | SV_IV_TMP | `invoices.templates.settings` | auth, verified        | —                                   |
| E1s | GET          | `invoices/pdf/{id}`                   | InvoiceController | invoice   | `invoices.pdf`                | XSS, revalidate       | `id`: UUID                          |
| E1t | GET          | `customers/invoices/{id}/`            | InvoiceController | IV_LK     | `invoices.link.copy`          | — (public)            | `id`: UUID                          |

### E2. Credit Notes

| #   | Method | URI                                        | Controller           | Action  | Route Name                    | Middleware            | Params                   |
| --- | ------ | ------------------------------------------ | -------------------- | ------- | ----------------------------- | --------------------- | ------------------------ |
| E2a | GET    | `credit-notes`                             | CreditNoteController | index   | `credit.note`                 | auth, XSS, revalidate | —                        |
| E2b | GET    | `custom-credit-note`                       | CreditNoteController | CST_CRT | `invoices.custom.credit.note` | auth, XSS, revalidate | —                        |
| E2c | POST   | `custom-credit-note`                       | CreditNoteController | CST_STR | `invoices.custom.credit.note` | auth, XSS, revalidate | —                        |
| E2d | GET    | `credit_notes/invoice`                     | CreditNoteController | GET_INV | `invoices.get`                | auth, XSS, revalidate | —                        |
| E2e | GET    | `invoices/{id}/credit-note`                | CreditNoteController | create  | `invoices.credit.note`        | auth, XSS, revalidate | `id`: UUID               |
| E2f | POST   | `invoices/{id}/credit-note`                | CreditNoteController | store   | `invoices.credit.note`        | auth, XSS, revalidate | `id`: UUID               |
| E2g | GET    | `invoices/{id}/credit-note/edit/{cn_id}`   | CreditNoteController | edit    | `invoices.edit.credit.note`   | auth, XSS, revalidate | `id`: UUID; `cn_id`: int |
| E2h | POST   | `invoices/{id}/credit-note/edit/{cn_id}`   | CreditNoteController | update  | `invoices.edit.credit.note`   | auth, XSS, revalidate | `id`: UUID; `cn_id`: int |
| E2i | DELETE | `invoices/{id}/credit-note/delete/{cn_id}` | CreditNoteController | destroy | `invoices.delete.credit.note` | auth, XSS, revalidate | `id`: UUID; `cn_id`: int |

### E3. Bills

| #   | Method       | URI                                | Controller     | Action     | Route Name                | Middleware            | Params                              |
| --- | ------------ | ---------------------------------- | -------------- | ---------- | ------------------------- | --------------------- | ----------------------------------- |
| E3a | GET          | `bills{id}/duplicate`              | BillController | duplicate  | `bills.duplicate`         | auth, XSS, revalidate | `id`: UUID                          |
| E3b | GET          | `bills{id}/shipping/print`         | BillController | SHP_DSP    | `bills.shipping.print`    | auth, XSS, revalidate | `id`: UUID                          |
| E3c | GET          | `billsindex`                       | BillController | index      | `bills.index`             | auth, XSS, revalidate | —                                   |
| E3d | POST         | `billsproduct/destroy`             | BillController | PRD_DST    | `bills.product.destroy`   | auth, XSS, revalidate | —                                   |
| E3e | POST         | `billsproduct`                     | BillController | product    | `bills.product`           | auth, XSS, revalidate | —                                   |
| E3f | POST         | `billsvendor`                      | BillController | vendor     | `bills.vendor`            | auth, XSS, revalidate | —                                   |
| E3g | GET          | `bills{id}/sent`                   | BillController | sent       | `bills.sent`              | auth, XSS, revalidate | `id`: UUID                          |
| E3h | GET          | `bills{id}/resent`                 | BillController | resent     | `bills.resent`            | auth, XSS, revalidate | `id`: UUID                          |
| E3i | GET          | `bills{id}/payment`                | BillController | payment    | `bills.payment`           | auth, XSS, revalidate | `id`: UUID                          |
| E3j | POST         | `bills{id}/payment`                | BillController | PAY_CRT    | `bills.payment`           | auth, XSS, revalidate | `id`: UUID                          |
| E3k | POST         | `bills{id}/payment/{pid}/destroy`  | BillController | PAY_DST    | `bills.payment.destroy`   | auth, XSS, revalidate | `id`: UUID; `pid`: int              |
| E3l | GET          | `billsitems`                       | BillController | items      | `bills.items`             | auth, XSS, revalidate | —                                   |
| E3m | GET          | `billscreate/{cid}`                | BillController | create     | `bills.create`            | auth, XSS, revalidate | `cid`: int (vendor ID)              |
| E3n | **RESOURCE** | `bills`                            | BillController | R          | `bills.*`                 | auth, XSS, revalidate | `{bill}`: UUID                      |
| E3o | GET          | `bills/preview/{template}/{color}` | BillController | PV_BIL     | `bills.preview`           | auth, XSS             | `template`: string; `color`: string |
| E3p | POST         | `bills/template/setting`           | BillController | SV_BIL_TMP | `bills.templates.setting` | auth, verified        | —                                   |
| E3q | GET          | `bills/pdf/{id}`                   | BillController | bill       | `bills.pdf`               | XSS, revalidate       | `id`: UUID                          |
| E3r | GET          | `bills/export`                     | BillController | export     | `bills.export`            | auth, XSS             | —                                   |
| E3s | GET          | `vendors/bills/{id}/`              | BillController | IV_LK      | `bills.link.copy`         | — (public)            | `id`: UUID                          |

### E4. Debit Notes

| #   | Method | URI                                    | Controller          | Action  | Route Name                | Middleware            | Params                   |
| --- | ------ | -------------------------------------- | ------------------- | ------- | ------------------------- | --------------------- | ------------------------ |
| E4a | GET    | `debit_notes`                          | DebitNoteController | index   | `debit.note`              | auth, XSS, revalidate | —                        |
| E4b | GET    | `custom-debit-note`                    | DebitNoteController | CST_CRT | `bills.custom.debit.note` | auth, XSS, revalidate | —                        |
| E4c | POST   | `custom-debit-note`                    | DebitNoteController | CST_STR | `bills.custom.debit.note` | auth, XSS, revalidate | —                        |
| E4d | GET    | `debit_notes/bill`                     | DebitNoteController | GET_BIL | `bills.get`               | auth, XSS, revalidate | —                        |
| E4e | GET    | `bills{id}/debit-note`                 | DebitNoteController | create  | `bills.debit.note`        | auth, XSS, revalidate | `id`: UUID               |
| E4f | POST   | `bills{id}/debit-note`                 | DebitNoteController | store   | `bills.debit.note`        | auth, XSS, revalidate | `id`: UUID               |
| E4g | GET    | `bills{id}/debit_notes/edit/{cn_id}`   | DebitNoteController | edit    | `bills.edit.debit.note`   | auth, XSS, revalidate | `id`: UUID; `cn_id`: int |
| E4h | POST   | `bills{id}/debit_notes/edit/{cn_id}`   | DebitNoteController | update  | `bills.edit.debit.note`   | auth, XSS, revalidate | `id`: UUID; `cn_id`: int |
| E4i | DELETE | `bills{id}/debit_notes/delete/{cn_id}` | DebitNoteController | destroy | `bills.delete.debit.note` | auth, XSS, revalidate | `id`: UUID; `cn_id`: int |

### E5. Payments

| #   | Method       | URI              | Controller        | Action | Route Name       | Middleware            | Params           |
| --- | ------------ | ---------------- | ----------------- | ------ | ---------------- | --------------------- | ---------------- |
| E5a | GET          | `payments/index` | PaymentController | index  | `payments.index` | auth, XSS, revalidate | —                |
| E5b | **RESOURCE** | `payments`       | PaymentController | R      | `payments.*`     | auth, XSS, revalidate | `{payment}`: int |

### E6. Revenue

| #   | Method       | URI              | Controller        | Action | Route Name       | Middleware            | Params           |
| --- | ------------ | ---------------- | ----------------- | ------ | ---------------- | --------------------- | ---------------- |
| E6a | GET          | `revenues/index` | RevenueController | index  | `revenues.index` | auth, XSS, revalidate | —                |
| E6b | **RESOURCE** | `revenues`       | RevenueController | R      | `revenues.*`     | auth, XSS, revalidate | `{revenue}`: int |

### E7. Expenses

| #   | Method       | URI                        | Controller        | Action      | Route Name                 | Middleware            | Params           |
| --- | ------------ | -------------------------- | ----------------- | ----------- | -------------------------- | --------------------- | ---------------- |
| E7a | GET          | `expenses/pdf/{id}`        | ExpenseController | expense     | `expenses.pdf`             | XSS, revalidate       | `id`: int/UUID   |
| E7b | GET          | `expenses/index`           | ExpenseController | index       | `expenses.index`           | auth, XSS, revalidate | —                |
| E7c | ANY          | `expenses/customer`        | ExpenseController | customer    | `expenses.customer`        | auth, XSS, revalidate | —                |
| E7d | POST         | `expenses/vendor`          | ExpenseController | vendor      | `expenses.vendor`          | auth, XSS, revalidate | —                |
| E7e | POST         | `expenses/employee`        | ExpenseController | employee    | `expenses.employee`        | auth, XSS, revalidate | —                |
| E7f | POST         | `expenses/product/destroy` | ExpenseController | PRD_DST     | `expenses.product.destroy` | auth, XSS, revalidate | —                |
| E7g | POST         | `expenses/product`         | ExpenseController | product     | `expenses.product`         | auth, XSS, revalidate | —                |
| E7h | GET          | `expenses/{id}/payment`    | ExpenseController | payment     | `expenses.payment`         | auth, XSS, revalidate | `id`: int        |
| E7i | GET          | `expenses/items`           | ExpenseController | items       | `expenses.items`           | auth, XSS, revalidate | —                |
| E7j | GET          | `expenses/create/{cid}`    | ExpenseController | create      | `expenses.create`          | auth, XSS, revalidate | `cid`: int       |
| E7k | **RESOURCE** | `expenses`                 | ExpenseController | R           | `expenses.*`               | auth, XSS, revalidate | `{expense}`: int |
| E7l | GET          | `/expense-list`            | ExpenseController | expenseList | `expenses.list`            | auth, XSS             | —                |

### E8. Taxes

| #   | Method       | URI     | Controller    | Action | Route Name | Middleware            | Params       |
| --- | ------------ | ------- | ------------- | ------ | ---------- | --------------------- | ------------ |
| E8a | **RESOURCE** | `taxes` | TaxController | R      | `taxes.*`  | auth, XSS, revalidate | `{tax}`: int |

### E9. Transactions

| #   | Method | URI                   | Controller            | Action | Route Name            | Middleware            | Params |
| --- | ------ | --------------------- | --------------------- | ------ | --------------------- | --------------------- | ------ |
| E9a | GET    | `reports/transaction` | TransactionController | index  | `transactions.index`  | auth, XSS, revalidate | —      |
| E9b | GET    | `transactions/export` | TransactionController | export | `transactions.export` | auth, XSS             | —      |

### E10. Proposals

| #    | Method       | URI                                    | Controller         | Action     | Route Name                  | Middleware            | Params                              |
| ---- | ------------ | -------------------------------------- | ------------------ | ---------- | --------------------------- | --------------------- | ----------------------------------- |
| E10a | GET          | `proposals/{id}/status/change`         | ProposalController | STT_CHG    | `proposals.status.change`   | auth, XSS, revalidate | `id`: UUID                          |
| E10b | GET          | `proposals/{id}/convert`               | ProposalController | convert    | `proposals.convert`         | auth, XSS, revalidate | `id`: UUID                          |
| E10c | GET          | `proposals/{id}/duplicate`             | ProposalController | duplicate  | `proposals.duplicate`       | auth, XSS, revalidate | `id`: UUID                          |
| E10d | POST         | `proposals/product/destroy`            | ProposalController | PRD_DST    | `proposals.product.destroy` | auth, XSS, revalidate | —                                   |
| E10e | POST         | `proposals/customer`                   | ProposalController | customer   | `proposals.customer`        | auth, XSS, revalidate | —                                   |
| E10f | POST         | `proposals/product`                    | ProposalController | product    | `proposals.product`         | auth, XSS, revalidate | —                                   |
| E10g | GET          | `proposals/items`                      | ProposalController | items      | `proposals.items`           | auth, XSS, revalidate | —                                   |
| E10h | GET          | `proposals/{id}/sent`                  | ProposalController | sent       | `proposals.sent`            | auth, XSS, revalidate | `id`: UUID                          |
| E10i | GET          | `proposals/{id}/resent`                | ProposalController | resent     | `proposals.resent`          | auth, XSS, revalidate | `id`: UUID                          |
| E10j | GET          | `proposals/create/{cid}`               | ProposalController | create     | `proposals.create`          | auth, XSS, revalidate | `cid`: int                          |
| E10k | **RESOURCE** | `proposal`                             | ProposalController | R          | `proposal.*`                | auth, XSS, revalidate | `{proposal}`: UUID                  |
| E10l | GET          | `proposals/preview/{template}/{color}` | ProposalController | PV_PPS     | `proposals.preview`         | auth, verified        | `template`: string; `color`: string |
| E10m | POST         | `proposals/templates/settings`         | ProposalController | SV_PPS_TMP | `proposalssettings`         | auth, verified        | —                                   |
| E10n | GET          | `proposals/pdfs/{id}`                  | ProposalController | proposal   | `proposals.pdf`             | XSS, revalidate       | `id`: UUID                          |
| E10o | GET          | `proposals/export`                     | ProposalController | export     | `proposals.export`          | auth, XSS             | —                                   |
| E10p | GET          | `customers/proposals/{id}/`            | ProposalController | IV_LK      | `proposals.link.copy`       | — (public)            | `id`: UUID                          |

### E11. Bank Accounts & Transfers

| #    | Method       | URI                    | Controller             | Action | Route Name             | Middleware            | Params                 |
| ---- | ------------ | ---------------------- | ---------------------- | ------ | ---------------------- | --------------------- | ---------------------- |
| E11a | **RESOURCE** | `bank_accounts`        | BankAccountController  | R      | `bank_accounts.*`      | auth, XSS, revalidate | `{bank_account}`: int  |
| E11b | GET          | `bank_transfers/index` | BankTransferController | IDX    | `bank_transfers.index` | auth, XSS, revalidate | —                      |
| E11c | **RESOURCE** | `bank_transfers`       | BankTransferController | R      | `bank_transfers.*`     | auth, XSS, revalidate | `{bank_transfer}`: int |

### E12. Chart of Accounts / Journal Entries

| #    | Method       | URI                                         | Controller               | Action  | Route Name                   | Middleware            | Params                    |
| ---- | ------------ | ------------------------------------------- | ------------------------ | ------- | ---------------------------- | --------------------- | ------------------------- |
| E12a | POST         | `chart_of_accounts/subtype`                 | ChartOfAccountController | GET_SBT | `chart_of_accounts.sub_type` | auth, XSS, revalidate | —                         |
| E12b | **RESOURCE** | `chart_of_accounts`                         | ChartOfAccountController | R       | `chart_of_accounts.*`        | auth, XSS, revalidate | `{chart_of_account}`: int |
| E12c | POST         | `journal_entries/account/destroy`           | JournalEntryController   | ACC_DST | `journalsaccount.destroy`    | auth, XSS, revalidate | —                         |
| E12d | DELETE       | `journal_entries/journal/destroy/{item_id}` | JournalEntryController   | JRN_DST | `journals.destroy`           | auth, XSS, revalidate | `item_id`: int            |
| E12e | **RESOURCE** | `journal_entries`                           | JournalEntryController   | R       | `journal_entries.*`          | auth, XSS, revalidate | `{journal_entry}`: int    |

### E13. Budgets & Goals

| #    | Method       | URI       | Controller       | Action | Route Name  | Middleware            | Params          |
| ---- | ------------ | --------- | ---------------- | ------ | ----------- | --------------------- | --------------- |
| E13a | **RESOURCE** | `budgets` | BudgetController | R      | `budgets.*` | auth, XSS, revalidate | `{budget}`: int |
| E13b | **RESOURCE** | `goals`   | GoalController   | R      | `goals.*`   | auth, XSS, revalidate | `{goal}`: int   |

### E14. Invoice Payment Gateways

| #    | Method | URI                                      | Controller                    | Action          | Route Name                          | Middleware            | Params                                |
| ---- | ------ | ---------------------------------------- | ----------------------------- | --------------- | ----------------------------------- | --------------------- | ------------------------------------- |
| E14a | POST   | `customers/pay-with-bank`                | BankTransferPaymentController | CST_PAY_BNK     | `customers.pay.with.bank`           | XSS                   | —                                     |
| E14b | GET    | `invoices/{id}/action`                   | BankTransferPaymentController | INV_ACT         | `invoices.action`                   | —                     | `id`: UUID                            |
| E14c | POST   | `invoices/{id}/change-action`            | BankTransferPaymentController | INV_CG_STT      | `invoices.change.status`            | —                     | `id`: UUID                            |
| E14d | ANY    | `invoices/with-benefit`                  | BenefitPaymentController      | INV_PAY_BF      | `invoices.benefit.initiate`         | —                     | —                                     |
| E14e | ANY    | `invoices/benefit/{invoice_id}/{amount}` | BenefitPaymentController      | GET_INV_PAY_STT | `invoices.benefit.callback`         | —                     | `invoice_id`: UUID; `amount`: numeric |
| E14f | POST   | `invoices/with-cashfree/payment`         | CashfreeController            | INV_PAY_CF      | `customers.pay.with.cashfree`       | —                     | —                                     |
| E14g | ANY    | `invoices/with-cashfree/status`          | CashfreeController            | GET_INV_PAY_STT | `invoices.cashfree.payment.success` | —                     | —                                     |
| E14h | ANY    | `payments/benefit/initiate`              | BenefitPaymentController      | INI_PAY         | `plans.pay.with.benefit`            | auth, XSS             | —                                     |
| E14i | ANY    | `payments/benefit/callback`              | BenefitPaymentController      | CB              | `benefit.callback`                  | auth, XSS             | —                                     |
| E14j | POST   | `cashfree/payments/store`                | CashfreeController            | CF_PAY_STR      | `plans.pay.with.cashfree`           | auth, XSS             | —                                     |
| E14k | ANY    | `cashfree/payments/success`              | CashfreeController            | CF_PAY_SCS      | `cashfree.payment.success`          | auth, XSS             | —                                     |
| E14l | POST   | `plan-pay-with-bank`                     | BankTransferPaymentController | PL_PAY_BNK      | `plans.pay.with.bank`               | auth, XSS, revalidate | —                                     |

### E15. Orders (Stripe)

| #    | Method | URI                        | Controller                    | Action   | Route Name             | Middleware            | Params         |
| ---- | ------ | -------------------------- | ----------------------------- | -------- | ---------------------- | --------------------- | -------------- |
| E15a | GET    | `/orders`                  | StripePaymentController       | IDX      | `orders.index`         | auth, XSS, revalidate | —              |
| E15b | GET    | `/stripe/{code}`           | StripePaymentController       | STRP     | `stripe`               | auth, XSS, revalidate | `code`: string |
| E15c | POST   | `/stripe`                  | StripePaymentController       | STRP_PST | `stripe.post`          | auth, XSS, revalidate | —              |
| E15d | POST   | `orders/{id}/changeaction` | BankTransferPaymentController | CHG_STT  | `orders.change.status` | auth, XSS             | `id`: int      |
| E15e | DELETE | `orders/{id}`              | BankTransferPaymentController | OD_DST   | `orders.destroy`       | auth, XSS             | `id`: int      |
| E15f | GET    | `orders/{id}/action`       | BankTransferPaymentController | action   | `orders.action`        | auth, XSS             | `id`: int      |

---

## F. HRM (Human Resource Management)

### F1. Employees

| #   | Method       | URI                          | Controller         | Action   | Route Name                    | Middleware | Params            |
| --- | ------------ | ---------------------------- | ------------------ | -------- | ----------------------------- | ---------- | ----------------- |
| F1a | POST         | `employees/json`             | EmployeeController | json     | `employees.json`              | auth, XSS  | —                 |
| F1b | POST         | `branches/employees/json`    | EmployeeController | EMP_JSON | `branches.employee.json`      | auth, XSS  | —                 |
| F1c | GET          | `employee-profile`           | EmployeeController | profile  | `employees.profile`           | auth, XSS  | —                 |
| F1d | GET          | `show-employee-profile/{id}` | EmployeeController | PRF_SHW  | `employees.show.profile`      | auth, XSS  | `id`: int         |
| F1e | GET          | `last-login`                 | EmployeeController | LST_LGN  | `last_login`                  | auth, XSS  | —                 |
| F1f | GET          | `employees/export`           | EmployeeController | export   | `employees.export`            | auth, XSS  | —                 |
| F1g | **RESOURCE** | `employees`                  | EmployeeController | R        | `employees.*`                 | auth, XSS  | `{employee}`: int |
| F1h | POST         | `employees/getdepartment`    | EmployeeController | GET_DPT  | `employees.getdepartment`     | auth, XSS  | —                 |
| F1i | GET          | `employees/import/file`      | EmployeeController | IMP_FL   | `employees.file.import`       | auth, XSS  | —                 |
| F1j | POST         | `employees/import/index`     | EmployeeController | import   | `employees.import`            | auth, XSS  | —                 |
| F1k | GET          | `employees/pdf/{id}`         | EmployeeController | JNL_PDF  | `joining_letter.download.pdf` | auth, XSS  | `id`: int         |
| F1l | GET          | `employees/doc/{id}`         | EmployeeController | JNL_DOC  | `joining_letter.download.doc` | auth, XSS  | `id`: int         |
| F1m | GET          | `employees/exp-pdf/{id}`     | EmployeeController | EC_PDF   | `exp.download.pdf`            | auth, XSS  | `id`: int         |
| F1n | GET          | `employees/exp-doc/{id}`     | EmployeeController | EC_DOC   | `exp.download.doc`            | auth, XSS  | `id`: int         |
| F1o | GET          | `employees/noc-pdf/{id}`     | EmployeeController | NOC_PDF  | `noc.download.pdf`            | auth, XSS  | `id`: int         |
| F1p | GET          | `employees/noc-doc/{id}`     | EmployeeController | NOC_DOC  | `noc.download.doc`            | auth, XSS  | `id`: int         |

### F2. Salary / Set Salaries

| #   | Method       | URI                             | Controller          | Action                          | Route Name                | Middleware | Params              |
| --- | ------------ | ------------------------------- | ------------------- | ------------------------------- | ------------------------- | ---------- | ------------------- |
| F2a | GET          | `employees/salary/{eid}`        | SetSalaryController | EMP_SL_BASIC                    | `employees.salary.basic`  | auth, XSS  | `eid`: int          |
| F2b | POST         | `employees/update/sallary/{id}` | SetSalaryController | EMP_SL_UPDATE                   | `employees.salary.update` | auth, XSS  | `id`: int           |
| F2c | GET          | `employees/salary`              | SetSalaryController | EMP_SL                          | `employees.salary`        | auth, XSS  | —                   |
| F2d | **RESOURCE** | `set_salaries`                  | SetSalaryController | R (only index/show/edit/create) | `set_salaries.*`          | auth, XSS  | `{set_salary}`: int |

### F3. Allowances, Commissions, Loans, Deductions, Overtime

| #   | Method       | URI                                  | Controller                    | Action         | Route Name                     | Middleware | Params                        |
| --- | ------------ | ------------------------------------ | ----------------------------- | -------------- | ------------------------------ | ---------- | ----------------------------- |
| F3a | GET          | `allowances/create/{eid}`            | AllowanceController           | ALW_CR         | `allowances.create`            | auth, XSS  | `eid`: int                    |
| F3b | **RESOURCE** | `allowances`                         | AllowanceController           | R              | `allowances.*`                 | auth, XSS  | `{allowance}`: int            |
| F3c | **RESOURCE** | `allowance_options`                  | AllowanceOptionController     | R              | `allowance_options.*`          | auth, XSS  | `{allowance_option}`: int     |
| F3d | GET          | `commissions/create/{eid}`           | CommissionController          | COM_CR         | `commissions.create`           | auth, XSS  | `eid`: int                    |
| F3e | **RESOURCE** | `commissions`                        | CommissionController          | R              | `commissions.*`                | auth, XSS  | `{commission}`: int           |
| F3f | **RESOURCE** | `deduction_options`                  | DeductionOptionController     | R              | `deduction_options.*`          | auth, XSS  | `{deduction_option}`: int     |
| F3g | GET          | `loans/create/{eid}`                 | LoanController                | LN_CRT         | `loans.create`                 | auth, XSS  | `eid`: int                    |
| F3h | **RESOURCE** | `loan_options`                       | LoanOptionController          | R              | `loan_options.*`               | auth, XSS  | `{loan_option}`: int          |
| F3i | **RESOURCE** | `loans`                              | LoanController                | R              | `loans.*`                      | auth, XSS  | `{loan}`: int                 |
| F3j | GET          | `saturation_deductions/create/{eid}` | SaturationDeductionController | STR_DD_CR      | `saturation_deductions.create` | auth, XSS  | `eid`: int                    |
| F3k | **RESOURCE** | `saturation_deductions`              | SaturationDeductionController | R              | `saturation_deductions.*`      | auth, XSS  | `{saturation_deduction}`: int |
| F3l | GET          | `other_payments/create/{eid}`        | OtherPaymentController        | OT_PAY_CR      | `other_payments.create`        | auth, XSS  | `eid`: int                    |
| F3m | **RESOURCE** | `other_payments`                     | OtherPaymentController        | R              | `other_payments.*`             | auth, XSS  | `{other_payment}`: int        |
| F3n | GET          | `overtimes/create/{eid}`             | OvertimeController            | overtimeCreate | `overtimes.create`             | auth, XSS  | `eid`: int                    |
| F3o | **RESOURCE** | `overtimes`                          | OvertimeController            | R              | `overtimes.*`                  | auth, XSS  | `{overtime}`: int             |

### F4. Payslips

| #   | Method       | URI                               | Controller            | Action      | Route Name                 | Middleware | Params                          |
| --- | ------------ | --------------------------------- | --------------------- | ----------- | -------------------------- | ---------- | ------------------------------- |
| F4a | GET          | `payslips/paysalary/{id}/{date}`  | PayslipController     | PAY_SLR     | `payslips.paysalary`       | auth, XSS  | `id`: int; `date`: string (Y-m) |
| F4b | GET          | `payslips/bulk_pay_create/{date}` | PayslipController     | BLK_PAY_CRT | `payslips.bulk_pay_create` | auth, XSS  | `date`: string (Y-m)            |
| F4c | POST         | `payslips/bulk_payment/{date}`    | PayslipController     | BLK_PAY     | `payslips.bulkpayment`     | auth, XSS  | `date`: string (Y-m)            |
| F4d | POST         | `payslips/search_json`            | PayslipController     | SRC_JSN     | `payslips.search_json`     | auth, XSS  | —                               |
| F4e | GET          | `payslips/employeepayslip`        | PayslipController     | EMP_PAY_SLP | `payslips.employeepayslip` | auth, XSS  | —                               |
| F4f | GET          | `payslips/show/{id}`              | PayslipController     | SHW_EMP     | `payslips.showemployee`    | auth, XSS  | `id`: int                       |
| F4g | GET          | `payslips/edit/{id}`              | PayslipController     | EDT_EMP     | `payslips.editemployee`    | auth, XSS  | `id`: int                       |
| F4h | POST         | `payslips/employee/update/{id}`   | PayslipController     | UPD_EMP     | `payslips.updateEmployee`  | auth, XSS  | `id`: int                       |
| F4i | GET          | `payslips/pdf/{id}/{m}`           | PayslipController     | pdf         | `payslips.pdf`             | auth, XSS  | `id`: int; `m`: string (month)  |
| F4j | GET          | `payslips/payslipPdf/{id}`        | PayslipController     | PAY_SLP_PDF | `payslips.payslipPdf`      | auth, XSS  | `id`: int                       |
| F4k | GET          | `payslips/send/{id}/{m}`          | PayslipController     | send        | `payslips.send`            | auth, XSS  | `id`: int; `m`: string          |
| F4l | GET          | `payslips/delete/{id}`            | PayslipController     | destroy     | `payslips.delete`          | auth, XSS  | `id`: int                       |
| F4m | **RESOURCE** | `payslips`                        | PayslipController     | R           | `payslips.*`               | auth, XSS  | `{payslip}`: int                |
| F4n | **RESOURCE** | `payslip_types` (VW::PY_SLP)      | PayslipTypeController | R           | `payslip_types.*`          | auth, XSS  | `{payslip_type}`: int           |
| F4o | POST         | `payslips/export`                 | PayslipController     | export      | `payslips.export`          | auth, XSS  | —                               |

### F5. Departments, Designations, Branches

| #   | Method       | URI            | Controller            | Action | Route Name       | Middleware | Params               |
| --- | ------------ | -------------- | --------------------- | ------ | ---------------- | ---------- | -------------------- |
| F5a | **RESOURCE** | `departments`  | DepartmentController  | R      | `departments.*`  | auth, XSS  | `{department}`: int  |
| F5b | **RESOURCE** | `designations` | DesignationController | R      | `designations.*` | auth, XSS  | `{designation}`: int |
| F5c | **RESOURCE** | `branches`     | BranchController      | R      | `branches.*`     | auth, XSS  | `{branch}`: int      |
| F5d | **RESOURCE** | `documents`    | DocumentController    | R      | `documents.*`    | auth, XSS  | `{document}`: int    |

### F6. Leave Management

| #   | Method       | URI                   | Controller          | Action            | Route Name             | Middleware | Params              |
| --- | ------------ | --------------------- | ------------------- | ----------------- | ---------------------- | ---------- | ------------------- |
| F6a | GET          | `leaves/{id}/action`  | LeaveController     | action            | `leaves.action`        | auth, XSS  | `id`: int           |
| F6b | POST         | `leaves/changeaction` | LeaveController     | CHG_ACT           | `leaves.change_action` | auth, XSS  | —                   |
| F6c | POST         | `leaves/jsoncount`    | LeaveController     | JSON_CT           | `leaves.jsoncount`     | auth, XSS  | —                   |
| F6d | **RESOURCE** | `leave`               | LeaveController     | R                 | `leave.*`              | auth, XSS  | `{leave}`: int      |
| F6e | **RESOURCE** | `leave_types`         | LeaveTypeController | R                 | `leave_types.*`        | auth, XSS  | `{leave_type}`: int |
| F6f | GET          | `leaves/export`       | ReportController    | LeaveReportExport | `leaves.export`        | auth       | —                   |

### F7. Attendance

| #   | Method       | URI                                   | Controller                   | Action     | Route Name                            | Middleware | Params                       |
| --- | ------------ | ------------------------------------- | ---------------------------- | ---------- | ------------------------------------- | ---------- | ---------------------------- |
| F7a | GET          | `employee_attendances/bulkattendance` | EmployeeAttendanceController | BK_ATD     | `employee_attendances.bulkattendance` | auth, XSS  | —                            |
| F7b | POST         | `employee_attendances/bulkattendance` | EmployeeAttendanceController | BK_ATD_DT  | `employee_attendances.bulkattendance` | auth, XSS  | —                            |
| F7c | POST         | `employee_attendances/attendance`     | EmployeeAttendanceController | attendance | `employee_attendances.attendance`     | auth, XSS  | —                            |
| F7d | **RESOURCE** | `employee_attendances`                | EmployeeAttendanceController | R          | `employee_attendances.*`              | auth, XSS  | `{employee_attendance}`: int |
| F7e | GET          | `attendance/import/file`              | EmployeeAttendanceController | importFile | `attendance.file.import`              | auth, XSS  | —                            |
| F7f | POST         | `attendance/import/index`             | EmployeeAttendanceController | import     | `attendance.import`                   | auth, XSS  | —                            |

### F8. Awards, Resignations, Travel, Promotions, Complaints, Warnings, Terminations

| #     | Method              | URI                             | Controller                | Action      | Route Name                 | Middleware     | Params                   |
| ----- | ------------------- | ------------------------------- | ------------------------- | ----------- | -------------------------- | -------------- | ------------------------ |
| F8a   | **RESOURCE**        | `award_types`                   | AwardTypeController       | R           | `award_types.*`            | auth, XSS      | `{award_type}`: int      |
| F8b   | **RESOURCE**        | `awards`                        | AwardController           | R           | `awards.*`                 | auth, XSS      | `{award}`: int           |
| F8c   | **RESOURCE**        | `resignations`                  | ResignationController     | R           | `resignations.*`           | auth, XSS      | `{resignation}`: int     |
| F8d   | **RESOURCE**        | `travels`                       | TravelController          | R           | `travels.*`                | auth, XSS      | `{travel}`: int          |
| F8e   | **RESOURCE**        | `promotions`                    | PromotionController       | R           | `promotions.*`             | auth, XSS      | `{promotion}`: int       |
| F8f   | **RESOURCE**        | `complaints`                    | ComplaintController       | R           | `complaints.*`             | auth, XSS      | `{complaint}`: int       |
| F8g   | **RESOURCE**        | `warnings`                      | WarningController         | R           | `warnings.*`               | auth, XSS      | `{warning}`: int         |
| F8h   | **RESOURCE**        | `terminations`                  | TerminationController     | R           | `terminations.*`           | auth, XSS      | `{termination}`: int     |
| F8i   | GET                 | `terminations/{id}/description` | TerminationController     | description | `terminations.description` | auth, verified | `id`: int                |
| F8j   | **RESOURCE**        | `terminationtype`               | TerminationTypeController | R           | `terminationtype.*`        | auth, XSS      | `{terminationtype}`: int |
| F8k-q | GET/POST/PUT/DELETE | `terminationtypes/*`            | TerminationTypeController | (aliases)   | `termination_types.*`      | auth, XSS      | `{terminationtype}`: int |
| F8r   | **RESOURCE**        | `transfers`                     | TransferController        | R           | `transfers.*`              | auth, XSS      | `{transfer}`: int        |

### F9. Training

| #   | Method       | URI                | Controller             | Action  | Route Name         | Middleware | Params                 |
| --- | ------------ | ------------------ | ---------------------- | ------- | ------------------ | ---------- | ---------------------- |
| F9a | POST         | `trainings/status` | TrainingController     | UPD_STT | `trainings.status` | auth, XSS  | —                      |
| F9b | **RESOURCE** | `trainings`        | TrainingController     | R       | `trainings.*`      | auth, XSS  | `{training}`: int      |
| F9c | **RESOURCE** | `training_types`   | TrainingTypeController | R       | `training_types.*` | auth, XSS  | `{training_type}`: int |
| F9d | **RESOURCE** | `trainers`         | TrainerController      | R       | `trainers.*`       | auth, XSS  | `{trainer}`: int       |

### F10. Announcements, Events, Meetings, Holidays

| #    | Method       | URI                               | Controller             | Action                | Route Name                        | Middleware     | Params                |
| ---- | ------------ | --------------------------------- | ---------------------- | --------------------- | --------------------------------- | -------------- | --------------------- |
| F10a | POST         | `announcements/getdepartment`     | AnnouncementController | getdepartment         | `announcements.getdepartment`     | auth, verified | —                     |
| F10b | POST         | `announcements/getemployee`       | AnnouncementController | getemployee           | `announcements.getemployee`       | auth, verified | —                     |
| F10c | **RESOURCE** | `announcement`                    | AnnouncementController | R                     | `announcement.*`                  | auth, XSS      | `{announcement}`: int |
| F10d | POST         | `events/get-department`           | EventController        | GET_DPT               | `events.getdepartment`            | auth, XSS      | —                     |
| F10e | POST         | `events/get-employee`             | EventController        | GET_EMP               | `events.getemployee`              | auth, XSS      | —                     |
| F10f | **RESOURCE** | `events`                          | EventController        | R                     | `events.*`                        | auth, XSS      | `{event}`: int        |
| F10g | ANY          | `events/get_event_data`           | EventController        | GET_EV_D              | `events.get_event_data`           | auth, XSS      | —                     |
| F10h | ANY          | `events/get_dashboard_event_data` | EventController        | getDashboardEventData | `events.get_dashboard_event_data` | auth, XSS      | —                     |
| F10i | POST         | `meetings/get-department`         | MeetingController      | GET_DPT               | `meetings.getdepartment`          | auth, XSS      | —                     |
| F10j | POST         | `meetings/get-employee`           | MeetingController      | GET_EMP               | `meetings.getemployee`            | auth, XSS      | —                     |
| F10k | **RESOURCE** | `meetings`                        | MeetingController      | R                     | `meetings.*`                      | auth, XSS      | `{meeting}`: int      |
| F10l | ANY          | `meetings/get_meeting_data`       | MeetingController      | GET_MT_D              | `meetings.get_meeting_data`       | auth, XSS      | —                     |
| F10m | GET          | `meeting-calendar`                | MeetingController      | calendar              | `meetings.calendar`               | auth, XSS      | —                     |
| F10n | **RESOURCE** | `holidays`                        | HolidayController      | R                     | `holidays.*`                      | auth, XSS      | `{holiday}`: int      |
| F10o | ANY          | `holidays/data`                   | HolidayController      | GET_HL_D              | `holidays.get_holiday_data`       | auth, XSS      | —                     |
| F10p | GET          | `holiday-calendar`                | HolidayController      | calendar              | `holidays.calendar`               | auth, XSS      | —                     |

### F11. Appraisals, Performance, Goals, Indicators, Company Policies

| #    | Method       | URI                       | Controller                | Action      | Route Name                   | Middleware            | Params                    |
| ---- | ------------ | ------------------------- | ------------------------- | ----------- | ---------------------------- | --------------------- | ------------------------- |
| F11a | **RESOURCE** | `company_policies`        | CompanyPolicyController   | R           | `company_policies.*`         | auth, XSS             | `{company_policy}`: int   |
| F11b | **RESOURCE** | `indicators`              | IndicatorController       | R           | `indicators.*`               | auth, XSS             | `{indicator}`: int        |
| F11c | **RESOURCE** | `appraisals`              | AppraisalController       | R           | `appraisals.*`               | auth, XSS             | `{appraisal}`: int        |
| F11d | POST         | `appraisals` (star route) | AppraisalController       | EMP_BY_STR  | `appraisals.employees.star`  | auth, XSS             | —                         |
| F11e | POST         | `appraisals1`             | AppraisalController       | EMP_BY_STR1 | `appraisals.employees.star1` | auth, XSS             | —                         |
| F11f | POST         | `appraisals/get-employee` | AppraisalController       | GET_EMP     | `appraisals.get.employee`    | auth, XSS             | —                         |
| F11g | **RESOURCE** | `goal_types`              | GoalTypeController        | R           | `goal_types.*`               | auth, XSS             | `{goal_type}`: int        |
| F11h | **RESOURCE** | `goal_trackings`          | GoalTrackingController    | R           | `goal_trackings.*`           | auth, XSS             | `{goal_tracking}`: int    |
| F11i | **RESOURCE** | `competencies`            | CompetenciesController    | R           | `competencies.*`             | auth, XSS             | `{competency}`: int       |
| F11j | **RESOURCE** | `performance_types`       | PerformanceTypeController | R           | `performance_types.*`        | auth, XSS, revalidate | `{performance_type}`: int |
| F11k | **RESOURCE** | `account_assets`          | AssetController           | R           | `account_assets.*`           | auth, XSS             | `{account_asset}`: int    |

### F12. Recruitment

| #        | Method       | URI                                 | Controller                  | Action              | Route Name                               | Middleware     | Params                      |
| -------- | ------------ | ----------------------------------- | --------------------------- | ------------------- | ---------------------------------------- | -------------- | --------------------------- |
| F12a     | **RESOURCE** | `jobs`                              | JobController               | R                   | `jobs.*`                                 | auth, XSS      | `{job}`: int                |
| F12b     | **RESOURCE** | `job-category`                      | JobCategoryController       | R                   | `job-category.*`                         | auth, XSS      | `{job_category}`: int       |
| F12c     | POST         | `job-stage/order`                   | JobStageController          | ORD                 | `jobs.stage.order`                       | auth, verified | —                           |
| F12d     | **RESOURCE** | `job-stage`                         | JobStageController          | R                   | `job-stage.*`                            | auth, XSS      | `{job_stage}`: int          |
| F12e     | GET          | `candidates-job-applications`       | JobApplicationController    | candidate           | `jobs.application.candidate`             | auth, XSS      | —                           |
| F12f     | POST         | `job-application/order`             | JobApplicationController    | order               | `jobs.application.order`                 | XSS            | —                           |
| F12g     | POST         | `job-application/{id}/rating`       | JobApplicationController    | rating              | `jobs.application.rating`                | XSS            | `id`: int                   |
| F12h     | DELETE       | `job-application/{id}/archive`      | JobApplicationController    | archive             | `jobs.application.archive`               | auth, XSS      | `id`: int                   |
| F12i     | POST         | `job-application/{id}/skill/store`  | JobApplicationController    | addSkill            | `jobs.application.skill.store`           | auth, XSS      | `id`: int                   |
| F12j     | POST         | `job-application/{id}/note/store`   | JobApplicationController    | addNote             | `jobs.application.note.store`            | auth, XSS      | `id`: int                   |
| F12k     | DELETE       | `job-application/{id}/note/destroy` | JobApplicationController    | destroyNote         | `jobs.application.note.destroy`          | auth, XSS      | `id`: int                   |
| F12l     | POST         | `job-application/getByJob`          | JobApplicationController    | getByJob            | `job_applications.get`                   | auth, XSS      | —                           |
| F12m     | GET          | `job-onboard`                       | JobApplicationController    | jobOnBoard          | `jobs.on.board`                          | auth, XSS      | —                           |
| F12n     | GET          | `jobs_onboards/create/{id}`         | JobApplicationController    | jobBoardCreate      | `jobs.on.board.create`                   | auth, XSS      | `id`: int                   |
| F12o     | POST         | `jobs_onboards/store/{id}`          | JobApplicationController    | jobBoardStore       | `jobs.on.board.store`                    | auth, XSS      | `id`: int                   |
| F12p     | GET          | `jobs_onboards/edit/{id}`           | JobApplicationController    | jobBoardEdit        | `jobs.on.board.edit`                     | auth, XSS      | `id`: int                   |
| F12q     | POST         | `jobs_onboards/update/{id}`         | JobApplicationController    | jobBoardUpdate      | `jobs.on.board.update`                   | auth, XSS      | `id`: int                   |
| F12r     | DELETE       | `jobs_onboards/delete/{id}`         | JobApplicationController    | jobBoardDelete      | `jobs.on.board.delete`                   | auth, XSS      | `id`: int                   |
| F12s     | GET          | `jobs_onboards/convert/{id}`        | JobApplicationController    | jobBoardConvert     | `jobs.on.board.convert`                  | auth, XSS      | `id`: int                   |
| F12t     | POST         | `jobs_onboards/convert/{id}`        | JobApplicationController    | jobBoardConvertData | `jobs.on.board.convert`                  | auth, XSS      | `id`: int                   |
| F12u     | POST         | `job-application/stage/change`      | JobApplicationController    | stageChange         | `jobs.application.stage.change`          | auth, XSS      | —                           |
| F12v     | **RESOURCE** | `job-application`                   | JobApplicationController    | R                   | `job-application.*`                      | auth, XSS      | `{job_application}`: int    |
| F12w     | GET          | `interview_schedules/create/{id?}`  | InterviewScheduleController | create              | `interview_schedules.create`             | auth, XSS      | `id`: int, optional         |
| F12x     | **RESOURCE** | `interview-schedule`                | InterviewScheduleController | R                   | `interview-schedule.*`                   | auth, XSS      | `{interview_schedule}`: int |
| F12y     | ANY          | `interview_schedules/data`          | InterviewScheduleController | GET_ITV_D           | `interview_schedules.get_interview_data` | auth, XSS      | —                           |
| F12z     | **RESOURCE** | `custom-question`                   | CustomQuestionController    | R                   | `custom-question.*`                      | auth, XSS      | `{custom_question}`: int    |
| F12za-zg | aliases      | `custom-questions/*`                | CustomQuestionController    | (aliases)           | `custom_questions.*`                     | auth, XSS      | `{custom_question}`: int    |
| F12zh    | GET          | `jobs_onboards/pdf/{id}`            | JobApplicationController    | OFL_PDF             | `offer_letter.download.pdf`              | auth, XSS      | `id`: int                   |
| F12zi    | GET          | `jobs_onboards/doc/{id}`            | JobApplicationController    | OFL_DC              | `offer_letter.download.doc`              | auth, XSS      | `id`: int                   |

### F13. Document Uploads

| #    | Method       | URI                | Controller               | Action | Route Name           | Middleware | Params                   |
| ---- | ------------ | ------------------ | ------------------------ | ------ | -------------------- | ---------- | ------------------------ |
| F13a | **RESOURCE** | `document_uploads` | DocumentUploadController | R      | `document_uploads.*` | auth, XSS  | `{document_upload}`: int |

---

## G. PROJECT MANAGEMENT

### G1. Projects

| #   | Method       | URI                                               | Controller        | Action                  | Route Name                          | Middleware | Params                                               |
| --- | ------------ | ------------------------------------------------- | ----------------- | ----------------------- | ----------------------------------- | ---------- | ---------------------------------------------------- |
| G1a | GET          | `project/{view?}`                                 | ProjectController | index                   | `projects.list`                     | auth, XSS  | `view`: string, optional                             |
| G1b | GET          | `projects-view`                                   | ProjectController | filterProjectView       | `filter.project.view`               | auth, XSS  | —                                                    |
| G1c | **RESOURCE** | `projects`                                        | ProjectController | R                       | `projects.*`                        | auth, XSS  | `{project}`: int                                     |
| G1d | GET          | `invite-project-member/{id}`                      | ProjectController | inviteMemberView        | `projects.invite.member.view`       | auth, XSS  | `id`: int                                            |
| G1e | POST         | `invite-project-user-member`                      | ProjectController | inviteProjectUserMember | `projects.invite.user.member`       | auth, XSS  | —                                                    |
| G1f | DELETE       | `projects/{id}/users/{uid}`                       | ProjectController | destroyProjectUser      | `projects.users.destroy`            | auth, XSS  | `id`: int; `uid`: int                                |
| G1g | POST         | `projects/{id}/store-stages/{slug}`               | ProjectController | storeProjectTaskStages  | `projects.stages.store`             | auth, XSS  | `id`: int; `slug`: string                            |
| G1h | PATCH        | `remove-user-from-project/{project_id}/{user_id}` | ProjectController | removeUserFromProject   | `remove.user.from.project`          | auth, XSS  | `project_id`: int; `user_id`: int                    |
| G1i | GET          | `projects-users`                                  | ProjectController | loadUser                | `projects.user`                     | auth, XSS  | —                                                    |
| G1j | GET          | `projects/{id}/gantt/{duration?}`                 | ProjectController | gantt                   | `projects.gantt`                    | auth, XSS  | `id`: int; `duration`: string, optional (week/month) |
| G1k | POST         | `projects/{id}/gantt`                             | ProjectController | ganttPost               | `projects.gantt.post`               | auth, XSS  | `id`: int                                            |
| G1l | GET          | `projects/{id}/users/{uid}/permission`            | ProjectController | userPermission          | `projects.users.permission`         | auth, XSS  | `id`: int; `uid`: int                                |
| G1m | POST         | `projects/{id}/users/{uid}/permission`            | ProjectController | userPermissionStore     | `projects.users.permissions.store`  | auth, XSS  | `id`: int; `uid`: int                                |
| G1n | GET          | `/project/copy/{id}`                              | ProjectController | copyproject             | `projects.copy`                     | auth, XSS  | `id`: int                                            |
| G1o | POST         | `/project/copy/store/{id}`                        | ProjectController | copyprojectstore        | `projects.copy.store`               | auth, XSS  | `id`: int                                            |
| G1p | GET          | `projects/time-tracker/{id}`                      | ProjectController | tracker                 | `projects.time.tracker`             | auth, XSS  | `id`: int                                            |
| G1q | ANY          | `projects/copy/link/{id}`                         | ProjectController | CP_LNK_ST               | `projects.copy.link`                | auth, XSS  | `id`: int                                            |
| G1r | ANY          | `projects/{id}/setting-create`                    | ProjectController | CP_LNK_ST_CRT           | `projects.copy_link.setting.create` | auth, XSS  | `id`: int                                            |
| G1s | GET          | `share-project/{lang?}`                           | ProjectController | shareProject            | `share.project`                     | auth, XSS  | `lang`: string, optional                             |

### G2. Milestones

| #   | Method | URI                             | Controller        | Action    | Route Name                    | Middleware | Params    |
| --- | ------ | ------------------------------- | ----------------- | --------- | ----------------------------- | ---------- | --------- |
| G2a | GET    | `projects/{id}/milestones`      | ProjectController | milestone | `projects.milestones`         | auth, XSS  | `id`: int |
| G2b | POST   | `projects/{id}/milestones`      | ProjectController | ML_STR    | `projects.milestones.store`   | auth, XSS  | `id`: int |
| G2c | GET    | `projects/milestones/{id}/edit` | ProjectController | ML_ED     | `projects.milestones.edit`    | auth, XSS  | `id`: int |
| G2d | POST   | `projects/milestones/{id}`      | ProjectController | ML_UPD    | `projects.milestones.update`  | auth, XSS  | `id`: int |
| G2e | DELETE | `projects/milestones/{id}`      | ProjectController | ML_DST    | `projects.milestones.destroy` | auth, XSS  | `id`: int |
| G2f | GET    | `projects/milestones/{id}/show` | ProjectController | ML_SHW    | `projects.milestones.show`    | auth, XSS  | `id`: int |

### G3. Project Tasks

| #    | Method | URI                                      | Controller            | Action        | Route Name                              | Middleware     | Params                               |
| ---- | ------ | ---------------------------------------- | --------------------- | ------------- | --------------------------------------- | -------------- | ------------------------------------ |
| G3a  | GET    | `stage/{id}/tasks`                       | ProjectTaskController | GET_STG_TSK   | `projects.tasks.stage`                  | auth, XSS      | `id`: int                            |
| G3b  | GET    | `projects/{id}/tasks`                    | ProjectTaskController | index         | `projects.tasks.index`                  | auth, XSS      | `id`: int                            |
| G3c  | GET    | `projects/{pid}/tasks/{sid}`             | ProjectTaskController | create        | `projects.tasks.create`                 | auth, XSS      | `pid`: int; `sid`: int               |
| G3d  | POST   | `projects/{pid}/tasks/{sid}`             | ProjectTaskController | store         | `projects.tasks.store`                  | auth, XSS      | `pid`: int; `sid`: int               |
| G3e  | GET    | `projects/{id}/tasks/{tid}/show`         | ProjectTaskController | show          | `projects.tasks.show`                   | auth, XSS      | `id`: int; `tid`: int                |
| G3f  | GET    | `projects/{id}/tasks/{tid}/edit`         | ProjectTaskController | edit          | `projects.tasks.edit`                   | auth, XSS      | `id`: int; `tid`: int                |
| G3g  | POST   | `projects/{id}/tasks/update/{tid}`       | ProjectTaskController | update        | `projects.tasks.update`                 | auth, XSS      | `id`: int; `tid`: int                |
| G3h  | DELETE | `projects/{id}/tasks/{tid}`              | ProjectTaskController | destroy       | `projects.tasks.destroy`                | auth, XSS      | `id`: int; `tid`: int                |
| G3i  | PATCH  | `projects/{id}/tasks/order`              | ProjectTaskController | TSK_OD_UPD    | `projects.tasks.update.order`           | auth, XSS      | `id`: int                            |
| G3j  | PATCH  | `update-task-priority-color`             | ProjectTaskController | UPD_TSK_PR_CL | `projects.tasks.update.priority.color`  | auth, XSS      | —                                    |
| G3k  | POST   | `projects/{id}/comment/{tid}/file`       | ProjectTaskController | CM_STR_F      | `projects.tasks.comment.store.file`     | auth, XSS      | `id`: int; `tid`: int                |
| G3l  | DELETE | `projects/{id}/comment/{tid}/file/{fid}` | ProjectTaskController | CM_DST_F      | `projects.tasks.comment.destroy.file`   | auth, verified | `id`: int; `tid`: int; `fid`: int    |
| G3m  | POST   | `projects/{id}/comment/{tid}`            | ProjectTaskController | CM_STR        | `projects.tasks.comment.store`          | auth, verified | `id`: int; `tid`: int                |
| G3n  | DELETE | `projects/{id}/comment/{tid}/{cid}`      | ProjectTaskController | CM_DST        | `projects.tasks.comment.destroy`        | auth, verified | `id`: int; `tid`: int; `cid`: int    |
| G3o  | POST   | `projects/{id}/checklist/{tid}`          | ProjectTaskController | CHKL_STR      | `projects.tasks.checklist.store`        | auth, verified | `id`: int; `tid`: int                |
| G3p  | POST   | `projects/{id}/checklist/update/{cid}`   | ProjectTaskController | CHKL_UPD      | `projects.tasks.checklist.update`       | auth, verified | `id`: int; `cid`: int                |
| G3q  | DELETE | `projects/{id}/checklist/{cid}`          | ProjectTaskController | CHKL_DST      | `projects.tasks.checklist.destroy`      | auth, verified | `id`: int; `cid`: int                |
| G3r  | POST   | `projects/{id}/change/{tid}/fav`         | ProjectTaskController | CG_FAV        | `projects.tasks.change.fav`             | auth, verified | `id`: int; `tid`: int                |
| G3s  | POST   | `projects/{id}/change/{tid}/complete`    | ProjectTaskController | CG_COM        | `projects.tasks.change.complete`        | auth, verified | `id`: int; `tid`: int                |
| G3t  | POST   | `projects/{id}/change/{tid}/progress`    | ProjectTaskController | CG_PRG        | `projects.taskschange.progress`         | auth, verified | `id`: int; `tid`: int                |
| G3u  | GET    | `projects/tasks/{id}/get`                | ProjectTaskController | GET_TSK       | `projects.tasks.get`                    | auth, XSS      | `id`: int                            |
| G3v  | GET    | `task-board/{view?}`                     | ProjectTaskController | TSK_BD        | `taskboards.view`                       | auth, XSS      | `view`: string, optional             |
| G3w  | GET    | `task-board-view`                        | ProjectTaskController | TSK_BD_VW     | `projects.taskboard.view`               | auth, XSS      | —                                    |
| G3x  | POST   | `calendar/get_task_data`                 | ProjectTaskController | GET_TSK_D     | `projects.tasks.calendar.get_task_data` | auth, XSS      | —                                    |
| G3y  | GET    | `/calendar/{id}/show`                    | ProjectTaskController | CLD_SHW       | `projects.tasks.calendar.show`          | auth, XSS      | `id`: int                            |
| G3z  | POST   | `/calendar/{id}/drag`                    | ProjectTaskController | CLD_DRG       | `projects.tasks.calendar.drag`          | auth, verified | `id`: int                            |
| G3za | GET    | `calendar/{task}/{pid?}`                 | ProjectTaskController | CLD_VW        | `projects.tasks.calendar`               | auth, XSS      | `task`: string; `pid`: int, optional |

### G4. Project Task Stages

| #   | Method       | URI                         | Controller          | Action | Route Name                  | Middleware     | Params                      |
| --- | ------------ | --------------------------- | ------------------- | ------ | --------------------------- | -------------- | --------------------------- |
| G4a | POST         | `project_task_stages/order` | TaskStageController | order  | `project_task_stages.order` | auth, verified | —                           |
| G4b | POST         | `project_task_stages-new`   | TaskStageController | STR_V  | `project_task_stages.new`   | auth, XSS      | —                           |
| G4c | **RESOURCE** | `project_task_stages`       | TaskStageController | R      | `project_task_stages.*`     | auth, XSS      | `{project_task_stage}`: int |

### G5. Project Expenses

| #   | Method | URI                                 | Controller        | Action  | Route Name                  | Middleware | Params                |
| --- | ------ | ----------------------------------- | ----------------- | ------- | --------------------------- | ---------- | --------------------- |
| G5a | GET    | `projects/{id}/expenses`            | ExpenseController | index   | `projects.expenses.index`   | auth, XSS  | `id`: int             |
| G5b | GET    | `projects/{pid}/expenses/create`    | ExpenseController | create  | `projects.expenses.create`  | auth, XSS  | `pid`: int            |
| G5c | POST   | `projects/{pid}/expenses/store`     | ExpenseController | store   | `projects.expenses.store`   | auth, XSS  | `pid`: int            |
| G5d | GET    | `projects/{id}/expenses/{eid}/edit` | ExpenseController | edit    | `projects.expenses.edit`    | auth, XSS  | `id`: int; `eid`: int |
| G5e | POST   | `projects/{id}/expenses/{eid}`      | ExpenseController | update  | `projects.expenses.update`  | auth, XSS  | `id`: int; `eid`: int |
| G5f | DELETE | `projects/{eid}/expenses/`          | ExpenseController | destroy | `projects.expenses.destroy` | auth, XSS  | `eid`: int            |

### G6. Project Bugs

| #   | Method       | URI                                | Controller              | Action        | Route Name                            | Middleware | Params                   |
| --- | ------------ | ---------------------------------- | ----------------------- | ------------- | ------------------------------------- | ---------- | ------------------------ |
| G6a | POST         | `project_stages/order`             | ProjectStagesController | order         | `project_stages.order`                | auth, XSS  | —                        |
| G6b | POST         | `projects/bugs/kanban/order`       | ProjectController       | BUG_KB_OD     | `projects.bugs.kanban.order`          | auth, XSS  | —                        |
| G6c | GET          | `projects/{id}/bugs/kanban`        | ProjectController       | BUG_KB        | `projects.tasks.bugs.kanban`          | auth, XSS  | `id`: int                |
| G6d | GET          | `projects/{id}/bugs`               | ProjectController       | bug           | `projects.tasks.bugs`                 | auth, XSS  | `id`: int                |
| G6e | GET          | `projects/{id}/bugs/create`        | ProjectController       | BUG_CRT       | `projects.tasks.bugs.create`          | auth, XSS  | `id`: int                |
| G6f | POST         | `projects/{id}/bugs/store`         | ProjectController       | BUG_ST        | `projects.tasks.bugs.store`           | auth, XSS  | `id`: int                |
| G6g | GET          | `projects/{id}/bugs/{bid}/edit`    | ProjectController       | BUG_EDT       | `projects.tasks.bugs.edit`            | auth, XSS  | `id`: int; `bid`: int    |
| G6h | POST         | `projects/{id}/bugs/{bid}/update`  | ProjectController       | BUG_UPD       | `projects.tasks.bugs.update`          | auth, XSS  | `id`: int; `bid`: int    |
| G6i | DELETE       | `projects/{id}/bugs/{bid}/destroy` | ProjectController       | BUG_DST       | `projects.tasks.bugs.destroy`         | auth, XSS  | `id`: int; `bid`: int    |
| G6j | GET          | `projects/{id}/bugs/{bid}/show`    | ProjectController       | BUG_SHW       | `projects.tasks.bugs.show`            | auth, XSS  | `id`: int; `bid`: int    |
| G6k | POST         | `projects/{id}/bugs/{bid}/comment` | ProjectController       | BUG_CMT_STR   | `projects.bugs.comments.store`        | auth, XSS  | `id`: int; `bid`: int    |
| G6l | POST         | `projects/bugs/{bid}/file`         | ProjectController       | BUG_CMT_STR_F | `projects.bugs.comments.file.store`   | auth, XSS  | `bid`: int               |
| G6m | DELETE       | `projects/bugs/comment/{id}`       | ProjectController       | BUG_CMT_DST   | `projects.bugs.comments.destroy`      | auth, XSS  | `id`: int                |
| G6n | POST         | `projects/bugs/file/{id}`          | ProjectController       | BUG_CMT_DST_F | `projects.bugs.comments.file.destroy` | auth, XSS  | `id`: int                |
| G6o | POST         | `bug_status/order`                 | BugStatusController     | ORD           | `bug_status.order`                    | auth, XSS  | —                        |
| G6p | GET          | `bugs_reports/{view?}`             | ProjectTaskController   | ALL_BUG       | `projects.bugs.view`                  | auth, XSS  | `view`: string, optional |
| G6q | **RESOURCE** | `project_stages`                   | ProjectStagesController | R             | `project_stages.*`                    | auth, XSS  | `{project_stage}`: int   |
| G6r | **RESOURCE** | `bug_status`                       | BugStatusController     | R             | `bug_status.*`                        | auth, XSS  | `{bug_status}`: int      |

### G7. Project Timesheets

| #   | Method | URI                                                             | Controller          | Action      | Route Name                               | Middleware | Params                                 |
| --- | ------ | --------------------------------------------------------------- | ------------------- | ----------- | ---------------------------------------- | ---------- | -------------------------------------- |
| G7a | GET    | `projects.timesheets/append-task`                               | TimesheetController | APD_TMS_TSK | `projects.timesheets.append.task`        | auth, XSS  | —                                      |
| G7b | GET    | `projects.timesheets/view`                                      | TimesheetController | FT_TMS_TBL  | `projects.timesheets.filters.view`       | auth, XSS  | —                                      |
| G7c | GET    | `projects.timesheets/table-view`                                | TimesheetController | FT_TMS_TBL  | `projects.timesheets.filters.table.view` | auth, XSS  | —                                      |
| G7d | GET    | `projects.timesheets/list`                                      | TimesheetController | TMS_LST     | `projects.timesheets.list`               | auth, XSS  | —                                      |
| G7e | GET    | `projects.timesheets/list-get`                                  | TimesheetController | GET_TMS_LST | `projects.timesheets.list.get`           | auth, XSS  | —                                      |
| G7f | GET    | `projects.timesheets/projects/{id}`                             | TimesheetController | TMS_VW      | `projects.timesheets.index`              | auth, XSS  | `id`: int                              |
| G7g | POST   | `projects.timesheets/projects/{id}`                             | TimesheetController | TMS_STR     | `projects.timesheets.store`              | auth, XSS  | `id`: int                              |
| G7h | GET    | `projects.timesheets/projects/{id}/create`                      | TimesheetController | TMS_CRT     | `projects.timesheets.create`             | auth, XSS  | `id`: int                              |
| G7i | GET    | `projects.timesheets/projects/{project_id}/edit/{timesheet_id}` | TimesheetController | TMS_ED      | `projects.timesheets.edit`               | auth, XSS  | `project_id`: int; `timesheet_id`: int |
| G7j | ANY    | `projects.timesheets/projects/update/{timesheet_id}`            | TimesheetController | TMS_UPD     | `projects.timesheets.update`             | auth, XSS  | `timesheet_id`: int                    |
| G7k | DELETE | `projects.timesheets/projects/{timesheet_id}`                   | TimesheetController | TMS_DST     | `projects.timesheets.destroy`            | auth, XSS  | `timesheet_id`: int                    |

### G8. Project Reports

| #   | Method       | URI                           | Controller              | Action            | Route Name                       | Middleware | Params                  |
| --- | ------------ | ----------------------------- | ----------------------- | ----------------- | -------------------------------- | ---------- | ----------------------- |
| G8a | POST         | `project_reports/data`        | ProjectReportController | ajax_data         | `project_reports.ajax`           | auth, XSS  | —                       |
| G8b | POST         | `project_reports/tasks/{id}`  | ProjectReportController | ajax_tasks_report | `project_reports.tasks.ajaxdata` | auth, XSS  | `id`: int               |
| G8c | GET          | `project_reports/export/{id}` | ProjectReportController | export            | `project_reports.export`         | auth, XSS  | `id`: int               |
| G8d | **RESOURCE** | `project_reports`             | ProjectReportController | R                 | `project_reports.*`              | auth, XSS  | `{project_report}`: int |

---

## H. CRM (Customer Relationship Management)

### H1. Deals

| #    | Method       | URI                             | Controller     | Action           | Route Name                       | Middleware     | Params                |
| ---- | ------------ | ------------------------------- | -------------- | ---------------- | -------------------------------- | -------------- | --------------------- |
| H1a  | POST         | `deals/user`                    | DealController | jsonUser         | `deals.user.json`                | auth, verified | —                     |
| H1b  | POST         | `deals/order`                   | DealController | order            | `deals.order`                    | auth, XSS      | —                     |
| H1c  | POST         | `deals/change-pipeline`         | DealController | changePipeline   | `deals.change.pipeline`          | auth, XSS      | —                     |
| H1d  | POST         | `deals/change-deal-status/{id}` | DealController | changeStatus     | `deals.change.status`            | auth, XSS      | `id`: int             |
| H1e  | GET          | `deals/{id}/labels`             | DealController | labels           | `deals.labels`                   | auth, XSS      | `id`: int             |
| H1f  | POST         | `deals/{id}/labels`             | DealController | labelStore       | `deals.labels.store`             | auth, XSS      | `id`: int             |
| H1g  | GET          | `deals/{id}/users`              | DealController | userEdit         | `deals.users.edit`               | auth, XSS      | `id`: int             |
| H1h  | PUT          | `deals/{id}/users`              | DealController | userUpdate       | `deals.users.update`             | auth, XSS      | `id`: int             |
| H1i  | DELETE       | `deals/{id}/users/{uid}`        | DealController | userDestroy      | `deals.users.destroy`            | auth, XSS      | `id`: int; `uid`: int |
| H1j  | GET          | `deals/{id}/clients`            | DealController | clientEdit       | `deals.clients.edit`             | auth, XSS      | `id`: int             |
| H1k  | PUT          | `deals/{id}/clients`            | DealController | clientUpdate     | `deals.clients.update`           | auth, XSS      | `id`: int             |
| H1l  | DELETE       | `deals/{id}/clients/{uid}`      | DealController | clientDestroy    | `deals.clients.destroy`          | auth, XSS      | `id`: int; `uid`: int |
| H1m  | GET          | `deals/{id}/products`           | DealController | productEdit      | `deals.products.edit`            | auth, XSS      | `id`: int             |
| H1n  | PUT          | `deals/{id}/products`           | DealController | productUpdate    | `deals.products.update`          | auth, XSS      | `id`: int             |
| H1o  | DELETE       | `deals/{id}/products/{uid}`     | DealController | productDestroy   | `deals.products.destroy`         | auth, XSS      | `id`: int; `uid`: int |
| H1p  | GET          | `deals/{id}/sources`            | DealController | sourceEdit       | `deals.sources.edit`             | auth, XSS      | `id`: int             |
| H1q  | PUT          | `deals/{id}/sources`            | DealController | sourceUpdate     | `deals.sources.update`           | auth, XSS      | `id`: int             |
| H1r  | DELETE       | `deals/{id}/sources/{uid}`      | DealController | sourceDestroy    | `deals.sources.destroy`          | auth, XSS      | `id`: int; `uid`: int |
| H1s  | POST         | `deals/{id}/file`               | DealController | fileUpload       | `deals.file.upload`              | auth, XSS      | `id`: int             |
| H1t  | GET          | `deals/{id}/file/{fid}`         | DealController | fileDownload     | `deals.file.download`            | auth, XSS      | `id`: int; `fid`: int |
| H1u  | DELETE       | `deals/{id}/file/delete/{fid}`  | DealController | fileDelete       | `deals.file.delete`              | auth, XSS      | `id`: int; `fid`: int |
| H1v  | POST         | `deals/{id}/note`               | DealController | noteStore        | `deals.note.store`               | auth           | `id`: int             |
| H1w  | GET          | `deals/{id}/tasks`              | DealController | taskCreate       | `deals.tasks.create`             | auth, XSS      | `id`: int             |
| H1x  | POST         | `deals/{id}/tasks`              | DealController | taskStore        | `deals.tasks.store`              | auth, XSS      | `id`: int             |
| H1y  | GET          | `deals/{id}/tasks/{tid}/show`   | DealController | taskShow         | `deals.tasks.show`               | auth, XSS      | `id`: int; `tid`: int |
| H1z  | GET          | `deals/{id}/tasks/{tid}/edit`   | DealController | taskEdit         | `deals.tasks.edit`               | auth, XSS      | `id`: int; `tid`: int |
| H1aa | PUT          | `deals/{id}/tasks/{tid}`        | DealController | taskUpdate       | `deals.tasks.update`             | auth, XSS      | `id`: int; `tid`: int |
| H1ab | PUT          | `deals/{id}/task_status/{tid}`  | DealController | taskUpdateStatus | `deals.tasks.update_status`      | auth, XSS      | `id`: int; `tid`: int |
| H1ac | DELETE       | `deals/{id}/tasks/{tid}`        | DealController | taskDestroy      | `deals.tasks.destroy`            | auth, XSS      | `id`: int; `tid`: int |
| H1ad | GET          | `deals/{id}/discussions`        | DealController | discussionCreate | `deals.discussions.create`       | auth, XSS      | `id`: int             |
| H1ae | POST         | `deals/{id}/discussions`        | DealController | discussionStore  | `deals.discussion.store`         | auth, XSS      | `id`: int             |
| H1af | GET          | `deals/{id}/permission/{cid}`   | DealController | permission       | `deals.client.permission`        | auth, XSS      | `id`: int; `cid`: int |
| H1ag | PUT          | `deals/{id}/permission/{cid}`   | DealController | permissionStore  | `deals.client.permissions.store` | auth, XSS      | `id`: int; `cid`: int |
| H1ah | GET          | `deals/list`                    | DealController | dealList         | `deals.list`                     | auth, XSS      | —                     |
| H1ai | GET          | `deals/{id}/call`               | DealController | callCreate       | `deals.calls.create`             | auth, XSS      | `id`: int             |
| H1aj | POST         | `deals/{id}/call`               | DealController | callStore        | `deals.calls.store`              | auth           | `id`: int             |
| H1ak | GET          | `deals/{id}/call/{cid}/edit`    | DealController | callEdit         | `deals.calls.edit`               | auth           | `id`: int; `cid`: int |
| H1al | PUT          | `deals/{id}/call/{cid}`         | DealController | callUpdate       | `deals.calls.update`             | auth           | `id`: int; `cid`: int |
| H1am | DELETE       | `deals/{id}/call/{cid}`         | DealController | callDestroy      | `deals.calls.destroy`            | auth, XSS      | `id`: int; `cid`: int |
| H1an | GET          | `deals/{id}/email`              | DealController | emailCreate      | `deals.emails.create`            | auth, XSS      | `id`: int             |
| H1ao | POST         | `deals/{id}/email`              | DealController | emailStore       | `deals.emails.store`             | auth, XSS      | `id`: int             |
| H1ap | **RESOURCE** | `deals`                         | DealController | R                | `deals.*`                        | auth, XSS      | `{deal}`: int         |

### H2. Leads

| #    | Method       | URI                             | Controller          | Action            | Route Name                 | Middleware     | Params                |
| ---- | ------------ | ------------------------------- | ------------------- | ----------------- | -------------------------- | -------------- | --------------------- |
| H2a  | POST         | `/lead_stages/order`            | LeadStageController | ORD               | `lead_stages.order`        | auth, verified | —                     |
| H2b  | **RESOURCE** | `lead_stages`                   | LeadStageController | R                 | `lead_stages.*`            | auth           | `{lead_stage}`: int   |
| H2c  | POST         | `/leads/json`                   | LeadController      | json              | `leads.json`               | auth, verified | —                     |
| H2d  | POST         | `/leads/order`                  | LeadController      | order             | `leads.order`              | auth, XSS      | —                     |
| H2e  | GET          | `/leads/list`                   | LeadController      | lead_list         | `leads.list`               | auth, XSS      | —                     |
| H2f  | POST         | `/leads/{id}/file`              | LeadController      | fileUpload        | `leads.file.upload`        | auth, XSS      | `id`: int             |
| H2g  | GET          | `/leads/{id}/file/{fid}`        | LeadController      | fileDownload      | `leads.file.download`      | auth, XSS      | `id`: int; `fid`: int |
| H2h  | DELETE       | `/leads/{id}/file/delete/{fid}` | LeadController      | fileDelete        | `leads.file.delete`        | auth, XSS      | `id`: int; `fid`: int |
| H2i  | POST         | `/leads/{id}/note`              | LeadController      | noteStore         | `leads.note.store`         | auth           | `id`: int             |
| H2j  | GET          | `/leads/{id}/labels`            | LeadController      | labels            | `leads.labels`             | auth, XSS      | `id`: int             |
| H2k  | POST         | `/leads/{id}/labels`            | LeadController      | labelStore        | `leads.labels.store`       | auth, XSS      | `id`: int             |
| H2l  | GET          | `/leads/{id}/users`             | LeadController      | userEdit          | `leads.users.edit`         | auth, XSS      | `id`: int             |
| H2m  | PUT          | `/leads/{id}/users`             | LeadController      | userUpdate        | `leads.users.update`       | auth, XSS      | `id`: int             |
| H2n  | DELETE       | `/leads/{id}/users/{uid}`       | LeadController      | userDestroy       | `leads.users.destroy`      | auth, XSS      | `id`: int; `uid`: int |
| H2o  | GET          | `/leads/{id}/products`          | LeadController      | productEdit       | `leads.products.edit`      | auth, XSS      | `id`: int             |
| H2p  | PUT          | `/leads/{id}/products`          | LeadController      | productUpdate     | `leads.products.update`    | auth, XSS      | `id`: int             |
| H2q  | DELETE       | `/leads/{id}/products/{uid}`    | LeadController      | productDestroy    | `leads.products.destroy`   | auth, XSS      | `id`: int; `uid`: int |
| H2r  | GET          | `/leads/{id}/sources`           | LeadController      | sourceEdit        | `leads.sources.edit`       | auth, XSS      | `id`: int             |
| H2s  | PUT          | `/leads/{id}/sources`           | LeadController      | sourceUpdate      | `leads.sources.update`     | auth, XSS      | `id`: int             |
| H2t  | DELETE       | `/leads/{id}/sources/{uid}`     | LeadController      | sourceDestroy     | `leads.sources.destroy`    | auth, XSS      | `id`: int; `uid`: int |
| H2u  | GET          | `/leads/{id}/discussions`       | LeadController      | discussionCreate  | `leads.discussions.create` | auth, XSS      | `id`: int             |
| H2v  | POST         | `/leads/{id}/discussions`       | LeadController      | discussionStore   | `leads.discussion.store`   | auth, XSS      | `id`: int             |
| H2w  | GET          | `/leads/{id}/show_convert`      | LeadController      | showConvertToDeal | `leads.convert.deal`       | auth, XSS      | `id`: int             |
| H2x  | POST         | `/leads/{id}/convert`           | LeadController      | convertToDeal     | `leads.convert.to.deal`    | auth, XSS      | `id`: int             |
| H2y  | GET          | `/leads/{id}/call`              | LeadController      | callCreate        | `leads.calls.create`       | auth, XSS      | `id`: int             |
| H2z  | POST         | `/leads/{id}/call`              | LeadController      | callStore         | `leads.calls.store`        | auth           | `id`: int             |
| H2aa | GET          | `/leads/{id}/call/{cid}/edit`   | LeadController      | callEdit          | `leads.calls.edit`         | auth, XSS      | `id`: int; `cid`: int |
| H2ab | PUT          | `/leads/{id}/call/{cid}`        | LeadController      | callUpdate        | `leads.calls.update`       | auth           | `id`: int; `cid`: int |
| H2ac | DELETE       | `/leads/{id}/call/{cid}`        | LeadController      | callDestroy       | `leads.calls.destroy`      | auth, XSS      | `id`: int; `cid`: int |
| H2ad | GET          | `/leads/{id}/email`             | LeadController      | emailCreate       | `leads.emails.create`      | auth, XSS      | `id`: int             |
| H2ae | POST         | `/leads/{id}/email`             | LeadController      | emailStore        | `leads.emails.store`       | auth           | `id`: int             |
| H2af | **RESOURCE** | `leads`                         | LeadController      | R                 | `leads.*`                  | auth, XSS      | `{lead}`: int         |

### H3. Pipelines, Stages, Labels, Sources, Clients

| #   | Method       | URI                          | Controller         | Action              | Route Name                | Middleware     | Params            |
| --- | ------------ | ---------------------------- | ------------------ | ------------------- | ------------------------- | -------------- | ----------------- |
| H3a | POST         | `/stages/order`              | StageController    | order               | `stages.order`            | auth, verified | —                 |
| H3b | POST         | `/stages/json`               | StageController    | json                | `stages.json`             | auth, verified | —                 |
| H3c | **RESOURCE** | `stages`                     | StageController    | R                   | `stages.*`                | auth, verified | `{stage}`: int    |
| H3d | **RESOURCE** | `pipelines`                  | PipelineController | R                   | `pipelines.*`             | auth, verified | `{pipeline}`: int |
| H3e | **RESOURCE** | `labels`                     | LabelController    | R                   | `labels.*`                | auth, verified | `{label}`: int    |
| H3f | **RESOURCE** | `sources`                    | SourceController   | R                   | `sources.*`               | auth, verified | `{source}`: int   |
| H3g | **RESOURCE** | `clients`                    | ClientController   | R                   | `clients.*`               | auth, XSS      | `{client}`: int   |
| H3h | ANY          | `client-reset-password/{id}` | ClientController   | clientPassword      | `clients.reset`           | auth, XSS      | `id`: int         |
| H3i | POST         | `client-reset-password/{id}` | ClientController   | clientPasswordReset | `clients.password.update` | auth, XSS      | `id`: int         |

### H4. Customers & Vendors

| #   | Method       | URI                      | Controller         | Action | Route Name              | Middleware            | Params            |
| --- | ------------ | ------------------------ | ------------------ | ------ | ----------------------- | --------------------- | ----------------- |
| H4a | GET          | `customers/{id}/show`    | CustomerController | show   | `customers.show`        | auth, XSS, revalidate | `id`: int         |
| H4b | **RESOURCE** | `customers`              | CustomerController | R      | `customers.*`           | auth, XSS, revalidate | `{customer}`: int |
| H4c | GET          | `customers/export`       | CustomerController | export | `customers.export`      | auth, XSS             | —                 |
| H4d | GET          | `customers/import/file`  | CustomerController | IMP_F  | `customers.file.import` | auth, XSS             | —                 |
| H4e | POST         | `customers/import/index` | CustomerController | import | `customers.import`      | auth, XSS             | —                 |
| H4f | GET          | `vendors/{id}/show`      | VendorController   | show   | `vendors.show`          | auth, XSS, revalidate | `id`: int         |
| H4g | **RESOURCE** | `vendors`                | VendorController   | R      | `vendors.*`             | auth, XSS, revalidate | `{vendor}`: int   |
| H4h | GET          | `vendors/export`         | VendorController   | export | `vendors.export`        | auth, XSS             | —                 |
| H4i | GET          | `vendors/import/file`    | VendorController   | IMP_F  | `vendors.file.import`   | auth, XSS             | —                 |
| H4j | POST         | `vendors/import/index`   | VendorController   | import | `vendors.import`        | auth, XSS             | —                 |

### H5. Zoom Meetings

| #   | Method       | URI                                   | Controller            | Action    | Route Name                            | Middleware | Params                |
| --- | ------------ | ------------------------------------- | --------------------- | --------- | ------------------------------------- | ---------- | --------------------- |
| H5a | ANY          | `zoom_meetings/projects/select/{bid}` | ZoomMeetingController | PRJ_W_USR | `zoom_meetings.projects.select`       | auth, XSS  | `bid`: int            |
| H5b | GET          | `zoom-meeting-calendar`               | ZoomMeetingController | calendar  | `zoom_meetings.calendar`              | auth, XSS  | —                     |
| H5c | **RESOURCE** | `zoom_meetings`                       | ZoomMeetingController | R         | `zoom_meetings.*`                     | auth, XSS  | `{zoom_meeting}`: int |
| H5d | ANY          | `zoom-meeting/get_zoom_meeting_data`  | ZoomMeetingController | GET_ZMM_D | `zoom_meetings.get_zoom_meeting_data` | auth, XSS  | —                     |

### H6. Contracts

| #   | Method       | URI                                   | Controller             | Action            | Route Name                             | Middleware            | Params                 |
| --- | ------------ | ------------------------------------- | ---------------------- | ----------------- | -------------------------------------- | --------------------- | ---------------------- |
| H6a | GET          | `contracts/{id}/description`          | ContractController     | DSCP              | `contracts.description`                | auth, XSS, revalidate | `id`: int              |
| H6b | GET          | `contracts/grid`                      | ContractController     | GRD               | `contracts.grid`                       | auth, XSS, revalidate | —                      |
| H6c | **RESOURCE** | `contracts`                           | ContractController     | R                 | `contracts.*`                          | auth, XSS, revalidate | `{contract}`: int      |
| H6d | POST         | `contracts/{id}/file`                 | ContractController     | F_UPL             | `contracts.file.upload`                | auth, XSS             | `id`: int              |
| H6e | GET          | `contracts/pdf/{id}`                  | ContractController     | PDF_FRM_CTC       | `contracts.download.pdf`               | auth                  | `id`: int              |
| H6f | GET          | `contracts/{id}/get_contract`         | ContractController     | PRNT_CTC          | `contracts.get`                        | auth                  | `id`: int              |
| H6g | POST         | `contracts/contract_status_edit/{id}` | ContractController     | CTC_ST_EDT        | `contracts.status`                     | auth, XSS             | `id`: int              |
| H6h | POST         | `contracts/{id}/contract_description` | ContractController     | CTC_DSCP_STR      | `contracts.contract_description.store` | auth                  | `id`: int              |
| H6i | GET          | `contracts/{id}/file/{fid}`           | ContractController     | F_DWN             | `contracts.file.download`              | auth, XSS             | `id`: int; `fid`: int  |
| H6j | DELETE       | `contracts/{id}/file/delete/{fid}`    | ContractController     | F_DEL             | `contracts.file.delete`                | auth, XSS             | `id`: int; `fid`: int  |
| H6k | GET          | `contracts/copy/{id}`                 | ContractController     | CPY_CTC           | `contracts.copy`                       | auth, XSS             | `id`: int              |
| H6l | POST         | `contracts/copy/store`                | ContractController     | CPY_CTC_STR       | `contracts.copy.store`                 | auth, XSS             | —                      |
| H6m | GET          | `contracts/{id}/mail`                 | ContractController     | SND_ML_CTC        | `contracts.send.mail`                  | auth, XSS             | `id`: int              |
| H6n | GET          | `/signature/{id}`                     | ContractController     | signature         | `contracts.signature`                  | auth                  | `id`: int              |
| H6o | POST         | `/signature-store`                    | ContractController     | signatureStore    | `contracts.signature.store`            | auth, XSS             | —                      |
| H6p | POST         | `contracts/{id}/comment`              | ContractController     | commentStore      | `contracts.comment.store`              | auth, verified        | `id`: int              |
| H6q | POST         | `contracts/{id}/notes`                | ContractController     | noteStore         | `contracts.note.store`                 | auth                  | `id`: int              |
| H6r | DELETE       | `contracts/{id}/notes`                | ContractController     | noteDestroy       | `contracts.note.destroy`               | auth                  | `id`: int              |
| H6s | DELETE       | `contracts/{id}/comment`              | ContractController     | commentDestroy    | `contracts.comment.destroy`            | auth, verified        | `id`: int              |
| H6t | GET          | `get-projects/{client_id}`            | ContractController     | clientWiseProject | `projects.by.user.id`                  | auth, XSS             | `client_id`: int       |
| H6u | ANY          | `contracts/clients/select/{bid}`      | ContractController     | clientwiseproject | `contracts.clients.select`             | auth, XSS             | `bid`: int             |
| H6v | **RESOURCE** | `contract_types`                      | ContractTypeController | R                 | `contract_types.*`                     | auth, XSS, revalidate | `{contract_type}`: int |

### H7. Supports

| #   | Method       | URI                   | Controller        | Action      | Route Name              | Middleware            | Params           |
| --- | ------------ | --------------------- | ----------------- | ----------- | ----------------------- | --------------------- | ---------------- |
| H7a | GET          | `supports/{id}/reply` | SupportController | reply       | `supports.reply`        | auth, XSS, revalidate | `id`: int        |
| H7b | POST         | `supports/{id}/reply` | SupportController | replyAnswer | `supports.reply.answer` | auth, XSS, revalidate | `id`: int        |
| H7c | GET          | `supports/grid`       | SupportController | grid        | `supports.grid`         | auth, XSS, revalidate | —                |
| H7d | **RESOURCE** | `supports`            | SupportController | R           | `supports.*`            | auth, XSS, revalidate | `{support}`: int |

---

## I. REPORTS

| #   | Method | URI                                                   | Controller       | Action                | Route Name                          | Middleware            | Params                                                                               |
| --- | ------ | ----------------------------------------------------- | ---------------- | --------------------- | ----------------------------------- | --------------------- | ------------------------------------------------------------------------------------ |
| I1  | GET    | `reports/income-summary`                              | ReportController | INC_SM                | `reports.income.summary`            | auth, XSS, revalidate | —                                                                                    |
| I2  | GET    | `reports/expense-summary`                             | ReportController | EXP_SM                | `reports.expense.summary`           | auth, XSS, revalidate | —                                                                                    |
| I3  | GET    | `reports/income-vs-expense-summary`                   | ReportController | INC_EXP_SM            | `reports.income.vs.expense.summary` | auth, XSS, revalidate | —                                                                                    |
| I4  | GET    | `reports/tax-summary`                                 | ReportController | TX_SM                 | `reports.tax.summary`               | auth, XSS, revalidate | —                                                                                    |
| I5  | GET    | `reports/invoice-summary`                             | ReportController | INV_SM                | `reports.invoice.summary`           | auth, XSS, revalidate | —                                                                                    |
| I6  | GET    | `reports/bill-summary`                                | ReportController | BL_SM                 | `reports.bill.summary`              | auth, XSS, revalidate | —                                                                                    |
| I7  | GET    | `reports/product-stock-report`                        | ReportController | PRD_STK               | `reports.product.stock.report`      | auth, XSS, revalidate | —                                                                                    |
| I8  | GET    | `reports/invoice-report`                              | ReportController | INV_SM                | `reports.invoice`                   | auth, XSS, revalidate | —                                                                                    |
| I9  | GET    | `reports/account-statement-report`                    | ReportController | ACC_STT               | `reports.account.statement`         | auth, XSS, revalidate | —                                                                                    |
| I10 | GET    | `reports/balance-sheet/{view?}`                       | ReportController | BL_SHT                | `reports.balance.sheet`             | auth, XSS, revalidate | `view`: string, optional (table/chart)                                               |
| I11 | GET    | `reports/profit-loss/{view?}`                         | ReportController | PRF_LS                | `reports.profit.loss`               | auth, XSS, revalidate | `view`: string, optional                                                             |
| I12 | GET    | `reports/ledger/{account?}`                           | ReportController | LDG_SM                | `reports.ledger`                    | auth, XSS, revalidate | `account`: int, optional                                                             |
| I13 | GET    | `reports/trial-balance`                               | ReportController | TRL_BL_SUM            | `reports.trial.balance`             | auth, XSS, revalidate | —                                                                                    |
| I14 | GET    | `reports-monthly-cashflow`                            | ReportController | MLY_CSH_FLW           | `reports.monthly.cashflow`          | auth, XSS             | —                                                                                    |
| I15 | GET    | `reports-quarterly-cashflow`                          | ReportController | QLY_CSH_FLW           | `reports.quarterly.cashflow`        | auth, XSS             | —                                                                                    |
| I16 | POST   | `trial-balance/export`                                | ReportController | TRL_BLC_EXP           | `reports.trial.balance.export`      | auth, XSS, revalidate | —                                                                                    |
| I17 | POST   | `balance-sheet/export`                                | ReportController | BLC_SHT_EXP           | `reports.balance.sheet.export`      | auth, XSS, revalidate | —                                                                                    |
| I18 | POST   | `balance-sheet/print/{view?}`                         | ReportController | BLC_SHT_PRT           | `reports.balance.sheet.print`       | auth, XSS, revalidate | `view`: string, optional                                                             |
| I19 | POST   | `print/trial-balance`                                 | ReportController | TRL_BLC_PRT           | `trial.balance.print`               | auth, XSS, revalidate | —                                                                                    |
| I20 | POST   | `export/profit-loss`                                  | ReportController | PRF_LS_EXP            | `reports.profit.loss.export`        | auth, XSS, revalidate | —                                                                                    |
| I21 | POST   | `print/profit-loss/{view?}`                           | ReportController | PRF_LS_PRT            | `reports.profit.loss.print`         | auth, XSS, revalidate | `view`: string, optional                                                             |
| I22 | GET    | `reports/sales`                                       | ReportController | SLS_RPT               | `reports.sales`                     | auth, XSS, revalidate | —                                                                                    |
| I23 | POST   | `sales/export`                                        | ReportController | SLS_RPT_EXP           | `reports.sales.export`              | auth, XSS, revalidate | —                                                                                    |
| I24 | POST   | `sales/report/print/`                                 | ReportController | SLS_RPT_PRT           | `reports.sales.report.print`        | auth, XSS, revalidate | —                                                                                    |
| I25 | GET    | `reports/receivables`                                 | ReportController | RCV_RPT               | `reports.receivables`               | auth, XSS, revalidate | —                                                                                    |
| I26 | POST   | `receivables/export`                                  | ReportController | RCV_EXP               | `receivables.export`                | auth, XSS, revalidate | —                                                                                    |
| I27 | POST   | `receivables/print`                                   | ReportController | RCV_PRT               | `reports.receivables.print`         | auth, XSS, revalidate | —                                                                                    |
| I28 | GET    | `reports/payables`                                    | ReportController | PAY_RPT               | `reports.payables`                  | auth, XSS, revalidate | —                                                                                    |
| I29 | POST   | `payables/print`                                      | ReportController | PAY_PRT               | `reports.payables.print`            | auth, XSS, revalidate | —                                                                                    |
| I30 | GET    | `reports/leave`                                       | ReportController | leave                 | `reports.leave`                     | auth, XSS             | —                                                                                    |
| I31 | GET    | `reports-leave`                                       | ReportController | leave                 | `reports.leave`                     | auth, XSS             | —                                                                                    |
| I32 | GET    | `employees/{id}/leave/{status}/{type}/{month}/{year}` | ReportController | employeeLeave         | `reports.employee.leave`            | auth, XSS             | `id`: int; `status`: string; `type`: string; `month`: int (1-12); `year`: int (YYYY) |
| I33 | GET    | `reports-payroll`                                     | ReportController | payroll               | `reports.payroll`                   | auth, XSS             | —                                                                                    |
| I34 | POST   | `reports-payroll/getdepartment`                       | ReportController | getPayrollDepartment  | `reports.payroll.getdepartment`     | auth, XSS             | —                                                                                    |
| I35 | POST   | `reports-payroll/getemployee`                         | ReportController | getPayrollEmployee    | `reports.payroll.getemployee`       | auth, XSS             | —                                                                                    |
| I36 | GET    | `reports-monthly-attendance`                          | ReportController | monthlyAttendance     | `reports.monthly.attendance`        | auth, XSS             | —                                                                                    |
| I37 | GET    | `reports/attendance/{month}/{branch}/{department}`    | ReportController | exportCsv             | `reports.attendance`                | auth, XSS             | `month`: string; `branch`: int; `department`: int                                    |
| I38 | POST   | `reports-monthly-attendance/getdepartment`            | ReportController | GET_DPT               | `reports.attendance.getdepartment`  | auth, XSS             | —                                                                                    |
| I39 | POST   | `reports-monthly-attendance/getemployee`              | ReportController | GET_EMP               | `reports.attendance.getemployee`    | auth, XSS             | —                                                                                    |
| I40 | GET    | `reports-lead`                                        | ReportController | leadReport            | `reports.lead`                      | auth, XSS             | —                                                                                    |
| I41 | GET    | `reports-deal`                                        | ReportController | dealReport            | `reports.deal`                      | auth, XSS             | —                                                                                    |
| I42 | GET    | `reports-warehouse`                                   | ReportController | warehouseReport       | `reports.warehouse`                 | auth, XSS             | —                                                                                    |
| I43 | GET    | `reports-daily-purchase`                              | ReportController | purchaseDailyReport   | `reports.daily.purchase`            | auth, XSS             | —                                                                                    |
| I44 | GET    | `reports-monthly-purchase`                            | ReportController | purchaseMonthlyReport | `reports.monthly.purchase`          | auth, XSS             | —                                                                                    |
| I45 | GET    | `reports-daily-pos`                                   | ReportController | posDailyReport        | `reports.daily.pos`                 | auth, XSS             | —                                                                                    |
| I46 | GET    | `reports-monthly-pos`                                 | ReportController | posMonthlyReport      | `reports.monthly.pos`               | auth, XSS             | —                                                                                    |
| I47 | GET    | `reports-pos-vs-purchase`                             | ReportController | posVsPurchaseReport   | `reports.pos.vs.purchase`           | auth, XSS             | —                                                                                    |
| I48 | GET    | `reports/payrolls/export`                             | ReportController | PayrollReportExport   | `reports.payroll.export`            | auth, XSS             | —                                                                                    |
| I49 | GET    | `account_statements/export`                           | ReportController | export                | `account_statements.export`         | auth, XSS             | —                                                                                    |

---

## J. POS (Point of Sale)

### J1. Purchases

| #   | Method       | URI                                    | Controller         | Action         | Route Name                     | Middleware            | Params                              |
| --- | ------------ | -------------------------------------- | ------------------ | -------------- | ------------------------------ | --------------------- | ----------------------------------- |
| J1a | GET          | `purchases/items`                      | PurchaseController | items          | `purchases.items`              | auth, XSS, revalidate | —                                   |
| J1b | GET          | `purchases/{id}/payment`               | PurchaseController | payment        | `purchases.payment`            | auth, XSS, revalidate | `id`: int                           |
| J1c | POST         | `purchases/{id}/payment`               | PurchaseController | createPayment  | `purchases.payment`            | auth, XSS, revalidate | `id`: int                           |
| J1d | POST         | `purchases/{id}/payment/{pid}/destroy` | PurchaseController | paymentDestroy | `purchases.payment.destroy`    | auth, XSS, revalidate | `id`: int; `pid`: int               |
| J1e | POST         | `purchases/product/destroy`            | PurchaseController | productDestroy | `purchases.product.destroy`    | auth, XSS, revalidate | —                                   |
| J1f | POST         | `purchases/vendor`                     | PurchaseController | vendor         | `purchases.vendor`             | auth, XSS, revalidate | —                                   |
| J1g | POST         | `purchases/product`                    | PurchaseController | product        | `purchases.product`            | auth, XSS, revalidate | —                                   |
| J1h | GET          | `purchases/create/{cid}`               | PurchaseController | create         | `purchases.create`             | auth, XSS, revalidate | `cid`: int                          |
| J1i | GET          | `purchases/{id}/sent`                  | PurchaseController | sent           | `purchases.sent`               | auth, XSS, revalidate | `id`: int                           |
| J1j | GET          | `purchases/{id}/resent`                | PurchaseController | resent         | `purchases.resent`             | auth, XSS, revalidate | `id`: int                           |
| J1k | **RESOURCE** | `purchases`                            | PurchaseController | R              | `purchases.*`                  | auth, XSS, revalidate | `{purchase}`: int                   |
| J1l | GET          | `purchases/preview/{template}/{color}` | PurchaseController | PV_PRC         | `purchases.preview`            | auth, XSS             | `template`: string; `color`: string |
| J1m | POST         | `purchases/templates/settings`         | PurchaseController | SV_PCR_TMP_STG | `purchases.templates.settings` | auth, XSS             | —                                   |
| J1n | GET          | `purchases/pdf/{id}`                   | PurchaseController | purchase       | `purchases.pdf`                | auth, XSS, revalidate | `id`: int                           |
| J1o | GET          | `vendors/purchases/{id}/`              | PurchaseController | PRC_LK         | `purchases.link.copy`          | — (public)            | `id`: int                           |

### J2. POS

| #   | Method       | URI                              | Controller    | Action     | Route Name                     | Middleware            | Params                              |
| --- | ------------ | -------------------------------- | ------------- | ---------- | ------------------------------ | --------------------- | ----------------------------------- |
| J2a | GET          | `pos/preview/{template}/{color}` | PosController | PV_POS     | `pos.preview`                  | auth, XSS             | `template`: string; `color`: string |
| J2b | POST         | `pos/template/setting`           | PosController | SV_POS_TMP | `purchases.templates.settings` | auth, XSS             | —                                   |
| J2c | GET          | `pos/pdf/{id}`                   | PosController | pos        | `pos.pdf`                      | auth, XSS, revalidate | `id`: int                           |
| J2d | GET          | `pos/data/store`                 | PosController | store      | `pos.data.store`               | auth, XSS, revalidate | —                                   |
| J2e | GET          | `printview/pos`                  | PosController | PRT_VW     | `pos.printview`                | auth, XSS, revalidate | —                                   |
| J2f | **RESOURCE** | `pos`                            | PosController | R          | `pos.*`                        | auth, XSS, revalidate | `{po}`: int                         |
| J2g | GET          | `pos/barcode`                    | PosController | barcode    | `pos.barcode`                  | auth, XSS             | —                                   |
| J2h | GET          | `settings/pos`                   | PosController | setting    | `pos.setting`                  | auth, XSS             | —                                   |
| J2i | POST         | `settings/barcode`               | PosController | BC_ST_STR  | `pos.barcode.setting`          | auth, XSS             | —                                   |
| J2j | GET          | `pos/print`                      | PosController | BC_PRT     | `pos.print`                    | auth, XSS             | —                                   |
| J2k | POST         | `pos/get-product`                | PosController | GET_PRD    | `pos.get.product`              | auth, XSS             | —                                   |
| J2l | ANY          | `pos-receipt`                    | PosController | receipt    | `pos.receipt`                  | auth, XSS             | —                                   |
| J2m | POST         | `pos/cart-discount`              | PosController | CRT_DSC    | `pos.cart.discount`            | auth, XSS             | —                                   |
| J2n | ANY          | `reports/pos`                    | PosController | report     | `pos.report`                   | auth, XSS             | —                                   |

### J3. Warehouses & Transfers

| #   | Method       | URI                                | Controller                  | Action  | Route Name                         | Middleware            | Params                      |
| --- | ------------ | ---------------------------------- | --------------------------- | ------- | ---------------------------------- | --------------------- | --------------------------- |
| J3a | **RESOURCE** | `warehouses`                       | WarehouseController         | R       | `warehouses.*`                     | auth, XSS, revalidate | `{warehouse}`: int          |
| J3b | POST         | `warehouse_transfers/get-product`  | WarehouseTransferController | GET_PRD | `warehouse_transfers.get.product`  | auth, XSS             | —                           |
| J3c | POST         | `warehouse_transfers/get-quantity` | WarehouseTransferController | GET_QT  | `warehouse_transfers.get.quantity` | auth, XSS             | —                           |
| J3d | **RESOURCE** | `warehouse_transfers`              | WarehouseTransferController | R       | `warehouse_transfers.*`            | auth, XSS, revalidate | `{warehouse_transfer}`: int |

### J4. Products / Cart

| #   | Method       | URI                                      | Controller                       | Action               | Route Name                               | Middleware                      | Params                            |
| --- | ------------ | ---------------------------------------- | -------------------------------- | -------------------- | ---------------------------------------- | ------------------------------- | --------------------------------- |
| J4a | GET          | `product_services/index`                 | ProductServiceController         | index                | `product_services.index`                 | auth, verified                  | —                                 |
| J4b | GET          | `product_services/{id}/detail`           | ProductServiceController         | WRH_DTL              | `product_services.detail`                | auth, verified                  | `id`: int                         |
| J4c | GET          | `product_services/export`                | ProductServiceController         | export               | `product_services.export`                | auth, verified                  | —                                 |
| J4d | POST         | `product_services/import`                | ProductServiceController         | import               | `product_services.import`                | auth, verified                  | —                                 |
| J4e | **RESOURCE** | `product_services`                       | ProductServiceController         | R                    | `product_services.*`                     | auth, XSS, revalidate           | `{product_service}`: int          |
| J4f | POST         | `empty-cart`                             | ProductServiceController         | EMP_CRT              | —                                        | auth, XSS                       | —                                 |
| J4g | POST         | `warehouse-empty-cart`                   | ProductServiceController         | WRH_EMP_CRT          | `warehouse-empty-cart`                   | auth, XSS                       | —                                 |
| J4h | POST         | `product_service_categories/get-account` | ProductServiceCategoryController | GET_ACC              | `product_service_categories.get_account` | auth, XSS, revalidate           | —                                 |
| J4i | **RESOURCE** | `product_service_categories`             | ProductServiceCategoryController | R                    | `product_service_categories.*`           | auth, XSS, revalidate           | `{product_service_category}`: int |
| J4j | **RESOURCE** | `product_service_units`                  | ProductServiceUnitController     | R                    | `product_service_units.*`                | auth, XSS, revalidate           | `{product_service_unit}`: int     |
| J4k | GET          | `product-categories`                     | ProductServiceCategoryController | GET_PRD_CAT          | `product_service_categories.categories`  | auth, XSS                       | —                                 |
| J4l | GET          | `name-search-products`                   | ProductServiceController         | searchProductsByName | `name.search.products`                   | auth, XSS                       | —                                 |
| J4m | GET          | `search-products`                        | ProductServiceController         | SRC_PRD              | `search.products`                        | auth, XSS                       | —                                 |
| J4n | GET          | `add-to-cart/{id}/{session}`             | ProductServiceController         | ADD_CRT              | —                                        | auth, XSS                       | `id`: int; `session`: string      |
| J4o | PATCH        | `update-cart`                            | ProductServiceController         | UPD_CRT              | —                                        | auth, XSS                       | —                                 |
| J4p | DELETE       | `remove-from-cart`                       | ProductServiceController         | RM_CRT               | —                                        | auth, XSS                       | —                                 |
| J4q | **RESOURCE** | `product_stocks`                         | ProductStockController           | R                    | `product_stocks.*`                       | auth, XSS, check.mount (set MW) | `{product_stock}`: int            |
| J4r | GET          | `product_stocks/export`                  | ReportController                 | stock_export         | `product_stocks.export`                  | auth, XSS (set MW)              | —                                 |

---

## K. PUBLIC / GUEST ROUTES

| #   | Method | URI                                                | Controller            | Action          | Route Name            | Middleware | Params                              |
| --- | ------ | -------------------------------------------------- | --------------------- | --------------- | --------------------- | ---------- | ----------------------------------- |
| K1  | GET    | `careers/{id}/{lang}`                              | JobController         | career          | `careers`             | XSS        | `id`: int; `lang`: string           |
| K2  | GET    | `jobs/requirement/{code}/{lang}`                   | JobController         | JB_RQ           | `jobs.requirement`    | XSS        | `code`: string; `lang`: string      |
| K3  | GET    | `jobs/apply/{code}/{lang}`                         | JobController         | JB_AP           | `jobs.apply`          | XSS        | `code`: string; `lang`: string      |
| K4  | POST   | `jobs/apply/data/{code}`                           | JobController         | JB_AP_DT        | `jobs.apply.data`     | XSS        | `code`: string                      |
| K5  | GET    | `customers/invoices/{id}/`                         | InvoiceController     | IV_LK           | `invoices.link.copy`  | —          | `id`: UUID                          |
| K6  | GET    | `customers/proposals/{id}/`                        | ProposalController    | IV_LK           | `proposals.link.copy` | —          | `id`: UUID                          |
| K7  | GET    | `vendors/bills/{id}/`                              | BillController        | IV_LK           | `bills.link.copy`     | —          | `id`: UUID                          |
| K8  | GET    | `vendors/purchases/{id}/`                          | PurchaseController    | PRC_LK          | `purchases.link.copy` | —          | `id`: int                           |
| K9  | GET    | `projects/copy-link/{id}`                          | ProjectController     | projectCopyLink | `projects.copy_link`  | —          | `id`: int                           |
| K10 | ANY    | `projects/link/{id}/{lang?}`                       | ProjectController     | projectlink     | `projects.link`       | XSS        | `id`: int; `lang`: string, optional |
| K11 | GET    | `forms/{code}`                                     | FormBuilderController | FM_VW           | `forms.view`          | XSS        | `code`: string                      |
| K12 | POST   | `forms/view_store`                                 | FormBuilderController | FM_VW_STR       | `forms.view.store`    | XSS        | —                                   |
| K13 | ANY    | `/cookie-consent`                                  | SystemController      | CK_CNSNT        | `cookie-consent`      | —          | —                                   |
| K14 | GET    | `.well-known/appspecific/com.chrome.devtools.json` | Closure               | —               | —                     | —          | —                                   |

---

## L. FORM BUILDER

| #   | Method       | URI                                   | Controller            | Action        | Route Name              | Middleware | Params                |
| --- | ------------ | ------------------------------------- | --------------------- | ------------- | ----------------------- | ---------- | --------------------- |
| L1  | **RESOURCE** | `form_builders`                       | FormBuilderController | R             | `form_builders.*`       | auth, XSS  | `{form_builder}`: int |
| L2  | GET          | `form_builders/{id}/field`            | FormBuilderController | FD_CRT        | `forms.fields.create`   | auth, XSS  | `id`: int             |
| L3  | POST         | `form_builders/{id}/field`            | FormBuilderController | FD_STR        | `forms.fields.store`    | auth, XSS  | `id`: int             |
| L4  | GET          | `form_builders/{id}/field/{fid}/show` | FormBuilderController | formFieldShow | `forms.fields.show`     | auth, XSS  | `id`: int; `fid`: int |
| L5  | GET          | `form_builders/{id}/field/{fid}/edit` | FormBuilderController | FD_EDT        | `forms.fields.edit`     | auth, XSS  | `id`: int; `fid`: int |
| L6  | POST         | `form_builders/{id}/field/{fid}`      | FormBuilderController | FD_UPD        | `forms.fields.update`   | auth, XSS  | `id`: int; `fid`: int |
| L7  | DELETE       | `form_builders/{id}/field/{fid}`      | FormBuilderController | FD_DST        | `forms.fields.destroy`  | auth, XSS  | `id`: int; `fid`: int |
| L8  | GET          | `forms/responses/{id}`                | FormBuilderController | VW_RES        | `forms.response`        | auth, XSS  | `id`: int             |
| L9  | GET          | `forms/responses/{id}/detail`         | FormBuilderController | RES_DT        | `forms.response.detail` | auth, XSS  | `id`: int             |
| L10 | GET          | `forms/binds/{id}`                    | FormBuilderController | FD_BD         | `forms.fields.bind`     | auth, XSS  | `id`: int             |
| L11 | POST         | `forms/binds/{id}/store`              | FormBuilderController | BD_STR        | `forms.bind.store`      | auth, XSS  | `id`: int             |

---

## M. AI / ChatGPT

| #   | Method | URI                        | Controller           | Action  | Route Name          | Middleware | Params                  |
| --- | ------ | -------------------------- | -------------------- | ------- | ------------------- | ---------- | ----------------------- |
| M1  | GET    | `generate/{template_name}` | AiTemplateController | create  | `generate`          | auth, XSS  | `template_name`: string |
| M2  | POST   | `generate/keywords/{id}`   | AiTemplateController | GET_KW  | `generate.keywords` | auth, XSS  | `id`: int               |
| M3  | POST   | `generate/response`        | AiTemplateController | AIG     | `generate.response` | auth, XSS  | —                       |
| M4  | GET    | `grammar/{template}`       | AiTemplateController | grammar | `grammar`           | auth, XSS  | `template`: string      |
| M5  | POST   | `grammar/response`         | AiTemplateController | GM_P    | `grammar.response`  | auth, XSS  | —                       |

---

## N. TIME TRACKER

| #   | Method | URI                     | Controller            | Action      | Route Name                   | Middleware | Params     |
| --- | ------ | ----------------------- | --------------------- | ----------- | ---------------------------- | ---------- | ---------- |
| N1  | GET    | `time_trackers`         | TimeTrackerController | index       | `time.tracker`               | auth, XSS  | —          |
| N2  | DELETE | `tracker/{tid}/destroy` | TimeTrackerController | destroy     | `time_trackers.destroy`      | auth, XSS  | `tid`: int |
| N3  | POST   | `tracker/image-view`    | TimeTrackerController | GET_TRT_IMG | `time_trackers.image.view`   | auth, XSS  | —          |
| N4  | DELETE | `tracker/image-remove`  | TimeTrackerController | RM_TRT_IMG  | `time_trackers.image.remove` | auth, XSS  | —          |

---

## O. API ROUTES

**File: `routes/api.php`** — Prefix: `/api` — Middleware: `XSS`, `throttle:10,1`

| #   | Method | URI                  | Controller    | Action      | Route Name       | Middleware                       | Params |
| --- | ------ | -------------------- | ------------- | ----------- | ---------------- | -------------------------------- | ------ |
| O1  | POST   | `/api/auth-login`    | ApiController | login       | `auth.login`     | XSS, throttle:10,1               | —      |
| O2  | POST   | `/api/logout`        | ApiController | logout      | `auth.logout`    | XSS, throttle:10,1, auth:sanctum | —      |
| O3  | GET    | `/api/get-projects`  | ApiController | GET_PRJ     | `projects.index` | XSS, throttle:10,1, auth:sanctum | —      |
| O4  | POST   | `/api/upload-photos` | ApiController | UP_IMG      | `photos.upload`  | XSS, throttle:10,1, auth:sanctum | —      |
| O5  | POST   | `/api/add-tracker`   | ApiController | ADD_TRK     | `trackers.store` | XSS, throttle:10,1, auth:sanctum | —      |
| O6  | POST   | `/api/stop-tracker`  | ApiController | stopTracker | `trackers.stop`  | XSS, throttle:10,1, auth:sanctum | —      |

---

## P. DYNAMIC PARAMETER SUMMARY

| Parameter           | Expected Type                   | Optional? | Regex Constraint | Used In                             |
| ------------------- | ------------------------------- | --------- | ---------------- | ----------------------------------- |
| `{id}`              | int or UUID (context-dependent) | No        | None             | Most routes                         |
| `{lang}`            | string (e.g. `en`, `pt-br`)     | Yes (`?`) | None             | Auth, Language, Career routes       |
| `{token}`           | string (UUID/hash)              | No        | None             | Password reset                      |
| `{hash}`            | string (SHA1)                   | No        | None             | Email verification                  |
| `{fileName}`        | string                          | No        | None             | Chatify download                    |
| `{template}`        | string (template slug)          | No        | None             | Preview routes                      |
| `{color}`           | string (hex/name)               | No        | None             | Preview routes                      |
| `{pid}`             | int                             | No        | None             | Payments, tasks, plans              |
| `{tid}`             | int                             | No        | None             | Tasks, trackers                     |
| `{uid}`             | int                             | No        | None             | Users in deals/leads/projects       |
| `{fid}`             | int                             | No        | None             | Files, form fields                  |
| `{cid}`             | int                             | No        | None             | Customers, clients, calls, comments |
| `{bid}`             | int                             | No        | None             | Bugs, contracts select              |
| `{cn_id}`           | int                             | No        | None             | Credit/debit notes                  |
| `{eid}`             | int                             | No        | None             | Employees, expenses                 |
| `{wid}`             | int                             | No        | None             | Webhooks                            |
| `{code}`            | string (slug/code)              | No        | None             | Jobs, Stripe, Forms                 |
| `{date}`            | string (Y-m format)             | No        | None             | Payslips                            |
| `{m}`               | string (month)                  | No        | None             | Payslip PDF/send                    |
| `{view}`            | string (e.g. grid/list)         | Yes (`?`) | None             | Dashboard, projects, bugs           |
| `{slug}`            | string                          | No        | None             | Project stages                      |
| `{session}`         | string                          | No        | None             | Cart                                |
| `{status}`          | string (enum)                   | No        | None             | Leave reports                       |
| `{type}`            | string                          | No        | None             | Leave, user info                    |
| `{month}`           | int (1-12)                      | No        | None             | Reports                             |
| `{year}`            | int (YYYY)                      | No        | None             | Reports                             |
| `{branch}`          | int                             | No        | None             | Attendance reports                  |
| `{department}`      | int                             | No        | None             | Attendance reports                  |
| `{duration}`        | string (week/month)             | Yes (`?`) | None             | Gantt chart                         |
| `{response}`        | string (accept/reject)          | No        | None             | Plan requests                       |
| `{invoice_id}`      | UUID                            | No        | None             | Benefit callback                    |
| `{amount}`          | numeric (float)                 | No        | None             | Benefit callback                    |
| `{project_id}`      | int                             | No        | None             | Timesheet edit, user removal        |
| `{timesheet_id}`    | int                             | No        | None             | Timesheets                          |
| `{user_id}`         | int                             | No        | None             | User removal from project           |
| `{client_id}`       | int                             | No        | None             | Client-wise projects                |
| `{item_id}`         | int                             | No        | None             | Journal destroy                     |
| `{account}`         | int                             | Yes (`?`) | None             | Ledger report                       |
| `{task}`            | string                          | No        | None             | Calendar view                       |
| `{custom_question}` | int                             | No        | None             | Custom question CRUD                |
| `{terminationtype}` | int                             | No        | None             | Termination type CRUD               |
| `{flag}`            | string                          | No        | None             | PaymentWall errors                  |

---

## ROUTE COUNT SUMMARY

| Category                    | Explicit Routes | Resource Routes (×7) | Estimated Total |
| --------------------------- | --------------- | -------------------- | --------------- |
| Auth                        | 14              | —                    | 14              |
| Home/Dashboard              | 12              | —                    | 12              |
| Admin/Settings              | ~60             | ~8 (×7=56)           | ~116            |
| Financial                   | ~80             | ~10 (×7=70)          | ~150            |
| HRM                         | ~85             | ~30 (×7=210)         | ~295            |
| Project Management          | ~70             | ~5 (×7=35)           | ~105            |
| CRM (Deals/Leads/Contracts) | ~95             | ~8 (×7=56)           | ~151            |
| Reports                     | ~49             | —                    | 49              |
| POS                         | ~35             | ~5 (×7=35)           | ~70             |
| Public                      | 14              | —                    | 14              |
| Form Builder                | 11              | 1 (×7=7)             | 18              |
| AI                          | 5               | —                    | 5               |
| Time Tracker                | 4               | —                    | 4               |
| API                         | 6               | —                    | 6               |
| **TOTAL**                   | **~540**        | **~67 (×7=469)**     | **~1009**       |

> **Note**: Some resource routes are registered multiple times (e.g. `users`, `taxes`, `account_assets`). Laravel uses the last registration, but all are listed for completeness.

What was deferred (needs further work or decision)
Needs systematic cross-controller work (Findings 4-5, 15-19): IDOR / cross-tenant scoping in UserController, EmployeeController, ClientController, RoleController, JobCategoryController, CompanyPolicyController, ProjectController. The right approach is a scoped query helper or global scope on these models — doing individual findOrFail() replacements in 6 controllers is error-prone.

Needs data migration before code change (Finding 6): Shared-link base64 "passwords" — hashing the new value with Hash::make() is trivial, but existing rows store base64 and must be re-generated, which means invalidating all current shared links.

Needs business intent clarification (Finding 7): JobController::jobApplyData() calls \_checkLogin() on what's routed as a public guest endpoint. Was that intentional (application requires account) or a bug (public career form)?

Note on Handler.php: Codex Finding 3 also cites app/Exceptions/Handler.php:82-92 and :160-170 for logging full request headers in exception paths. These were not in scope for this commit — the exception handler is correct behaviour in principle (log context on errors) but should redact Cookie, Authorization, and X-CSRF-TOKEN headers. Let me know if you want that cleaned up as a follow-on.

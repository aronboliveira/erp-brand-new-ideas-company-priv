<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, Route};
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects direct (non‑AJAX) browser hits on create/edit routes that serve
 * modal partials back to the parent index route with a `?modal=` query param.
 *
 * The admin layout JS (modal-autoopen.js) picks up the param and triggers
 * the standard `data-ajax-popup` fetch, so the user lands on the list page
 * with the correct modal already open.
 *
 * Full‑page create/edit views (those with @extends) are excluded.
 */
class RedirectModalRoutes
{
    /**
     * Resource prefixes whose create/edit views ARE full pages (use @extends).
     * These should NOT be redirected.
     */
    private const FULL_PAGE_RESOURCES = [
        'bank_transfers',
        'bills',
        'budgets',
        'clients',
        'contracts',
        'custom_pages',
        'customers',
        'deals',
        'discover',
        'employees',
        'expenses',
        'faqs',
        'features',
        'invoices',
        'jobs',
        'join_us',
        'journal_entries',
        'landingpage',
        'lead_stages',
        'overtimes',
        'payslips',
        'permission',
        'permissions',
        'pricing_plans',
        'product_stocks',
        'proposals',
        'purchases',
        'screenshots',
        'set_salaries',
        'settings',
        'testimonials',
        'warehouse',
        'warehouse_transfers',
    ];

    /**
     * Route‑name suffixes that indicate a "modal partial" action.
     */
    private const MODAL_SUFFIXES = ['.create', '.edit'];

    public function handle(Request $request, Closure $next): Response
    {
        // Only redirect browser‑initiated GET requests (not AJAX, not JSON API)
        if (
            !$request->isMethod('GET')
            || $request->ajax()
            || $request->wantsJson()
            || $request->expectsJson()
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || $request->has('_modal_partial')  // escape‑hatch for AJAX fetches
        ) {
            return $next($request);
        }

        $routeName = Route::currentRouteName();
        if (!$routeName) {
            return $next($request);
        }

        // Determine if route is a *.create or *.edit route
        $suffix = null;
        foreach (self::MODAL_SUFFIXES as $s) {
            if (str_ends_with($routeName, $s)) {
                $suffix = $s;
                break;
            }
        }
        if ($suffix === null) {
            return $next($request);
        }

        // Extract the resource prefix (e.g. "branches" from "branches.create")
        $resource = substr($routeName, 0, -strlen($suffix));

        // Skip full‑page resources
        if (in_array($resource, self::FULL_PAGE_RESOURCES, true)) {
            return $next($request);
        }

        // Build the index route name: resource.index
        $indexRouteName = $resource . '.index';
        if (!Route::has($indexRouteName)) {
            // No index route → fall through (LandingPage sub‑resources, etc.)
            return $next($request);
        }

        // Action: "create" or "edit"
        $action = ltrim($suffix, '.');

        $params = ['modal' => $action];

        // For edit routes, carry the model ID so JS can build the correct URL
        if ($action === 'edit') {
            $routeParams = $request->route()?->parameters() ?? [];
            // Typically the first parameter is the model ID
            $firstParam = reset($routeParams);
            if ($firstParam !== false) {
                $params['modal_id'] = is_object($firstParam) && method_exists($firstParam, 'getKey')
                    ? $firstParam->getKey()
                    : $firstParam;
            }
        }

        Log::debug('[RedirectModalRoutes] Redirecting direct hit', [
            'from'  => $routeName,
            'to'    => $indexRouteName,
            'params' => $params,
        ]);

        return redirect()->route($indexRouteName, $params);
    }
}

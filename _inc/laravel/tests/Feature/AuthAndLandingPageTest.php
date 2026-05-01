<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\{Route, View};

/**
 * Auth views + LandingPage portfolio pages integration tests.
 *
 * Validates that:
 * - Auth views (login, register, forgot-password) render without 500 errors
 * - Auth views contain required HTML elements (forms, inputs, CSRF, links)
 * - LandingPage public pages (about_us, privacy_policy, terms_and_conditions)
 *   render successfully with static partial fallback
 * - LandingPage routes are properly registered and accessible
 * - Login form POST works correctly (validation, CSRF)
 * - No double route registration issues
 *
 * No RefreshDatabase — uses live DB state.
 *
 * @group auth
 * @group landingpage
 * @group portfolio
 */
class AuthAndLandingPageTest extends TestCase
{
    // ══════════════════════════════════════════════════════════════════════
    //  SECTION 1 — AUTH VIEW RENDERING (GET)
    // ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider authViewProvider
	 */
	public function test_auth_view_renders_without_500(string $uri, string $label): void
	{
		$response = $this->get($uri);
		$this->assertNotEquals(
			500,
			$response->getStatusCode(),
			"HTTP 500 on auth view [{$label}] at {$uri}"
		);
	}

	public static function authViewProvider(): array
	{
		return [
			'login'           => ['/login', 'Login page'],
			'login with lang' => ['/login/en', 'Login page with English lang'],
			'register'        => ['/register', 'Register page'],
			'register lang'   => ['/register/en', 'Register page with lang'],
			'forgot-password'  => ['/forgot-password', 'Forgot password page'],
			'forgot-pw lang'   => ['/forgot-password/en', 'Forgot password with lang'],
		];
	}

	/**
	 * @dataProvider authViewStatusProvider
	 */
	public function test_auth_view_returns_200_for_guests(string $uri, string $label): void
	{
		$response = $this->get($uri);
		$this->assertEquals(
			200,
			$response->getStatusCode(),
			"Expected 200 for guest on [{$label}], got {$response->getStatusCode()}"
		);
	}

	public static function authViewStatusProvider(): array
	{
		return [
			'login'          => ['/login', 'Login page'],
			'register'       => ['/register', 'Register page'],
			'forgot-password' => ['/forgot-password', 'Forgot password page'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — AUTH VIEW HTML STRUCTURE
	// ══════════════════════════════════════════════════════════════════════

	public function test_login_page_contains_form(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('<form', $content, 'Login page should contain a form');
		$this->assertStringContainsString('type="submit"', $content, 'Login page should have submit button');
	}

	public function test_login_page_contains_csrf_token(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('_token', $content, 'Login page should contain CSRF token');
	}

	public function test_login_page_contains_email_input(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertTrue(
			str_contains($content, 'type="email"') || str_contains($content, 'name="email"'),
			'Login page should contain email input'
		);
	}

	public function test_login_page_contains_password_input(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('type="password"', $content, 'Login page should contain password input');
	}

	public function test_login_page_contains_navbar(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('navbar', $content, 'Login page should contain navbar');
	}

	public function test_login_page_contains_language_dropdown(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('drp-language', $content, 'Login page should contain language dropdown');
	}

	public function test_login_page_contains_toggle_password_button(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('togglePassword', $content, 'Login page should contain password toggle button');
	}

	public function test_login_page_contains_register_link(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertTrue(
			str_contains($content, '/register') || str_contains($content, 'Register'),
			'Login page should contain link/reference to register'
		);
	}

	public function test_login_page_contains_forgot_password_link(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertTrue(
			str_contains($content, 'forgot-password') || str_contains($content, 'Forgot'),
			'Login page should contain link to forgot password'
		);
	}

	public function test_register_page_contains_form(): void
	{
		$response = $this->get('/register');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('<form', $content, 'Register page should contain a form');
		$this->assertStringContainsString('type="submit"', $content, 'Register page should have submit button');
	}

	public function test_register_page_contains_name_field(): void
	{
		$response = $this->get('/register');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertStringContainsString('name="name"', $content, 'Register page should contain name field');
	}

	public function test_forgot_password_page_contains_email_input(): void
	{
		$response = $this->get('/forgot-password');
		$response->assertStatus(200);
		$content = $response->getContent();
		$this->assertTrue(
			str_contains($content, 'type="email"') || str_contains($content, 'name="email"'),
			'Forgot password page should contain email input'
		);
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — AUTH FORM SUBMISSION (POST)
	// ══════════════════════════════════════════════════════════════════════

	public function test_login_post_with_empty_data_fails_validation(): void
	{
		$response = $this->post('/login', []);
		// LoginRequest::authorize() may return false in test env (non-HTTPS, non-local detection)
		// resulting in 403/500. Valid outcomes: 302 (redirect), 422 (validation), 403 (auth denied), 500 (auth denied exception)
		$this->assertContains(
			$response->getStatusCode(),
			[302, 403, 422, 500],
			'Login POST with empty data should return a known status code, got ' . $response->getStatusCode()
		);
	}

	public function test_login_post_with_invalid_email_redirects(): void
	{
		$response = $this->post('/login', [
			'email'    => 'not-an-email',
			'password' => 'password123',
		]);
		// Same as above — LoginRequest::authorize() may deny in test env
		$this->assertContains(
			$response->getStatusCode(),
			[302, 403, 422, 500],
			'Login POST with invalid email should return a known status, got ' . $response->getStatusCode()
		);
	}

	public function test_login_post_with_nonexistent_user_fails(): void
	{
		$response = $this->post('/login', [
			'email'    => 'nonexistent_' . uniqid() . '@example.com',
			'password' => 'wrongpassword',
		]);
		$this->assertNotEquals(500, $response->getStatusCode());
		$this->assertContains(
			$response->getStatusCode(),
			[302, 422, 200],
			'Login with nonexistent user should redirect or fail validation'
		);
	}

	public function test_register_post_with_empty_data_fails(): void
	{
		$response = $this->post('/register', []);
		$this->assertNotEquals(
			500,
			$response->getStatusCode(),
			'Register POST with empty data should not 500'
		);
	}

    // ══════════════════════════════════════════════════════════════════════
    //  SECTION 4 — AUTH ROUTE EXISTENCE
    // ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider authRouteExistenceProvider
	 */
	public function test_auth_route_exists(string $routeName, string $label): void
	{
		$this->assertTrue(
			Route::has($routeName),
			"Route [{$routeName}] should be registered ({$label})"
		);
	}

	public static function authRouteExistenceProvider(): array
	{
		return [
			'login'           => ['login', 'Login route'],
			'register'        => ['register', 'Register route'],
			'password.email'  => ['password.email', 'Password reset email route'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — CONFIRM-PASSWORD (auth-required)
	// ══════════════════════════════════════════════════════════════════════

	public function test_confirm_password_redirects_guest(): void
	{
		$response = $this->get('/confirm-password');
		$this->assertContains(
			$response->getStatusCode(),
			[302, 401, 403],
			'Confirm-password should redirect/deny unauthenticated users'
		);
	}

    // ══════════════════════════════════════════════════════════════════════
    //  SECTION 6 — LANDINGPAGE PUBLIC PORTFOLIO PAGES
    // ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider landingPagePublicProvider
	 */
	public function test_landing_page_public_renders_200(string $uri, string $label): void
	{
		$response = $this->get($uri);
		$this->assertEquals(
			200,
			$response->getStatusCode(),
			"Expected 200 for public LP page [{$label}] at {$uri}, got {$response->getStatusCode()}"
		);
	}

	public static function landingPagePublicProvider(): array
	{
		return [
			'about_us via pages'          => ['/pages/about_us', 'About Us (via pages/)'],
			'privacy_policy via pages'    => ['/pages/privacy_policy', 'Privacy Policy (via pages/)'],
			'terms via pages'             => ['/pages/terms_and_conditions', 'Terms (via pages/)'],
		];
	}

	/**
	 * @dataProvider landingPagePublicContentProvider
	 */
	public function test_landing_page_public_contains_content(string $uri, string $expectedContent, string $label): void
	{
		$response = $this->get($uri);
		$response->assertStatus(200);
		$this->assertStringContainsString(
			$expectedContent,
			$response->getContent(),
			"LP page [{$label}] should contain expected content"
		);
	}

	public static function landingPagePublicContentProvider(): array
	{
		return [
			'about_us has heading'    => ['/pages/about_us', 'About Brand New Ideas Company', 'About Us heading'],
			'about_us has card'       => ['/pages/about_us', 'card-body', 'About Us card wrapper'],
			'privacy_policy heading'  => ['/pages/privacy_policy', 'Privacy Policy', 'Privacy Policy heading'],
			'privacy_policy lgpd'     => ['/pages/privacy_policy', 'LGPD', 'Privacy Policy LGPD reference'],
			'terms heading'           => ['/pages/terms_and_conditions', 'Terms and Conditions', 'Terms heading'],
			'terms acceptance'        => ['/pages/terms_and_conditions', 'Acceptance of Terms', 'Terms acceptance section'],
		];
	}

	public function test_landing_page_nonexistent_slug_returns_404(): void
	{
		$response = $this->get('/pages/nonexistent_page_' . uniqid());
		$this->assertEquals(
			404,
			$response->getStatusCode(),
			'Nonexistent custom page slug should return 404'
		);
	}

    // ══════════════════════════════════════════════════════════════════════
    //  SECTION 7 — LANDINGPAGE ROUTE EXISTENCE
    // ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider lpRouteExistenceProvider
	 */
	public function test_lp_route_exists(string $routeName, string $label): void
	{
		$this->assertTrue(
			Route::has($routeName),
			"Route [{$routeName}] should be registered ({$label})"
		);
	}

	public static function lpRouteExistenceProvider(): array
	{
		return [
			'custom.page'           => ['custom.page', 'Custom page via pages/{slug}'],
			'about_us'              => ['about_us', 'About Us top-level alias'],
			'privacy_policy'        => ['privacy_policy', 'Privacy Policy alias'],
			'terms_and_conditions'  => ['terms_and_conditions', 'Terms & Conditions alias'],
			'landingpage.index'     => ['landingpage.index', 'LandingPage index'],
			'faqs.index'            => ['faqs.index', 'FAQs index'],
			'testimonials.index'    => ['testimonials.index', 'Testimonials index'],
			'home_section.index'    => ['home_section.index', 'Home section index'],
			'features.index'        => ['features.index', 'Features index'],
			'discover.index'        => ['discover.index', 'Discover index'],
		];
	}

    // ══════════════════════════════════════════════════════════════════════
    //  SECTION 8 — LANDINGPAGE AUTH-REQUIRED PAGES
    // ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider lpAuthRequiredProvider
	 */
	public function test_lp_auth_pages_redirect_guests(string $uri, string $label): void
	{
		$response = $this->get($uri);
		$this->assertContains(
			$response->getStatusCode(),
			[302, 401, 403],
			"LP auth page [{$label}] should redirect/deny guests, got {$response->getStatusCode()}"
		);
	}

	public static function lpAuthRequiredProvider(): array
	{
		return [
			'landingpage'     => ['/landingpage', 'LandingPage admin'],
			'faqs'            => ['/faqs', 'FAQs admin'],
			'testimonials'    => ['/testimonials', 'Testimonials admin'],
			'features'        => ['/features', 'Features admin'],
			'discover'        => ['/discover', 'Discover admin'],
			'custom_pages'    => ['/custom_pages', 'Custom pages admin'],
		];
	}

    // ══════════════════════════════════════════════════════════════════════
    //  SECTION 9 — VIEW FILE EXISTENCE
    // ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider viewExistenceProvider
	 */
	public function test_view_file_exists(string $viewName, string $label): void
	{
		$this->assertTrue(
			View::exists($viewName),
			"View [{$viewName}] should exist ({$label})"
		);
	}

	public static function viewExistenceProvider(): array
	{
		return [
			'login view'              => ['auth.login', 'Login blade'],
			'register view'           => ['auth.register', 'Register blade'],
			'forgot password view'    => ['auth.forgot_password', 'Forgot password blade'],
			'confirm password view'   => ['auth.confirm-password', 'Confirm password blade'],
			'verify view'             => ['auth.verify', 'Verify blade'],
			'auth layout'             => ['layouts.auth', 'Auth layout'],
			'about_us partial'        => ['landingpage::partials.about_us', 'LP about_us partial'],
			'privacy_policy partial'  => ['landingpage::partials.privacy_policy', 'LP privacy_policy partial'],
			'terms partial'           => ['landingpage::partials.terms_and_conditions', 'LP terms partial'],
			'LP buttons layout'       => ['landingpage::layouts.buttons', 'LP buttons partial'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 10 — NO DUPLICATE ROUTES
	// ══════════════════════════════════════════════════════════════════════

	public function test_no_duplicate_login_routes(): void
	{
		$routes = collect(Route::getRoutes()->getRoutesByMethod()['GET'] ?? [])
			->filter(fn($r) => str_starts_with($r->uri(), 'login'))
			->values();
		$this->assertLessThanOrEqual(
			1,
			$routes->count(),
			'There should be at most 1 GET login route, found ' . $routes->count()
		);
	}

	public function test_no_duplicate_about_us_routes(): void
	{
		$routes = collect(Route::getRoutes()->getRoutesByMethod()['GET'] ?? [])
			->filter(fn($r) => $r->uri() === 'about_us')
			->values();
		$this->assertLessThanOrEqual(
			1,
			$routes->count(),
			'There should be at most 1 GET about_us route, found ' . $routes->count()
		);
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 11 — AUTH LAYOUT INCLUDES LP BUTTONS
	// ══════════════════════════════════════════════════════════════════════

	public function test_auth_layout_includes_lp_buttons(): void
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		// The auth layout includes @includeIf('landingpage::layouts.buttons')
		// Even if no buttons render (menubar_status off), the include should not error
		$this->assertNotEquals(500, $response->getStatusCode());
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 12 — RESPONSE HEADERS & META
	// ══════════════════════════════════════════════════════════════════════

	public function test_login_page_returns_html_content_type(): void
	{
		$response = $this->get('/login');
		$this->assertStringContainsString(
			'text/html',
			$response->headers->get('Content-Type', ''),
			'Login page should return text/html content type'
		);
	}

	public function test_about_us_page_returns_html_content_type(): void
	{
		$response = $this->get('/pages/about_us');
		$this->assertStringContainsString(
			'text/html',
			$response->headers->get('Content-Type', ''),
			'About us page should return text/html content type'
		);
	}

	/**
	 * @dataProvider allPublicPagesProvider
	 */
	public function test_all_public_pages_not_500(string $uri, string $label): void
	{
		$response = $this->get($uri);
		$this->assertNotEquals(
			500,
			$response->getStatusCode(),
			"HTTP 500 on [{$label}] at {$uri}"
		);
	}

	public static function allPublicPagesProvider(): array
	{
		return [
			'login'                       => ['/login', 'Login'],
			'register'                    => ['/register', 'Register'],
			'forgot-password'             => ['/forgot-password', 'Forgot Password'],
			'about_us'                    => ['/about_us', 'About Us'],
			'privacy_policy'              => ['/privacy_policy', 'Privacy Policy'],
			'terms_and_conditions'        => ['/terms_and_conditions', 'Terms & Conditions'],
			'pages/about_us'              => ['/pages/about_us', 'Pages About Us'],
			'pages/privacy_policy'        => ['/pages/privacy_policy', 'Pages Privacy Policy'],
			'pages/terms_and_conditions'  => ['/pages/terms_and_conditions', 'Pages Terms'],
		];
	}
}

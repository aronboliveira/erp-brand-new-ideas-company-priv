<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\StripHtmlComments;
use Illuminate\Http\{Request, Response};
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(StripHtmlComments::class)]
#[Group('middleware')]
#[Group('strip-html-comments')]
class StripHtmlCommentsTest extends TestCase
{
	private function makeResponse(string $content, string $contentType = 'text/html'): Response
	{
		$response = new Response($content, 200);
		$response->headers->set('Content-Type', $contentType);
		return $response;
	}

	private function runMiddleware(Response $expected): Response
	{
		$middleware = new StripHtmlComments();
		$request = Request::create('/test', 'GET');
		return $middleware->handle($request, fn() => $expected);
	}

	// ───────── Strips comments from HTML ─────────

	#[Test]
	public function strips_single_line_html_comment(): void
	{
		$html = '<div><!-- This is a comment --><p>Content</p></div>';
		$response = $this->runMiddleware($this->makeResponse($html));
		$this->assertStringNotContainsString('<!--', $response->getContent());
		$this->assertStringContainsString('<p>Content</p>', $response->getContent());
	}

	#[Test]
	public function strips_multiline_html_comment(): void
	{
		$html = "<div>\n<!-- Multi\nline\ncomment -->\n<p>After</p></div>";
		$response = $this->runMiddleware($this->makeResponse($html));
		$this->assertStringNotContainsString('<!--', $response->getContent());
		$this->assertStringContainsString('<p>After</p>', $response->getContent());
	}

	#[Test]
	public function strips_multiple_comments(): void
	{
		$html = '<!-- First --><p>A</p><!-- Second --><p>B</p><!-- Third -->';
		$response = $this->runMiddleware($this->makeResponse($html));
		$content = $response->getContent();
		$this->assertSame(0, preg_match_all('/<!--/', $content));
		$this->assertStringContainsString('<p>A</p>', $content);
		$this->assertStringContainsString('<p>B</p>', $content);
	}

	// ───────── Skips non-HTML content types ─────────

	#[Test]
	public function skips_json_response(): void
	{
		$json = '{"key": "<!-- not stripped -->"}';
		$response = $this->runMiddleware($this->makeResponse($json, 'application/json'));
		$this->assertStringContainsString('<!-- not stripped -->', $response->getContent());
	}

	#[Test]
	public function skips_plain_text_response(): void
	{
		$text = 'Some text <!-- comment -->';
		$response = $this->runMiddleware($this->makeResponse($text, 'text/plain'));
		$this->assertStringContainsString('<!-- comment -->', $response->getContent());
	}

	// ───────── Handles edge cases ─────────

	#[Test]
	public function handles_empty_content(): void
	{
		$response = $this->runMiddleware($this->makeResponse(''));
		$this->assertSame('', $response->getContent());
	}

	#[Test]
	public function handles_no_comments(): void
	{
		$html = '<p>No comments here</p>';
		$response = $this->runMiddleware($this->makeResponse($html));
		$this->assertSame($html, $response->getContent());
	}

	#[Test]
	public function preserves_conditional_ie_comments_stripped(): void
	{
		// The middleware strips ALL <!-- --> including IE conditionals
		$html = '<!--[if IE]><p>IE</p><![endif]--><p>Normal</p>';
		$response = $this->runMiddleware($this->makeResponse($html));
		// IE conditionals are in <!-- --> syntax, so they get stripped
		$this->assertStringContainsString('<p>Normal</p>', $response->getContent());
	}

	// ───────── Performance ─────────

	#[Test]
	public function stripping_performance(): void
	{
		$comments = str_repeat('<!-- comment -->', 100);
		$html = "<html><body>{$comments}<p>End</p></body></html>";
		$middleware = new StripHtmlComments();
		$request = Request::create('/test', 'GET');

		$start = hrtime(true);
		for ($i = 0; $i < 100; $i++) {
			$resp = $this->makeResponse($html);
			$middleware->handle($request, fn() => $resp);
		}
		$perCall = (hrtime(true) - $start) / 1e6 / 100;
		$this->assertLessThan(50.0, $perCall, "Stripping averaged {$perCall}ms per call");
	}
}

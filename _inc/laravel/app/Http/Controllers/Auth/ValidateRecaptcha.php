<?php

namespace App\Http\Controllers\Auth;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ValidateRecaptcha implements ValidationRule
{
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		$response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
			'secret' => config('services.recaptcha.secret'),
			'response' => $value,
			'remoteip' => request()->ip()
		]);

		if (!$response->json('success')) {
			$fail('The :attribute failed reCAPTCHA verification.');
		}
	}
}

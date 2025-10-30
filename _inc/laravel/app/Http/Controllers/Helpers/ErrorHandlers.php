<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{Auth, Log, Redirect, Route};
use Symfony\Component\HttpFoundation\Response;

function defaultPermissionDenial(
	Request $request,
	mixed $err,
	string $ref = '#UNDEFINED_REFERENCE',
	string $redirectPath = '/',
	bool $autoRedirect = true,
	?array $json = null,
	bool $autoBack = true,
): RedirectResponse|JsonResponse {
	Log::error("$ref denied permission", ['error' => $err]);
	session()->flash('error', 'You do not have permission to perform this action.');
	$redirectPath = $redirectPath !== '/' && str_starts_with($redirectPath, '/') && Utility::isValidRouteUrl($redirectPath) ? $redirectPath : '/';
	if (!Auth::check())
		return redirect('/login')->with('error', 'You must be logged in to access this page.');
	if (!$request->wantsJson()) {
		if ($autoBack) return redirect()->back()->with('error', 'You do not have permission to perform this action.');
		if ($autoRedirect || !$request->wantsJson()) {
			$redirectPath = $redirectPath !== '/' && str_starts_with($redirectPath, '/') && Utility::isValidRouteUrl($redirectPath) ? $redirectPath : '/';
			return !str_starts_with($redirectPath, '/') && Route::has($redirectPath)
				? redirect()->route($redirectPath) : redirect('/');
		}
	}
	return $autoRedirect
		? Redirect::to(getRedirectUrl($request, $redirectPath))
		: response()->json(['error' => 'Unauthorized', ...(is_array($json) ? $json : [])], 401);
}

function defaultUndefinedException(
	Request $request,
	mixed $err,
	string $ref = '# UNDEFINED_REFERENCE',
	string $redirectPath = '/',
	bool $autoRedirect = true,
	?array $json = null,
	int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
	bool $autoBack = true,
): RedirectResponse|JsonResponse {
	$errMsg = 'Internal Server error (Http 5xx). Please notify the development team and/or your administrator.';
	Log::debug("$ref raised undefined error", ['error' => $err]);
	session()->flash('error', 'An unexpected error occurred.');
	$redirectPath = $redirectPath !== '/' && str_starts_with($redirectPath, '/') && Utility::isValidRouteUrl($redirectPath) ? $redirectPath : '/';
	if (!Auth::check())
		return redirect('/login')->with('error', $errMsg . ' Code: ' . $status);
	if (!$request->wantsJson()) {
		if ($autoBack) return redirect()->back()->with('error', $errMsg . ' Code: ' . $status);
		if ($autoRedirect || !$request->wantsJson()) {
			$redirectPath = $redirectPath !== '/' && str_starts_with($redirectPath, '/') && Utility::isValidRouteUrl($redirectPath) ? $redirectPath : '/';
			return !str_starts_with($redirectPath, '/') && Route::has($redirectPath)
				? redirect()->route($redirectPath) : redirect('/');
		}
	}
	return $autoRedirect
		? Redirect::to(getRedirectUrl($request, $redirectPath))
		: response()->json(['error' => $errMsg, ...(is_array($json) ? $json : [])], $status);
}

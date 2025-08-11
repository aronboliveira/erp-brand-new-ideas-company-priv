<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

function getRedirectUrl(Request $request, string $fallback): string
{
    if ($request->has('next'))
        return $request->input('next');
    if ($request->headers->has('referer'))
        return $request->headers->get('referer');
    return $fallback ? $fallback : URL::previous();
}

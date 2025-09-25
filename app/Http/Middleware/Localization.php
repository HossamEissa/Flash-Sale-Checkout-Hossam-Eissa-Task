<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Supported locales for the application.
     */
    protected $supportedLocales = ['en', 'en_GB', 'ar'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('lang') && $this->isSupportedLocale(Session::get('lang'))) {
            App::setLocale(Session::get('lang'));
        }

        elseif ($request->hasHeader('Accept-Language')) {
            $locale = $this->parseLocale($request->header('Accept-Language'));
            if ($locale && $this->isSupportedLocale($locale)) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }

    /**
     * Validate if the locale is supported.
     */
    protected function isSupportedLocale($locale): bool
    {
        return in_array($locale, $this->supportedLocales);
    }

    /**
     * Parse and extract the primary locale from the Accept-Language header.
     */
    protected function parseLocale(string $acceptLanguage): ?string
    {
        $locales = explode(',', $acceptLanguage);
        return $locales[0] ?? null;
    }
}

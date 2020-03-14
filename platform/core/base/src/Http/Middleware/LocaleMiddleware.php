<?php

namespace Botble\Base\Http\Middleware;

use Assets;
use Closure;
use Exception;
use Illuminate\Http\Request;

class LocaleMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        app()->setLocale(env('APP_LOCALE', config('app.locale')));

        if ($request->session()->has('site-locale') &&
            array_key_exists($request->session()->get('site-locale'), Assets::getAdminLocales())
        ) {
            app()->setLocale($request->session()->get('site-locale'));
            $request->setLocale($request->session()->get('site-locale'));
        }

        return $next($request);
    }
}

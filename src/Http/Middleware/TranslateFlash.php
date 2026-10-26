<?php

namespace ME\SchoolManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Success / error messages are written in English in the controllers; the
 * theme shows them as toasts. Translate them into the current language just
 * before the page that shows them is rendered.
 */
class TranslateFlash
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->getLocale() !== 'en' && $request->hasSession()) {
            foreach (['success', 'error'] as $key) {
                $message = $request->session()->get($key);
                if (is_string($message) && ($translated = sm_t($message)) !== $message) {
                    $request->session()->put($key, $translated); // still flashed: removed after this request
                }
            }
        }
        return $next($request);
    }
}

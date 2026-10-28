<?php

namespace ME\SchoolManagement\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The theme's registration answers "send OTP" with the code itself
 * (`otp_debug`), which lets anyone register with someone else's phone or
 * e-mail. Strip it everywhere except a local development install.
 */
class HideOtpDebug
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && !app()->isLocal()) {
            $data = $response->getData(true);
            if (is_array($data) && array_key_exists('otp_debug', $data)) {
                unset($data['otp_debug']);
                $response->setData($data);
            }
        }

        return $response;
    }
}

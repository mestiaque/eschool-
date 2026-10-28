<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\Route;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 6.1 — the theme's registration OTP must not reach the browser. */
class HideOtpDebugTest extends TestCase
{
    public function test_otp_debug_is_removed_outside_local(): void
    {
        Route::middleware('web')->post('/__otp-test', fn () => response()->json(['success' => true, 'otp_debug' => 123456]));

        $this->post('/__otp-test')->assertOk()->assertJson(['success' => true])->assertJsonMissingPath('otp_debug');

        $this->app['env'] = 'local';
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->post('/__otp-test')->assertJsonPath('otp_debug', 123456);
    }
}

<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Tests\TestCase;

class PortalAndSettingsTest extends TestCase
{
    public function test_portal_pin_is_hashed_and_login_works(): void
    {
        $student = Student::where('is_active', true)->first();
        $student->update(['portal_pin' => '482913']);
        $this->assertTrue(Hash::check('482913', $student->fresh()->getRawOriginal('portal_pin')));
        $this->assertArrayNotHasKey('portal_pin', $student->fresh()->toArray());

        $this->post('/student-portal/login', ['student_id' => $student->student_id, 'portal_pin' => '482913'])
            ->assertRedirect(route('portal.dashboard'));
    }

    public function test_portal_login_locks_after_five_wrong_pins(): void
    {
        $student = Student::where('is_active', true)->first();
        $student->update(['portal_pin' => '482913']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/student-portal/login', ['student_id' => $student->student_id, 'portal_pin' => '000000']);
        }
        $this->post('/student-portal/login', ['student_id' => $student->student_id, 'portal_pin' => '482913'])
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Too many attempts'));
    }

    public function test_currency_and_date_settings_apply(): void
    {
        $this->actingAs($this->admin());
        $this->post('/settings/general', ['currency' => 'USD', 'timezone' => 'Asia/Dhaka', 'date_format' => 'd/m/Y'])->assertSessionHas('success');
        $this->assertSame('$1,250', sm_money(1250));
        $this->assertSame('17/10/2026', sm_date('2026-10-17'));
        SchoolSetting::put('currency', 'BDT');
        $this->assertSame('৳1,250', sm_money(1250));
    }

    public function test_bangla_translation_is_used(): void
    {
        app()->setLocale('bn');
        $this->assertSame('ছাত্র-ছাত্রী', sm_t('Students'));
        app()->setLocale('en');
        $this->assertSame('Students', sm_t('Students'));
    }

    public function test_absence_alert_is_sent_when_enabled(): void
    {
        Http::fake(['*' => Http::response(['response_code' => 202], 200)]);
        config(['services.sms_api_url' => 'https://sms.test/send', 'services.sms_api_key' => 'k', 'services.sms_sender_id' => 's']);
        SchoolSetting::put('notify_absence', '1');
        $this->actingAs($this->admin());

        $student = Student::whereHas('guardian', fn ($q) => $q->whereNotNull('guardian_phone'))->first();
        $before = \DB::table('sms_logs')->count();
        $this->postJson('/students/attendance/cell', ['student_id' => $student->id, 'date' => '2020-01-05', 'status' => 'absent'])->assertOk();
        $this->assertGreaterThan($before, \DB::table('sms_logs')->count(), 'an SMS attempt was logged');
    }
}

<?php

namespace ME\SchoolManagement\Services;

use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\Student;

/**
 * Sends school messages through the theme's SMS gateway and mailer
 * (me_sms / me_mail, configured on the theme's SMS & Mail Configuration pages).
 * Which automatic messages go out is chosen on Settings → Notifications.
 */
class SchoolNotifier
{
    /** Automatic notifications that can be switched on/off. */
    public const EVENTS = [
        'notify_payment_receipt' => 'Payment receipt to guardian when a fee payment is taken',
        'notify_absence'         => 'Absence alert to guardian when a student is marked absent',
        'notify_new_pin'         => 'Portal PIN to guardian when a student is admitted or the PIN is reset',
    ];

    public static function enabled(string $event): bool
    {
        return (bool) SchoolSetting::get($event, '0');
    }

    public static function schoolName(): string
    {
        return SchoolSetting::get('school_name', config('app.name', 'School'));
    }

    /** Guardian phone, falling back to father, mother, then the student's own number. */
    public static function guardianPhone(Student $student): ?string
    {
        $g = $student->guardian;
        foreach ([$g?->guardian_phone, $g?->father_phone, $g?->mother_phone, $student->phone] as $phone) {
            if ($phone && preg_match('/\d{6,}/', preg_replace('/\D/', '', $phone))) return $phone;
        }
        return null;
    }

    public static function guardianEmail(Student $student): ?string
    {
        $email = $student->guardian?->guardian_email ?: $student->email;
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public static function sms(?string $to, string $message): bool
    {
        return $to && function_exists('me_sms') && me_sms($to, $message);
    }

    public static function mail(?string $to, string $subject, string $html): bool
    {
        return $to && function_exists('me_mail') && me_mail($to, $subject, $html);
    }

    /**
     * Message a student's guardian by SMS and (when an address exists) e-mail.
     * @return array{sms: bool, mail: bool}
     */
    public static function guardian(Student $student, string $text, ?string $subject = null): array
    {
        $school = self::schoolName();
        return [
            'sms'  => self::sms(self::guardianPhone($student), "{$text}\n- {$school}"),
            'mail' => $subject ? self::mail(self::guardianEmail($student), "{$subject} - {$school}", '<p>' . nl2br(e($text)) . '</p>') : false,
        ];
    }

    /** Run a batch of sends after the response is sent, so the page doesn't wait on the gateway. */
    public static function later(callable $work): void
    {
        dispatch(function () use ($work) { $work(); })->afterResponse();
    }

    // ── Message builders ─────────────────────────────────────────────

    public static function paymentReceipt(\ME\SchoolManagement\Models\FeeCollection $invoice, float $amount): void
    {
        if (!self::enabled('notify_payment_receipt') || !$invoice->student) return;
        $due = (float) $invoice->due_amount;
        self::later(fn () => self::guardian($invoice->student,
            "Payment received: " . sm_money($amount, 2) . " for {$invoice->student->full_name} ("
            . ($invoice->feeType->name ?? 'fee') . ", {$invoice->invoice_no})."
            . ($due > 0 ? ' Remaining due: ' . sm_money($due, 2) . '.' : ' Fully paid. Thank you.'),
            'Payment receipt'));
    }

    public static function absence(Student $student, string $date): void
    {
        if (!self::enabled('notify_absence')) return;
        self::later(fn () => self::guardian($student,
            "{$student->full_name} was absent from school on " . sm_date($date) . '.',
            'Absence notice'));
    }
}

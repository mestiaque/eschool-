<?php

use ME\SchoolManagement\Models\SchoolSetting;

if (!function_exists('sm_setting')) {
    /** A school setting (Settings → School / General / Session), cached. */
    function sm_setting(string $key, $default = null)
    {
        return SchoolSetting::cached()[$key] ?? $default;
    }
}

if (!function_exists('sm_currency')) {
    /** Currency symbol chosen in Settings → General. */
    function sm_currency(): string
    {
        $value = (string) sm_setting('currency', 'BDT');
        return ['BDT' => '৳', 'USD' => '$', 'INR' => '₹'][$value] ?? ($value !== '' ? $value : '৳');
    }
}

if (!function_exists('sm_money')) {
    /** Amount with the school's currency symbol, e.g. ৳1,250. */
    function sm_money($amount, int $decimals = 0): string
    {
        $amount = (float) $amount;
        return ($amount < 0 ? '-' : '') . sm_currency() . number_format(abs($amount), $decimals);
    }
}

if (!function_exists('sm_date')) {
    /** Date in the format chosen in Settings → General ("—" when empty). */
    function sm_date($date, bool $withTime = false): string
    {
        if (empty($date)) return '—';
        try {
            $date = $date instanceof \DateTimeInterface ? \Carbon\Carbon::instance($date) : \Carbon\Carbon::parse($date);
        } catch (\Throwable $e) {
            return (string) $date;
        }
        $format = sm_setting('date_format') ?: 'd M Y';
        return $date->format($format . ($withTime ? ', h:i A' : ''));
    }
}

if (!function_exists('sm_t')) {
    /** Translate UI text (resources/lang/<locale>/school-management.php); falls back to the English text. */
    function sm_t(string $text): string
    {
        $key = "school-management::school-management.{$text}";
        return trans()->has($key) ? trans($key) : $text;
    }
}

if (!function_exists('sm_time')) {
    /** Time of day as "08:00 AM" ("—" when empty), the format used across the school pages. */
    function sm_time($time): string
    {
        if (empty($time)) return '—';
        try {
            return \Carbon\Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            return (string) $time;
        }
    }
}

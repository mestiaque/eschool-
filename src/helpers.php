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

if (!function_exists('sm_amount_words')) {
    /**
     * Amount in words for receipts, in the Bangladeshi system (lakh, crore):
     * "বারো হাজার পাঁচশত টাকা মাত্র" in Bangla, "Twelve Thousand Five Hundred Taka Only" otherwise.
     */
    function sm_amount_words($amount, ?string $locale = null): string
    {
        $bn = ($locale ?? app()->getLocale()) === 'bn';
        $amount = round(abs((float) $amount), 2);
        $taka = (int) floor($amount);
        $paisa = (int) round(($amount - $taka) * 100);

        $small = $bn
            ? ['শূন্য', 'এক', 'দুই', 'তিন', 'চার', 'পাঁচ', 'ছয়', 'সাত', 'আট', 'নয়', 'দশ', 'এগারো', 'বারো', 'তেরো', 'চৌদ্দ', 'পনেরো', 'ষোলো', 'সতেরো', 'আঠারো', 'উনিশ',
               'বিশ', 'একুশ', 'বাইশ', 'তেইশ', 'চব্বিশ', 'পঁচিশ', 'ছাব্বিশ', 'সাতাশ', 'আটাশ', 'ঊনত্রিশ', 'ত্রিশ', 'একত্রিশ', 'বত্রিশ', 'তেত্রিশ', 'চৌত্রিশ', 'পঁয়ত্রিশ', 'ছত্রিশ', 'সাঁইত্রিশ', 'আটত্রিশ', 'ঊনচল্লিশ',
               'চল্লিশ', 'একচল্লিশ', 'বিয়াল্লিশ', 'তেতাল্লিশ', 'চুয়াল্লিশ', 'পঁয়তাল্লিশ', 'ছেচল্লিশ', 'সাতচল্লিশ', 'আটচল্লিশ', 'ঊনপঞ্চাশ', 'পঞ্চাশ', 'একান্ন', 'বাহান্ন', 'তিপ্পান্ন', 'চুয়ান্ন', 'পঞ্চান্ন', 'ছাপ্পান্ন', 'সাতান্ন', 'আটান্ন', 'ঊনষাট',
               'ষাট', 'একষট্টি', 'বাষট্টি', 'তেষট্টি', 'চৌষট্টি', 'পঁয়ষট্টি', 'ছেষট্টি', 'সাতষট্টি', 'আটষট্টি', 'ঊনসত্তর', 'সত্তর', 'একাত্তর', 'বাহাত্তর', 'তিয়াত্তর', 'চুয়াত্তর', 'পঁচাত্তর', 'ছিয়াত্তর', 'সাতাত্তর', 'আটাত্তর', 'ঊনআশি',
               'আশি', 'একাশি', 'বিরাশি', 'তিরাশি', 'চুরাশি', 'পঁচাশি', 'ছিয়াশি', 'সাতাশি', 'আটাশি', 'ঊননব্বই', 'নব্বই', 'একানব্বই', 'বিরানব্বই', 'তিরানব্বই', 'চুরানব্বই', 'পঁচানব্বই', 'ছিয়ানব্বই', 'সাতানব্বই', 'আটানব্বই', 'নিরানব্বই']
            : ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $under100 = fn (int $n) => $bn || $n < 20 ? $small[$n] : $tens[intdiv($n, 10)] . ($n % 10 ? ' ' . $small[$n % 10] : '');
        [$hundred, $thousand, $lakh, $crore] = $bn ? ['শত', 'হাজার', 'লক্ষ', 'কোটি'] : [' Hundred', 'Thousand', 'Lakh', 'Crore'];

        $words = function (int $n) use (&$words, $under100, $hundred, $thousand, $lakh, $crore, $small): string {
            if ($n === 0) return '';
            $parts = [];
            if ($n >= 10000000) { $parts[] = $words(intdiv($n, 10000000)) . ' ' . $crore; $n %= 10000000; }
            if ($n >= 100000)   { $parts[] = $under100(intdiv($n, 100000)) . ' ' . $lakh; $n %= 100000; }
            if ($n >= 1000)     { $parts[] = $under100(intdiv($n, 1000)) . ' ' . $thousand; $n %= 1000; }
            if ($n >= 100)      { $parts[] = $small[intdiv($n, 100)] . $hundred; $n %= 100; }
            if ($n > 0)         { $parts[] = $under100($n); }
            return implode(' ', $parts);
        };

        $text = ($taka ? $words($taka) : $small[0]) . ($bn ? ' টাকা' : ' Taka');
        if ($paisa) $text .= ($bn ? ' ' : ' and ') . $under100($paisa) . ($bn ? ' পয়সা' : ' Paisa');
        return $text . ($bn ? ' মাত্র' : ' Only');
    }
}

if (!function_exists('sm_t_message')) {
    /**
     * Translate a message that may carry values, e.g. "Result generated for 12 students.":
     * an exact entry first, then entries with :placeholders ("Result generated for :n students.").
     * Captured values are translated too, so "Cannot approve: <reason>" translates both parts.
     */
    function sm_t_message(string $message, int $depth = 0): string
    {
        if (($exact = sm_t($message)) !== $message || $depth > 2) return $exact;

        static $patterns = [];
        $locale = app()->getLocale();
        if (!isset($patterns[$locale])) {
            $lines = trans('school-management::school-management');
            $patterns[$locale] = [];
            foreach (is_array($lines) ? $lines : [] as $key => $line) {
                if (!preg_match('/:[a-z]+/', $key)) continue;
                preg_match_all('/:([a-z]+)/', $key, $names);
                $regex = '/^' . preg_replace('/\\\\?:[a-z]+/', '(.+?)', preg_quote($key, '/')) . '$/su';
                $patterns[$locale][] = [$regex, $names[1], $line];
            }
        }

        foreach ($patterns[$locale] as [$regex, $names, $line]) {
            if (preg_match($regex, $message, $m)) {
                $vars = [];
                foreach ($names as $i => $name) $vars[':' . $name] = sm_t_message($m[$i + 1], $depth + 1);
                return strtr($line, $vars);
            }
        }
        return $message;
    }
}

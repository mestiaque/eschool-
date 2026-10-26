<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Tests\TestCase;

/** bug.md 5.6 — page text, flash messages and validation in Bangla. */
class TranslationTest extends TestCase
{
    public function test_pages_flash_messages_and_validation_are_translated(): void
    {
        $this->actingAs($this->admin())->withSession(['locale' => 'bn']);

        // Labels that used to stay English ("Class *", empty-state text).
        $this->get('/academic/class-tests')->assertOk()->assertSee('ক্লাস', false);

        // Flash message shown on the next page is translated.
        $this->withSession(['locale' => 'bn', 'success' => 'Holiday added'])->get('/holidays')
            ->assertSee('ছুটি যোগ হয়েছে', false);

        // Validation message in Bangla.
        $this->from('/holidays')->post('/holidays', []);
        $errors = session('errors')->getBag('default')->all();
        $this->assertTrue(collect($errors)->contains(fn ($m) => str_contains($m, 'দিতে হবে')), implode(' | ', $errors));
    }

    public function test_english_stays_english(): void
    {
        $this->actingAs($this->admin())->withSession(['locale' => 'en', 'success' => 'Holiday added'])
            ->get('/holidays')->assertSee('Holiday added');
    }
}

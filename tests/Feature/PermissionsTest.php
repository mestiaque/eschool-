<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Tests\TestCase;

class PermissionsTest extends TestCase
{
    public function test_a_role_without_permissions_cannot_open_admin_pages(): void
    {
        $this->actingAs($this->userWithRole('no-permissions-role'));
        $this->get('/fees/collection')->assertForbidden();
        $this->get('/settings/general')->assertForbidden();
        $this->post('/expenses/categories', ['name' => 'X'])->assertForbidden();
    }

    public function test_accountant_reaches_finance_but_not_settings(): void
    {
        (new \ME\SchoolManagement\Database\Seeders\SchoolRolesSeeder)->run();
        $this->actingAs($this->userWithRole('accountant'));
        $this->get('/fees/collection')->assertOk();
        $this->get('/expenses')->assertOk();
        $this->get('/settings/general')->assertForbidden();
        $this->get('/library/books')->assertForbidden();
    }

    public function test_super_admin_passes_everything(): void
    {
        $this->actingAs($this->admin());
        $this->get('/settings/general')->assertOk();
        $this->get('/trash')->assertOk();
    }
}

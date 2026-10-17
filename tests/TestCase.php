<?php

namespace ME\SchoolManagement\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use ME\Models\User;
use ME\SchoolManagement\Database\Seeders\SchoolManagementDemoSeeder;

/**
 * Every test runs in a transaction on the "school_test" database, which is
 * migrated (and seeded with demo data once) automatically.
 */
abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    private static bool $prepared = false;

    public function createApplication()
    {
        $app = require SM_HOST_APP . '/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$prepared) {
            abort_unless(DB::getDatabaseName() === 'school_test', 500, 'Tests must run on school_test.');
            DB::rollBack(); // migrations/seeding outside the per-test transaction
            Artisan::call('migrate', ['--force' => true]);
            if (DB::table('students')->count() === 0) {
                Artisan::call('db:seed', ['--class' => SchoolManagementDemoSeeder::class, '--force' => true]);
            }
            self::$prepared = true;
            DB::beginTransaction();
        }
    }

    protected function userWithRole(string $slug): User
    {
        $user = User::create(['name' => "Test {$slug}", 'email' => uniqid($slug) . '@test.local', 'password' => bcrypt('secret'), 'is_active' => 1]);
        $roleId = DB::table('roles')->where('slug', $slug)->value('id')
            ?? DB::table('roles')->insertGetId(['name' => $slug, 'slug' => $slug, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $user->id]);
        return $user->fresh();
    }

    protected function admin(): User
    {
        return $this->userWithRole('super_admin');
    }
}

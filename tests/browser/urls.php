<?php
$base = getenv('SM_HOST_APP') ?: dirname(__DIR__, 3) . '/app';
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
if (DB::getDatabaseName() !== 'school_test') { fwrite(STDERR, "wrong db\n"); exit(1); }
// browser test user (super admin)
$u = \ME\Models\User::firstOrCreate(['email' => 'browser@test.local'], ['name' => 'Browser Test', 'password' => bcrypt('browser-pass'), 'is_active' => 1]);
$rid = DB::table('roles')->whereIn('slug', ['super_admin','super-admin'])->value('id') ?? DB::table('roles')->insertGetId(['name'=>'Super Admin','slug'=>'super_admin']);
DB::table('role_user')->updateOrInsert(['role_id' => $rid, 'user_id' => $u->id]);
$first = fn($t) => DB::table($t)->value('id'); // null: nothing to open, skip the page
$out = [];
foreach (app('router')->getRoutes()->getRoutes() as $r) {
    if (!str_starts_with($r->getActionName(), 'ME\\SchoolManagement') || !in_array('GET', $r->methods())) continue;
    $uri = $r->uri();
    if (preg_match('#^(student-portal|my/|api|students-search|sections/|subjects/|optional|routine$|rooms-list|available|book-copies|student/|subjects-list|staff/all|tours/\{id\}/fee|settings/backup)#', $uri)) continue;
    if (str_contains($uri, 'print') || str_contains($uri, 'receipt') || str_contains($uri, 'download') || str_contains($uri, 'badge') || str_contains($uri, 'marksheet/')) continue;
    $map = ['academic/classes'=>'classes','academic/rooms'=>'rooms','academic/tours'=>'school_tours','academic/class-tests'=>'class_tests','academic/assignments'=>'assignments','students'=>'students','teachers'=>'teachers','library/books'=>'books','committee'=>'committees'];
    $missing = false;
    $u2 = preg_replace_callback('/\{(\w+)\??\}/', function ($m) use ($uri, $map, $first, &$missing) {
        foreach ($map as $p => $t) if (str_starts_with($uri, $p)) return $first($t) ?? ($missing = true);
        return 1; }, $uri);
    if (!$missing) $out[] = '/' . ltrim($u2, '/');
}
echo json_encode(array_values(array_unique($out)));

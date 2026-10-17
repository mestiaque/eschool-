<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Tests\TestCase;

/** Every page and every write endpoint must answer without a server error. */
class PagesTest extends TestCase
{
    private function packageRoutes(bool $get)
    {
        return collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($r) => str_starts_with($r->getActionName(), 'ME\\SchoolManagement')
                && in_array('GET', $r->methods()) === $get
                && !str_contains($r->uri(), 'backup'));
    }

    private function fill(string $uri): string
    {
        $ids = [
            'class_id' => 'classes', 'classId' => 'classes', 'student_id' => 'students', 'studentId' => 'students',
            'exam_id' => 'exams', 'bookId' => 'books',
        ];
        return '/' . preg_replace_callback('/\{(\w+)\??\}/', function ($m) use ($uri, $ids) {
            if (isset($ids[$m[1]])) return DB::table($ids[$m[1]])->value('id');
            foreach (['academic/classes' => 'classes', 'academic/rooms' => 'rooms', 'academic/syllabi' => 'syllabi',
                      'academic/tours' => 'school_tours', 'academic/class-tests' => 'class_tests', 'academic/assignments' => 'assignments',
                      'students' => 'students', 'student/' => 'students', 'teachers' => 'teachers', 'library/books' => 'books',
                      'committee' => 'committees', 'tours' => 'school_tours'] as $prefix => $table) {
                if (str_starts_with($uri, $prefix)) return DB::table($table)->value('id') ?? 1;
            }
            return 1;
        }, $uri);
    }

    public function test_every_page_renders_for_a_super_admin(): void
    {
        $this->actingAs($this->admin());
        $errors = [];
        foreach ($this->packageRoutes(true) as $route) {
            if (str_starts_with($route->uri(), 'student-portal') || str_starts_with($route->uri(), 'my/')) continue;
            $res = $this->get($this->fill($route->uri()));
            if ($res->status() >= 500) $errors[] = $route->uri() . ': ' . ($res->exception?->getMessage() ?? $res->status());
        }
        $this->assertSame([], $errors);
    }

    public function test_empty_submissions_never_cause_a_server_error(): void
    {
        $this->actingAs($this->admin());
        $errors = [];
        foreach ($this->packageRoutes(false) as $route) {
            $uri = preg_replace('/\{\w+\??\}/', '999999', $route->uri());
            $res = $this->call($route->methods()[0], '/' . $uri, []);
            if ($res->status() >= 500) $errors[] = $route->methods()[0] . ' ' . $route->uri() . ': ' . ($res->exception?->getMessage() ?? '');
        }
        $this->assertSame([], $errors);
    }

    public function test_student_portal_pages_render(): void
    {
        $student = \ME\SchoolManagement\Models\Student::first();
        foreach (['/student-portal', '/student-portal/fees', '/student-portal/results', '/student-portal/library',
                  '/student-portal/notices', '/student-portal/attendance'] as $url) {
            $this->withSession(['portal_student_id' => $student->id])->get($url)->assertOk();
        }
    }
}

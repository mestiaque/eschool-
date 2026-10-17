<?php

namespace ME\SchoolManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TeacherSidebar
{
    /**
     * Swap the admin sidebar for the teacher-only one when the logged-in
     * user's account is linked to a teacher record.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->teacher_id) {
            config(['sidebar' => require __DIR__ . '/../../Config/teacher-sidebar.php']);
        }

        return $next($request);
    }
}

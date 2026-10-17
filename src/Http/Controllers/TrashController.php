<?php

namespace ME\SchoolManagement\Http\Controllers;

use ME\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ME\SchoolManagement\Models\Staff;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Teacher;

/**
 * Recycle Bin for students, teachers and staff (soft-deleted records).
 * Permanent delete cascades to all their history, so it is super-admin only
 * and needs the record's ID typed in.
 */
class TrashController extends Controller
{
    private const TYPES = [
        'students' => Student::class,
        'teachers' => Teacher::class,
        'staff'    => Staff::class,
    ];

    public function index(Request $request)
    {
        $type  = array_key_exists($request->type, self::TYPES) ? $request->type : 'students';
        $model = self::TYPES[$type];
        $items = $model::onlyTrashed()->orderByDesc('deleted_at')->paginate(25)->withQueryString();
        $counts = collect(self::TYPES)->map(fn ($m) => $m::onlyTrashed()->count());

        return view('school-management::settings.trash', compact('type', 'items', 'counts'));
    }

    public function restore(string $type, $id)
    {
        $record = $this->find($type, $id);
        $record->restore();
        if (function_exists('me_change_log')) {
            me_change_log('Restored from Recycle Bin: ' . $this->label($record))->subject($record)->record(['deleted' => 'yes'], ['deleted' => 'no']);
        }
        return back()->with('success', $this->label($record) . ' restored.');
    }

    public function forceDelete(Request $request, string $type, $id)
    {
        $record = $this->find($type, $id);
        abort_unless(auth()->user()?->isSuperAdmin() || (function_exists('me_is_developer') && me_is_developer()), 403,
            'Only a super admin can permanently delete.');

        $code = $record->student_id ?? $record->teacher_id ?? $record->staff_id ?? (string) $record->id;
        if (trim((string) $request->confirm_code) !== (string) $code) {
            return back()->with('error', "Type {$code} exactly to confirm the permanent delete.");
        }

        $label = $this->label($record);
        $photo = $record->photo ?? null;
        $delete = fn () => $record->forceDelete();
        function_exists('me_change_log')
            ? me_change_log("Permanently deleted: {$label}")->watch($record)->delete($delete)
            : $delete();
        if ($photo) Storage::disk('public')->delete($photo);

        return back()->with('success', "{$label} permanently deleted with all related records.");
    }

    private function find(string $type, $id)
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);
        return self::TYPES[$type]::onlyTrashed()->findOrFail($id);
    }

    private function label($record): string
    {
        $name = trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
        $code = $record->student_id ?? $record->teacher_id ?? $record->staff_id ?? null;
        return $name . ($code ? " ({$code})" : '');
    }
}

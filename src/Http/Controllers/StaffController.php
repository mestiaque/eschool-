<?php

namespace ME\SchoolManagement\Http\Controllers;

use ME\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ME\SchoolManagement\Models\Staff;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::query();

        if ($request->search) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('first_name', 'like', "%$q%")
                   ->orWhere('last_name', 'like', "%$q%")
                   ->orWhere('staff_id', 'like', "%$q%")
                   ->orWhere('phone', 'like', "%$q%");
            });
        }
        if ($request->designation) {
            $query->where('designation', $request->designation);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $staffList    = $query->orderBy('designation')->orderBy('first_name')->paginate(20)->withQueryString();
        $designations = Staff::designations();

        return view('school-management::staff.index', compact('staffList', 'designations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id'      => 'nullable|string|max:50|unique:staff,staff_id',
            'first_name'    => 'required|string|max:255',
            'designation'   => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'joining_date'  => 'nullable|date',
            'leaving_date'  => 'nullable|date|after_or_equal:joining_date',
            'salary'        => 'nullable|numeric|min:0',
            'photo'         => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'staff_id','first_name','last_name','gender','date_of_birth','religion',
            'blood_group','phone','email','nid','present_address','permanent_address',
            'designation','joining_date','leaving_date','salary','bank_name',
            'bank_account_no','emergency_contact_name','emergency_contact_phone',
            'relationship','notes',
        ]);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['salary']    = $data['salary'] ?? 0;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('staff/photos', 'public');
        }

        Staff::create($data);
        return back()->with('success', 'Staff member added successfully.');
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $request->validate([
            'staff_id'      => 'nullable|string|max:50|unique:staff,staff_id,' . $id,
            'first_name'    => 'required|string|max:255',
            'designation'   => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'joining_date'  => 'nullable|date',
            'leaving_date'  => 'nullable|date|after_or_equal:joining_date',
            'salary'        => 'nullable|numeric|min:0',
            'photo'         => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'staff_id','first_name','last_name','gender','date_of_birth','religion',
            'blood_group','phone','email','nid','present_address','permanent_address',
            'designation','joining_date','leaving_date','salary','bank_name',
            'bank_account_no','emergency_contact_name','emergency_contact_phone',
            'relationship','notes',
        ]);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['salary']    = $data['salary'] ?? 0;

        if ($request->hasFile('photo')) {
            if ($staff->photo) Storage::disk('public')->delete($staff->photo);
            $data['photo'] = $request->file('photo')->store('staff/photos', 'public');
        }

        $staff->update($data);
        return back()->with('success', 'Staff member updated successfully.');
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        // Soft delete: keep the photo so a restore brings everything back.
        $log = function_exists('me_change_log') ? me_change_log("Staff moved to Recycle Bin: {$staff->first_name} {$staff->last_name}")->watch($staff) : null;
        $log ? $log->delete(fn () => $staff->delete()) : $staff->delete();
        return back()->with('success', 'Staff member moved to the Recycle Bin.');
    }

    public function getAll()
    {
        $staff = Staff::where('is_active', true)
            ->orderBy('designation')->orderBy('first_name')
            ->get(['id','first_name','last_name','designation']);
        return response()->json($staff);
    }
}

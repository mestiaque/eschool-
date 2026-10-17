<?php

namespace ME\SchoolManagement\Http\Controllers;

use ME\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ME\SchoolManagement\Models\Committee;
use ME\SchoolManagement\Models\CommitteeMember;

class CommitteeController extends Controller
{
    public function index()
    {
        $committees = Committee::withCount('members')->orderBy('is_active', 'desc')->orderBy('id', 'desc')->paginate(20);
        return view('school-management::committee.index', compact('committees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'formed_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:formed_date',
        ]);
        $data = $request->only(['name','academic_year','formed_date','expiry_date','description']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        Committee::create($data);
        return back()->with('success', 'Committee created.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'formed_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:formed_date',
        ]);
        $data = $request->only(['name','academic_year','formed_date','expiry_date','description']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        Committee::findOrFail($id)->update($data);
        return back()->with('success', 'Committee updated.');
    }

    public function destroy($id)
    {
        Committee::findOrFail($id)->delete();
        return back()->with('success', 'Committee deleted.');
    }

    public function show($id)
    {
        $committee = Committee::with('members')->findOrFail($id);
        $roles     = Committee::roles();
        return view('school-management::committee.show', compact('committee', 'roles'));
    }

    public function showPrint($id)
    {
        $committee = Committee::with('members')->findOrFail($id);
        $roles     = Committee::roles();
        return view('school-management::committee.show-print', compact('committee', 'roles'));
    }

    public function storeMember(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'role'   => 'required|string|max:255',
            'serial' => 'nullable|integer|min:0',
            'email'  => 'nullable|email|max:255',
            'photo'  => 'nullable|image|max:2048',
        ]);
        $committee = Committee::findOrFail($id);

        $data = $request->only(['name','role','serial','phone','email','address','profession','notes']);
        $data['committee_id'] = $committee->id;
        $data['serial'] = $data['serial'] ?? ($committee->members()->max('serial') + 1);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('committee/photos', 'public');
        }

        CommitteeMember::create($data);
        return back()->with('success', 'Member added.');
    }

    public function updateMember(Request $request, $id, $memberId)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'role'   => 'required|string|max:255',
            'serial' => 'nullable|integer|min:0',
            'email'  => 'nullable|email|max:255',
            'photo'  => 'nullable|image|max:2048',
        ]);
        $member = CommitteeMember::where('committee_id', $id)->findOrFail($memberId);

        $data = $request->only(['name','role','serial','phone','email','address','profession','notes']);
        if ($data['serial'] === null) unset($data['serial']); // NOT NULL column

        if ($request->hasFile('photo')) {
            if ($member->photo) Storage::disk('public')->delete($member->photo);
            $data['photo'] = $request->file('photo')->store('committee/photos', 'public');
        }

        $member->update($data);
        return back()->with('success', 'Member updated.');
    }

    public function destroyMember($id, $memberId)
    {
        $member = CommitteeMember::where('committee_id', $id)->findOrFail($memberId);
        if ($member->photo) Storage::disk('public')->delete($member->photo);
        $member->delete();
        return back()->with('success', 'Member removed.');
    }

    public function reorder(Request $request, $id)
    {
        foreach ($request->order ?? [] as $serial => $memberId) {
            CommitteeMember::where('committee_id', $id)->where('id', $memberId)->update(['serial' => $serial + 1]);
        }
        return response()->json(['success' => true]);
    }
}

@extends('me::printMaster2')
@section('title', $committee->name)

@section('contents')

@include('school-management::partials.print-header', [
    'title' => $committee->name,
    'subtitle' => collect([
        $committee->academic_year,
        sm_date($committee->formed_date),
        $committee->expiry_date ? 'Expires: '.sm_date($committee->expiry_date) : null,
    ])->filter()->implode(' | '),
])

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ sm_t('Name') }}</th>
            <th>{{ sm_t('Role') }}</th>
            <th>{{ sm_t('Profession') }}</th>
            <th>{{ sm_t('Phone') }}</th>
            <th>{{ sm_t('Address') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($committee->members as $i => $member)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $member->name }}</td>
            <td>{{ $roles[$member->role] ?? $member->role }}</td>
            <td>{{ $member->profession ?? '—' }}</td>
            <td>{{ $member->phone ?? '—' }}</td>
            <td>{{ $member->address ?? '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">{{ sm_t('No members added yet') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>

@endsection

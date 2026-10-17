@extends('me::master')

@section('title', sm_t('Recycle Bin'))

@section('content')
<div class="container-fluid">
    <ul class="nav nav-tabs mb-3">
        @foreach(['students' => 'Students', 'teachers' => 'Teachers', 'staff' => 'Staff'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $type === $key ? 'active' : '' }}" href="{{ route('trash.index', ['type' => $key]) }}">
                {{ $label }} <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $counts[$key] }}</span>
            </a>
        </li>
        @endforeach
    </ul>

    <div class="card">
        <div class="card-body">
            <p class="text-muted small mb-3">
                Deleted records stay here with all their history (fees, marks, attendance). <strong>Restore</strong> brings them back unchanged.
                <strong>Delete forever</strong> also erases that history and cannot be undone (super admin only).
            </p>
            <div class="table-responsive">
                <table class="table table-encodex table-sm table-bordered align-middle">
                    <thead class="table-dark">
                        <tr><th>{{ sm_t('ID') }}</th><th>{{ sm_t('Name') }}</th><th>{{ sm_t('Phone') }}</th><th>{{ sm_t('Deleted') }}</th><th style="width:330px">{{ sm_t('Action') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        @php $code = $item->student_id ?? $item->teacher_id ?? $item->staff_id ?? $item->id; @endphp
                        <tr>
                            <td>{{ $code }}</td>
                            <td>{{ trim($item->first_name . ' ' . $item->last_name) }}</td>
                            <td>{{ $item->phone ?? '—' }}</td>
                            <td>{{ $item->deleted_at?->format('d M Y, h:i A') }}</td>
                            <td>
                                <form method="POST" action="{{ route('trash.restore', [$type, $item->id]) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-encodex-save btn-sm"><i class="fas fa-undo"></i> Restore</button>
                                </form>
                                @if(auth()->user()->isSuperAdmin())
                                <form method="POST" action="{{ route('trash.force', [$type, $item->id]) }}" class="d-inline-flex gap-1"
                                      onsubmit="return confirm('Permanently delete {{ $code }} and ALL related records? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <input type="text" name="confirm_code" class="form-control form-control-sm" style="width:130px" placeholder="Type {{ $code }}" required>
                                    <button class="btn btn-encodex-delete btn-sm"><i class="fas fa-trash"></i> Delete forever</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">The Recycle Bin is empty</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

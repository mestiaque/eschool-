@extends('me::master')

@section('title', sm_t('Backup & Restore'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.backup.create') }}">
                        @csrf
                        <div class="mb-3">
                            <h5>{{ sm_t('Create Backup') }}</h5>
                            <p class="text-muted">{{ sm_t('Download a full SQL dump of the database.') }}</p>
                            <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Download Backup Now') }}</button>
                        </div>
                    </form>

                    <hr>

                    <h5>{{ sm_t('Restore Backup') }}</h5>
                    @if(auth()->user()->isSuperAdmin())
                    <p class="text-muted small">{{ sm_t('Replaces') }} <strong>all current data</strong> with the uploaded backup. The current data is saved
                        to <code>storage/app/backups</code> first, so a mistaken restore can be undone.</p>
                    <form method="POST" action="{{ route('settings.backup.restore') }}" enctype="multipart/form-data"
                          onsubmit="return confirm('Replace ALL current data with this backup?')">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Backup file (.sql)') }}</label>
                            <input type="file" name="backup_file" accept=".sql" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Type') }} <strong>RESTORE</strong> to confirm</label>
                            <input type="text" name="confirm" class="form-control form-control-sm" autocomplete="off" required>
                        </div>
                        <button type="submit" class="btn btn-encodex-delete btn-sm"><i class="fas fa-upload"></i> {{ sm_t('Restore Backup') }}</button>
                    </form>
                    @else
                    <p class="text-muted small mb-0">{{ sm_t('Only a super admin can restore a backup.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('me::master')

@section('title', sm_t('Book Return'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card mt-5">
                <div class="card-body">
                    <form method="GET" action="{{ route('library.issues') }}" class="glass-search-form">
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Issue ID / Book ID') }}</label>
                            <input type="number" name="issue_id" class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-encodex-search btn-sm w-100">{{ sm_t('Search') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

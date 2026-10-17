@extends('me::master')

@section('title', sm_t('Profit/Loss Report'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>{{ sm_t('Total Income') }}</h5>
                                    <h2>{{ $incomes ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>{{ sm_t('Total Expense') }}</h5>
                                    <h2>{{ $expenses ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>{{ sm_t('Net Profit/Loss') }}</h5>
                                    <h2>{{ $profit ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

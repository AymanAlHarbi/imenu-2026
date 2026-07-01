@extends('layouts.app', ['title' => __('loyalty.title')])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card shadow"><div class="card-body">
                <h6 class="text-uppercase text-muted">{{ __('loyalty.members') }}</h6>
                <h1>{{ $stats['members'] }}</h1>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow"><div class="card-body">
                <h6 class="text-uppercase text-muted">{{ __('loyalty.points_outstanding') }}</h6>
                <h1>{{ $stats['points_outstanding'] }}</h1>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow"><div class="card-body">
                <h6 class="text-uppercase text-muted">{{ __('loyalty.points_given') }}</h6>
                <h1>{{ $stats['points_given'] }}</h1>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow"><div class="card-body">
                <h6 class="text-uppercase text-muted">{{ __('loyalty.points_redeemed') }}</h6>
                <h1>{{ $stats['points_redeemed'] }}</h1>
            </div></div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header bg-white border-0">
                    <h3 class="mb-0">{{ __('loyalty.top_clients') }}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('loyalty.points_on_card') }}</th>
                                <th>{{ __('loyalty.lifetime_points') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topClients as $acc)
                                <tr>
                                    <td>{{ $acc->client->name ?? '-' }}</td>
                                    <td>{{ $acc->points_balance }}</td>
                                    <td>{{ $acc->lifetime_points }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">{{ __('No items') }} ...</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

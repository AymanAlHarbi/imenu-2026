@extends('layouts.app', ['title' => __('loyalty.title')])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12">

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-warning">{{ $errors->first() }}</div>
            @endif

            @if (! $enabled)
                <div class="alert alert-info">{{ __('loyalty.program_not_enabled') }}</div>
            @else

            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0">
                    <h3 class="mb-0">{{ __('loyalty.title') }} - {{ $restorant->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-uppercase text-muted">{{ __('loyalty.points_on_card') }}</h6>
                            <h1>{{ $account->points_balance }}</h1>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-uppercase text-muted">{{ __('loyalty.lifetime_points') }}</h6>
                            <h1>{{ $account->lifetime_points }}</h1>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-uppercase text-muted">{{ __('loyalty.redeemable_value') }}</h6>
                            <h1>{{ $account->redeemable_value }} {{ config('settings.cashier_currency') }}</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0">
                    <h3 class="mb-0">{{ __('loyalty.redeem_points') }}</h3>
                    <small class="text-muted">{{ __('loyalty.redeem_rule', ['ratio' => $ratio]) }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('loyaltypoints.redeem', $restorant) }}" class="form-inline">
                        @csrf
                        <input type="number" name="points" min="{{ $minPoints }}" step="{{ $ratio }}"
                               class="form-control mr-2" placeholder="{{ __('loyalty.points') }}" required>
                        <button type="submit" class="btn btn-primary">{{ __('loyalty.get_it') }}</button>
                    </form>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-white border-0">
                    <h3 class="mb-0">{{ __('Status History') }}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Points') }}</th>
                                <th>{{ __('Details') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($account->transactions as $tx)
                                <tr>
                                    <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ __('loyalty.type_'.$tx->type) }}</td>
                                    <td class="{{ $tx->points >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $tx->points >= 0 ? '+' : '' }}{{ $tx->points }}
                                    </td>
                                    <td>{{ $tx->description }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">{{ __('No items') }} ...</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app', ['title' => __('loyaltypoints::general.title')])

@section('content')
<div class="container-fluid mt--7">

    @if (! $enabled)
        <div class="alert alert-warning">{{ __('loyaltypoints::general.program_not_enabled') }}</div>
    @endif

    <div class="mb-4">
        <a href="{{ route('loyaltypoints.report') }}" class="btn btn-secondary">{{ __('Go back') }}</a>
        <a href="{{ route('loyaltypoints.report.run') }}" class="btn btn-primary">{{ __('loyaltypoints::general.run_now') }}</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h3>{{ __('loyaltypoints::general.checked') }}: {{ $result['checked'] }}
                &nbsp;|&nbsp;
                {{ __('loyaltypoints::general.awarded') }}: <span class="text-success">{{ $result['awarded'] }}</span>
            </h3>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-white border-0">
            <h3 class="mb-0">{{ __('loyaltypoints::general.diagnostics') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Last status') }}</th>
                        <th>{{ __('Payment status') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Points') }}</th>
                        <th>{{ __('loyaltypoints::general.result') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($result['details'] as $row)
                        <tr class="{{ $row['reason'] === 'awarded' ? 'table-success' : '' }}">
                            <td>{{ $row['id_formated'] }}</td>
                            <td>{{ $row['last_status'] }}</td>
                            <td>{{ $row['payment_status'] }}</td>
                            <td>{{ $row['client_id'] ?? __('loyaltypoints::general.guest_no_client') }}</td>
                            <td>{{ $row['points'] ?? '-' }}</td>
                            <td>{{ __('loyaltypoints::general.reason_'.$row['reason']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">{{ __('loyaltypoints::general.no_final_orders_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

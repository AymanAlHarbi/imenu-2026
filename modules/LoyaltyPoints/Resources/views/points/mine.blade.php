@extends('layouts.app', ['title' => __('loyaltypoints::general.title')])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header bg-white border-0">
                    <h3 class="mb-0">{{ __('loyaltypoints::general.title') }}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('loyaltypoints::general.points_on_card') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['restorant']->name }}</td>
                                    <td>{{ $row['points_balance'] }}</td>
                                    <td>
                                        <a href="{{ route('loyaltypoints.index', $row['restorant']) }}" class="btn btn-sm btn-primary">
                                            {{ __('loyaltypoints::general.redeem_points') }}
                                        </a>
                                    </td>
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

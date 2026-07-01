@extends('layouts.app', ['title' => __('loyaltypoints::general.title')])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12">

            @if ($errors->any())
                <div class="alert alert-warning">{{ $errors->first() }}</div>
            @endif

            @if (! $enabled)
                <div class="alert alert-info">{{ __('loyaltypoints::general.program_not_enabled') }}</div>
            @else

            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0">
                    <h3 class="mb-0">{{ __('loyaltypoints::general.title') }} - {{ $restorant->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-uppercase text-muted">{{ __('loyaltypoints::general.points_on_card') }}</h6>
                            <h1>{{ $account->points_balance }}</h1>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-uppercase text-muted">{{ __('loyaltypoints::general.lifetime_points') }}</h6>
                            <h1>{{ $account->lifetime_points }}</h1>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-uppercase text-muted">{{ __('loyaltypoints::general.redeemable_value') }}</h6>
                            <h1>{{ $account->redeemable_value }} {{ config('settings.cashier_currency') }}</h1>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('redeemed_code'))
                <div class="card shadow mb-4" style="border:2px solid #2dce89;">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-3">
                            {{ __('loyaltypoints::general.coupon_ready_title', ['value' => session('redeemed_value')]) }}
                        </h3>

                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <span id="loyaltyCouponCode" class="badge badge-lg"
                                  style="font-size:1.6rem; padding:14px 24px; background:#f4f5f7; border:2px dashed #2dce89; letter-spacing:2px;">
                                {{ session('redeemed_code') }}
                            </span>
                            <button type="button" class="btn btn-success ml-3" onclick="loyaltyCopyCode()">
                                {{ __('loyaltypoints::general.copy_code') }}
                            </button>
                        </div>
                        <p id="loyaltyCopyConfirm" class="text-success" style="display:none;">
                            {{ __('loyaltypoints::general.code_copied') }}
                        </p>

                        <ol class="text-right d-inline-block mt-3" style="text-align:start;">
                            <li>{{ __('loyaltypoints::general.step_copy') }}</li>
                            <li>{{ __('loyaltypoints::general.step_go_checkout') }}</li>
                            <li>{{ __('loyaltypoints::general.step_paste_field') }}</li>
                            <li>{{ __('loyaltypoints::general.step_apply') }}</li>
                        </ol>

                        @if ($vendorUrl)
                            <div class="mt-3">
                                <a href="{{ $vendorUrl }}" class="btn btn-primary btn-lg">
                                    {{ __('loyaltypoints::general.go_to_restaurant') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <script>
                function loyaltyCopyCode() {
                    var text = document.getElementById('loyaltyCouponCode').innerText.trim();
                    navigator.clipboard.writeText(text).then(function () {
                        document.getElementById('loyaltyCopyConfirm').style.display = 'block';
                    }).catch(function () {
                        // Fallback for older browsers
                        var el = document.createElement('textarea');
                        el.value = text;
                        document.body.appendChild(el);
                        el.select();
                        document.execCommand('copy');
                        document.body.removeChild(el);
                        document.getElementById('loyaltyCopyConfirm').style.display = 'block';
                    });
                }
                </script>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0">
                    <h3 class="mb-0">{{ __('loyaltypoints::general.redeem_points') }}</h3>
                    <small class="text-muted">{{ __('loyaltypoints::general.redeem_rule', ['ratio' => $ratio]) }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('loyaltypoints.redeem', $restorant) }}" id="loyaltyRedeemForm">
                        @csrf
                    </form>

                    <div class="d-flex align-items-center justify-content-between p-3"
                         style="border:1px solid #e9ecef; border-radius:12px;">
                        <div class="custom-control custom-switch" style="padding-right: 2.5em;">
                            <input type="checkbox" class="custom-control-input" id="loyaltyToggle"
                                   {{ $canRedeem ? '' : 'disabled' }}
                                   onchange="if(this.checked){document.getElementById('loyaltyRedeemForm').submit();}">
                            <label class="custom-control-label" for="loyaltyToggle"></label>
                        </div>
                        <div class="flex-grow-1 mr-3">
                            @if ($canRedeem)
                                <span class="font-weight-bold">
                                    {{ __('loyaltypoints::general.use_balance', ['points' => $usablePoints]) }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ __('loyaltypoints::general.use_balance_value', ['value' => $usableValue]) }}
                                </small>
                            @else
                                <span class="font-weight-bold text-muted">
                                    {{ __('loyaltypoints::general.use_balance', ['points' => $account->points_balance]) }}
                                </span>
                                <br>
                                <small class="text-danger">
                                    {{ __('loyaltypoints::general.not_enough_points_hint', ['points' => $minPoints]) }}
                                </small>
                            @endif
                        </div>
                    </div>
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
                                    <td>{{ __('loyaltypoints::general.type_'.$tx->type) }}</td>
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

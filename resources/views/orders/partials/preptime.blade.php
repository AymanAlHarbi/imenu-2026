{{-- iMenu 2026 — مدة التجهيز: حالة للمقهى لا حقل لكل طلب (مواصفة شاشة الكاشير) --}}
@if (auth()->user() && (auth()->user()->hasRole('owner') || auth()->user()->hasRole('staff')) && auth()->user()->restorant)
    @php
        $imenuVendor = auth()->user()->restorant;
        $imenuIsAuto = \App\Services\PrepTime::isAuto($imenuVendor);
        $imenuMinutes = \App\Services\PrepTime::minutes($imenuVendor);
        $imenuAccuracy = \App\Services\Trust::vendorAccuracy($imenuVendor);
    @endphp

    <div class="card shadow mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center">

                <div class="mr-4 mb-2">
                    <span class="text-muted text-sm d-block">{{ __('Preparation time now') }}</span>
                    <span class="h3 mb-0">{{ $imenuMinutes }} {{ __('min') }}</span>
                    <span class="badge badge-pill {{ $imenuIsAuto ? 'badge-info' : 'badge-warning' }} ml-1">
                        {{ $imenuIsAuto ? __('Automatic') : __('Manual') }}
                    </span>
                </div>

                <form method="POST" action="{{ route('vendor.preptime') }}" class="mb-2 mr-4">
                    @csrf
                    @foreach (\App\Services\PrepTime::SLICES as $slice)
                        <button type="submit" name="minutes" value="{{ $slice }}"
                                style="min-height:44px;min-width:56px"
                                class="btn btn-sm {{ (! $imenuIsAuto && $imenuMinutes == $slice) ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $slice }}
                        </button>
                    @endforeach
                    <button type="submit" name="minutes" value="0"
                            style="min-height:44px"
                            class="btn btn-sm {{ $imenuIsAuto ? 'btn-info' : 'btn-outline-secondary' }}">
                        {{ __('Automatic') }}
                    </button>
                </form>

                @if ($imenuAccuracy !== null)
                    <div class="mb-2">
                        <span class="text-muted text-sm d-block">{{ __('Your accuracy') }}</span>
                        <span class="h3 mb-0" style="color: {{ $imenuAccuracy >= 80 ? '#2E5C43' : ($imenuAccuracy >= 60 ? '#D8A15E' : '#C0392B') }}">
                            {{ $imenuAccuracy }}%
                        </span>
                        <span class="text-muted text-sm">{{ __('of orders ready on time') }}</span>
                    </div>
                @endif

            </div>
            <small class="text-muted d-block mt-1">
                {{ __('This applies to incoming orders until you change it. The customer sees a clock time, not a duration.') }}
            </small>
        </div>
    </div>
@endif

@extends('layouts.app', ['title' => __('Clients')])

@section('content')
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    </div>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Clients') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('clients.export') }}" class="btn btn-sm btn-outline-primary">{{ __('Export CSV') }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        @include('partials.flash')
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Phone') }}</th>
                                    <th scope="col">{{ __('Orders') }}</th>
                                    <th scope="col">{{ __('Total spent') }}</th>
                                    <th scope="col">{{ __('Last order') }}</th>
                                    <th scope="col">{{ __('Creation Date') }}</th>
                                    @if(config('settings.enable_birth_date_on_register'))
                                        <th scope="col">{{ __('Birth Date') }}</th>
                                    @endif
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clients as $client)
                                    @php
                                        $ordersCount = $client->orders()->count();
                                        $totalSpent = $ordersCount ? $client->orders()->sum('order_price') - $client->orders()->sum('discount') : 0;
                                        $lastOrderDate = $ordersCount ? \Carbon\Carbon::parse($client->orders()->max('created_at')) : null;

                                        $waPhone = null;
                                        if ($client->phone) {
                                            $waPhone = preg_replace('/[^0-9]/', '', $client->phone);
                                            if (substr($waPhone, 0, 2) == '00') {
                                                $waPhone = substr($waPhone, 2);
                                            } elseif (substr($waPhone, 0, 1) == '0') {
                                                $waPhone = '966'.substr($waPhone, 1);
                                            }
                                        }

                                        $avatarColors = [['#EEEDFE','#3C3489'],['#E1F5EE','#085041'],['#FAECE7','#712B13'],['#FBEAF0','#72243E'],['#E6F1FB','#0C447C']];
                                        $avColor = $avatarColors[$client->id % 5];
                                        $avLetter = mb_substr(trim($client->name), 0, 1);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="media align-items-center">
                                                <span class="avatar rounded-circle" style="background-color: {{ $avColor[0] }}; color: {{ $avColor[1] }}; font-weight: 700; font-size: 17px; width: 42px; height: 42px; min-width: 42px; margin: 0 12px;">{{ $avLetter }}</span>
                                                <div class="media-body">
                                                    <a href="{{ route('clients.edit', $client) }}" style="font-size: 15px; font-weight: 700; color: #32325d; display: block; line-height: 1.5;">{{ $client->name }}</a>
                                                    <a href="mailto:{{ $client->email }}" style="font-size: 12px; color: #8898aa; display: block; max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" dir="ltr">{{ $client->email }}</a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($client->phone)
                                                <a class="text-sm text-success" href="https://wa.me/{{ $waPhone }}" target="_blank" dir="ltr">
                                                    <i class="fab fa-whatsapp"></i> {{ $client->phone }}
                                                </a>
                                            @else
                                                <span class="text-sm text-muted">{{ __('Not registered') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('clients.edit', $client) }}" class="badge badge-pill badge-{{ $ordersCount ? 'primary' : 'secondary' }}">{{ $ordersCount }}</a>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">@money($totalSpent, config('settings.cashier_currency'), config('settings.do_convertion'))</span>
                                        </td>
                                        <td>
                                            @if ($lastOrderDate)
                                                <span class="text-sm">{{ $lastOrderDate->locale(Config::get('app.locale'))->calendar() }}</span>
                                                <br/>
                                                <small class="text-muted">{{ $lastOrderDate->locale(Config::get('app.locale'))->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ $client->created_at->locale(Config::get('app.locale'))->isoFormat('D MMM YYYY') }}</span>
                                            <br/>
                                            <small class="text-muted">{{ $client->created_at->locale(Config::get('app.locale'))->diffForHumans() }}</small>
                                        </td>
                                        @if(config('settings.enable_birth_date_on_register'))
                                            <td>{{ $client->birth_date }}</td>
                                        @endif
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <a href="{{ route('clients.edit', $client) }}" type="button" class="dropdown-item">
                                                        {{ __('Info') }}
                                                    </a>
                                                    @hasrole('admin')
                                                        <form action="{{ route('clients.destroy', $client) }}" method="post">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to deactivate this user?") }}') ? this.parentElement.submit() : ''">
                                                                {{ __('Deactivate') }}
                                                            </button>
                                                        </form>
                                                    @endhasrole
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{ $clients->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
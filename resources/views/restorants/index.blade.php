@extends('layouts.app', ['title' => __('Restaurants')])
@section('admin_title')
    {{__('Restaurants')}}
@endsection
@section('content')
    @include('restorants.partials.modals')
    <style>
        .res-stat-num{font-size:26px;font-weight:700;font-variant-numeric:tabular-nums}
        .res-stat-ic{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0}
        .res-cell{display:flex;align-items:center;gap:12px}
        .res-cell .nm{font-weight:700;font-size:14px}
        .res-mlink{display:flex;align-items:center;gap:6px;margin-top:3px;direction:ltr;justify-content:flex-end}
        .res-mlink a{font-size:12px}
        .res-copy{border:0;background:#F3ECE0;color:#26537C;width:22px;height:22px;border-radius:5px;font-size:11px;cursor:pointer;line-height:1}
        .res-copy:hover{background:#26537C;color:#fff}
        .res-owner-email{color:#5A7590;font-size:12px;direction:ltr;text-align:right}
        .res-wa{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#e6f9f1;color:#1faa5c;font-size:12px;margin-right:6px;vertical-align:-6px}
        .res-wa:hover{background:#1faa5c;color:#fff}
        .res-dt-sub{color:#8FA3B8;font-size:11.5px;margin-top:2px}
        .res-act{width:32px;height:32px;border-radius:9px;border:1px solid #E6DCCC;background:#fff;color:#5A7590;font-size:13px;display:inline-flex;align-items:center;justify-content:center}
        .res-act:hover{border-color:#E8952F;color:#B96F14}
        .res-chip{border:1px solid #E6DCCC;background:#fff;color:#5A7590;border-radius:999px;padding:5px 15px;font-size:12.5px;font-weight:500}
        .res-chip.on,.res-chip:hover{background:#16304C;border-color:#16304C;color:#fff}
        .res-search-input{border:1px solid #dee2e6;border-radius:8px;padding:8px 12px;font-size:13.5px;width:100%;max-width:340px}
        .res-plan-badge{padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;background:#F3ECE0;color:#26537C;display:inline-block}
        .res-plan-badge.none{background:#FAF6EF;color:#8FA3B8}
    </style>
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    </div>

    <div class="container-fluid mt--7">
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card shadow card-body flex-row align-items-center justify-content-between py-3">
                    <div><div class="res-stat-num">{{ $stats['total'] }}</div><small class="text-muted">{{ __('Total restaurants') }}</small></div>
                    <div class="res-stat-ic bg-gradient-primary"><i class="fas fa-store"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card shadow card-body flex-row align-items-center justify-content-between py-3">
                    <div><div class="res-stat-num text-success">{{ $stats['active'] }}</div><small class="text-muted">{{ __('Active') }}</small></div>
                    <div class="res-stat-ic bg-gradient-success"><i class="fas fa-check"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card shadow card-body flex-row align-items-center justify-content-between py-3">
                    <div><div class="res-stat-num text-danger">{{ $stats['inactive'] }}</div><small class="text-muted">{{ __('Not active') }}</small></div>
                    <div class="res-stat-ic bg-gradient-danger"><i class="fas fa-pause"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card shadow card-body flex-row align-items-center justify-content-between py-3">
                    <div><div class="res-stat-num">{{ $stats['newThisMonth'] }}</div><small class="text-muted">{{ __('New this month') }}</small></div>
                    <div class="res-stat-ic bg-gradient-orange"><i class="fas fa-plus"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Restaurants') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('admin.restaurants.create') }}" class="btn btn-sm btn-primary">{{ __('Add Restaurant') }}</a>
                                @endif
                                <a href="{{ route('admin.restaurants.index') }}?downlodcsv=true" class="btn btn-sm btn-outline-primary">{{ __('Export CSV') }}</a>
                                @if(auth()->user()->hasRole('admin') && config('settings.enable_import_csv'))
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-import-restaurants">{{ __('Import from CSV') }}</button>
                                @endif
                            </div>
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-md-6 mb-2">
                                <form method="GET" action="{{ route('admin.restaurants.index') }}">
                                    @if(request('status'))
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                    @endif
                                    <input class="res-search-input" type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search') }}...">
                                </form>
                            </div>
                            <div class="col-md-6 mb-2 text-md-right">
                                @php $qs = request('search') ? ['search' => request('search')] : []; @endphp
                                <a class="res-chip {{ !request('status') ? 'on' : '' }}" href="{{ route('admin.restaurants.index', $qs) }}">{{ __('All') }}</a>
                                <a class="res-chip {{ request('status') == 'active' ? 'on' : '' }}" href="{{ route('admin.restaurants.index', array_merge($qs, ['status' => 'active'])) }}">{{ __('Active') }}</a>
                                <a class="res-chip {{ request('status') == 'inactive' ? 'on' : '' }}" href="{{ route('admin.restaurants.index', array_merge($qs, ['status' => 'inactive'])) }}">{{ __('Not active') }}</a>
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
                                    <th scope="col">{{ __('Owner') }}</th>
                                    <th scope="col">{{ __('Plan') }}</th>
                                    <th scope="col">{{ __('Orders') }}</th>
                                    <th scope="col">{{ __('Creation Date') }}</th>
                                    <th scope="col">{{ __('Active') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($restorants as $restorant)
                                    <tr>
                                        <td>
                                            <div class="res-cell">
                                                <img class="rounded" src="{{ $restorant->icon }}" width="42px" height="42px" alt="">
                                                <div>
                                                    @if(auth()->user()->hasRole('manager'))
                                                        <a class="nm" href="{{ route('admin.restaurants.loginas', $restorant) }}">{{ $restorant->name }}</a>
                                                    @else
                                                        <a class="nm" href="{{ route('admin.restaurants.edit', $restorant) }}">{{ $restorant->name }}</a>
                                                    @endif
                                                    <div class="res-mlink">
                                                        <button type="button" class="res-copy" data-link="{{ $restorant->link }}" title="{{ __('Copy') }}"><i class="fas fa-copy"></i></button>
                                                        <a href="{{ $restorant->link }}" target="_blank">{{ str_replace(['https://', 'http://'], '', $restorant->link) }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span>{{ $restorant->user ? $restorant->user->name : __('Deleted') }}</span>
                                            @if($restorant->user && $restorant->user->phone)
                                                <a class="res-wa" target="_blank" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $restorant->user->phone) }}" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                            @endif
                                            <div class="res-owner-email">
                                                <a href="mailto:{{ $restorant->user ? $restorant->user->email : '' }}">{{ $restorant->user ? $restorant->user->email : '' }}</a>
                                            </div>
                                        </td>
                                        <td>
                                            @php $planName = $restorant->user ? $planNames->get($restorant->user->mplanid()) : null; @endphp
                                            @if($planName)
                                                <span class="res-plan-badge">{{ $planName }}</span>
                                            @else
                                                <span class="res-plan-badge none">{{ __('No plan found') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ $restorant->orders_count }}</span>
                                            <div class="res-dt-sub">
                                                @if($restorant->last_order_at)
                                                    {{ __('Last order') }}: {{ \Carbon\Carbon::parse($restorant->last_order_at)->locale(Config::get('app.locale'))->diffForHumans() }}
                                                @else
                                                    {{ __('No orders yet') }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span>{{ $restorant->created_at->locale(Config::get('app.locale'))->isoFormat('D MMMM YYYY') }}</span>
                                            <div class="res-dt-sub">{{ $restorant->created_at->locale(Config::get('app.locale'))->diffForHumans() }}</div>
                                        </td>
                                        <td>
                                           @if($restorant->active == 1)
                                                <span class="badge badge-success">{{ __('Active') }}</span>
                                           @else
                                                <span class="badge badge-warning">{{ __('Not active') }}</span>
                                           @endif
                                        </td>
                                        <td class="text-right">
                                            @if($restorant->active == 0)
                                                <a class="res-act" style="color:#1a9e68;border-color:#bfe9d6" href="{{ route('restaurant.activate', $restorant) }}" title="{{ __('Activate') }}"><i class="fas fa-play"></i></a>
                                            @endif
                                            <a class="res-act" href="{{ route('admin.restaurants.edit', $restorant) }}" title="{{ __('Edit') }}"><i class="fas fa-pen"></i></a>
                                            <a class="res-act" href="{{ route('admin.restaurants.loginas', $restorant) }}" title="{{ __('Login as') }}"><i class="fas fa-sign-in-alt"></i></a>
                                            <div class="dropdown d-inline-block">
                                                <a class="res-act" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    @if ($hasCloner)
                                                     <a class="dropdown-item" href="{{ route('admin.restaurants.create')."?cloneWith=".$restorant->id }}">{{ __('Clone it') }}</a>
                                                    @endif
                                                    @if($restorant->active == 1)
                                                        <form action="{{ route('admin.restaurants.destroy', $restorant) }}" method="post">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to deactivate this restaurant?") }}') ? this.parentElement.submit() : ''">
                                                                {{ __('Deactivate') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a class="dropdown-item warning red" onclick="return confirm(' {{ __("Are you sure you want to delete this Restaurant from Database? This will aslo delete all data related to it. This is irreversible step.") }}')"  href="{{ route('admin.restaurant.remove',$restorant)}}">{{ __('Delete') }}</a>
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
                            {{ $restorants->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
    <script type="text/javascript">
        document.querySelectorAll('.res-copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                navigator.clipboard.writeText(btn.getAttribute('data-link'));
                var icon = btn.querySelector('i');
                icon.className = 'fas fa-check';
                setTimeout(function () { icon.className = 'fas fa-copy'; }, 1200);
            });
        });
    </script>
@endsection

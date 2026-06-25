@extends('general.index', $setup)

@section('thead')
    <th>{{ __('Status') }}</th>
    <th>{{ __('Name') }}</th>
    <th>{{ __('Code') }}</th>
    <th>{{ __('Limit number') }}</th>
    <th>{{ __('Used from') }}</th>
    <th></th>
    <th>{{ __('crud.actions') }}</th>
@endsection
@section('tbody')
    @foreach ($setup['items'] as $item)
        <tr>
            <td>
                @if ($item->limit_to_num_uses > 0 && $item->used_count >= $item->limit_to_num_uses)
                    <span class="badge badge-danger">{{ __('Used') }}</span>
               @else
                    <span class="badge badge-success">{{ __('Active') }}</span>
                @endif
            </td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->code }}</td>
            <td>{{ $item->limit_to_num_uses }}</td>
            <td>{{ $item->used_count }}</td>
            <td>
                @if ($item->limit_to_num_uses == 1 && $item->used_count ==0)
                    <a href="{{ route('coupons.use',[$parameter_name]=$item->id) }}" class="btn btn-success btn-sm">{{ __('Client redeem the award') }}</a>
                @endif
            </td>
            @include('partials.tableactions',$setup)
            
        </tr>
    @endforeach
@endsection

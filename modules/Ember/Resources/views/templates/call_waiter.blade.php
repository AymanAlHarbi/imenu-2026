<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content" style="border:0;border-radius:18px;overflow:hidden;">
            <div class="modal-body p-0">
                <div class="card border-0" style="background:var(--em-canvas);">
                    <div class="card-header bg-transparent pb-2 border-0">
                        <h4 class="text-center mt-2 mb-2" style="font-weight:700;color:var(--em-ink);">{{ __('Call Waiter') }}</h4>
                    </div>
                    <div class="card-body px-4 py-4">
                        <form role="form" method="post" action="{{ route('call.waiter') }}">
                            @csrf
                            @if (!isset($_GET['tid']))
                                @include('partials.fields',$fields)
                            @else
                                <input type="hidden" value="{{ $_GET['tid'] }}" name="table_id" id="table_id"/>
                            @endif
                            <div class="text-center">
                                <button type="submit" class="em-addbtn" style="width:auto;padding:11px 26px;">{{ __('Call Now') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

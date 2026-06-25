    @extends('layouts.app', ['title' => __('Setup WhatsApp Notifications')])

    @section('content')
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    </div>
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col-xl-12 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                {{ __('Setup WhatsApp Notifications') }}
                            </div>
                            <!-- Go to api setup -->
                            <div class="col-4 text  text-right">
                                <a href="{{ route('whatsi.api') }}" class="btn btn-sm btn-primary">{{ __('Change API Key') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="get" action="{{ route('whatsi.campaings') }}" autocomplete="off">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <h6 class="heading-small text-muted mb-4"></h6>
                                    <!-- include select -->
                                    @include('partials.select', ['class'=>"col-6",'name'=>"Select Order update notification",'id'=>"selected_id",'placeholder'=>"Select type",'data'=>$campaigns,'required'=>true, 'value'=>$selected_campaign])
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
                                    </div>
                                    
                                </div>
                                <div class="col-lg-6">
                                    @isset($error)
                                    <div class="alert alert-danger" role="alert">
                                                {{ $error }}
                                            </div>
                                    @endisset
                                    
                                    <h6 class="heading-small text-muted mb-4">{{ __('Get your clients notified via WhatsApp Automatically') }}</h6>

                                    <h4>How to create your campaign?</h4>
                                    <p>1. Go to <a href="{{ config('whatsi.url') }}" target="_blank"><mark> {{    config('whatsi.name',"What.si") }}</mark></a> and login with your account</p>
                                    <p>2. Go to <a href="{{ config('whatsi.url') }}/api/wpbox/campaings/apis" target="_blank"><mark> {{  config('whatsi.name',"What.si") }}' API Campaigns<mark> </a> and create your API Campaings.</p>
                                    <p>3. Refresh, and select your campaign.</p>

                                    <br />
                                    <h4>What data we send to the   <a href="{{ config('whatsi.url') }}" target="_blank"><mark> {{    config('whatsi.name',"What.si") }}</mark></a> API</h4>
                                    <script src="{{ config('whatsi.info','https://gist.github.com/dimovdaniel/e0d2b1c146216491200bdba519dbb69f.js') }}"></script>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.footers.auth')
    </div>
    @endsection
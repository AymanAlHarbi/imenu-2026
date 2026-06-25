@extends('layouts.app', ['title' => __('Setup API')])

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
                            {{ __('Setup API Key') }}
                        </div>
                     
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('whatsi.store_api') }}" autocomplete="off">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <h6 class="heading-small text-muted mb-4">{{ __('API Key') }}</h6>
                                @include('partials.input',['class'=>"col-12", 'ftype'=>'input','name'=>"API Key",'id'=>"api_key" ,'placeholder'=>"Enter API Key",'required'=>true, 'value'=>$api_key])
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h6 class="heading-small text-muted mb-4">{{ __('Get your clients notified via WhatsApp Automatically') }}</h6>
                                
                                <h5>How to get API Key?</h5>
                                    <p>1. Go to <a href="{{ config('whatsi.url') }}" target="_blank"><mark> {{    config('whatsi.name',"What.si") }}</mark></a> and create an account</p>
                                    <p>2. Go to <a href="{{ config('whatsi.url') }}/api/wpbox/info" target="_blank"><mark> {{  config('whatsi.name',"What.si") }}' API Dasboard<mark> </a> and get your API Key</p>
                                    <p>3. Paste your API Key here</p>
                                @isset($error)
                                        <div class="alert alert-danger" role="alert">
                                            {{ $error }}
                                        </div>
                                @endisset

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
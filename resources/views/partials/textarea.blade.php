@isset($separator)
    <br />
    <h4 class="display-4 mb-0">{{ __($separator) }}</h4>
    <hr />
@endisset
<div class="form-group{{ $errors->has($id) ? ' has-danger' : '' }}  @isset($class) {{ $class }} @endisset">
    <label class="form-control-label" for="{{ $id }}">{{ __($name) }}</label>
    <textarea @isset($dir) dir="{{ $dir }}" @endisset @isset($spellcheck) spellcheck="{{ $spellcheck }}" @endisset class="form-control form-control-alternative @isset($editclass) {{ $editclass }} @endisset {{ $errors->has($id) ? ' is-invalid' : '' }}" name="{{ $id }}" id="{{ $id }}"  rows="{{ isset($rows) ? $rows : 4 }}" cols="50">{{ old($id, isset($value)?$value:'') }}</textarea>
    @if ($errors->has($id))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first($id) }}</strong>
        </span>
    @endif
    @isset($additionalInfo)
        <small class="text-muted"><strong>{{ __($additionalInfo) }}</strong></small>
    @endisset
</div>

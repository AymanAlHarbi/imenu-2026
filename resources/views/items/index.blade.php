@extends('layouts.app', ['title' => __('Restaurant Menu Management')])
@section('admin_title')
    {{__('Menu')}}
@endsection
@section('content')
    @include('items.partials.modals', ['restorant_id' => $restorant_id])
    
    <div class="header bg-gradient-primary pb-7 pt-5 pt-md-8">
        <div class="container-fluid">
            <div class="header-body">
            <div class="row align-items-center py-4">
                <!--<div class="col-lg-6 col-7">
                </div>-->
                <div class="col-lg-12 col-12 text-right">
                    @if (isset($hasMenuPDf)&&$hasMenuPDf)
                        <a target="_blank" href="{{ route('menupdf.download')}}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> {{ __('PDF Menu') }}</a>
                    @endif
                    <button class="btn btn-icon btn-1 btn-sm btn-info" type="button" data-toggle="modal" data-target="#modal-items-category" data-toggle="tooltip" data-placement="top" title="{{ __('Add new category')}}">
                        <span class="btn-inner--icon"><i class="fa fa-plus"></i> {{ __('Add new category') }}</span>
                    </button>
                    @if($canAdd)
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-import-items" onClick=(setRestaurantId({{ $restorant_id }}))>
                        <span class="btn-inner--icon"><i class="fa fa-file-excel"></i> {{ __('Import from CSV') }}</span>
                    </button>
                    @endif
                    @if(config('settings.enable_miltilanguage_menus'))
                        @include('items.partials.languages')
                    @endif
                </div>
            </div>
            </div>
        </div>
    </div>
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col-xl-12 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-0">{{ __('Restaurant Menu Management') }} @if(config('settings.enable_miltilanguage_menus')) ({{ $currentLanguage}}) @endif</h3>
                                    </div>
                                    <div class="col-auto">
                                        <!--<button class="btn btn-icon btn-1 btn-sm btn-primary" type="button" data-toggle="modal" data-target="#modal-items-category" data-toggle="tooltip" data-placement="top" title="{{ __('Add new category')}}">
                                            <span class="btn-inner--icon"><i class="fa fa-plus"></i> {{ __('Add new category') }}</span>
                                        </button>
                                        @if($canAdd)
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-import-items" onClick=(setRestaurantId({{ $restorant_id }}))>
                                                <span class="btn-inner--icon"><i class="fa fa-file-excel"></i> {{ __('Import from CSV') }}</span>
                                            </button>
                                        @endif
                                        @if(config('settings.enable_miltilanguage_menus'))
                                            @include('items.partials.languages')
                                        @endif-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br/>
                    <div class="col-12">
                        @include('partials.flash')
                    </div>
                    <div class="card-body">
                        @if(count($categories)==0)
                            <div class="col-lg-3" >
                                <a  data-toggle="modal" data-target="#modal-items-category" data-toggle="tooltip" data-placement="top" title="{{ __('Add new category')}}">
                                    <div class="card">
                                        <img class="card-img-top" src="{{ asset('images') }}/default/add_new_item.jpg" alt="...">
                                        <div class="card-body">
                                            <h3 class="card-title text-primary text-uppercase">{{ __('Add first category') }}</h3> 
                                        </div>
                                    </div>
                                </a>
                                <br />
                            </div>
                        @endif
                       
                        @foreach ($categories as $index => $category)
                        
                        <div class="alert alert-default category-container">
                            <div class="row">
                                <div class="col">
                                    <span class="h1 font-weight-bold mb-0 text-white">{{ $category->name }}
                                        @if($category->active == 0)
                                            <span class="badge badge-neutral ml-2">{{ __('Paused') }}</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="row">
                                        <script>
                                            function setSelectedCategoryId(id){
                                                $('#category_id').val(id);
                                            }

                                            function setRestaurantId(id){
                                                $('#res_id').val(id);
                                            }

                                        </script>
                                        @if($canAdd)
                                            <button class="btn btn-icon btn-1 btn-sm btn-primary" type="button" data-toggle="modal" data-target="#modal-new-item" data-toggle="tooltip" data-placement="top" title="{{ __('Add item in') }} {{$category->name}}" onClick=(setSelectedCategoryId({{ $category->id }})) >
                                                <span class="btn-inner--icon"><i class="fa fa-plus"></i></span>
                                            </button>
                                        @else
                                            <a href="{{ route('plans.current')}}" class="btn btn-icon btn-1 btn-sm btn-warning" type="button"  >
                                                <span class="btn-inner--icon"><i class="fa fa-plus"></i> {{ __('Menu size limit reaced') }}</span>
                                            </a>
                                        @endif
                                        <button class="btn btn-icon btn-1 btn-sm btn-warning" type="button" id="edit" data-toggle="modal" data-target="#modal-edit-category" data-toggle="tooltip" data-placement="top" title="{{ __('Edit category') }} {{ $category->name }}" data-id="<?= $category->id ?>" data-name="<?= $category->name ?>" >
                                            <span class="btn-inner--icon"><i class="fa fa-edit"></i></span>
                                        </button>

                                        <!-- If category is active, show the pause button -->
                                        @if($category->active == 1)
                                            <a href="{{ route('categories.pause', $category->id) }}" class="btn btn-icon btn-1 btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="{{ __('Pause category') }} {{ $category->name }}">
                                                <span class="btn-inner--icon"><i class="fa fa-pause"></i></span>
                                            </a>
                                        @endif

                                        <!-- If category is paused, show the resume button -->
                                        @if($category->active == 0)
                                            <a href="{{ route('categories.resume', $category->id) }}" class="btn btn-icon btn-1 btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="{{ __('Resume category') }} {{ $category->name }}">
                                                <span class="btn-inner--icon"><i class="fa fa-play"></i></span>
                                            </a>
                                        @endif

                                       

                                        <form action="{{ route('categories.destroy', $category) }}" method="post">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-icon btn-1 btn-sm btn-danger" type="button" onclick="confirm('{{ __("Are you sure you want to delete this category?") }}') ? this.parentElement.submit() : ''" data-toggle="tooltip" data-placement="top" title="{{ __('Delete') }} {{$category->name}}">
                                                <span class="btn-inner--icon"><i class="fa fa-trash"></i></span>
                                            </button>
                                        </form>

                                        @if(count($categories)>1)
                                            <div style="margin-left: 10px; margin-right: 10px">|</div>
                                        @endif

                                         <!-- UP -->
                                         @if ($index!=0)
                                            <a href="{{ route('items.reorder',['up'=>$category->id]) }}"  class="btn btn-icon btn-1 btn-sm btn-success" >
                                                <span class="btn-inner--icon"><i class="fas fa-arrow-up"></i></span>
                                            </a>
                                         @endif
                                         

                                        <!-- DOWN -->
                                        @if ($index+1!=count($categories))
                                            <a href="{{ route('items.reorder',['up'=>$categories[$index+1]->id]) }}" class="btn btn-icon btn-1 btn-sm btn-success">
                                                <span class="btn-inner--icon"><i class="fas fa-arrow-down"></i></span>
                                            </a>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                       
                        
                        <div class="row justify-content-center">
                            <div class="col-lg-12">
                                <div class="row row-grid category-items">
                                    @foreach ( $category->items as $item)
                                        <div class="col-lg-3 item" data-item-id="{{ $item->id }}">
                                            <a href="{{ route('items.edit', $item) }}">
                                                <div class="card">
                                                    <img class="card-img-top" src="{{ $item->logom }}" alt="...">
                                                    <div class="card-body">
                                                        <h3 class="card-title text-primary text-uppercase">{{ $item->name }}</h3>
                                                        <p class="card-text description mt-3">{{ $item->description }}</p>

                                                        <div class="d-flex justify-content-between align-items-end">
                                                            <div>
                                                                <span class="badge badge-primary badge-pill">@money($item->price, config('settings.cashier_currency'),config('settings.do_convertion'))</span>
                                                        
                                                                <p class="mt-3 mb-0 text-sm">
                                                                    @if($item->available == 1)
                                                                    <span class="text-success mr-2">{{ __("AVAILABLE") }}</span>
                                                                    @else
                                                                    <span class="text-danger mr-2">{{ __("UNAVAILABLE") }}</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                            <div class="item-move-btns">
                                                                <button type="button" class="btn-move-item" data-dir="up" title="تحريك لأعلى"><i class="fas fa-arrow-up"></i></button>
                                                                <button type="button" class="btn-move-item" data-dir="down" title="تحريك لأسفل"><i class="fas fa-arrow-down"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br/>
                                            </a>
                                        </div>
                                    @endforeach
                                    @if($canAdd)
                                    <div class="col-lg-3" >
                                        <a   data-toggle="modal" data-target="#modal-new-item" data-toggle="tooltip" data-placement="top" href="javascript:void(0);" onclick=(setSelectedCategoryId({{ $category->id }}))>
                                            <div class="card">
                                                <img class="card-img-top" src="{{ asset('images') }}/default/add_new_item.jpg" alt="...">
                                                <div class="card-body">
                                                    <h3 class="card-title text-primary text-uppercase">{{ __('Add item') }}</h3>
                                                </div>
                                            </div>
                                        </a>
                                        <br />
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
  $("[data-target='#modal-edit-category']").on('click',function() {
    var id = $(this).attr('data-id');
    var name = $(this).attr('data-name');


    
    $('#cat_name').val(name);
    $("#form-edit-category").attr("action", "/categories/"+id);
})
</script>
<style>
.item-move-btns{display:flex;gap:6px}
.btn-move-item{width:34px;height:34px;padding:0;border:none;border-radius:8px;background:#2dce89;color:#fff;font-size:14px;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(45,206,137,.4);cursor:pointer}
.btn-move-item:active{transform:scale(.92)}
#order-toast{position:fixed;bottom:18px;left:50%;transform:translateX(-50%) translateY(80px);background:#2dce89;color:#fff;font-weight:600;padding:10px 22px;border-radius:30px;box-shadow:0 6px 18px rgba(45,206,137,.5);transition:transform .3s ease;z-index:9999;pointer-events:none}
#order-toast.show{transform:translateX(-50%) translateY(0)}
.item-card-flash{box-shadow:0 0 0 3px rgba(45,206,137,.45)!important}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // حفظ ترتيب منتجات قسم معيّن في السيرفر
    function saveOrder(container){
        var items = Array.from(container.querySelectorAll('.item'))
            .map(function(el){ return el.dataset.itemId; })
            .filter(function(id){ return id != null; });

        fetch('{{ route('items.reorderitems') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ items: items })
        })
        .then(function(r){ return r.json(); })
        .then(function(data){ if(data.success){ showToast(); } })
        .catch(function(err){ console.error('Error:', err); });
    }

    // رسالة "تم حفظ الترتيب"
    var toast = document.createElement('div');
    toast.id = 'order-toast';
    toast.textContent = '✓ تم حفظ الترتيب';
    document.body.appendChild(toast);
    var toastTimer;
    function showToast(){
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function(){ toast.classList.remove('show'); }, 1600);
    }

    // السحب والإفلات — مع ضغطة مطوّلة على اللمس حتى لا يعلّق تمرير الصفحة
    document.querySelectorAll('.category-items').forEach(function(container){
        new Sortable(container, {
            animation: 150,
            delay: 300,
            delayOnTouchOnly: true,
            onEnd: function(evt){ saveOrder(evt.to); }
        });
    });

    // أزرار التحريك لأعلى/لأسفل
    document.addEventListener('click', function(e){
        var btn = e.target.closest('.btn-move-item');
        if(!btn) return;
        e.preventDefault();
        e.stopPropagation();

        var itemEl = btn.closest('.item');
        var container = btn.closest('.category-items');

        if(btn.dataset.dir === 'up'){
            var prev = itemEl.previousElementSibling;
            if(!prev || !prev.classList.contains('item')) return;
            container.insertBefore(itemEl, prev);
        } else {
            var next = itemEl.nextElementSibling;
            if(!next || !next.classList.contains('item')) return;
            container.insertBefore(next, itemEl);
        }

        var card = itemEl.querySelector('.card');
        if(card){
            card.classList.add('item-card-flash');
            setTimeout(function(){ card.classList.remove('item-card-flash'); }, 700);
        }
        itemEl.scrollIntoView({behavior:'smooth', block:'nearest'});

        saveOrder(container);
    });
});
</script>
@endsection
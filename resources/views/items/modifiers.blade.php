{{-- iMenu 2026 — مجموعات الخيارات بفروق أسعار جمعية.
     تُخزَّن JSON في configs على الصنف — بلا هجرة قاعدة بيانات. --}}
@php $imenuGroups = \App\Services\Modifiers::groups($item); @endphp

<style>
.mg-card{border:1px solid #E6DCCC;border-radius:14px;padding:14px;margin-bottom:12px;background:#FAF6EF}
.mg-head{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end}
.mg-head > div{flex:1 1 150px}
.mg-opt{display:flex;gap:8px;align-items:center;margin-top:6px}
.mg-opt input[type=text]{flex:1 1 auto}
.mg-opt input[type=number]{width:110px}
.mg-lbl{display:block;font-size:.8rem;color:#5A7590;margin-bottom:3px;font-weight:600}
.mg-x{border:0;background:transparent;color:#C0392B;font-size:18px;line-height:1;cursor:pointer;padding:0 6px}
.mg-limits{display:none;gap:8px}
.mg-limits.on{display:flex}
</style>

<div class="card card-profile shadow">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-8">
                <h5 class="h3 mb-0">{{ __('Option groups') }}</h5>
                <small class="text-muted">{{ __('Size, coffee type, add-ons — with a price difference for each choice') }}</small>
            </div>
            <div class="col-4 text-right">
                <button type="button" class="btn btn-sm btn-primary" onclick="mgAddGroup()">{{ __('Add group') }}</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('items.modifiers.store', $item) }}">
            @csrf
            <div id="mg-list"></div>

            {{-- حالة فارغة: البطاقة كانت زر «حفظ» وحده وسط الخلاء --}}
            <div id="mg-empty" class="text-center py-4" style="display:none">
                <div style="font-size:26px;line-height:1">&#9776;</div>
                <div class="mt-2" style="font-weight:600;color:#17324E">{{ __('No option groups yet') }}</div>
                <div class="text-muted" style="font-size:.85rem">{{ __('Add a group such as Size or Coffee type') }}</div>
                <button type="button" class="btn btn-sm btn-primary mt-3" onclick="mgAddGroup()">{{ __('Add group') }}</button>
            </div>

            <div id="mg-save" class="text-center mt-3" style="display:none">
                <button type="submit" class="btn btn-primary">{{ __('Save option groups') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
var mgData = @json($imenuGroups);
var mgMaxGroups = {{ \App\Services\Modifiers::MAX_GROUPS }};
var mgT = {
  groupName: @json(__('Group name')),
  required:  @json(__('Mandatory')),
  multiple:  @json(__('Multiple choice')),
  min:       @json(__('Minimum')),
  max:       @json(__('Maximum, 0 = no limit')),
  option:    @json(__('Choice')),
  delta:     @json(__('Price difference')),
  addOption: @json(__('Add choice')),
  removeG:   @json(__('Remove group')),
  hint:      @json(__('Example: Size — Small +0 · Large +3')),
};

function mgEl(html){ var d=document.createElement('div'); d.innerHTML=html.trim(); return d.firstChild; }

function mgOptionRow(gi, oi, opt){
  opt = opt || {name:'', delta:0};
  return mgEl(
    '<div class="mg-opt">'+
      '<input type="text" class="form-control form-control-sm" name="groups['+gi+'][options]['+oi+'][name]" placeholder="'+mgT.option+'" value="'+(opt.name||'').replace(/"/g,'&quot;')+'">'+
      '<input type="number" step="0.01" class="form-control form-control-sm" name="groups['+gi+'][options]['+oi+'][delta]" placeholder="'+mgT.delta+'" value="'+(opt.delta||0)+'">'+
      '<button type="button" class="mg-x" onclick="this.parentNode.remove()">&times;</button>'+
    '</div>');
}

function mgGroupCard(gi, g){
  g = g || {name:'', required:false, multiple:false, min:0, max:1, options:[{name:'',delta:0}]};
  var card = mgEl(
    '<div class="mg-card" data-gi="'+gi+'">'+
      '<div class="mg-head">'+
        '<div><span class="mg-lbl">'+mgT.groupName+'</span>'+
          '<input type="text" class="form-control form-control-sm" name="groups['+gi+'][name]" value="'+(g.name||'').replace(/"/g,'&quot;')+'" placeholder="'+mgT.groupName+'"></div>'+
        '<div style="flex:0 0 auto"><span class="mg-lbl">&nbsp;</span>'+
          '<label class="mb-0"><input type="checkbox" name="groups['+gi+'][required]" value="1" '+(g.required?'checked':'')+'> '+mgT.required+'</label></div>'+
        '<div style="flex:0 0 auto"><span class="mg-lbl">&nbsp;</span>'+
          '<label class="mb-0"><input type="checkbox" class="mg-multi" name="groups['+gi+'][multiple]" value="1" '+(g.multiple?'checked':'')+'> '+mgT.multiple+'</label></div>'+
        '<div class="mg-limits '+(g.multiple?'on':'')+'" style="flex:0 0 auto">'+
          '<div><span class="mg-lbl">'+mgT.min+'</span><input type="number" min="0" class="form-control form-control-sm" style="width:90px" name="groups['+gi+'][min]" value="'+(g.min||0)+'"></div>'+
          '<div><span class="mg-lbl">'+mgT.max+'</span><input type="number" min="0" class="form-control form-control-sm" style="width:110px" name="groups['+gi+'][max]" value="'+(g.max||0)+'"></div>'+
        '</div>'+
        '<div style="flex:0 0 auto"><span class="mg-lbl">&nbsp;</span>'+
          '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.mg-card\').remove(); mgSyncEmpty();">'+mgT.removeG+'</button></div>'+
      '</div>'+
      '<div class="mg-options mt-2"></div>'+
      '<button type="button" class="btn btn-sm btn-secondary mt-2">'+mgT.addOption+'</button>'+
      '<div class="text-muted mt-1" style="font-size:.78rem">'+mgT.hint+'</div>'+
    '</div>');

  var optionsBox = card.querySelector('.mg-options');
  (g.options && g.options.length ? g.options : [{name:'',delta:0}]).forEach(function(o, oi){
    optionsBox.appendChild(mgOptionRow(gi, oi, o));
  });

  card.querySelector('.mg-multi').addEventListener('change', function(){
    card.querySelector('.mg-limits').classList.toggle('on', this.checked);
  });

  card.querySelector('button.btn-secondary.mt-2').addEventListener('click', function(){
    optionsBox.appendChild(mgOptionRow(gi, optionsBox.children.length + Date.now() % 1000, null));
  });

  return card;
}

/* البطاقة تعرض حالتها: إما مجموعات وزر حفظ، أو دعوة للإضافة — لا كلاهما */
function mgSyncEmpty(){
  var list = document.getElementById('mg-list');
  if (!list) { return; }
  var has = list.children.length > 0;
  document.getElementById('mg-empty').style.display = has ? 'none' : 'block';
  document.getElementById('mg-save').style.display  = has ? 'block' : 'none';
}

function mgAddGroup(){
  var list = document.getElementById('mg-list');
  if (list.children.length >= mgMaxGroups) { return; }
  list.appendChild(mgGroupCard(list.children.length + Date.now() % 1000, null));
  mgSyncEmpty();
}

(function mgInit(){
  var list = document.getElementById('mg-list');
  if (!list) { return; }
  mgData.forEach(function(g, gi){ list.appendChild(mgGroupCard(gi, g)); });
  mgSyncEmpty();
})();
</script>

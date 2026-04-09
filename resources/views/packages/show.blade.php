@extends('layouts.app')
@section('title', $package->name)
@section('breadcrumb', 'Пакеты')
@section('breadcrumb_current', $package->name)

@section('header_actions')
@can('update', $package)
<a href="{{ route('packages.edit', $package) }}" class="btn btn-secondary">Изменить</a>
@endcan
@can('delete', $package)
<form method="POST" action="{{ route('packages.destroy', $package) }}" onsubmit="return confirm('Удалить {{ $package->name }} и все его версии?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger">Удалить</button>
</form>
@endcan
@endsection

@section('content')
@php
    $canReportPackage = !auth()->user()->isGhost()
        && !auth()->user()->isAdmin()
        && !auth()->user()->isCreator()
        && !$package->user->isCreator()
        && auth()->id() !== $package->user_id
        && $package->is_active;

    $alreadyReportedPackage = $canReportPackage
        ? \App\Models\PackageReport::where('package_id', $package->id)->where('reporter_id', auth()->id())->exists()
        : false;
@endphp

<div class="box" style="margin-bottom:16px;">
    <div class="box-body" style="display:flex; align-items:flex-start; gap:16px;">
        <div style="width:48px; height:48px; background:#21262d; border:1px solid #30363d; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="24" height="24" viewBox="0 0 16 16" fill="currentColor" style="color:#8b949e;"><path d="m8.878.392 5.25 3.045c.54.314.872.89.872 1.514v6.098a1.75 1.75 0 0 1-.872 1.514l-5.25 3.045a1.75 1.75 0 0 1-1.756 0l-5.25-3.045A1.75 1.75 0 0 1 1 11.049V4.951c0-.624.332-1.2.872-1.514L7.122.392a1.75 1.75 0 0 1 1.756 0ZM7.875 1.69l-4.63 2.685L8 7.133l4.755-2.758-4.63-2.685a.248.248 0 0 0-.25 0ZM2.5 5.677v5.372c0 .096.052.185.132.232l4.868 2.832V8.913Zm6.5 8.436 4.868-2.832a.269.269 0 0 0 .132-.232V5.677L9 8.913Z"/></svg>
        </div>
        <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px;">
                <h1 style="font-size:20px; font-weight:600; color:#e6edf3;">{{ $package->name }}</h1>
                <span class="label {{ $package->is_active ? 'label-success' : 'label-default' }}">{{ $package->is_active ? 'Активен' : 'Неактивен' }}</span>
                @if($package->channel)<span class="label label-default mono">{{ $package->channel->name }}</span>@endif
            </div>
            <p style="font-size:14px; color:#8b949e; margin-bottom:8px;">{{ $package->description ?? 'Описание не указано.' }}</p>
            <div style="display:flex; gap:16px; font-size:12px; color:#6e7681; flex-wrap:wrap;">
                <span>Владелец: <strong style="color:#8b949e;">{{ $package->user->name }}</strong></span>
                <span>Slug: <code class="mono" style="color:#8b949e;">{{ $package->slug }}</code></span>
                <span>Создан {{ $package->created_at->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>
</div>

@can('update', $package)
<div class="box" style="margin-bottom:16px;">
    <div class="box-header"><h3>Добавить версию</h3></div>
    <div class="box-body">
        <form method="POST" action="{{ route('packages.versions.store', $package) }}" style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
            @csrf
            <div class="form-group" style="margin:0; flex-shrink:0;">
                <label class="form-label" style="font-size:12px;">Номер версии</label>
                <input type="text" name="version" placeholder="1.0.0" required pattern="^\d+\.\d+\.\d+.*$"
                    class="form-input" style="width:120px;" value="{{ old('version') }}">
            </div>
            <div class="form-group" style="margin:0; flex:1; min-width:200px;">
                <label class="form-label" style="font-size:12px;">Список изменений</label>
                <input type="text" name="changelog" placeholder="Что изменилось в этой версии?" value="{{ old('changelog') }}"
                    class="form-input">
            </div>
            <button type="submit" class="btn btn-primary" style="flex-shrink:0;">Добавить версию</button>
        </form>
    </div>
</div>
@endcan

@forelse($package->versions->sortByDesc('created_at') as $version)
<div class="box" style="margin-bottom:16px;">
    <div class="box-header">
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#8b949e;"><path d="M1 7.775V2.75C1 1.784 1.784 1 2.75 1h5.025c.464 0 .91.184 1.238.513l6.25 6.25a1.75 1.75 0 0 1 0 2.474l-5.026 5.026a1.75 1.75 0 0 1-2.474 0l-6.25-6.25A1.752 1.752 0 0 1 1 7.775Zm1.5 0c0 .066.026.13.073.177l6.25 6.25a.25.25 0 0 0 .354 0l5.025-5.025a.25.25 0 0 0 0-.354l-6.25-6.25a.25.25 0 0 0-.177-.073H2.75a.25.25 0 0 0-.25.25ZM6 5a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>
            <span style="font-size:14px; font-weight:600; color:#e6edf3; font-family:monospace;">v{{ $version->version }}</span>
            @if($version->is_published)<span class="label label-success">Опубликована</span>@endif
            @if($version->is_revoked)<span class="label label-danger">Отозвана</span>@endif
            @if(!$version->is_published && !$version->is_revoked)<span class="label label-default">Черновик</span>@endif
            @if($version->changelog)
            <span style="font-size:13px; color:#8b949e;">— {{ $version->changelog }}</span>
            @endif
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:12px; color:#6e7681;">{{ $version->created_at->format('d.m.Y') }}</span>
            @can('update', $package)
            <form method="POST" action="{{ route('builds.store', $version) }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm3.82 2.03 3-3a.75.75 0 0 1 1.06 0l1.25 1.25a.75.75 0 0 1-1.06 1.06L9 8.81l-2.47 2.47a.75.75 0 0 1-1.21-.28Z"/></svg>
                    Запустить сборку
                </button>
            </form>
            @endcan
        </div>
    </div>

    @can('update', $package)
    <div style="padding:16px; border-bottom:1px solid #21262d;">
        <div style="font-size:12px; font-weight:600; color:#8b949e; text-transform:uppercase; letter-spacing:.04em; margin-bottom:12px;">Загрузить файлы</div>
        <form method="POST" action="{{ route('versions.files.upload', $version) }}" enctype="multipart/form-data" id="form-{{ $version->id }}">
            @csrf
            <div class="dropzone" id="dz-{{ $version->id }}"
                ondrop="handleDrop(event,{{ $version->id }})"
                ondragover="event.preventDefault();this.classList.add('active')"
                ondragleave="this.classList.remove('active')">
                <svg width="24" height="24" viewBox="0 0 16 16" fill="currentColor" style="color:#6e7681; margin-bottom:8px;"><path d="M2.341 3.493A2.5 2.5 0 0 1 4.5 2.5h.03A2.5 2.5 0 0 1 7 4.5V5h2v-.5a2.5 2.5 0 0 1 2.47-2.499h.03a2.5 2.5 0 0 1 2.5 2.5v.03a2.5 2.5 0 0 1-1.787 2.394l-.003.001-2.715.905V9.5H9v-.5A2.5 2.5 0 0 1 6.53 6.503h-.03A2.5 2.5 0 0 1 4 4V2.5ZM3 16.5v-5h1.5v5ZM11.5 11.5v5H13v-5ZM8 9v7h1.5V9Z"/></svg>
                <p style="font-size:13px; color:#8b949e; margin-bottom:8px;">Перетащите файлы сюда или <button type="button" onclick="document.getElementById('fi-{{ $version->id }}').click()" style="background:none; border:none; color:#58a6ff; cursor:pointer; font-size:13px; padding:0;">выберите файлы</button></p>
                <input type="file" name="files[]" multiple class="form-input" id="fi-{{ $version->id }}" style="display:none;" onchange="showFiles(this,{{ $version->id }})">
            </div>
            <div id="fl-{{ $version->id }}" style="margin-top:8px;"></div>
            <button type="button" id="ub-{{ $version->id }}" class="btn btn-primary btn-sm" style="display:none; margin-top:8px;"
                onclick="uploadFiles({{ $version->id }})">Загрузить файлы</button>
            <div id="prog-{{ $version->id }}" style="display:none; margin-top:10px;">
                <div style="height:4px; background:#21262d; border-radius:2px; overflow:hidden;">
                    <div id="bar-{{ $version->id }}" style="height:100%; background:#238636; width:0%; transition:width .2s;"></div>
                </div>
                <div id="pct-{{ $version->id }}" style="font-size:12px; color:#8b949e; margin-top:4px;">Загрузка...</div>
            </div>
        </form>
    </div>
    @endcan

    @if($version->files->count() > 0)
    <div style="padding:16px;">
        <div style="font-size:12px; font-weight:600; color:#8b949e; text-transform:uppercase; letter-spacing:.04em; margin-bottom:12px;">Файлы ({{ $version->files->count() }})</div>
        <table class="gh-table">
            <thead><tr><th>Файл</th><th>Размер</th><th>SHA-256</th><th>Проверка</th><th></th></tr></thead>
            <tbody>
                @foreach($version->files as $file)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="color:#6e7681; flex-shrink:0;"><path d="M2 1.75C2 .784 2.784 0 3.75 0h6.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v9.586A1.75 1.75 0 0 1 13.25 16h-9.5A1.75 1.75 0 0 1 2 14.25Zm1.75-.25a.25.25 0 0 0-.25.25v12.5c0 .138.112.25.25.25h9.5a.25.25 0 0 0 .25-.25V6h-2.75A1.75 1.75 0 0 1 9 4.25V1.5Zm6.75.062V4.25c0 .138.112.25.25.25h2.688Z"/></svg>
                            <span class="mono">{{ $file->original_name }}</span>
                        </div>
                    </td>
                    <td style="color:#8b949e; font-size:12px;">{{ $file->size ? number_format($file->size/1024,1).' КБ' : '—' }}</td>
                    <td><code class="mono" style="color:#8b949e; font-size:11px;">{{ substr($file->sha256 ?? 'ожидание',0,16) }}…</code></td>
                    <td>
                        @if($file->verification_status->value==='valid')<span class="label label-success">Проверено</span>
                        @elseif($file->verification_status->value==='invalid')<span class="label label-danger">Ошибка</span>
                        @else<span class="label label-warning">Ожидание</span>@endif
                    </td>
                    <td>
                        @can('update', $package)
                        <form method="POST" action="{{ route('files.destroy', $file) }}" onsubmit="return confirm('Удалить этот файл?')" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($version->builds->count() > 0)
    <div style="padding:16px; border-top:1px solid #21262d;">
        <div style="font-size:12px; font-weight:600; color:#8b949e; text-transform:uppercase; letter-spacing:.04em; margin-bottom:12px;">История сборок</div>
        @foreach($version->builds->sortByDesc('created_at')->take(3) as $build)
        <a href="{{ route('builds.show', $build) }}" style="display:flex; align-items:center; gap:10px; padding:6px 0; text-decoration:none; border-bottom:1px solid #21262d; transition:opacity .1s;" onmouseenter="this.style.opacity='.75'" onmouseleave="this.style.opacity='1'">
            <span style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:{{ match($build->status->value) {'completed'=>'#3fb950','failed'=>'#f85149','processing'=>'#d29922',default=>'#6e7681'} }};"></span>
            <span style="font-size:13px; color:#8b949e; font-family:monospace;">{{ substr($build->uuid,0,8) }}</span>
            <span class="label {{ match($build->status->value) {'completed'=>'label-success','failed'=>'label-danger','processing'=>'label-warning',default=>'label-default'} }}">
                {{ match($build->status->value) {'completed'=>'Завершено','failed'=>'Ошибка','processing'=>'Выполняется',default=>'В очереди'} }}
            </span>
            <span style="font-size:12px; color:#6e7681; margin-left:auto;">{{ $build->created_at->diffForHumans() }}</span>
        </a>
        @endforeach
    </div>
    @endif
</div>
@empty
<div class="box" style="padding:48px; text-align:center; color:#6e7681;">
    <svg width="32" height="32" viewBox="0 0 16 16" fill="currentColor" style="margin-bottom:12px; color:#30363d;"><path d="M1 7.775V2.75C1 1.784 1.784 1 2.75 1h5.025c.464 0 .91.184 1.238.513l6.25 6.25a1.75 1.75 0 0 1 0 2.474l-5.026 5.026a1.75 1.75 0 0 1-2.474 0l-6.25-6.25A1.752 1.752 0 0 1 1 7.775Z"/></svg>
    <p>Версий пока нет. Используйте форму выше, чтобы добавить первую версию.</p>
</div>
@endforelse

<script>
function handleDrop(e,id){e.preventDefault();document.getElementById('dz-'+id).classList.remove('active');var inp=document.getElementById('fi-'+id);inp.files=e.dataTransfer.files;showFiles(inp,id);}

function showFiles(inp,id){
    var list=document.getElementById('fl-'+id),btn=document.getElementById('ub-'+id);
    list.innerHTML='';
    if(!inp.files.length){btn.style.display='none';return;}
    var html='<div style="display:flex;flex-direction:column;gap:4px;">';
    var totalSize=0;
    for(var i=0;i<inp.files.length;i++){
        totalSize+=inp.files[i].size;
        html+='<div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#8b949e;"><svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="color:#3fb950;flex-shrink:0;"><path d="M2 1.75C2 .784 2.784 0 3.75 0h6.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v9.586A1.75 1.75 0 0 1 13.25 16h-9.5A1.75 1.75 0 0 1 2 14.25V1.75Z"/></svg><span class="mono">'+inp.files[i].name+'</span><span style="color:#6e7681;">'+(inp.files[i].size/1048576).toFixed(2)+' МБ</span></div>';
    }
    list.innerHTML=html+'</div>';
    btn.style.display='inline-flex';
}

var _uploading=false;
window.addEventListener('beforeunload',function(e){
    if(_uploading){e.preventDefault();e.returnValue='Идёт загрузка файлов. Если вы уйдёте, загрузка прервётся.';}
});

function uploadFiles(id){
    var form=document.getElementById('form-'+id);
    var btn=document.getElementById('ub-'+id);
    var prog=document.getElementById('prog-'+id);
    var bar=document.getElementById('bar-'+id);
    var pct=document.getElementById('pct-'+id);

    var fd=new FormData(form);
    var xhr=new XMLHttpRequest();

    xhr.upload.onprogress=function(e){
        if(e.lengthComputable){
            var p=Math.round(e.loaded/e.total*100);
            bar.style.width=p+'%';
            var loaded=(e.loaded/1048576).toFixed(1);
            var total=(e.total/1048576).toFixed(1);
            pct.textContent='Загрузка: '+p+'% ('+loaded+' / '+total+' МБ)';
        }
    };

    xhr.onload=function(){
        _uploading=false;
        if(xhr.status<400){
            pct.textContent='Обработка...';
            bar.style.background='#3fb950';
            window.location.href=xhr.responseURL||window.location.pathname;
        } else {
            pct.textContent='Ошибка при загрузке ('+xhr.status+').';
            bar.style.background='#da3633';
            btn.disabled=false;
            btn.textContent='Загрузить файлы';
        }
    };

    xhr.onerror=function(){
        _uploading=false;
        pct.textContent='Ошибка соединения.';
        bar.style.background='#da3633';
        btn.disabled=false;
        btn.textContent='Загрузить файлы';
    };

    xhr.open('POST',form.action);
    xhr.send(fd);

    _uploading=true;
    btn.disabled=true;
    btn.textContent='Загрузка...';
    prog.style.display='block';
    bar.style.width='0%';
}
</script>

@if($canReportPackage)
<div class="box" style="margin-top:16px;">
    <div class="box-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="#d29922"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM8 5.25a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-1.5 0V6A.75.75 0 0 1 8 5.25Z"/></svg>
            Пожаловаться на пакет
        </h3>
    </div>
    <div style="padding:16px;">
        @if($alreadyReportedPackage)
        <p style="font-size:13px; color:#8b949e; margin:0;">Вы уже отправили жалобу на этот пакет. Ожидайте рассмотрения администратором.</p>
        @else
        <form method="POST" action="{{ route('packages.report', $package) }}">
            @csrf
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label" for="pkg_report_reason">Причина жалобы</label>
                <textarea id="pkg_report_reason" name="reason" rows="3" class="form-textarea" required
                    placeholder="Опишите, что именно нарушает правила платформы..."></textarea>
            </div>
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Отправить жалобу администратору?')">
                Отправить жалобу
            </button>
        </form>
        @endif
    </div>
</div>
@endif
@endsection

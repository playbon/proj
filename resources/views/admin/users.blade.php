@extends('layouts.app')
@section('title', 'Пользователи')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Пользователи')

@section('content')
<div class="box">
    <div class="box-header">
        <h3>Зарегистрированные пользователи</h3>
        <span class="label label-default">{{ $users->total() }} всего</span>
    </div>
    <table class="gh-table">
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Зарегистрирован</th>
                <th>Последняя активность</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            @php $role = $user->role->value; $isOnline = in_array($user->id, $onlineIds); @endphp
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="position:relative; width:32px; height:32px; flex-shrink:0;">
                            <div style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700;
                                {{ $role==='creator' ? 'background:rgba(188,140,255,.12); color:#bc8cff;' : ($role==='admin' ? 'background:rgba(248,81,73,.12); color:#f85149;' : ($role==='ghost' ? 'background:#0d1117; color:#3d444d; border:1px solid #21262d;' : 'background:#21262d; color:#8b949e;')) }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            @if($isOnline)
                            <span style="position:absolute; bottom:0; right:0; width:9px; height:9px; border-radius:50%; background:#3fb950; border:2px solid #161b22;"></span>
                            @endif
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:600; color:#e6edf3;">{{ $user->name }}</div>
                            @if($user->id === auth()->id())
                            <span class="label label-success" style="font-size:10px; padding:0 5px; line-height:16px;">вы</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="font-size:13px; color:#8b949e;">{{ $user->email }}</td>
                <td>
                    <span class="label {{ match($role) {'creator'=>'label-creator','admin'=>'label-danger','publisher'=>'label-info','ghost'=>'label-default',default=>'label-default'} }}">
                        {{ match($role) {'creator'=>'Создатель','admin'=>'Администратор','publisher'=>'Публикатор','ghost'=>'Призрак',default=>'Наблюдатель'} }}
                    </span>
                </td>
                <td style="font-size:12px; color:#8b949e;">{{ $user->created_at->format('d.m.Y') }}</td>
                <td style="font-size:12px;">
                    @if($isOnline)
                        <span style="display:flex; align-items:center; gap:5px; color:#3fb950;">
                            <span class="online-dot"></span> Онлайн
                        </span>
                    @elseif($user->last_seen_at)
                        <span style="color:#6e7681;">{{ $user->last_seen_at->diffForHumans() }}</span>
                    @else
                        <span style="color:#3d444d;">—</span>
                    @endif
                </td>
                <td style="text-align:right;">
                    @if($user->id !== auth()->id())
                    @php
                        $canChangeRole = auth()->user()->isCreator()
                            || (!$user->isAdmin() && auth()->user()->isAdmin());
                    @endphp
                    @if($canChangeRole)
                    <form method="POST" action="{{ route('admin.users.role', $user) }}" style="display:flex; align-items:center; gap:6px; justify-content:flex-end;">
                        @csrf @method('PUT')
                        <select name="role" class="form-select" style="width:160px; padding:3px 8px; font-size:12px;" onchange="this.form.submit()">
                            @if(auth()->user()->isCreator())
                            <option value="creator"    {{ $role==='creator'   ? 'selected' : '' }}>Создатель</option>
                            <option value="admin"      {{ $role==='admin'     ? 'selected' : '' }}>Администратор</option>
                            @endif
                            <option value="publisher"  {{ $role==='publisher' ? 'selected' : '' }}>Публикатор</option>
                            <option value="viewer"     {{ $role==='viewer'    ? 'selected' : '' }}>Наблюдатель</option>
                            <option value="ghost"      {{ $role==='ghost'     ? 'selected' : '' }}>Призрак</option>
                        </select>
                    </form>
                    @else
                    <span style="font-size:12px; color:#3d444d;">нет доступа</span>
                    @endif
                    @else
                    <span style="font-size:12px; color:#6e7681;">текущий пользователь</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($users->hasPages())
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid #21262d; font-size:13px; color:#8b949e;">
        <span>Показано {{ $users->firstItem() }}–{{ $users->lastItem() }} из {{ $users->total() }}</span>
        <div style="display:flex; gap:4px;">
            @if(!$users->onFirstPage())<a href="{{ $users->previousPageUrl() }}" class="btn btn-secondary btn-sm">Назад</a>@endif
            @if($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}" class="btn btn-secondary btn-sm">Вперёд</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection

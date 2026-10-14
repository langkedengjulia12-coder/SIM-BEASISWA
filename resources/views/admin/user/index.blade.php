@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Manajemen User</h3>
        <a href="{{ route('admin.user.create') }}" class="inline-flex items-center rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800">+ Tambah Akun</a>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif



        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:60px;">No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th style="width:200px;">Role</th>
                    <th style="width:160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $loop->iteration + ($users->currentPage()-1)*$users->perPage() }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ implode(', ', $user->getRoleNames()->toArray()) ?: '-' }}</td>
                    <td>
                        @php
                            $firstSuperAdmin = \App\Models\User::whereHas('roles', function($q){ $q->where('name','Super Admin'); })->orderBy('id')->first();
                            $firstId = $firstSuperAdmin ? $firstSuperAdmin->id : null;
                            $me = auth()->user();
                            $isSuperTarget = $user->hasRole('Super Admin');
                            $canManage = true;

                            if ($isSuperTarget) {
                                if ($me->id !== $firstId) {
                                    // only first super admin can manage superadmins
                                    $canManage = false;
                                }
                            }
                        @endphp

                        @if($canManage)
                            <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a>
                            @if(! $user->hasRole('Super Admin') || (auth()->user()->id === $firstId))
                                <form action="{{ route('admin.user.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endif
                        @else
                            <button class="btn btn-sm btn-secondary" disabled>--</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
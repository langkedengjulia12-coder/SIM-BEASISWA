@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Ubah Pengguna</h3></div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('admin.user.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label for="password">Password (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
            </div>

            <div class="form-group">
                <label for="roles">Role</label>
                <select name="roles[]" id="roles" class="form-control" multiple required>
                    @foreach($roles as $role)
                    <option value="{{ $role }}" {{ in_array($role, $user->getRoleNames()->toArray()) ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            <button class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
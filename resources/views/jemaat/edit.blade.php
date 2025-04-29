@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <div class="card">
        <h2>Edit Jemaat</h2>
        <form action="{{ route('jemaat.update', $jemaat->id) }}" method="POST" style="width: 100%;">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Username</label>
                <input type="text" name="username" value="{{ $jemaat->username }}" required 
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nama Lengkap</label>
                <input type="text" name="fullname" value="{{ $jemaat->fullname }}" required 
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Tanggal Lahir</label>
                <input type="date" name="dateofbirth" value="{{ $jemaat->dateofbirth }}" required 
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Alamat</label>
                <textarea name="address" required 
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-height: 100px;">{{ $jemaat->address }}</textarea>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Gereja</label>
                <select name="church_id" required 
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Pilih Gereja</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ $jemaat->church_id == $church->id ? 'selected' : '' }}>
                            {{ $church->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="button-detail">Update</button>
        </form>
    </div>
</div>
@endsection

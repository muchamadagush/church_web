@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1>{{ isset($prayer_schedule) ? 'Edit' : 'Tambah' }} Jadwal Doa Wilayah</h1>
    
    <div class="card" style="padding: 20px; max-width: 1200px; width: 100%; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form action="{{ isset($prayer_schedule) ? route('worship-schedules.prayer-schedules.update', $prayer_schedule->id) : route('worship-schedules.prayer-schedules.store') }}" method="POST" style="width: 100%;">
            @csrf
            @if(isset($prayer_schedule)) @method('PUT') @endif

            <div style="margin-bottom: 20px;">
                <label for="tanggal" style="display: block; margin-bottom: 8px;">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" 
                    value="{{ old('tanggal', isset($prayer_schedule) ? $prayer_schedule->tanggal->format('Y-m-d') : '') }}"
                    required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                @error('tanggal')
                    <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="nama_gereja" style="display: block; margin-bottom: 8px;">Nama Gereja</label>
                <select id="nama_gereja" name="nama_gereja" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: white; box-sizing: border-box;">
                    <option value="">Pilih Gereja</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->name }}" {{ old('nama_gereja', $prayer_schedule->nama_gereja ?? '') == $church->name ? 'selected' : '' }}>
                            {{ $church->name }}
                        </option>
                    @endforeach
                </select>
                @error('nama_gereja')
                    <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="pimpinan_pujian" style="display: block; margin-bottom: 8px;">Pimpinan Pujian</label>
                <input type="text" id="pimpinan_pujian" name="pimpinan_pujian" 
                    value="{{ old('pimpinan_pujian', $prayer_schedule->pimpinan_pujian ?? '') }}"
                    required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                @error('pimpinan_pujian')
                    <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="pengkhotbah" style="display: block; margin-bottom: 8px;">Pengkhotbah</label>
                <input type="text" id="pengkhotbah" name="pengkhotbah" 
                    value="{{ old('pengkhotbah', $prayer_schedule->pengkhotbah ?? '') }}"
                    required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                @error('pengkhotbah')
                    <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
                <a href="{{ route('worship-schedules.prayer-schedules.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
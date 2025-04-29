@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1>{{ isset($schedule) ? 'Edit' : 'Tambah' }} Jadwal Kunjungan</h1>
    
    <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form action="{{ isset($schedule) ? route('worship-schedules.visits.update', $schedule->id) : route('worship-schedules.visits.store') }}" 
              method="POST" 
              style="width: 100%;">
            @csrf
            @if(isset($schedule)) @method('PUT') @endif

            <div style="margin-bottom: 15px;">
                <label for="church_id">
                    Nama Gereja
                    <span style="color: #dc2626;">*</span>
                </label>
                <select name="church_id" id="church_id" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Pilih Gereja</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ old('church_id', $schedule->church_id ?? '') == $church->id ? 'selected' : '' }}>
                            {{ $church->name }}
                        </option>
                    @endforeach
                </select>
                @error('church_id')
                    <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 15px;">
                <label for="visit_date">
                    Tanggal Kunjungan
                    <span style="color: #dc2626;">*</span>
                </label>
                <input
                    type="date"
                    id="visit_date"
                    name="visit_date" 
                    value="{{ old('visit_date', isset($schedule) ? $schedule->visit_date->format('Y-m-d') : '') }}"
                    required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; margin-top: 5px;">
                @error('visit_date')
                    <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
                <a href="{{ route('worship-schedules.visits.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
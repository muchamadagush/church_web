@extends('layouts.app')

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding: 20px;">
  <h1>{{ isset($schedule) ? 'Edit' : 'Tambah' }} Jadwal Khotbah</h1>

  @if(session('error'))
  <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #dc2626; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
    {{ session('error') }}
  </div>
  @endif

  <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <form id="sermonForm" action="{{ isset($schedule) ? route('worship-schedules.sermons.update', $schedule->id) : route('worship-schedules.sermons.store') }}" method="POST">
      @csrf
      @if(isset($schedule)) @method('PUT') @endif

      <div style="margin-bottom: 20px;">
        <label for="pengkhotbah">
          Nama Pengkhotbah
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" id="pengkhotbah" name="pengkhotbah" value="{{ old('pengkhotbah', $schedule->pengkhotbah ?? '') }}" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        @error('pengkhotbah')
        <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
        @enderror
      </div>

      <div style="margin-bottom: 15px;">
        <label for="church_id">
          Gereja
          <span style="color: #dc2626;">*</span>
        </label>
        <select id="church_id" name="church_id" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
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
        <label for="month">
          Bulan
          <span style="color: #dc2626;">*</span>
        </label>
        <select id="month" name="month" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
          <option value="">Pilih Bulan</option>
          <option value="Januari" {{ old('month', $schedule->month ?? '') == 'Januari' ? 'selected' : '' }}>Januari</option>
          <option value="Februari" {{ old('month', $schedule->month ?? '') == 'Februari' ? 'selected' : '' }}>Februari</option>
          <option value="Maret" {{ old('month', $schedule->month ?? '') == 'Maret' ? 'selected' : '' }}>Maret</option>
          <option value="April" {{ old('month', $schedule->month ?? '') == 'April' ? 'selected' : '' }}>April</option>
          <option value="Mei" {{ old('month', $schedule->month ?? '') == 'Mei' ? 'selected' : '' }}>Mei</option>
          <option value="Juni" {{ old('month', $schedule->month ?? '') == 'Juni' ? 'selected' : '' }}>Juni</option>
          <option value="Juli" {{ old('month', $schedule->month ?? '') == 'Juli' ? 'selected' : '' }}>Juli</option>
          <option value="Agustus" {{ old('month', $schedule->month ?? '') == 'Agustus' ? 'selected' : '' }}>Agustus</option>
          <option value="September" {{ old('month', $schedule->month ?? '') == 'September' ? 'selected' : '' }}>September</option>
          <option value="Oktober" {{ old('month', $schedule->month ?? '') == 'Oktober' ? 'selected' : '' }}>Oktober</option>
          <option value="November" {{ old('month', $schedule->month ?? '') == 'November' ? 'selected' : '' }}>November</option>
          <option value="Desember" {{ old('month', $schedule->month ?? '') == 'Desember' ? 'selected' : '' }}>Desember</option>
        </select>
        <small style="color: #666; font-size: 0.875em;">Tanggal & waktu akan otomatis diset ke minggu terakhir bulan (10:00-12:00)</small>
        @error('month')
        <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
        @enderror
      </div>

      <div style="margin-top: 20px; display: flex; gap: 10px;">
        <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
        <a href="{{ route('worship-schedules.sermons.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
      </div>
    </form>
  </div>
</div>

@endsection

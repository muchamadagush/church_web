@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
  <h1>{{ isset($schedule) ? 'Edit' : 'Tambah' }} Jadwal Kunjungan Kaum Wanita</h1>

  <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <form action="{{ isset($schedule) ? route('worship-schedules.women-visits.update', $schedule->id) : route('worship-schedules.women-visits.store') }}" method="POST" style="width: 100%;">
      @csrf
      @if(isset($schedule)) @method('PUT') @endif

      @if($errors->has('schedule_conflict'))
      <div style="background: #fee2e2; color: #dc2626; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
        {{ $errors->first('schedule_conflict') }}
      </div>
      @endif

      <div style="margin-bottom: 15px;">
        <label for="start_datetime">
          Mulai (Tanggal & Jam)
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="datetime-local" id="start_datetime" name="start_datetime"
          value="{{ old('start_datetime', isset($schedule) ? $schedule->start_datetime->format('Y-m-d\TH:i') : '') }}"
          required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        @error('start_datetime')
          <div style="color: #dc2626; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>

      <div style="margin-bottom: 15px;">
        <label for="end_datetime">
          Selesai (Tanggal & Jam)
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="datetime-local" id="end_datetime" name="end_datetime"
          value="{{ old('end_datetime', isset($schedule) ? $schedule->end_datetime->format('Y-m-d\TH:i') : '') }}"
          required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        @error('end_datetime')
          <div style="color: #dc2626; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>

      <div style="margin-bottom: 15px;">
        <label for="church_id">
          Tempat Pelayanan
          <span style="color: #dc2626;">*</span>
        </label>
        <select name="church_id" id="church_id" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
          <option value="">Pilih Tempat Pelayanan</option>
          @foreach($churches as $church)
          <option value="{{ $church->id }}" {{ old('church_id', $schedule->church_id ?? '') == $church->id ? 'selected' : '' }}>
            {{ $church->name }}
          </option>
          @endforeach
        </select>
        @error('church_id')
          <div style="color: #dc2626; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>

      <div style="margin-bottom: 15px;">
        <label for="worship_leader">
          Pimpin Pujian
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" id="worship_leader" name="worship_leader" value="{{ old('worship_leader', $schedule->worship_leader ?? '') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; margin-top: 5px;">
        @error('worship_leader')
          <div style="color: #dc2626; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>

      <div style="margin-bottom: 15px;">
        <label for="preacher">
          Pengkhotbah
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" id="preacher" name="preacher" value="{{ old('preacher', $schedule->preacher ?? '') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; margin-top: 5px;">
        @error('preacher')
          <div style="color: #dc2626; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>

      <div style="margin-top: 20px;">
        <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
          Simpan
        </button>
        <a href="{{ route('worship-schedules.women-visits.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin-left: 10px;">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

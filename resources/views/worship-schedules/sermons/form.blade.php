@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
  <h1>{{ isset($schedule) ? 'Edit' : 'Tambah' }} Jadwal Khotbah</h1>

  @if(session('error'))
  <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #dc2626; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
    {{ session('error') }}
  </div>
  @endif

  <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <form id="sermonForm" action="{{ isset($schedule) ? route('worship-schedules.sermons.update', $schedule->id) : route('worship-schedules.sermons.store') }}" method="POST" style="width: 100%;">
      @csrf
      @if(isset($schedule)) @method('PUT') @endif

      <div style="margin-bottom: 15px; max-width: 100%;">
        <label for="pengkhotbah">Nama Pengkhotbah <span style="color: #dc2626;">*</span></label>
        <input type="text" id="pengkhotbah" name="pengkhotbah" value="{{ old('pengkhotbah', $schedule->pengkhotbah ?? '') }}" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        @error('pengkhotbah')<span style="color: red; font-size: 0.875em;">{{ $message }}</span>@enderror
      </div>

      <div style="margin-bottom: 15px;">
        <label>Gereja <span style="color:#dc2626">*</span></label>
        <select name="church_id" required style="width: 100%; padding: 10px; margin-top: 5px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
          <option value="">Pilih Gereja</option>
          @foreach($churches as $church)
            <option value="{{ $church->id }}" {{ old('church_id', $schedule->church_id ?? '') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
          @endforeach
        </select>
        @error('church_id')<span style="color:red;font-size:0.8em">{{ $message }}</span>@enderror
      </div>

      <!-- Bulan dihapus -->

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:15px;">
        <div>
          <label>Mulai <span style="color:#dc2626">*</span></label>
          <input type="datetime-local" name="start_datetime" value="{{ old('start_datetime', isset($schedule->start_datetime) && $schedule->start_datetime ? $schedule->start_datetime->format('Y-m-d\\TH:i') : '') }}" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;" />
          @error('start_datetime')<span style="color:red;font-size:0.8em">{{ $message }}</span>@enderror
        </div>
        <div>
          <label>Selesai <span style="color:#dc2626">*</span></label>
          <input type="datetime-local" name="end_datetime" value="{{ old('end_datetime', isset($schedule->end_datetime) && $schedule->end_datetime ? $schedule->end_datetime->format('Y-m-d\\TH:i') : '') }}" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;" />
          @error('end_datetime')<span style="color:red;font-size:0.8em">{{ $message }}</span>@enderror
        </div>
      </div>

      <div style="margin-top: 20px; display: flex; gap: 10px;">
        <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
        <a href="{{ route('worship-schedules.sermons.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('sermonForm').addEventListener('submit', function(e){
    const start = document.querySelector('input[name="start_datetime"]').value;
    const end = document.querySelector('input[name="end_datetime"]').value;
    if(start && end && new Date(start) >= new Date(end)){
      e.preventDefault();
      alert('Waktu selesai harus lebih besar daripada waktu mulai');
    }
  });
</script>
@endsection

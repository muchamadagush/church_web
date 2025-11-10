<!-- Tambahkan referensi Bootstrap dan Bootstrap-datepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
  <h1>{{ isset($schedule) ? 'Edit' : 'Tambah' }} Jadwal Natal</h1>

  <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <form action="{{ isset($schedule) ? route('worship-schedules.christmas.update', $schedule->id) : route('worship-schedules.christmas.store') }}" 
          method="POST" 
          style="width: 100%;">
      @csrf
      @if(isset($schedule))
        @method('PUT')
      @endif
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
        <div>
          <label style="display: block; margin-bottom: 5px;">
            Mulai
            <span style="color: #dc2626;">*</span>
          </label>
          <input
            type="datetime-local"
            name="start_datetime"
            value="{{ old('start_datetime', isset($schedule) && $schedule->start_datetime ? $schedule->start_datetime->format('Y-m-d\\TH:i') : '') }}"
            required
            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
          >
          @error('start_datetime')
            <span style="color: red; font-size: 0.8em;">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label style="display: block; margin-bottom: 5px;">
            Selesai
            <span style="color: #dc2626;">*</span>
          </label>
          <input
            type="datetime-local"
            name="end_datetime"
            value="{{ old('end_datetime', isset($schedule) && $schedule->end_datetime ? $schedule->end_datetime->format('Y-m-d\\TH:i') : '') }}"
            required
            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
          >
          @error('end_datetime')
            <span style="color: red; font-size: 0.8em;">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 5px;">
          Tempat Ibadah
          <span style="color: #dc2626;">*</span>
        </label>
        <select 
          name="church_id" 
          required
          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
          <option value="">-- Pilih Tempat Ibadah --</option>
          @foreach($churches as $church)
            <option value="{{ $church->id }}" {{ old('church_id', isset($schedule) ? $schedule->church_id : '') == $church->id ? 'selected' : '' }}>
              {{ $church->name }}
            </option>
          @endforeach
        </select>
        @error('church_id')
          <span style="color: red; font-size: 0.8em;">{{ $message }}</span>
        @enderror
      </div>

      <div style="display: flex; gap: 10px;">
        <button type="submit" 
                style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
          {{ isset($schedule) ? 'Update' : 'Simpan' }}
        </button>
        <a href="{{ route('worship-schedules.christmas.index') }}" 
           style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; text-align: center;">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>

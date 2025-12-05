@extends('layouts.app')

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding: 20px;">
  <h1>Edit Jadwal Khotbah - {{ $pengkhotbah }}</h1>

  @if(session('error'))
  <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #dc2626; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
    {{ session('error') }}
  </div>
  @endif

  <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <form id="sermonForm" action="{{ route('worship-schedules.sermons.update-preacher', ['pengkhotbah' => urlencode($pengkhotbah)]) }}" method="POST">
      @csrf
      @method('PUT')

      <div style="margin-bottom: 20px;">
        <label for="pengkhotbah">
          Nama Pengkhotbah
          <span style="color: #dc2626;">*</span>
        </label>
        <select id="pengkhotbah" name="pengkhotbah" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
          <option value="">Pilih Pengkhotbah</option>
          @foreach($preachers as $preacher)
            <option value="{{ $preacher['name'] }}" {{ $pengkhotbah == $preacher['name'] ? 'selected' : '' }}>
              {{ $preacher['name'] }} ({{ $preacher['home_church'] }})
            </option>
          @endforeach
        </select>
        @error('pengkhotbah')
        <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
        @enderror
      </div>

      <div id="schedulesList" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
          <h3 style="margin: 0;">Jadwal Khotbah</h3>
          <button type="button" onclick="addSchedule()" style="background: #10b981; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
            + Tambah Jadwal
          </button>
        </div>
        
        @foreach($schedules as $index => $schedule)
        <div class="schedule-item" style="border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-bottom: 10px; background: #f9fafb;">
          <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: start;">
            <div>
              <label>Gereja <span style="color: #dc2626;">*</span></label>
              <select name="schedules[{{ $index }}][church_id]" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Pilih Gereja</option>
                @foreach($churches as $church)
                  <option value="{{ $church->id }}" {{ $schedule->church_id == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label>Bulan <span style="color: #dc2626;">*</span></label>
              <select name="schedules[{{ $index }}][month]" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Pilih Bulan</option>
                @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                  <option value="{{ $bulan }}" {{ $schedule->month == $bulan ? 'selected' : '' }}>{{ $bulan }}</option>
                @endforeach
              </select>
            </div>
            <div style="padding-top: 27px;">
              <button type="button" onclick="removeSchedule(this)" style="background: #ef4444; color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                Hapus
              </button>
            </div>
          </div>
          <input type="hidden" name="schedules[{{ $index }}][id]" value="{{ $schedule->id }}">
        </div>
        @endforeach
      </div>

      <div style="margin-top: 20px; display: flex; gap: 10px;">
        <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
        <a href="{{ route('worship-schedules.sermons.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
let scheduleIndex = {{ count($schedules) }};

function addSchedule() {
  const schedulesList = document.getElementById('schedulesList');
  const newSchedule = document.createElement('div');
  newSchedule.className = 'schedule-item';
  newSchedule.style.cssText = 'border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-bottom: 10px; background: #f9fafb;';
  
  newSchedule.innerHTML = `
    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: start;">
      <div>
        <label>Gereja <span style="color: #dc2626;">*</span></label>
        <select name="schedules[${scheduleIndex}][church_id]" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
          <option value="">Pilih Gereja</option>
          @foreach($churches as $church)
            <option value="{{ $church->id }}">{{ $church->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label>Bulan <span style="color: #dc2626;">*</span></label>
        <select name="schedules[${scheduleIndex}][month]" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
          <option value="">Pilih Bulan</option>
          @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
            <option value="{{ $bulan }}">{{ $bulan }}</option>
          @endforeach
        </select>
      </div>
      <div style="padding-top: 27px;">
        <button type="button" onclick="removeSchedule(this)" style="background: #ef4444; color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
          Hapus
        </button>
      </div>
    </div>
  `;
  
  schedulesList.appendChild(newSchedule);
  scheduleIndex++;
}

function removeSchedule(button) {
  const scheduleItems = document.querySelectorAll('.schedule-item');
  if (scheduleItems.length > 1) {
    button.closest('.schedule-item').remove();
  } else {
    alert('Minimal harus ada 1 jadwal');
  }
}
</script>
@endsection

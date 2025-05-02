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
        <label for="pengkhotbah">
          Nama Pengkhotbah
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" id="pengkhotbah" name="pengkhotbah" value="{{ old('pengkhotbah', $schedule->pengkhotbah ?? '') }}" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        @error('pengkhotbah')
        <span style="color: red; font-size: 0.875em;">{{ $message }}</span>
        @enderror
      </div>

      <div id="scheduleEntries" style="width: 100%;">
        <!-- Schedule entries will be added here -->
      </div>

      <div style="margin-bottom: 20px; width: 100%;">
        <button type="button" onclick="addScheduleEntry()" style="background: #4CAF50; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; margin-left: auto;">
          + Tambah Jadwal
        </button>
      </div>

      <div style="margin-top: 20px; display: flex; gap: 10px;">
        <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
        <a href="{{ route('worship-schedules.sermons.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
      </div>
    </form>
  </div>
</div>

<template id="scheduleEntryTemplate">
  <div class="schedule-entry" style="border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-bottom: 15px; width: 100%; box-sizing: border-box;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
      <h3 style="margin: 0;">Jadwal #%index%</h3>
      <button type="button" onclick="removeScheduleEntry(this)" style="background: #dc2626; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;">
        Hapus
      </button>
    </div>

    <div style="margin-bottom: 15px; width: 100%;">
      <label>Gereja</label>
      <select name="churches[%index%][church_id]" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        <option value="">Pilih Gereja</option>
        {!! $churches->map(function($church) {
        return sprintf('<option value="%s">%s</option>', $church->id, $church->name);
        })->join('') !!}
      </select>
    </div>

    <div style="margin-bottom: 15px; width: 100%;">
      <label>Bulan</label>
      <select name="churches[%index%][month]" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        <option value="">Pilih Bulan</option>
        <option value="jan">Januari</option>
        <option value="feb">Februari</option>
        <option value="mar">Maret</option>
        <option value="apr">April</option>
        <option value="may">Mei</option>
        <option value="jun">Juni</option>
        <option value="jul">Juli</option>
        <option value="aug">Agustus</option>
        <option value="sep">September</option>
        <option value="oct">Oktober</option>
      </select>
    </div>
  </div>
</template>

<script>
  let entryIndex = 0;

  // This will be populated from the controller
  const existingUsedMonths = @json($usedMonths ?? []);

  document.addEventListener('DOMContentLoaded', function() {
    const scheduleDetails = @json($schedule->details ?? []);
    const container = document.getElementById('scheduleEntries');
    const templateHtml = document.getElementById('scheduleEntryTemplate').innerHTML;

    scheduleDetails.forEach(detail => {
      const template = templateHtml.replace(/%index%/g, entryIndex);
      container.insertAdjacentHTML('beforeend', template);

      const churchSelect = container.querySelector(`select[name="churches[${entryIndex}][church_id]"]`);
      const monthSelect = container.querySelector(`select[name="churches[${entryIndex}][month]"]`);

      if (churchSelect) churchSelect.value = detail.church_id;
      if (monthSelect) {
        monthSelect.value = detail.month;
        monthSelect.addEventListener('change', updateMonthOptions);
      }

      entryIndex++;
    });

    if (scheduleDetails.length === 0) {
      addScheduleEntry();
    }

    // Update month options at page load
    updateMonthOptions();
  });

  function addScheduleEntry() {
    const template = document.getElementById('scheduleEntryTemplate').innerHTML
      .replace(/%index%/g, entryIndex++);
    document.getElementById('scheduleEntries')
      .insertAdjacentHTML('beforeend', template);

    // Get the newly added month select
    const newSelect = document.querySelector(`select[name="churches[${entryIndex-1}][month]"]`);
    if (newSelect) {
      // Add change event listener
      newSelect.addEventListener('change', updateMonthOptions);
    }

    // Update options after adding new entry
    updateMonthOptions();
  }

  function removeScheduleEntry(button) {
    button.closest('.schedule-entry').remove();
    updateMonthOptions();
  }

  // Update the function to consider both form selections and database records
  function updateMonthOptions() {
    // Get all month select elements
    const monthSelects = document.querySelectorAll('select[name^="churches"][name$="[month]"]');
    
    // Track selected months and their corresponding elements
    const selectedMonths = new Map();
    
    // First pass - gather all selected values
    monthSelects.forEach(select => {
      if (select.value && select.value !== '') {
        selectedMonths.set(select.value, select);
      }
    });
    
    // Second pass - update available options in each select
    monthSelects.forEach(select => {
      // Store current selection
      const currentValue = select.value;
      
      // Show/hide options based on selections
      Array.from(select.options).forEach(option => {
        if (option.value === '' || option.value === currentValue) {
          // Always show empty option and current selection
          option.hidden = false;
        } else {
          // Hide if option is:
          // 1. Selected in another dropdown
          const isSelectedElsewhere = selectedMonths.has(option.value) && 
                                    selectedMonths.get(option.value) !== select;
          
          // 2. Already used in database (unless it's the current selection)
          const isUsedInDatabase = existingUsedMonths.includes(option.value);
                                  
          option.hidden = isSelectedElsewhere || isUsedInDatabase;
        }
      });
    });
  }

  let churchIndex = 1;

  function addChurchEntry() {
    const template = document.querySelector('.church-entry').cloneNode(true);
    template.querySelectorAll('[name]').forEach(el => {
      el.name = el.name.replace('[0]', `[${churchIndex}]`);
    });
    document.getElementById('churchEntries').appendChild(template);
    churchIndex++;
  }

  // Tambahkan ini di bagian <script> yang sudah ada
  document.getElementById('sermonForm').addEventListener('submit', function(e) {
    const entries = document.querySelectorAll('.schedule-entry');
    if (entries.length === 0) {
      e.preventDefault();
      alert('Mohon tambahkan minimal satu jadwal');
      return false;
    }

    const pengkhotbah = document.getElementById('pengkhotbah').value;
    if (!pengkhotbah.trim()) {
      e.preventDefault();
      alert('Mohon isi nama pengkhotbah');
      return false;
    }
  });

</script>
@endsection

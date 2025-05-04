@extends('layouts.app')

@section('content')
<!-- Tambahkan referensi Bootstrap dan Bootstrap-datepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<div style="max-width: 1200px; margin: 0 auto;">
  <div style="margin-bottom: 40px;">
    <h1>{{ isset($keuangan) ? 'Edit' : 'Tambah' }} Data Keuangan</h1>
  </div>

  @if($errors->any())
  <div style="background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
    <ul style="margin: 0; padding-left: 20px;">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <form action="{{ isset($keuangan) ? route('keuangan.update', $keuangan->id) : route('keuangan.store') }}" method="POST" style="width: 100%;">
      @csrf
      @if(isset($keuangan))
      @method('PUT')
      @endif

      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">
          Tanggal
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" id="datepicker-input" name="tanggal" value="{{ old('tanggal', isset($keuangan) ? $keuangan->tanggal : '') }}" required readonly style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; cursor: pointer;">
      </div>

      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">
          Nominal
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" name="nominal" id="nominal" value="{{ old('nominal', isset($keuangan) ? ($keuangan->debit ? $keuangan->debit : $keuangan->kredit) : '') }}" placeholder="Rp. 2000" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
      </div>

      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">
          Jenis
          <span style="color: #dc2626;">*</span>
        </label>
        <div style="display: flex; gap: 20px;">
          <label style="display: flex; align-items: center; cursor: pointer;">
            <input type="radio" name="jenis" value="debit" {{ old('jenis', isset($keuangan) && $keuangan->debit ? 'debit' : '') == 'debit' ? 'checked' : '' }} style="margin-right: 8px;" class="jenis-radio">
            <span>Debit (Pemasukan)</span>
          </label>
          <label style="display: flex; align-items: center; cursor: pointer;">
            <input type="radio" name="jenis" value="kredit" {{ old('jenis', isset($keuangan) ? ($keuangan->kredit ? 'kredit' : '') : 'kredit') == 'kredit' ? 'checked' : '' }} style="margin-right: 8px;" class="jenis-radio">
            Kredit (Pengeluaran)
          </label>
        </div>
      </div>

      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">
          Keterangan
          <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" name="keterangan" value="{{ old('keterangan', isset($keuangan) ? $keuangan->keterangan : '') }}" placeholder="Masukkan Keterangan" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
      </div>

      <div style="margin-top: 20px; display: flex; gap: 10px;">
        <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
        <a href="{{ route('keuangan.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
      </div>
    </form>
  </div>
</div>

<!-- Modal untuk Datepicker -->
<div class="modal fade" id="datepickerModal" tabindex="-1" aria-labelledby="datepickerModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: fit-content; background: white; border-radius:8px">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="datepickerModalLabel">Pilih Tanggal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="datepicker-container"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="save-date">Ok</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // Inisialisasi datepicker di dalam modal
    $('#datepicker-container').datepicker({
      format: 'yyyy-mm-dd'
      , autoclose: true
      , todayHighlight: true
    });

    // Tampilkan modal ketika input di-klik
    $('#datepicker-input').on('click', function() {
      $('#datepickerModal').modal('show');
    });

    // Simpan tanggal yang dipilih ke input
    $('#save-date').on('click', function() {
      const selectedDate = $('#datepicker-container').datepicker('getFormattedDate');
      $('#datepicker-input').val(selectedDate);
      $('#datepickerModal').modal('hide');
    });
  });

  // Format currency input with Rp prefix
  document.addEventListener('DOMContentLoaded', function() {
    const nominalInput = document.getElementById('nominal');
    const value = nominalInput.value;

    // Initially format the value
    if (value) {
      nominalInput.value = 'Rp. ' + new Intl.NumberFormat('id-ID').format(value);
    }

    nominalInput.addEventListener('input', function(e) {
      // Remove non-numeric characters
      let value = this.value.replace(/[^\d]/g, '');

      if (value) {
        // Format with thousand separators
        value = new Intl.NumberFormat('id-ID').format(value);
        this.value = 'Rp. ' + value;
      } else {
        this.value = '';
      }
    });

    // Handle form submission to convert to numeric value
    const form = nominalInput.closest('form');
    form.addEventListener('submit', function() {
      nominalInput.value = nominalInput.value.replace(/[^\d]/g, '');
    });

    // Show golden dot for kredit option
    function updateKreditIndicator() {
      const kreditRadio = document.querySelector('input[name="jenis"][value="kredit"]');
      const indicator = document.querySelector('.kredit-indicator');

      if (kreditRadio.checked) {
        indicator.style.display = 'inline-block';
      } else {
        indicator.style.display = 'none';
      }
    }

    // Initial update
    updateKreditIndicator();

    // Update whenever radio changes
    document.querySelectorAll('.jenis-radio').forEach(radio => {
      radio.addEventListener('change', updateKreditIndicator);
    });
  });

</script>
@endsection

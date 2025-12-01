@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="color: #333; margin: 0;">Jadwal Pertukaran Khotbah</h1>
    <div style="display: flex; gap: 10px;">
      @if(\App\Helpers\PermissionHelper::hasPermission('create', 'worship-schedules'))
      <button onclick="showGenerateModal()" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
        🤖 Generate Jadwal
      </button>
      <a href="{{ route('worship-schedules.sermons.create') }}" class="button-detail">+ Tambah Data</a>
      @endif
    </div>
  </div>

  @if(session('success'))
  <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
    {{ session('success') }}
  </div>
  @endif

  <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse;">
      <thead style="background: #f0f0f0;">
        <tr style="background: #f8f9fa;">
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">No</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tanggal & Waktu</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Pengkhotbah</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Gereja</th>
          @if($canEdit || $canDelete)
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; width: 150px;">Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @forelse($schedules as $index => $schedule)
        <tr style="border-bottom: 1px solid #dee2e6;">
          <td style="padding: 15px; text-align: left;">{{ ($schedules->currentPage() - 1) * $schedules->perPage() + $index + 1 }}</td>
          <td style="padding: 15px; vertical-align: top;">{{ $schedule->start_datetime ? \Carbon\Carbon::parse($schedule->start_datetime)->format('d F Y H:i') : '-' }} - {{ $schedule->end_datetime ? \Carbon\Carbon::parse($schedule->end_datetime)->format('H:i') : '-' }}</td>
          <td style="padding: 15px; vertical-align: top;">
            <div style="font-weight: 500; color: #333;">{{ $schedule->pengkhotbah }}</div>
          </td>
          <td style="padding: 15px; vertical-align: top;">{{ $schedule->church->name ?? '-' }}</td>
          @if($canEdit || $canDelete)
          <td style="padding: 15px; text-align: center; vertical-align: top;">
            <div style="display: flex; gap: 5px;">
              <a href="{{ route('worship-schedules.sermons.edit', $schedule->id) }}" style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
                Ubah
              </a>
              <button type="button" onclick="showDeleteModal({{ $schedule->id }})" style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                Hapus
              </button>
            </div>
          </td>
          @endif
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align: center; padding: 15px;">Tidak ada jadwal khotbah</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Pagination -->
    <div style="padding: 15px;">
      @if(isset($schedules))
      <div class="pagination-container" style="display: flex; justify-content: start; margin-top: 20px;">
        <ul style="display: flex; list-style: none; padding: 0; margin: 0; align-items: center;">
          <!-- Previous page link -->
          @if ($schedules->onFirstPage())
          <li style="margin: 0 5px;"><span style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;">«</span></li>
          @else
          <li style="margin: 0 5px;"><a href="{{ $schedules->previousPageUrl() }}" style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #fff; color: #4839EB; text-decoration: none; border: 1px solid #dee2e6;">«</a></li>
          @endif

          <!-- Page numbers -->
          @foreach ($schedules->getUrlRange(1, $schedules->lastPage()) as $page => $url)
          <li style="margin: 0 5px;">
            <a href="{{ $url }}" style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; {{ $page == $schedules->currentPage() ? 'background-color: #4839EB; color: #fff; border: 1px solid #4839EB;' : 'background-color: #fff; color: #4839EB; border: 1px solid #dee2e6;' }} text-decoration: none;">{{ $page }}</a>
          </li>
          @endforeach

          <!-- Next page link -->
          @if ($schedules->hasMorePages())
          <li style="margin: 0 5px;"><a href="{{ $schedules->nextPageUrl() }}" style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #fff; color: #4839EB; text-decoration: none; border: 1px solid #dee2e6;">»</a></li>
          @else
          <li style="margin: 0 5px;"><span style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;">»</span></li>
          @endif
        </ul>
      </div>
      @endif
    </div>
  </div>

  <div style="padding: 10px 15px 10px 0; margin-top: 15px;">
    <div style="margin-bottom: 5px;"><strong>Keterangan:</strong></div>
    <div style="margin-bottom: 5px;">1. Jika gembala berhalangan, maka harus mengutus salah satu majelis jemaat untuk menggantikan</div>
    <div style="margin-bottom: 5px;">2. Mohon semua gembala bertanggung jawab dan pelayanan Pertukaran pengkhotbah</div>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
  <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; text-align: center;">
    <h2 style="font-size: 24px; margin-bottom: 10px; color: #333;">Konfirmasi Hapus</h2>
    <p style="font-size: 16px; margin-bottom: 20px; color: #666;">Apakah Anda yakin ingin menghapus data ini?</p>
    <div style="display: flex; justify-content: center; gap: 12px;">
      <form id="deleteForm" method="POST" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" style="background: #4839EB; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px;">
          Hapus
        </button>
      </form>
      <button onclick="hideDeleteModal()" style="background: #dc3545; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px;">
        Batal
      </button>
    </div>
  </div>
</div>

<!-- Generate Modal -->
<div id="generateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
  <div style="background: white; padding: 30px; border-radius: 12px; width: 500px;">
    <h2 style="font-size: 24px; margin-bottom: 20px; color: #333;">Generate Jadwal Otomatis</h2>
    <form id="generateForm" method="POST" action="{{ route('worship-schedules.sermons.generate') }}">
      @csrf
      <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Tahun</label>
        <input type="number" name="year" value="{{ date('Y') }}" min="2024" max="2099" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
      </div>
      <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Durasi per Jadwal (menit)</label>
        <input type="number" name="duration" value="120" min="30" max="480" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        <small style="color: #666;">Rentang: 30-480 menit</small>
      </div>
      <div style="padding: 15px; background: #f0f9ff; border-left: 4px solid #3b82f6; margin-bottom: 20px; border-radius: 4px;">
        <strong style="color: #1e40af;">ℹ️ Catatan:</strong>
        <ul style="margin: 10px 0 0 20px; color: #1e3a8a;">
          <li>Sistem akan membuat <strong>12 jadwal otomatis</strong> dalam satu tahun</li>
          <li>Jadwal di setiap <strong>minggu terakhir</strong> tiap bulan</li>
          <li>Waktu mulai tetap: <strong>Jam 10:00 pagi</strong></li>
          <li>11 pengkhotbah akan dirotasi secara merata</li>
          <li>Gembala tidak akan ditugaskan ke gereja sendiri</li>
          <li>Minimal setiap pengkhotbah memiliki 1 jadwal per tahun</li>
        </ul>
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" onclick="hideGenerateModal()" style="background: #6c757d; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px;">
          Batal
        </button>
        <button type="submit" style="background: #10b981; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px;">
          Generate
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function showDeleteModal(id) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('worship-schedules.sermons.destroy', ':id') }}".replace(':id', id);
    modal.style.display = 'flex';
  }

  function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
  }

  function showGenerateModal() {
    document.getElementById('generateModal').style.display = 'flex';
  }

  function hideGenerateModal() {
    document.getElementById('generateModal').style.display = 'none';
  }

</script>
@endsection

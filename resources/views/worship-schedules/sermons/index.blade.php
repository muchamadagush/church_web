@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="color: #333; margin: 0;">Jadwal Pertukaran Khotbah</h1>
    <div style="display: flex; gap: 10px;">
      @if(\App\Helpers\PermissionHelper::hasPermission('create', 'worship-schedules'))
      <button onclick="openGenerateModal()" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">🤖 Generate Jadwal</button>
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
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Pengkhotbah</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Gereja</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tanggal & Waktu</th>
          @if($canEdit || $canDelete)
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @php $no = ($schedules->currentPage() - 1) * $schedules->perPage() + 1; @endphp
        @forelse($schedules as $schedule)
        <tr style="border-bottom: 1px solid #dee2e6;">
          <td style="padding: 15px; text-align: left;">{{ $no++ }}</td>
          <td style="padding: 15px; text-align: left;">
            <div style="font-weight: 500; color: #333;">{{ $schedule->pengkhotbah }}</div>
          </td>
          <td style="padding: 15px; text-align: left;">
            <div style="font-weight: 500; color: #333;">{{ optional($schedule->church)->name ?? '-' }}</div>
          </td>
          <td style="padding: 15px; text-align: left;">
            @if($schedule->start_datetime)
            <div style="font-size: 14px; color: #666;">
              <div>📅 {{ $schedule->start_datetime->format('d M Y') }}
              <div>🕐 {{ $schedule->start_datetime->format('H:i') }} - {{ $schedule->end_datetime->format('H:i') }}</div>
            </div>
            @else
            <span style="color: #999;">-</span>
            @endif
          </td>
          @if($canEdit || $canDelete)
          <td style="padding: 15px; text-align: center;">
            @if($canEdit)
            <a href="{{ route('worship-schedules.sermons.edit', $schedule->id) }}" style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
              Ubah
            </a>
            @endif
            @if($canDelete)
            <button type="button" onclick="showDeleteModal({{ $schedule->id }})" style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
              Hapus
            </button>
            @endif
          </td>
          @endif
        </tr>
        @empty
        <tr>
          <td colspan="{{ ($canEdit || $canDelete) ? 5 : 4 }}" style="text-align: center; padding: 15px;">Tidak ada jadwal khotbah</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Pagination -->
    @if(isset($schedules) && $schedules->hasPages())
    <div class="pagination-container" style="display: flex; justify-content: flex-start; padding: 15px;">
      <ul style="display: flex; list-style: none; padding: 0; margin: 0; align-items: center;">
        <!-- Previous page link -->
        @if ($schedules->onFirstPage())
        <li style="margin: 0 5px;"><span style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;">«</span></li>
        @else
        <li style="margin: 0 5px;"><a href="{{ $schedules->previousPageUrl() }}" style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #fff; color: #4839EB; text-decoration: none; border: 1px solid #dee2e6;">«</a></li>
        @endif

        <!-- Page numbers -->
        @php
          $startPage = max(1, $schedules->currentPage() - 2);
          $endPage = min($schedules->lastPage(), $schedules->currentPage() + 2);
        @endphp

        <!-- First page and ellipsis if needed -->
        @if($startPage > 1)
        <li style="margin: 0 5px;">
          <a href="{{ $schedules->url(1) }}" style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #fff; color: #4839EB; text-decoration: none; border: 1px solid #dee2e6;">1</a>
        </li>
        @if($startPage > 2)
        <li style="margin: 0 5px;"><span style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;">...</span></li>
        @endif
        @endif

        @for ($page = $startPage; $page <= $endPage; $page++)
        <li style="margin: 0 5px;">
          <a href="{{ $schedules->url($page) }}" style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; {{ $page == $schedules->currentPage() ? 'background-color: #4839EB; color: #fff; border: 1px solid #4839EB;' : 'background-color: #fff; color: #4839EB; border: 1px solid #dee2e6;' }} text-decoration: none;">{{ $page }}</a>
        </li>
        @endfor

        <!-- Last page and ellipsis if needed -->
        @if($endPage < $schedules->lastPage())
        @if($endPage < $schedules->lastPage() - 1)
        <li style="margin: 0 5px;"><span style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;">...</span></li>
        @endif
        <li style="margin: 0 5px;">
          <a href="{{ $schedules->url($schedules->lastPage()) }}" style="display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 4px; background-color: #fff; color: #4839EB; text-decoration: none; border: 1px solid #dee2e6;">{{ $schedules->lastPage() }}</a>
        </li>
        @endif

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

  <div style="padding: 10px 15px 10px 0; margin-top: 15px;">
    <div style="margin-bottom: 5px;"><strong>Keterangan:</strong></div>
    <div style="margin-bottom: 5px;">1. Jika gembala berhalangan, maka harus mengutus salah satu majelis jemaat untuk menggantikan</div>
    <div style="margin-bottom: 5px;">2. Mohon semua gembala bertanggung jawab dan pelayanan Pertukaran pengkhotbah</div>
  </div>
</div>

<!-- Generate Modal -->
<div id="generateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
  <div style="background: white; padding: 30px; border-radius: 12px; width: 500px; max-height: 80vh; overflow-y: auto;">
    <h2 style="font-size: 24px; margin-bottom: 20px; color: #333;">Generate Jadwal Pertukaran Khotbah</h2>
    <form id="generateForm" action="{{ route('worship-schedules.sermons.generate') }}" method="POST">
      @csrf
      <div style="margin-bottom: 20px;">
        <label for="year" style="display: block; margin-bottom: 8px; font-weight: 500;">Tahun Jadwal:</label>
        <input type="number" id="year" name="year" value="{{ date('Y') }}" min="2020" max="2030" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
      </div>
      <div style="margin-bottom: 20px;">
        <strong>Catatan:</strong>
        <ul style="margin-top: 10px; padding-left: 20px;">
          <li>Buat jadwal otomatis selamat satu tahun</li>
          <li>Semua pengkhotbah akan bertukar mimbar melayani di gereja lain (tidak melayani di gerejanya sendiri)</li>
          <li>Jam ibadah 10.00</li>
          <li>Setiap bulan minggu terakhir, 11 gembala akan melayani di gereja lain</li>
          <li>Gereja secara random</li>
          <li>Generate hanya jika tahun tersebut masih kosong</li>
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

<!-- Delete Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
  <div style="background: white; padding: 30px; border-radius: 10px; width: 400px; text-align: center;">
    <h2 style="font-size: 24px; margin-bottom: 10px;">Apakah Anda Yakin</h2>
    <p style="font-size: 18px; margin-bottom: 20px;">Ingin Menghapus Data?</p>
    <div style="display: flex; justify-content: center; gap: 10px;">
      <form id="deleteForm" method="POST" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" style="background: #4839EB; color: white; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-size: 16px;">
          Hapus
        </button>
      </form>
      <button onclick="hideDeleteModal()" style="background: #FF0000; color: white; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-size: 16px;">
        Batal
      </button>
    </div>
  </div>
</div>

<script>
  function openGenerateModal() {
    const modal = document.getElementById('generateModal');
    modal.style.display = 'flex';
  }

  function hideGenerateModal() {
    const modal = document.getElementById('generateModal');
    modal.style.display = 'none';
  }

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

</script>
@endsection

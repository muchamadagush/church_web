@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Data Jadwal Perkunjungan Kaum Muda</h1>
    @if(\App\Helpers\PermissionHelper::hasPermission('create', 'worship-schedules'))
    <div style="display: flex; gap: 10px;">
      <button onclick="showGenerateModal()" @if(isset($hasTodaySchedules) && $hasTodaySchedules) disabled title="Sudah ada jadwal untuk hari ini" @endif style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; opacity: {{ (isset($hasTodaySchedules) && $hasTodaySchedules) ? '0.6' : '1' }};">
        🤖 Generate Jadwal
      </button>
      <a href="{{ route('worship-schedules.youth-visit.create') }}" class="button-detail">+ Tambah Data</a>
    </div>
    @endif
  </div>

  @if(session('success'))
  <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
    {{ session('success') }}
  </div>
  @endif

  <div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead style="background: #f5f5f5;">
        <tr>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">No</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tanggal & Waktu</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Nama Gereja</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Pimpin Pujian</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Pengkhotbah</th>
          @if($canEdit || $canDelete)
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Action</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @forelse($schedules as $index => $schedule)
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 15px;">{{ ($schedules->currentPage() - 1) * $schedules->perPage() + $index + 1 }}</td>
          <td style="padding: 15px;">{{ $schedule->start_datetime?->format('d F Y H:i') }} - {{ $schedule->end_datetime?->format('d F Y H:i') }}</td>
          <td style="padding: 15px;">{{ $schedule->church->name }}</td>
          <td style="padding: 15px;">{{ $schedule->worship_leader }}</td>
          <td style="padding: 15px;">{{ $schedule->speaker }}</td>
          @if($canEdit || $canDelete)
          <td style="padding: 15px; text-align: center;">
            <div style="display: flex; gap: 5px;">
              <a href="{{ route('worship-schedules.youth-visit.edit', $schedule->id) }}" style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
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
          <td colspan="7" style="padding: 15px; text-align: center;">Tidak ada data</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Pagination -->
    <div style="padding: 15px;">
      @if(isset($schedules))
      <div class="pagination-container" style="display: flex; justify-content: center; margin-top: 20px;">
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
  function showDeleteModal(id) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('worship-schedules.youth-visit.destroy', ':id') }}".replace(':id', id);
    modal.style.display = 'flex';
  }

  function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
  }

</script>

<!-- Generate Modal -->
<div id="generateModal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
  <div style="background:white; padding:30px; border-radius:12px; width:520px;">
    <h2 style="font-size:22px; margin-bottom:18px; color:#333;">Generate Jadwal Kunjungan Kaum Muda Otomatis</h2>
    <form method="POST" action="{{ route('worship-schedules.youth-visit.generate') }}">
      @csrf
      <!-- Tanggal & jam otomatis: hari ini 09:00 -->
      <div style="margin-bottom:16px;">
        <label style="display:block; margin-bottom:5px; font-weight:500;">Durasi per Jadwal (menit)</label>
        <input type="number" name="duration" value="120" min="30" max="480" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
        <small style="color:#666;">Rentang: 30-480 menit</small>
      </div>
      <!-- Pimpinan pujian & pengkhotbah akan otomatis ditetapkan per gereja -->
      <div style="padding:15px; background:#f0f9ff; border-left:4px solid #3b82f6; margin-bottom:20px; border-radius:4px;">
        <strong style="color:#1e40af;">ℹ️ Catatan:</strong>
        <ul style="margin:10px 0 0 20px; color:#1e3a8a;">
          <li>Generate hanya untuk hari ini dan hanya jika masih kosong</li>
          <li>Mulai otomatis jam 09:00 dengan jeda 15 menit antar kunjungan</li>
          <li>Nama WL & Pengkhotbah otomatis berdasarkan nama gereja</li>
          <li>Tidak akan membuat jadwal overlap</li>
        </ul>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px;">
        <button type="button" onclick="hideGenerateModal()" style="background:#6c757d; color:white; border:none; padding:10px 22px; border-radius:6px; cursor:pointer; font-size:14px;">Batal</button>
        <button type="submit" style="background:#10b981; color:white; border:none; padding:10px 22px; border-radius:6px; cursor:pointer; font-size:14px;">Generate</button>
      </div>
    </form>
  </div>
</div>

<script>
  function showGenerateModal(){ document.getElementById('generateModal').style.display = 'flex'; }
  function hideGenerateModal(){ document.getElementById('generateModal').style.display = 'none'; }
  document.getElementById('generateModal').addEventListener('click', function(e){ if(e.target === this){ hideGenerateModal(); } });
</script>
@endsection

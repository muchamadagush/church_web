@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Data Jadwal Natal</h1>
    @if(\App\Helpers\PermissionHelper::hasPermission('create', 'prayer-schedules'))
    <a href="{{ route('worship-schedules.christmas.create') }}" class="button-detail">+ Tambah Data</a>
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
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">No</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tanggal</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tempat Ibadah</th>
          @if($canEdit || $canDelete)
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @forelse($schedules as $index => $schedule)
          <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 15px; text-align: center;">{{ $index + 1 }}</td>
            <td style="padding: 15px;">{{ $schedule->schedule_date->format('d F Y') }}</td>
            <td style="padding: 15px;">{{ $schedule->church->name }}</td>
          @if($canEdit || $canDelete)
            <td style="padding: 15px; text-align: center;">
                <a
                  href="{{ route('worship-schedules.christmas.edit', $schedule->id) }}"
                  style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;"
                >
                  Ubah
                </a>
                <button
                  type="button"
                  onclick="showDeleteModal({{ $schedule->id }})"
                  style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;"
                >
                  Hapus
                </button>
              </div>
            </td>
          @endif
          </tr>
        @empty
          <tr>
            <td colspan="4" style="padding: 15px; text-align: center;">Tidak ada data</td>
          </tr>
        @endforelse
      </tbody>
    </table>
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
    form.action = "{{ route('worship-schedules.christmas.destroy', ':id') }}".replace(':id', id);
    modal.style.display = 'flex';
  }

  function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
  }

  // Close modal when clicking outside
  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
      hideDeleteModal();
    }
  });
</script>
@endsection
@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Data Pengumuman</h1>
    @if(\App\Helpers\PermissionHelper::hasPermission('create', 'pengumuman'))
    <a href="{{ route('pengumuman.create') }}" class="button-detail">+ Tambah Data</a>
    @endif
  </div>

  <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
    <div style="flex: 1; max-width: 350px;">
      <form action="{{ route('pengumuman.index') }}" method="GET" id="searchForm">
        <div style="position: relative;">
          <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari pengumuman..." style="width: 100%; padding: 10px 40px 10px 15px; border: 1px solid #ddd; border-radius: 25px; box-sizing: border-box; font-size: 14px;">
          <button type="submit" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
        </div>
      </form>
    </div>

    <!-- Reset Button -->
    @if(!empty($search))
    <div>
      <a href="{{ route('pengumuman.index') }}" style="padding: 10px 20px; background-color: #f8f9fa; color: #333; border: 1px solid #ddd; border-radius: 25px; text-decoration: none; display: inline-block;">
        Reset Filter
      </a>
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
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">No</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Judul Pengumuman</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tanggal Pengumuman</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Banner</th>
          @if($canEdit || $canDelete)
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @if(isset($announcements) && count($announcements) > 0)
        @forelse($announcements as $index => $announcement)
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 15px; text-align: center;">{{ $index + 1 }}</td>
          <td style="padding: 15px;">{{ $announcement->title }}</td>
          <td style="padding: 15px;">{{ \Carbon\Carbon::parse($announcement->announcement_date)->format('d M Y') }}</td>
          <td style="padding: 15px;">
            @if($announcement->banner)
            <img src="{{ asset('storage/' . $announcement->banner) }}" alt="Banner" style="max-width: 100px; max-height: 60px; object-fit: cover;">
            @else
            No Banner
            @endif
          </td>
          @if($canEdit || $canDelete)
          <td style="padding: 15px; text-align: center;">
            <a href="{{ route('pengumuman.edit', $announcement->id) }}" style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
              Ubah
            </a>
            <button type="button" onclick="showDeleteModal({{ $announcement->id }})" style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
              Hapus
            </button>
          </td>
          @endif
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan="4" style="padding: 15px; text-align: center;">Tidak ada data pengumuman</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>

  @if(isset($announcements))
  <div style="margin-top: 20px;">
    {{ $announcements->links() }}
  </div>
  @endif
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
    form.action = `{{ route('pengumuman.index') }}/${id}`;
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

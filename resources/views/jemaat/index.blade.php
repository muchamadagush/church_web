@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Data Jemaat</h1>
    @if(\App\Helpers\PermissionHelper::hasPermission('create', 'jemaat'))
    <a href="{{ route('jemaat.create') }}" class="button-detail">
      + Tambah Data
    </a>
    @endif
  </div>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div style="max-width: 400px; width: 100%;">
      <form action="{{ route('jemaat.index') }}" method="GET">
        <div style="display: flex; gap: 10px;">
          <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari jemaat..." style="flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
          <button type="submit" style="padding: 8px 16px; background-color: #c9a035; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Cari
          </button>
          @if(!empty($search))
          <a href="{{ route('jemaat.index') }}" style="padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 4px; text-decoration: none; display: inline-block;">
            Reset
          </a>
          @endif
        </div>
      </form>
    </div>
    <div>
      <a href="{{ route('jemaat.export') }}" class="button-detail">
        Download
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 8px;">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
      </a>
    </div>
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
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Username</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Nama Lengkap</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tanggal Lahir</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Alamat</th>
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @if(isset($jemaats) && count($jemaats) > 0)
        @forelse($jemaats as $index => $jemaat)
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 15px; text-align: center;">{{ $index + 1 }}</td>
          <td style="padding: 15px;">{{ $jemaat->username }}</td>
          <td style="padding: 15px;">{{ $jemaat->fullname }}</td>
          <td style="padding: 15px;">{{ $jemaat->dateofbirth ? date('d M Y', strtotime($jemaat->dateofbirth)) : '-' }}</td>
          <td style="padding: 15px;">{{ $jemaat->address }}</td>
          <td style="padding: 15px; text-align: center;">
            <a href="{{ route('jemaat.edit', $jemaat) }}" style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
              Ubah
            </a>
            <button type="button" onclick="showDeleteModal({{ $jemaat->id }})" style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
              Hapus
            </button>
          </td>
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan="6" style="padding: 15px; text-align: center;">Tidak ada data jemaat</td>
        </tr>
        @endif
      </tbody>
      <div>
        @if(isset($jemaats))
        {{ $jemaats->links() }}
        @endif
      </div>
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
    form.action = `{{ route('jemaat.index') }}/${id}`;
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

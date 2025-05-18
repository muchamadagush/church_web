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

  <!-- Removed background, padding, border-radius and box-shadow -->
  <div style="margin-bottom: 20px;">
    <!-- Search & Filter Section -->
    <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
      <!-- Search Bar - Adjusted width -->
      <div style="flex: 1; max-width: 350px;">
        <form action="{{ route('jemaat.index') }}" method="GET" id="searchForm">
          <div style="position: relative;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Nama Jemaat..." style="width: 100%; padding: 10px 40px 10px 15px; border: 1px solid #ddd; border-radius: 25px; box-sizing: border-box; font-size: 14px;">
            <!-- Add hidden input for church_id to preserve filter when searching -->
            @if($churchId)
            <input type="hidden" name="church_id" value="{{ $churchId }}">
            @endif
            <button type="submit" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
            </button>
          </div>
        </form>
      </div>

      <!-- Church Filter with clearer dropdown icon -->
      <div style="width: 250px; position: relative;">
        <select name="church_id" id="churchFilter" style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 25px; appearance: none; padding-right: 40px; background-color: white;">
          @foreach($churches ?? [] as $church)
          <option value="{{ $church->id }}" {{ ($churchId == $church->id) ? 'selected' : '' }}>
            {{ $church->name }}
          </option>
          @endforeach
        </select>
        <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#666" viewBox="0 0 16 16">
            <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
          </svg>
        </div>
      </div>

      <!-- Reset Button -->
      @if(!empty($search) || request('church_id'))
      <div>
        <a href="{{ route('jemaat.index') }}" style="padding: 10px 20px; background-color: #f8f9fa; color: #333; border: 1px solid #ddd; border-radius: 25px; text-decoration: none; display: inline-block;">
          Reset Filter
        </a>
      </div>
      @endif

      <!-- Export Button -->
      @if(\App\Helpers\PermissionHelper::hasPermission('download', 'jemaat'))
      <div style="margin-left: auto;">
        <a href="{{ route('jemaat.export') }}?search={{ $search ?? '' }}&church_id={{ $churchId ?? '' }}" class="button-detail" style="border-radius: 25px; display: flex; align-items: center;">
          Download
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 8px;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </a>
      </div>
      @endif
    </div>

    <!-- Church & Gembala Info Note - Updated to use dynamic data -->
    <div style="padding: 10px 15px 10px 0; margin-bottom: 15px;">
      <div style="margin-bottom: 5px;"><strong>Nama Gereja:</strong> {{ $currentChurch->name ?? 'Belum dipilih' }}</div>
      <div style="margin-bottom: 5px;"><strong>Nama Gembala:</strong> {{ $pastor->fullname ?? 'Tidak tersedia' }}</div>
      <div><strong>Jumlah KK:</strong> {{ $kkCount ?? 0 }}</div>
    </div>
  </div>

  @if(session('success'))
  <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
    {{ session('success') }}
  </div>
  @endif

  <div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead style="background: #f0f0f0;">
        <tr>
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">No</th>
          <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Status</th>
          <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Nama Lengkap</th>
          <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Tempat Lahir</th>
          <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Tanggal Lahir</th>
          <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Jenis Kelamin</th>
          @if(\App\Helpers\PermissionHelper::hasPermission('age', 'jemaat'))
          <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Usia</th>
          @endif
          @if($canEdit || $canDelete)
          <th style="padding: 15px; text-align: center; border-bottom: 1px solid #dee2e6;">Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @if(isset($jemaats) && count($jemaats) > 0)
        @foreach($jemaats as $index => $jemaat)
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 15px; text-align: center;">{{ $index + 1 }}</td>
          <td style="padding: 15px;">
            @if($jemaat->family_status == 'kepala_keluarga')
            Ayah
            @elseif($jemaat->family_status == 'istri')
            Istri
            @elseif($jemaat->family_status == 'anak')
            Anak
            @else
            {{ $jemaat->family_status }}
            @endif
          </td>
          <td style="padding: 15px;">{{ $jemaat->fullname }}</td>
          <td style="padding: 15px;">{{ $jemaat->birthplace ?? '-' }}</td>
          <td style="padding: 15px;">{{ $jemaat->dateofbirth ? date('d M Y', strtotime($jemaat->dateofbirth)) : '-' }}</td>
          <td style="padding: 15px;">{{ $jemaat->gender == 'male' ? 'Laki Laki' : 'Perempuan' }}</td>
          @if(\App\Helpers\PermissionHelper::hasPermission('age', 'jemaat'))
          <td style="padding: 15px;">
            @php
            $birthDate = new DateTime($jemaat->dateofbirth);
            $today = new DateTime('today');
            $age = $birthDate->diff($today)->y;
            $isParent = ($jemaat->family_status == 'kepala_keluarga' || $jemaat->family_status == 'istri');

            // If under 30 but is a parent (ayah/istri), categorize as Dewasa
            if ($age >= 31 || $isParent) {
            echo "$age(Dewasa)";
            } elseif ($age >= 18) {
            echo "$age(Pemuda)";
            } elseif ($age >= 13) {
            echo "$age(Remaja)";
            } else {
            echo "$age(Sekolah Minggu)";
            }
            @endphp
          </td>
          @endif
          @if($canEdit || $canDelete)
          <td style="padding: 15px; text-align: center;">
            <a href="{{ route('jemaat.edit', $jemaat) }}" style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
              Ubah
            </a>
            <button type="button" onclick="showDeleteModal({{ $jemaat->id }})" style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
              Hapus
            </button>
          </td>
          @endif
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan="7" style="padding: 15px; text-align: center;">Tidak ada data jemaat</td>
        </tr>
        @endif
      </tbody>
    </table>
    <div style="padding: 15px;">
      @if(isset($jemaats))
      {{ $jemaats->links() }}
      @endif
    </div>
  </div>

  <!-- Church & Gembala Info Note - Updated to use dynamic data -->
  <div style="padding: 10px 15px 10px 0; margin-top: 15px;">
    <div style="margin-bottom: 5px;"><strong>Keterangan:</strong></div>
    <div style="margin-bottom: 5px;">1. Untuk status hanya diisi apabila dalam satu kartu keluarga user tersebut</div>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
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

  // Add church filter auto-submit
  document.getElementById('churchFilter').addEventListener('change', function() {
    const searchForm = document.getElementById('searchForm');
    const searchValue = document.querySelector('input[name="search"]').value;
    
    // Clear any existing church_id inputs
    const existingChurchInputs = searchForm.querySelectorAll('input[name="church_id"]');
    existingChurchInputs.forEach(input => input.remove());
    
    // Add the selected church_id
    const churchFilter = document.createElement('input');
    churchFilter.type = 'hidden';
    churchFilter.name = 'church_id';
    churchFilter.value = this.value;
    searchForm.appendChild(churchFilter);
    
    searchForm.submit();
  });

</script>
@endsection

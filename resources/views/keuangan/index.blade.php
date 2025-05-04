@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Data Keuangan</h1>
    <a href="{{ route('keuangan.create') }}" class="button-detail">
      + Tambah Data
    </a>
  </div>

  <div style="display: flex; justify-content: end; align-items: center; margin-bottom: 20px;">
    <div>
      <a href="{{ route('keuangan.download') }}" class="button-detail">
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

  <!-- Main Data Table -->
  <div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead style="background: #f5f5f5;">
        <tr>
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">No</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Tanggal</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Debit (Pemasukan)</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Kredit (Pengeluaran)</th>
          <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Keterangan</th>
          <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($keuangan as $index => $item)
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 15px; text-align: center;">{{ $index + 1 }}</td>
          <td style="padding: 15px;">{{ \Carbon\Carbon::parse($item->tanggal)->format('d F Y') }}</td>
          <td style="padding: 15px;">{{ $item->debit ? number_format($item->debit, 0, ',', '.') : '' }}</td>
          <td style="padding: 15px;">{{ $item->kredit ? number_format($item->kredit, 0, ',', '.') : '' }}</td>
          <td style="padding: 15px;">{{ $item->keterangan }}</td>
          <td style="padding: 15px; text-align: center;">
            <a href="{{ route('keuangan.edit', $item) }}" style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
              Ubah
            </a>
            <button type="button" onclick="showDeleteModal({{ $item->id }})" style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
              Hapus
            </button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="padding: 15px; text-align: center;">Tidak ada data keuangan</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination Links -->
  <div style="display: flex; justify-content: end;">
    {{ $keuangan->links('pagination::bootstrap-4') }}
  </div>

  <!-- Financial Summary Table -->
  <h3 style="margin-top: 40px">Summary Keuangan</h3>
  <div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead style="background: #f5f5f5;">
        <tr>
          <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6; background-color: #e9ecef;">Total Debit (Pemasukan)</th>
          <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6; background-color: #e9ecef;">Total Kredit (Pengeluaran)</th>
          <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6; background-color: #e9ecef;">Sisa Keuangan</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding: 12px; text-align: center; font-weight: 500; color: #28a745;">Rp. {{ number_format($totalDebit, 0, ',', '.') }}</td>
          <td style="padding: 12px; text-align: center; font-weight: 500; color: #dc3545;">Rp. {{ number_format($totalKredit, 0, ',', '.') }}</td>
          <td style="padding: 12px; text-align: center; font-weight: 700; color: {{ $total >= 0 ? '#28a745' : '#dc3545' }};">Rp. {{ number_format(abs($total), 0, ',', '.') }}</td>
        </tr>
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
    form.action = `{{ route('keuangan.index') }}/${id}`;
    modal.style.display = 'flex';
  }

  function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
  }

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
      hideDeleteModal();
    }
  });
</script>

<style>
    /* Custom styling for pagination */
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 20px 0;
        justify-content: center;
    }
    
    .page-item {
        margin: 0 2px;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    .page-item.active .page-link {
        background-color: #c9a035;
        border-color: #c9a035;
    }
    
    .page-link {
        position: relative;
        display: block;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        color: #333;
        background-color: #fff;
        border: 1px solid #dee2e6;
        text-decoration: none;
    }
    
    .page-link:hover {
        color: #333;
        background-color: #e9ecef;
        border-color: #dee2e6;
        text-decoration: none;
    }
</style>
@endsection

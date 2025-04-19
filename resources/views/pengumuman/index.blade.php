@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Data Pengumuman</h2>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <form action="{{ route('pengumuman.index') }}" method="GET" style="position: relative;">
            <input type="text" name="search" placeholder="Cari Judul Pengumuman"
                value="{{ request('search') }}"
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 250px;">
            <button type="submit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>
        <div>
            <a href="{{ route('pengumuman.create') }}" class="button-detail">Tambah Pengumuman</a>
            <a href="#" class="button-detail">
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
        <div style="background: #4CAF50; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f5f5f5;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Nama Gereja</th>
                    <th style="padding: 15px; text-align: left;">Judul Pengumuman</th>
                    <th style="padding: 15px; text-align: left;">Tanggal Pengumuman</th>
                    <th style="padding: 15px; text-align: left;">Banner</th>
                    <th style="padding: 15px; text-align: left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($announcements) && count($announcements) > 0)
                    @foreach($announcements as $announcement)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;">{{ $announcement->church->name }}</td>
                        <td style="padding: 15px;">{{ $announcement->title }}</td>
                        <td style="padding: 15px;">{{ \Carbon\Carbon::parse($announcement->announcement_date)->format('d M Y') }}</td>
                        <td style="padding: 15px;">
                            @if($announcement->banner)
                                <img src="{{ asset('storage/' . $announcement->banner) }}" alt="Banner" style="max-width: 100px; max-height: 60px; object-fit: cover;">
                            @else
                                No Banner
                            @endif
                        </td>
                        <td style="padding: 15px;">
                            <div style="display: flex; gap: 5px;">
                                <a href="{{ route('pengumuman.edit', $announcement->id) }}"
                                    style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
                                    Ubah
                                </a>
                                <button type="button" onclick="showDeleteModal({{ $announcement->id }})"
                                    style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="padding: 15px; text-align: center;">Tidak ada data pengumuman</td>
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
            <form id="deleteForm" action="" method="POST" style="display: inline;">
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
        form.action = `{{ url('pengumuman') }}/${id}`; // Fixed route construction
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

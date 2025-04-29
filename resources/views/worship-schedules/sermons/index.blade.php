@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="color: #333; margin: 0;">Jadwal Pertukaran Khotbah</h1>
        <a href="{{ route('worship-schedules.sermons.create') }}" class="button-detail">+ Tambah Data</a>
    </div>

    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse; border-radius: 8px;">
            <thead style=" border-radius: 8px;>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">No</th>
                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Pengkhotbah</th>
                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Jadwal Khotbah</th>
                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $schedule)
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 15px; text-align: left;">{{ $index + 1 }}</td>
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="font-weight: 500; color: #333;">{{ $schedule->pengkhotbah }}</div>
                        </td>
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($schedule->details as $detail)
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-weight: 500;">{{ $detail->church->name }}</span>
                                        <span style="color: #666;">•</span>
                                        <span style="color: #D4A44D;">{{ ucfirst($detail->month) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: center; vertical-align: top;">
                            <div style="display: flex; gap: 5px;">
                                <a href="{{ route('worship-schedules.sermons.edit', $schedule->id) }}"
                                    style="background: #ff9f43; color: white; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 14px;">
                                    Ubah
                                </a>
                                <button type="button" onclick="showDeleteModal({{ $schedule->id }})"
                                    style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
            <button onclick="hideDeleteModal()" 
                    style="background: #dc3545; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px;">
                Batal
            </button>
        </div>
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
</script>
@endsection

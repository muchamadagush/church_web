@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Data Jemaat</h2>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <form action="{{ route('jemaat.index') }}" method="GET" style="position: relative;">
            <input type="text" name="search" placeholder="Cari Nama Jemaat" 
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
            <a href="{{ route('jemaat.create') }}" class="button-detail">Tambah Jemaat</a>
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
                    <th style="padding: 15px; text-align: left;">Username</th>
                    <th style="padding: 15px; text-align: left;">Nama Lengkap</th>
                    <th style="padding: 15px; text-align: left;">Email</th>
                    <th style="padding: 15px; text-align: left;">Tanggal Lahir</th>
                    <th style="padding: 15px; text-align: left;">Alamat</th>
                    <th style="padding: 15px; text-align: left;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($jemaats) && count($jemaats) > 0)
                    @foreach($jemaats as $jemaat)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;">{{ $jemaat->username }}</td>
                        <td style="padding: 15px;">{{ $jemaat->fullname }}</td>
                        <td style="padding: 15px;">{{ $jemaat->email }}</td>
                        <td style="padding: 15px;">{{ $jemaat->dateofbirth ? date('d M Y', strtotime($jemaat->dateofbirth)) : '-' }}</td>
                        <td style="padding: 15px;">{{ $jemaat->address }}</td>
                        <td style="padding: 15px;">
                            <a href="{{ route('jemaat.edit', $jemaat) }}" 
                                style="background: #ff9f43; color: white; border: none; padding: 5px 15px; border-radius: 4px; margin-right: 5px; text-decoration: none; display: inline-block;">
                                Ubah
                            </a>
                            <form action="{{ route('jemaat.destroy', $jemaat) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                    style="background: #ff4757; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
                                    Delete
                                </button>
                            </form>
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
@endsection

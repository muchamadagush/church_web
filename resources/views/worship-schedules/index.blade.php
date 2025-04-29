@extends('layouts.app')

@section('content')
<div style="display: grid; grid-template-columns: (auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; align-items: center; margin: 0 auto;">
    <!-- Jadwal Doa -->
    <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
        <img src="{{ asset('images/prayer-icon.svg') }}" alt="Prayer" class="icon">
        <div>
            <h2>Jadwal Doa Wilayah</h2>
            <p>Lihat Jadwal Doa Wilayah</p>
            <a href="{{ route('worship-schedules.prayer-schedules.index') }}" class="button-detail">
                Lihat Detail
            </a>
        </div>
    </div>

    <!-- Jadwal Pertukaran Khotbah -->
    <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
        <img src="{{ asset('images/sermon-icon.svg') }}" alt="Sermon" class="icon">
        <div>
        <h2>Jadwal Pertukaran Khotbah</h2>
        <p>Pengumuman Terbaru Pertukaran Khotbah</p>
        <a href="{{ route('worship-schedules.sermons.index') }}" class="button-detail">
            Lihat Detail
        </a>
</div>
    </div>

    <!-- Jadwal Perkunjungan Ketua Wilayah -->
    <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
        <img src="{{ asset('images/leader-icon.svg') }}" alt="Leader" class="icon">
        <div>
        <h2>Jadwal Perkunjungan Ketua Wilayah</h2>
        <p>Lihat Jadwal Ketua Wilayah</p>
        <a href="{{ route('worship-schedules.visits.index') }}" class="button-detail">
            Lihat Detail
        </a>
        </div>
    </div>

    <!-- Jadwal Perkunjungan Kaum Wanita -->
    <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
        <img src="{{ asset('images/women-icon.svg') }}" alt="Women" class="icon">
        <div>
        <h2>Jadwal Perkunjungan Kaum Wanita</h2>
        <p>Lihat Jadwal Kaum Wanita</p>
        <a href="{{ route('worship-schedules.women-visits.index') }}" class="button-detail">
            Lihat Detail
        </a>
        </div>
    </div>
</div>
@endsection

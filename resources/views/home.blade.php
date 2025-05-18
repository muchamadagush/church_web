@extends('layouts.app')

@section('content')
<div style="display: grid; grid-template-columns: (auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; align-items: center; margin: 0 auto;">
  <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
    <img src="{{ asset('images/people.svg') }}" alt="Data Jemaat" class="icon" style="width: 100px; height: 100px;">
    <div>
      <h2>Data Jemaat</h2>
      <p>Statistik Jemaat, Statistik Usia dan Lainya</p>
      <a href="{{ route('jemaat.index') }}" class="button-detail">Lihat Detail</a>
    </div>
  </div>

  <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
    <img src="{{ asset('images/speaker.svg') }}" alt="Pengumuman" class="icon" style="width: 100px; height: 100px;">
    <div>
      <h2>Pengumuman</h2>
      <p>Pengumuman Terbaru Kegiatan Gereja</p>
      <a href="{{ route('pengumuman.index') }}" class="button-detail">Lihat Detail</a>
    </div>
  </div>

  @if(\App\Helpers\PermissionHelper::hasPermission('view', 'worship-schedules'))
  <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
    <img src="{{ asset('images/calendar.svg') }}" alt="Jadwal Ibadah" class="icon" style="width: 100px; height: 100px;">
    <div>
      <h2>Jadwal Ibadah</h2>
      <p>Lihat Jadwal Ibadah</p>
      <a href="{{ route('worship-schedules.index') }}" class="button-detail">Lihat Detail</a>
    </div>
  </div>
  @endif

  @if(\App\Helpers\PermissionHelper::hasPermission('view', 'keuangan'))
  <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
    <img src="{{ asset('images/finance.svg') }}" alt="Jadwal Ibadah" class="icon" style="width: 100px; height: 100px;">
    <div>
      <h2>Keuangan</h2>
      <p>Lihat Keuangan</p>
      <a href="{{ route('keuangan.index') }}" class="button-detail">Lihat Detail</a>
    </div>
  </div>
  @endif

  <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
    <img src="{{ asset('images/history.png') }}" alt="Jadwal Ibadah" class="icon" style="width: 100px; height: 100px;">
    <div>
      <h2>Sejarah</h2>
      <p>Lihat Sejarah</p>
      <a href="{{ route('history') }}" class="button-detail">Lihat Detail</a>
    </div>
  </div>

  <div class="card" style="display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px;">
    <img src="{{ asset('images/about.png') }}" alt="Jadwal Ibadah" class="icon" style="width: 100px; height: 100px;">
    <div>
      <h2>Tentang Kami</h2>
      <p>Lihat Tentang Kami</p>
      <a href="{{ route('about') }}" class="button-detail">Lihat Detail</a>
    </div>
  </div>
</div>
@endsection

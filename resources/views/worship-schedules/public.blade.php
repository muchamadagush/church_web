@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Jadwal Ibadah - {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h1>
    </div>

    <!-- Sermon Schedules -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="padding: 15px; background-color: #f8f9fa; margin: 0; border-bottom: 1px solid #dee2e6; color: #4839EB;">
            Jadwal Tukar Mimbar
        </h2>
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">Tanggal</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Gereja Tujuan</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Gereja Asal</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Pembicara</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sermonSchedules as $schedule)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; text-align: center;">{{ date('d M Y', strtotime($schedule->date)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->destination_church }}</td>
                        <td style="padding: 12px;">{{ $schedule->origin_church }}</td>
                        <td style="padding: 12px;">{{ $schedule->speaker }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 12px; text-align: center;">Tidak ada jadwal tukar mimbar untuk bulan ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Prayer Schedules -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="padding: 15px; background-color: #f8f9fa; margin: 0; border-bottom: 1px solid #dee2e6; color: #4839EB;">
            Jadwal Doa
        </h2>
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">Tanggal</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Lokasi</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Waktu</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prayerSchedules as $schedule)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; text-align: center;">{{ date('d M Y', strtotime($schedule->date)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->location }}</td>
                        <td style="padding: 12px;">{{ date('H:i', strtotime($schedule->time)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 12px; text-align: center;">Tidak ada jadwal doa untuk bulan ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Visit Schedules -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="padding: 15px; background-color: #f8f9fa; margin: 0; border-bottom: 1px solid #dee2e6; color: #4839EB;">
            Jadwal Kunjungan Kepala Villa
        </h2>
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">Tanggal</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Lokasi</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Waktu</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitSchedules as $schedule)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; text-align: center;">{{ date('d M Y', strtotime($schedule->date)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->location }}</td>
                        <td style="padding: 12px;">{{ date('H:i', strtotime($schedule->time)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 12px; text-align: center;">Tidak ada jadwal kunjungan untuk bulan ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Women Visit Schedules -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="padding: 15px; background-color: #f8f9fa; margin: 0; border-bottom: 1px solid #dee2e6; color: #4839EB;">
            Jadwal Kunjungan Perempuan
        </h2>
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">Tanggal</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Lokasi</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Waktu</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($womenVisitSchedules as $schedule)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; text-align: center;">{{ date('d M Y', strtotime($schedule->date)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->location }}</td>
                        <td style="padding: 12px;">{{ date('H:i', strtotime($schedule->time)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 12px; text-align: center;">Tidak ada jadwal kunjungan perempuan untuk bulan ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Youth Visit Schedules -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="padding: 15px; background-color: #f8f9fa; margin: 0; border-bottom: 1px solid #dee2e6; color: #4839EB;">
            Jadwal Kunjungan Pemuda
        </h2>
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">Tanggal</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Lokasi</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Waktu</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($youthVisitSchedules as $schedule)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; text-align: center;">{{ date('d M Y', strtotime($schedule->date)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->location }}</td>
                        <td style="padding: 12px;">{{ date('H:i', strtotime($schedule->time)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 12px; text-align: center;">Tidak ada jadwal kunjungan pemuda untuk bulan ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Christmas Schedules -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 style="padding: 15px; background-color: #f8f9fa; margin: 0; border-bottom: 1px solid #dee2e6; color: #4839EB;">
            Jadwal Natal
        </h2>
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">Tanggal</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Lokasi</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Waktu</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($christmasSchedules as $schedule)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; text-align: center;">{{ date('d M Y', strtotime($schedule->date)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->location }}</td>
                        <td style="padding: 12px;">{{ date('H:i', strtotime($schedule->time)) }}</td>
                        <td style="padding: 12px;">{{ $schedule->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 12px; text-align: center;">Tidak ada jadwal natal untuk bulan ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

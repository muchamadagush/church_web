@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Data Jemaat Keseluruhan</h1>
        <a href="{{ route('jemaat.index') }}" class="button-detail" style="border-radius: 25px;">
            Kembali ke Data Jemaat
        </a>
    </div>

    <div class="card" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead style="background: #f0f0f0;">
                <tr>
                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; vertical-align: middle; row-span: 2;">No</th>
                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; vertical-align: middle; row-span: 2;">Nama Gereja</th>
                    <th style="padding: 15px; text-align: center; border-bottom: 1px solid #dee2e6; vertical-align: middle; column-span: 2; text-align: center;" colspan="2">Jenis Kelamin</th>
                    <th style="padding: 15px; text-align: center; border-bottom: 1px solid #dee2e6; vertical-align: middle; column-span: 4; text-align: center;" colspan="4">Kategori Umur</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dee2e6;">Pria</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dee2e6;">Wanita</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dee2e6;">S. Minggu</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dee2e6;">Remaja</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dee2e6;">Pemuda</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dee2e6;">Dewasa</th>
                </tr>
            </thead>
            <tbody>
                @forelse($churchStats as $index => $stat)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; text-align: center;">{{ $index + 1 }}</td>
                    <td style="padding: 12px;">{{ $stat['church']->name }}</td>
                    <td style="padding: 12px; text-align: center;">{{ $stat['maleCount'] }}</td>
                    <td style="padding: 12px; text-align: center;">{{ $stat['femaleCount'] }}</td>
                    <td style="padding: 12px; text-align: center;">{{ $stat['totalSMinggu'] }}</td>
                    <td style="padding: 12px; text-align: center;">{{ $stat['totalRemaja'] }}</td>
                    <td style="padding: 12px; text-align: center;">{{ $stat['totalPemuda'] }}</td>
                    <td style="padding: 12px; text-align: center;">{{ $stat['totalDewasa'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 15px; text-align: center;">Tidak ada data jemaat</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .card {
            overflow-x: auto;
        }
    }
</style>
@endsection

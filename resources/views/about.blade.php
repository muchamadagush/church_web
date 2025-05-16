@extends('layouts.app')

@section('content')
<div style="width: 100%; max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="width: 100%; margin: 0 auto; text-align: center;">
      <div style="position: relative;">
          <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('{{ asset('images/logo-ggp.svg') }}'); background-repeat: no-repeat; background-position: center; opacity: 0.1; z-index: -1;"></div>
            <div style="margin-bottom: 40px;">
                <h2 style="color: #ffc107; font-size: 24px; font-weight: bold; margin-bottom: 20px; position: relative; display: inline-block;">VISI</h2>
                <div style="width: 60px; height: 3px; background-color: #ffc107; margin: 0 auto 20px auto;"></div>
                <div style="margin-top: 20px;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; font-weight: bold;">GEREJA IDAMAN KRISTUS</h3>
                </div>
            </div>
            
            <div style="margin-bottom: 40px;">
                <h2 style="color: #ffc107; font-size: 24px; font-weight: bold; margin-bottom: 20px; position: relative; display: inline-block;">MISI</h2>
                <div style="width: 60px; height: 3px; background-color: #ffc107; margin: 0 auto 20px auto;"></div>
                <div style="margin-top: 20px; margin: 0 auto;">
                    <p style="margin-bottom: 10px; line-height: 1.6;">1. Membangun Persekutuan yang Hidup dan Nyata</p>
                    <p style="margin-bottom: 10px; line-height: 1.6;">2. Mengaktualisasikan Penyembahan yang Benar, Menjangkau, dan Memuridkan Orang</p>
                    <p style="margin-bottom: 10px; line-height: 1.6;">3. Mengoptimalkan Pembinaan yang Cukup dan Merata serta Berkesinambungan</p>
                    <p style="margin-bottom: 10px; line-height: 1.6;">4. Meningkatkan Penginjilan ke Dalam dan Keluar</p>
                    <p style="margin-bottom: 10px; line-height: 1.6;">5. Menciptakan Pengelolaan yang Baik dan Rohani</p>
                </div>
            </div>

            <div style="margin-bottom: 40px;">
                <h2 style="color: #ffc107; font-size: 24px; font-weight: bold; margin-bottom: 20px; position: relative; display: inline-block;">VISI Antara 2021 - 2026</h2>
                <div style="width: 60px; height: 3px; background-color: #ffc107; margin: 0 auto 20px auto;"></div>
                <div style="margin-top: 20px;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; font-weight: bold;">"THREEFOLD"</h3>
                </div>
            </div>

            <div style="margin-bottom: 40px;">
                <h2 style="color: #ffc107; font-size: 24px; font-weight: bold; margin-bottom: 20px; position: relative; display: inline-block;">Motto</h2>
                <div style="width: 60px; height: 3px; background-color: #ffc107; margin: 0 auto 20px auto;"></div>
                <div style="margin-top: 20px;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; font-weight: bold;">"Exceed"</h3>
                    <p style="margin-top: 20px; font-weight: 500;">2 Korintus 8:3</p>
                    <p style="margin-bottom: 10px; line-height: 1.6;">Aku bersaksi, bahwa mereka telah memberikan menurut kemampuan mereka, bahkan melampaui kemampuan mereka</p>
                </div>
            </div>
            </div>

            <div style="margin-bottom: 40px;">
                <h2 style="color: #ffc107; font-size: 24px; font-weight: bold; margin-bottom: 20px; position: relative; display: inline-block;">STRUKTUR PENGURUS</h2>
                <div style="width: 60px; height: 3px; background-color: #ffc107; margin: 0 auto 20px auto;"></div>
                <div style="margin-top: 20px;">
                    <img src="{{ asset('images/struktur.svg') }}" style="max-width: 100%; height: auto;" alt="Struktur Organisasi">
                </div>
            </div>
      </div>
    </div>
</div>
@endsection

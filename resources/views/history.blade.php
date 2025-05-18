@extends('layouts.app')

@section('content')
<div style="width: 100%; max-width: 1200px; margin: 0 auto; padding: 20px;">
  <div style="position: relative;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('{{ asset('images/logo-ggp.svg') }}'); background-repeat: no-repeat; background-position: center; opacity: 0.1; z-index: -1;"></div>
    <h1 style="text-align: center; color: #D4A44D; margin-bottom: 30px; font-size: 28px; position: relative;">
      Sejarah
      <span style="content: ''; display: block; width: 100px; height: 2px; background-color: #D4A44D; margin: 15px auto 0;"></span>
    </h1>

    <div style="text-align: justify; line-height: 1.6; margin-bottom: 40px; color: #333;">
      <p>
        GGP Wilayah Baruppu' di Barereŋ Kec. Baruppu' Toraja Utara Sulawesi Selatan. Pada Tahun 1942 masuk ke Baruppu' yang di bawah oleh Alm. Yunus Pooŋetik, Alm. Liu Hasibuan dan Alm. Y. Massasaŋ. GGP ini berulah di Poŋglamba yang dipembalikan oleh Pdt. Sarŋo dan kemudian dipindahkan ke Pa'Kappan yang di gembalakan oleh Pdt. Arruŋ.
      </p>
      <p>
        Seiring berjalannya waktu kini GGP Wilayah Baruppu' tersebut mengalami perkembangan, di bawah ini pimpinan ketua wilayah Pdt. FRITS NATUN, S.Th dan wakil I Pdt. DANIEL JOHNI, S.Th dan wakil II Pdm. MESAKH BENNU', S.Th dengan memiliki jumlah 212 Kepala Keluarga 1.035 Jiwa.
      </p>
    </div>

    <h1 style="text-align: center; color: #D4A44D; margin-bottom: 30px; font-size: 28px; position: relative;">
      Galeri
      <span style="content: ''; display: block; width: 100px; height: 2px; background-color: #D4A44D; margin: 15px auto 0;"></span>
    </h1>

    <div style="text-align: center; margin-bottom: 40px;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/church2.svg') }}" alt="Interior Gereja 2" style="width: 100%; height: 250px; object-fit: cover; display: block;">
        </div>
        <div style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/church1.svg') }}" alt="Interior Gereja 1" style="width: 100%; height: 250px; object-fit: cover; display: block;">
        </div>
      </div>
    </div>
  </div>

  <h1 style="text-align: center; color: #D4A44D; font-size: 28px; position: relative;">
    LOKASI
    <span style="content: ''; display: block; width: 100px; height: 2px; background-color: #D4A44D; margin: 15px auto 0;"></span>
  </h1>

  <div style="text-align: center; margin-bottom: 40px;">
    <a href="https://maps.app.goo.gl/EnM5YygM9kX7pN1M7" target="_blank" rel="noopener noreferrer">
      <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3984.8704420107924!2d119.77821499999999!3d-2.8537470000000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMsKwNTEnMTMuNSJTIDExOcKwNDYnNDEuNiJF!5e0!3m2!1sid!2sid!4v1747557770746!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </a>
  </div>
</div>

<style>
  @media (max-width: 768px) {
    [style*="width: 60%"] {
      width: 90% !important;
    }
  }

</style>
@endsection

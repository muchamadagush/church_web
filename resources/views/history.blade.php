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
        GGP Wilayah Baruppu’ di Barereng Kec. Baruppu’ Toraja Utara Sulawesi Selatan. Pada Tahun 1942 masuk ke Baruppu’ yang di bawah oleh Alm. Yunus Pongtasik, Alm. Lisu Hasibuan dan Alm. Y. Massang. GPP ini bermula di Ponglamba yang digembalakan oleh Pdt. Siang dan kemudian dipindahkan ke Pa’Kappan yang di gembalakan oleh Pdt. Arrung.
      </p>
      <p>
        Seiring berjalannya waktu kini GGP Wilayah Baruppu’ tersebut mengalami perkembangan, di bawah pimpinan ketua wilayah Pdt. FRITS NATUN, S.Th dan wakil I Pdt. DANIEL JOHNI, S.Th dan wakil II Pdm. MESAKH BENNU’, S.Th dengan memiliki jumlah 212 Kepala Keluarga 1.035 Jiwa
      </p>
    </div>

    <h1 style="text-align: center; color: #D4A44D; margin-bottom: 30px; font-size: 28px; position: relative;">
      Galeri
      <span style="content: ''; display: block; width: 100px; height: 2px; background-color: #D4A44D; margin: 15px auto 0;"></span>
    </h1>

    <div style="text-align: center; margin-bottom: 40px;">
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px;">
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/1.jpg') }}" alt="Interior Gereja 1" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/2.jpg') }}" alt="Interior Gereja 2" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/3.jpg') }}" alt="Kegiatan Gereja 1" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/4.jpg') }}" alt="Kegiatan Gereja 2" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/5.jpg') }}" alt="Kegiatan Gereja 3" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/6.jpg') }}" alt="Acara Gereja" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/7.jpg') }}" alt="Interior Gereja 1" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/8.jpg') }}" alt="Interior Gereja 2" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/9.jpg') }}" alt="Kegiatan Gereja 1" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/10.jpg') }}" alt="Kegiatan Gereja 2" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/11.jpg') }}" alt="Kegiatan Gereja 3" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/12.jpg') }}" alt="Acara Gereja" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/13.jpg') }}" alt="Kegiatan Gereja 2" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/14.jpg') }}" alt="Kegiatan Gereja 3" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
        <div class="gallery-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
          <img src="{{ asset('images/15.jpg') }}" alt="Acara Gereja" style="width: 100%; height: 220px; object-fit: cover; display: block; cursor: pointer;" onclick="openModal(this.src)">
        </div>
      </div>
    </div>

    <!-- Modal for image display -->
    <div id="imageModal" style="display: none; position: fixed; z-index: 1000; padding-top: 50px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9);">
      <span class="close" style="position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
      <img id="modalImage" style="margin: auto; display: block; max-width: 90%; max-height: 80vh;">
    </div>

    <script>
      function openModal(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modal.style.display = "block";
        modalImg.src = src;

        // Add event listener to close when clicking outside the image
        modal.addEventListener('click', function(event) {
          if (event.target === modal || event.target.className === 'close') {
            closeModal();
          }
        });
      }

      function closeModal() {
        document.getElementById('imageModal').style.display = "none";
      }

      // Close modal when ESC key is pressed
      document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
          closeModal();
        }
      });

      // Add close button event listener
      document.querySelector('.close').addEventListener('click', closeModal);

    </script>

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

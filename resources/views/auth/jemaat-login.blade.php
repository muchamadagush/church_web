<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Jemaat GGP</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <!-- Tambahkan referensi Bootstrap dan Bootstrap-datepicker -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</head>
<body style="font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: white;">
  <div id="notification" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); padding: 15px 30px; border-radius: 5px; color: white; font-size: 16px; z-index: 1000; display: none;"></div>

  <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; gap: 60px; padding: 0 20px; box-sizing: border-box;">
    <div style="flex: 1;">
      <h1 style="font-size: 24px; margin-bottom: 30px; font-weight: bold; text-align: center;">LOGIN JEMAAT GGP</h1>
      <form method="POST" action="{{ route('jemaat.login.submit') }}">
        @csrf
        <div style="margin-bottom: 20px; position: relative;">
          <input type="text" name="fullname" placeholder="Masukkan Nama Lengkap" required style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 20px; font-size: 16px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 20px; position: relative;">
          <input type="text" id="datepicker-input" name="dateofbirth" placeholder="Tanggal Lahir (YYYY-MM-DD)" placeholder="Tanggal Lahir (YYYY-MM-DD)" required readonly style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 20px; font-size: 16px; box-sizing: border-box;">
        </div>
        <button type="submit" style="width: 100%; padding: 15px; background-color: #D4A44D; color: white; border: none; border-radius: 20px; font-size: 16px; cursor: pointer; text-transform: uppercase;">Masuk</button>
      </form>

      <div style="display: flex; justify-content: center; margin-top: 15px; font-size: 14px;">
        <a href="{{ route('login') }}" style="color: #D4A44D; text-decoration: none;">Login sebagai Admin/Gembala</a>
      </div>

      <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
        © Gereja Gerakan Pentakosta
      </div>
    </div>
    <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
      <img src="{{ asset('images/logo-ggp.svg') }}" alt="Logo GGP" style="width: 100%; max-width: 500px; height: auto;">
    </div>
  </div>

  <!-- Modal untuk Datepicker -->
  <div class="modal fade" id="datepickerModal" tabindex="-1" aria-labelledby="datepickerModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: fit-content; background: white; border-radius:8px">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="datepickerModalLabel">Pilih Tanggal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="datepicker-container"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="save-date">Ok</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      // Inisialisasi datepicker di dalam modal
      $('#datepicker-container').datepicker({
        format: 'yyyy-mm-dd'
        , autoclose: true
        , todayHighlight: true
      });

      // Tampilkan modal ketika input di-klik
      $('#datepicker-input').on('click', function() {
        $('#datepickerModal').modal('show');
      });

      // Simpan tanggal yang dipilih ke input
      $('#save-date').on('click', function() {
        const selectedDate = $('#datepicker-container').datepicker('getFormattedDate');
        $('#datepicker-input').val(selectedDate);
        $('#datepickerModal').modal('hide');
      });
    });

    // Fungsi untuk menampilkan notifikasi
    function showNotification(message, type) {
      const notification = document.getElementById('notification');
      notification.textContent = message;
      notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336';
      notification.style.display = 'block';

      // Hilangkan notifikasi setelah 3 detik
      setTimeout(() => {
        notification.style.display = 'none';
      }, 3000);
    }

    // Handle form submission
    document.querySelector('form').addEventListener('submit', async (e) => {
      e.preventDefault();

      try {
        const response = await fetch('{{ route("jemaat.login.submit") }}', {
          method: 'POST'
          , headers: {
            'Content-Type': 'application/json'
            , 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
          }
          , body: JSON.stringify({
            fullname: document.querySelector('input[name="fullname"]').value
            , dateofbirth: document.querySelector('input[name="dateofbirth"]').value
          })
        });

        const data = await response.json();

        if (response.ok) {
          showNotification('Berhasil Masuk', 'success');
          setTimeout(() => {
            window.location.href = '{{ route("home") }}';
          }, 1000);
        } else {
          showNotification(data.message || 'Nama lengkap atau tanggal lahir tidak valid', 'error');
        }
      } catch (error) {
        showNotification('Terjadi kesalahan saat login', 'error');
      }
    });

  </script>
</body>
</html>

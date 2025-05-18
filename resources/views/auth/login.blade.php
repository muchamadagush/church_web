<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login GGP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: white;
        }
        .login-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            gap: 60px;
            padding: 0 20px;
            box-sizing: border-box;
        }
        .login-container {
            flex: 1;
        }
        .logo-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .logo-container img {
            width: 100%;
            max-width: 500px;
            height: auto;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
            text-align: center;
        }
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 20px;
            height: 20px;
        }
        .toggle-password img {
            position: absolute;
            width: 20px;
            height: 20px;
            transition: opacity 0.3s;
        }
        .eye-on {
            opacity: 1;
        }
        .eye-off {
            opacity: 0;
        }
        button {
            width: 100%;
            padding: 15px;
            background-color: #D4A44D;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 16px;
            cursor: pointer;
            text-transform: uppercase;
        }
        .copyright {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 30px;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            z-index: 1000;
            display: none;
        }
        .notification.success {
            background-color: #4CAF50;
        }
        .notification.error {
            background-color: #f44336;
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {
            .login-wrapper {
                max-width: 900px;
                gap: 40px;
            }
            .logo-container img {
                max-width: 400px;
            }
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column-reverse;
                gap: 30px;
            }
            .login-container, .logo-container {
                width: 100%;
                max-width: 400px;
            }
            .logo-container img {
                max-width: 350px;
            }
            h1 {
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 0 15px;
            }
            .logo-container img {
                max-width: 250px;
            }
            input {
                padding: 12px;
            }
            button {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div id="notification" class="notification"></div>
    
    <div class="login-wrapper">
        <div class="login-container">
            <h1>LOGIN GGP</h1>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="input-group">
                    <input type="text" name="username" placeholder="Masukkan username" required>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Masukkan Sandi" required>
                    <div class="toggle-password" onclick="togglePassword()">
                        <img src="{{ asset('images/eye-on.svg') }}" class="eye-off" alt="show">
                        <img src="{{ asset('images/eye-off.svg') }}" class="eye-on" alt="hide">
                    </div>
                </div>
                <button type="submit">Masuk</button>
            </form>

            <div style="display: flex; justify-content: center; margin-top: 15px; font-size: 14px;">
                <a href="{{ route('jemaat.login') }}" style="color: #D4A44D; text-decoration: none;">Login sebagai Jemaat</a>
            </div>

            <div class="copyright">
                © Gereja Gerakan Pentakosta
            </div>
        </div>
        <div class="logo-container">
            <img src="{{ asset('images/logo-ggp.svg') }}" alt="Logo GGP">
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeOn = document.querySelector('.eye-on');
            const eyeOff = document.querySelector('.eye-off');

            if (password.type === 'password') {
                password.type = 'text';
                eyeOn.style.opacity = '0';
                eyeOff.style.opacity = '1';
            } else {
                password.type = 'password';
                eyeOn.style.opacity = '1';
                eyeOff.style.opacity = '0';
            }
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
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
                const response = await fetch('{{ route("login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        username: document.querySelector('input[name="username"]').value,
                        password: document.querySelector('input[name="password"]').value
                    })
                });

                const data = await response.json();
                
                if (response.ok) {
                    showNotification('Berhasil Masuk', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("home") }}';
                    }, 1000);
                } else {
                    showNotification(data.message || 'Username tidak ditemukan atau password anda salah', 'error');
                }
            } catch (error) {
                showNotification('Username tidak ditemukan atau password anda salah', 'error');
            }
        });
    </script>
</body>
</html>

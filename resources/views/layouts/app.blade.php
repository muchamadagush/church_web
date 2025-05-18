<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Informasi GGP</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: url("{{ asset('images/bg-dashboard.svg') }}");
      background-color: #D4A44D;
      color: white;
      padding: 80px;
      text-align: center;
    }

    .navbar {
      position: sticky;
      top: 0;
      z-index: 99;
      background: #333333;
      padding: 15px 0;
      overflow-x: auto;
      justify-content: center !important;
    }

    .navbar ul {
      list-style: none;
      margin: 0;
      padding: 0 15px;
      display: flex;
      justify-content: center;
      gap: 20px;
      white-space: nowrap;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      padding: 5px 15px;
      position: relative;
    }

    .navbar a.active {
      color: #D4A44D;
    }

    .navbar a.active::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 15px;
      right: 15px;
      height: 2px;
      background: #D4A44D;
      border-radius: 2px;
    }

    .navbar a:hover {
      color: #D4A44D;
    }

    .content {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      width: -webkit-fill-available;
      width: -moz-available;
      width: stretch;
      max-width: 100%;
      margin: 0 auto;
    }

    /* Modern Scrollbar Styles */
    .content::-webkit-scrollbar {
      width: 8px;
    }

    .content::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    .content::-webkit-scrollbar-thumb {
      background: #D4A44D;
      border-radius: 4px;
    }

    .content::-webkit-scrollbar-thumb:hover {
      background: #c29340;
    }

    /* Firefox scrollbar */
    .content {
      scrollbar-width: thin;
      scrollbar-color: #D4A44D #f1f1f1;
    }

    .card {
      background: white;
      border-radius: 10px;
      padding: 30px;
      /* margin-bottom: 20px; */
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .card h2 {
      margin: 15px 0;
      font-size: 24px;
      color: #333;
    }

    .card p {
      color: #666;
      margin-bottom: 20px;
    }

    .icon {
      width: 60px;
      height: 60px;
      margin-bottom: 15px;
    }

    .button-detail {
      background: #D4A44D;
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
      text-decoration: none;
    }

    .button-detail:hover {
      background: #c29340;
    }

    @media (max-width: 768px) {
      .header {
        padding: 40px 20px;
      }

      .header h1 {
        font-size: 24px;
      }

      .navbar ul {
        justify-content: flex-start;
      }

      .card {
        padding: 20px;
      }
    }

  </style>
</head>
<body>
  <div class="header">
    <h1>Selamat Datang Di Sistem Informasi GGP</h1>
  </div>

  <nav class="navbar">
    <ul>
      <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
      
      <li><a href="{{ route('jemaat.index') }}" class="{{ request()->routeIs('jemaat.*') ? 'active' : '' }}">Jemaat</a></li>
      
      <li><a href="{{ route('pengumuman.index') }}" class="{{ request()->routeIs('pengumuman.*') ? 'active' : '' }}">Pengumuman</a></li>

      @if(\App\Helpers\PermissionHelper::hasPermission('view', 'worship-schedules'))
      <li><a href="{{ route('worship-schedules.index') }}" class="{{ request()->routeIs('worship-schedules.*') ? 'active' : '' }}">Jadwal Ibadah</a></li>
      @endif

      @if(\App\Helpers\PermissionHelper::hasPermission('view', 'keuangan'))
      <li><a href="{{ route('keuangan.index') }}" class="{{ request()->routeIs('keuangan.*') ? 'active' : '' }}">Keuangan</a></li>
      @endif

      <li><a href="{{ route('history') }}" class="{{ request()->routeIs('history') ? 'active' : '' }}">Sejarah</a></li>

      <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">Tentang Kami</a></li>

      @auth
      <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
      @else
      <li><a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a></li>
      @endauth
    </ul>
  </nav>

  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
  </form>

  <div class="content">
    @yield('content')
  </div>
</body>
</html>

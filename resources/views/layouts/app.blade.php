<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Sharp Edge Barbershop')</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  @php $u = session('user'); @endphp
  <header>
    <div class="wrap bar">
      <a class="brand" href="{{ route('home') }}">Sharp Edge</a>
      <nav aria-label="Main">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'on' : '' }}">Home</a>
        <a href="{{ route('book') }}" class="{{ request()->routeIs('book') ? 'on' : '' }}">Book</a>
        <a href="{{ route('track') }}" class="{{ request()->routeIs('track') ? 'on' : '' }}">Track</a>
        @if($u)
          <a href="{{ route('bookings.mine') }}" class="{{ request()->routeIs('bookings.mine') ? 'on' : '' }}">My bookings</a>
          @if(($u['role'] ?? '') === 'admin')
            <a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.*') ? 'on' : '' }}">Admin</a>
          @endif
          <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit">Log out ({{ e(explode(' ', $u['name'])[0]) }})</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'on' : '' }}">Log in</a>
          <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'on' : '' }}">Register</a>
        @endif
      </nav>
    </div>
  </header>
  <main class="wrap">
    @if(session('success'))
      <div class="msg ok" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="msg err" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="msg err" role="alert">{{ $errors->first() }}</div>
    @endif
    @yield('content')
  </main>
  <footer class="wrap">Fictional shop &middot; school project &middot; records stored in private JSON files</footer>
</body>
</html>

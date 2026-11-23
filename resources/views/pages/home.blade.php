@extends('layouts.app')

@section('content')
  <h1>Fresh cut,<br>no waiting around.</h1>
  <p class="lead">Pick your barber, pick a time, and show up. Open daily, 9:00 AM to 6:00 PM.</p>
  <p><a class="btn" href="{{ route('book') }}">Book a visit</a> <a class="btn ghost" href="{{ route('track') }}">Track a booking</a></p>

  <h2>Inside the shop</h2>
  <div class="gallery">
    <figure><img src="{{ asset('images/shop.jfif') }}" alt="Inside Sharp Edge Barbershop" loading="lazy"><figcaption>Our shop</figcaption></figure>
    <figure><img src="{{ asset('images/haircut.jfif') }}" alt="Example of a fresh haircut" loading="lazy"><figcaption>Fresh cuts, every day</figcaption></figure>
  </div>

  <h2>Services</h2>
  <ul class="list">
    @foreach($services as $s)
      <li><span>{{ $s['name'] }}</span><strong>₱{{ number_format($s['price'], 2) }}</strong></li>
    @endforeach
  </ul>

  <h2>Barbers</h2>
  <ul class="list">
    @foreach($barbers as $b)
      <li><span>{{ $b['name'] }}</span></li>
    @endforeach
  </ul>

  <p class="location"><span>Find us</span>104 Mariano St., Bintog, Plaridel, Bulacan</p>
@endsection

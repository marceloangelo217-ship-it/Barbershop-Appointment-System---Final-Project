@extends('layouts.app')
@section('title','Track a booking - Sharp Edge')
@section('content')
  <h1>Track a booking</h1>
  <form class="stack" method="GET" action="{{ route('track') }}">
    <label>Reference number<input name="ref" value="{{ $ref }}" placeholder="BB-20261123-0001" required></label>
    <button class="btn">Check status</button>
  </form>

  @if($searched)
    @if($booking)
      <h2>Result</h2>
      <div class="card"><dl>
        <dt>Reference</dt><dd><b>{{ $booking['ref'] }}</b></dd>
        <dt>Status</dt><dd><span class="badge b-{{ strtolower($booking['status']) }}">{{ $booking['status'] }}</span></dd>
        <dt>Service</dt><dd>{{ $booking['service'] }} (₱{{ number_format($booking['price'],2) }})</dd>
        <dt>Barber</dt><dd>{{ $booking['barberName'] }}</dd>
        <dt>When</dt><dd>{{ \App\Services\BookingData::nice($booking['date']) }}, {{ \App\Services\BookingData::t12($booking['slot']) }}</dd>
      </dl></div>
    @else
      <p class="msg err" style="margin-top:1rem">No booking found with that reference.</p>
    @endif
  @endif
@endsection

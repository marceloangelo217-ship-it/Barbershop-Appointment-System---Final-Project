@extends('layouts.app')
@section('title','Book a visit - Sharp Edge')
@section('content')
  <h1>Book a visit</h1>
  <form method="GET" action="{{ route('book') }}" class="row" id="draftForm">
    <label>Service
      <select name="service" onchange="document.getElementById('draftForm').submit()">
        <option value="">Choose…</option>
        @foreach($services as $s)
          <option value="{{ $s['id'] }}" {{ (string)($draft['service'] ?? '') === (string)$s['id'] ? 'selected' : '' }}>{{ $s['name'] }} – ₱{{ number_format($s['price'],2) }}</option>
        @endforeach
      </select>
    </label>
    <label>Barber
      <select name="barber" onchange="document.getElementById('draftForm').submit()">
        <option value="">Choose…</option>
        @foreach($barbers as $b)
          <option value="{{ $b['id'] }}" {{ (string)($draft['barber'] ?? '') === (string)$b['id'] ? 'selected' : '' }}>{{ $b['name'] }}</option>
        @endforeach
      </select>
    </label>
    <label>Date
      <input type="date" name="date" min="{{ $today }}" max="{{ $maxDate }}" value="{{ $draft['date'] ?? '' }}" onchange="document.getElementById('draftForm').submit()">
    </label>
  </form>

  @php $ready = !empty($draft['service']) && !empty($draft['barber']) && !empty($draft['date']); @endphp
  @if($ready)
    <h2>Available times</h2>
    <form method="POST" action="{{ route('book.store') }}">
      @csrf
      <input type="hidden" name="service" value="{{ $draft['service'] }}">
      <input type="hidden" name="barber" value="{{ $draft['barber'] }}">
      <input type="hidden" name="date" value="{{ $draft['date'] }}">
      <div class="slots">
        @foreach($slots as $s)
          @php
            $past = strtotime(($draft['date'] ?? '') . ' ' . $s) <= time();
            $off = $past || in_array($s, $taken, true);
          @endphp
          <label class="slot {{ old('slot') === $s ? 'sel' : '' }}" style="display:block;text-align:center;{{ $off ? 'opacity:.55;' : '' }}">
            <input type="radio" name="slot" value="{{ $s }}" {{ $off ? 'disabled' : '' }} {{ old('slot') === $s ? 'checked' : '' }} style="position:absolute;opacity:0;width:1px;height:1px">
            {{ \App\Services\BookingData::t12($s) }}
          </label>
        @endforeach
      </div>
      <div class="row">
        <label>Mobile number<input name="phone" type="tel" value="{{ old('phone', $user['phone']) }}" required></label>
        <label>Notes (optional)<input name="notes" maxlength="200" value="{{ old('notes') }}"></label>
      </div>
      <button class="btn">Confirm booking</button>
    </form>
    <script>
      document.querySelectorAll('.slot input[type=radio]').forEach(r => {
        const sync = () => document.querySelectorAll('.slot').forEach(l => l.classList.toggle('sel', l.querySelector('input').checked));
        r.addEventListener('change', sync); sync();
      });
    </script>
  @else
    <p class="muted">Choose a service, a barber and a date to see free times.</p>
  @endif
@endsection

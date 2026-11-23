@extends('layouts.app')
@section('title','My bookings - Sharp Edge')
@section('content')
  <h1>My bookings</h1>
  @if(count($bookings))
    <div class="tw"><table>
      <thead><tr><th>Reference</th><th>When</th><th>Service</th><th>Barber</th><th>Status</th><th></th></tr></thead>
      <tbody>
      @foreach($bookings as $b)
        <tr>
          <td>{{ $b['ref'] }}</td>
          <td>{{ \App\Services\BookingData::nice($b['date']) }}<br><small class="muted">{{ \App\Services\BookingData::t12($b['slot']) }}</small></td>
          <td>{{ $b['service'] }}</td>
          <td>{{ $b['barberName'] }}</td>
          <td><span class="badge b-{{ strtolower($b['status']) }}">{{ $b['status'] }}</span></td>
          <td>
            @if($b['status'] === 'Pending')
              <form method="POST" action="{{ route('bookings.cancel', $b['ref']) }}" onsubmit="return confirm('Cancel this booking?')">
                @csrf
                <button class="btn sm danger">Cancel</button>
              </form>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table></div>
  @else
    <p class="muted">No bookings yet. <a href="{{ route('book') }}">Book your first visit</a>.</p>
  @endif
@endsection

@extends('layouts.app')
@section('title','Admin - Sharp Edge')
@section('content')
  <h1>Admin</h1>
  <div class="stats">
    <div><b>{{ $stats['today'] }}</b><span class="muted">today</span></div>
    <div><b>{{ $stats['pending'] }}</b><span class="muted">pending</span></div>
    <div><b>{{ $stats['total'] }}</b><span class="muted">total</span></div>
  </div>

  <form class="row" method="GET" action="{{ route('admin.index') }}">
    <label>Search<input name="q" value="{{ $q }}" placeholder="Name, phone or reference"></label>
    <label>Status
      <select name="status">
        <option value="">All</option>
        @foreach(['Pending','Approved','Completed','Rejected','Cancelled'] as $s)
          <option {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
      </select>
    </label>
    <label>Date<input type="date" name="date" value="{{ $date }}"></label>
    <label>&nbsp;<button class="btn ghost">Apply filters</button></label>
  </form>

  @if(count($bookings))
    <div class="tw"><table>
      <thead><tr><th>Reference</th><th>Customer</th><th>When</th><th>Service</th><th>Barber</th><th>Status</th><th></th></tr></thead>
      <tbody>
      @foreach($bookings as $b)
        <tr>
          <td>{{ $b['ref'] }}</td>
          <td>{{ $b['name'] }}<br><small class="muted">{{ $b['phone'] }}</small></td>
          <td>{{ \App\Services\BookingData::nice($b['date']) }}<br><small class="muted">{{ \App\Services\BookingData::t12($b['slot']) }}</small></td>
          <td>{{ $b['service'] }}</td>
          <td>{{ $b['barberName'] }}</td>
          <td><span class="badge b-{{ strtolower($b['status']) }}">{{ $b['status'] }}</span></td>
          <td><div class="acts">
            @foreach(($next[$b['status']] ?? []) as $n)
              <form method="POST" action="{{ route('admin.status', $b['ref']) }}" style="display:inline">
                @csrf
                <input type="hidden" name="status" value="{{ $n }}">
                <button class="btn sm {{ in_array($n,['Rejected','Cancelled']) ? 'danger' : 'ghost' }}">{{ $n }}</button>
              </form>
            @endforeach
          </div></td>
        </tr>
      @endforeach
      </tbody>
    </table></div>
  @else
    <p class="muted">No bookings match.</p>
  @endif
@endsection

@extends('layouts.app')
@section('title','Log in - Sharp Edge')
@section('content')
  <h1>Log in</h1>
  <form class="stack" method="POST" action="{{ route('login') }}">
    @csrf
    <label>Email<input name="email" type="email" autocomplete="email" value="{{ old('email') }}" required></label>
    <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
    <button class="btn">Log in</button>
  </form>
  <p class="muted">No account? <a href="{{ route('register') }}">Register</a></p>
@endsection

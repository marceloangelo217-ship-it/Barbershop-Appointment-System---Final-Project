@extends('layouts.app')
@section('title','Register - Sharp Edge')
@section('content')
  <h1>Create account</h1>
  <form class="stack" method="POST" action="{{ route('register') }}">
    @csrf
    <label>Full name<input name="name" autocomplete="name" value="{{ old('name') }}" required></label>
    <label>Email<input name="email" type="email" autocomplete="email" value="{{ old('email') }}" required></label>
    <label>Mobile number<input name="phone" type="tel" placeholder="09171234567" value="{{ old('phone') }}" required></label>
    <label>Password (8+ characters)<input name="password" type="password" autocomplete="new-password" required></label>
    <label>Confirm password<input name="password_confirmation" type="password" autocomplete="new-password" required></label>
    <button class="btn">Create account</button>
  </form>
  <p class="muted">Already registered? <a href="{{ route('login') }}">Log in</a></p>
@endsection

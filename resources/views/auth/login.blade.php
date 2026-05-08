@extends('layouts.auth')

@section('content')
    <h1>🍽️ Login</h1>
    @if(session('status'))
        <div class="flash" style="margin-bottom: 20px;">{{ session('status') }}</div>
    @endif
    <form method="POST" action="/login">
        @csrf
        <div class="field">
            <label for="email">📧 Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
            @error('email')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="password">🔐 Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            @error('password')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="button">✨ Continue</button>
    </form>
    <p>New here? <a href="/register">Create an account →</a></p>
@endsection

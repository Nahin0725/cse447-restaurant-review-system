@extends('layouts.auth')

@section('content')
    <h1>🚀 Create Account</h1>
    @if(session('status'))
        <div class="flash" style="margin-bottom: 20px;">{{ session('status') }}</div>
    @endif
    <form method="POST" action="/register">
        @csrf
        <div class="field">
            <label for="username">👤 Username <span style="color: #4CAF50;">(10 chars, letters + numbers + symbols)</span></label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="e.g., John@123" maxlength="10" required>
            @error('username')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="email">📧 Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
            @error('email')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="contact_info">📱 Phone Number</label>
            <input type="text" id="contact_info" name="contact_info" value="{{ old('contact_info') }}" placeholder="12 digits" required>
            @error('contact_info')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="password">🔐 Password <span style="color: #4CAF50;">(minimum 12 characters)</span></label>
            <input type="password" id="password" name="password" placeholder="Enter a strong password" minlength="12" required>
            @error('password')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="role">👥 Role</label>
            <select id="role" name="role" required>
                <option value="">-- Select your role --</option>
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>👨‍💼 User (Reviewer)</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>⚙️ Admin (Moderator)</option>
            </select>
            @error('role')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="button">✨ Register</button>
    </form>
    <p>Already registered? <a href="/login">Login now</a></p>
@endsection

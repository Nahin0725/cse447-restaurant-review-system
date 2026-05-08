@extends('layouts.app')

@section('content')
    <h1>Profile</h1>

    <a href="/dashboard" class="button secondary" style="margin-bottom: 20px;">Back</a>

    @php
        $profileDecryptionFailed = empty($user['username']) || empty($user['email']) || empty($user['contact_info']);
    @endphp

    @if($profileDecryptionFailed)
        <div class="card" style="background: #fff1f2; border: 1px solid #fecaca; color: #7f1d1d; margin-bottom: 20px; padding: 16px; border-radius: 12px;">
            <strong>Notice:</strong> Your stored profile data could not be decrypted with the current key material. Please update your Username and Contact below and save your profile to restore plaintext access.
        </div>
    @endif

    <div class="card" style="margin-bottom: 24px;">
        <h2>Your Profile Information</h2>
        <p><strong>User ID:</strong> {{ $user['user_id'] }}</p>
        <p><strong>Role:</strong> {{ ucfirst($user['role']) }}</p>
        <p><strong>Username:</strong> {{ $user['username'] }}</p>
        <p><strong>Email:</strong> {{ $user['email'] }}</p>
        <p><strong>Phone Number:</strong> {{ $user['contact_info'] }}</p>
        <p><strong>Registered At:</strong> {{ $user['created_at']?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
    </div>

    @if(session('status'))
        <div class="card" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; margin-bottom: 20px; padding: 16px; border-radius: 12px;">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="card" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; margin-bottom: 20px; padding: 16px; border-radius: 12px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/profile">
        @csrf
        <div class="field">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username', $user['username']) }}" required>
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user['email']) }}" required>
        </div>
        <div class="field">
            <label>Phone Number</label>
            <input type="text" name="contact_info" value="{{ old('contact_info', $user['contact_info']) }}" required placeholder="12 digits">
        </div>
        <div class="field">
            <label>New Password</label>
            <input type="password" name="password" placeholder="Leave blank to keep current password">
        </div>
        <button type="submit" class="button">Update Profile</button>
    </form>
@endsection

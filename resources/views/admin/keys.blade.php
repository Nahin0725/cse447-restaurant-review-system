@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="/dashboard" class="button secondary">Back to Dashboard</a>
    </div>
    <h1>Key Management</h1>
    <p>Active keys are encrypted in the database. Rotating keys generates fresh RSA and ECC key material.</p>
    <form method="POST" action="/admin/keys/rotate">
        @csrf
        <button type="submit" class="button">Rotate Keys</button>
    </form>

    <h2 style="margin-top: 20px;">Stored Key Records</h2>
    <ul>
        @foreach($keys as $key)
            <li>
                <strong>{{ strtoupper($key->algorithm) }} ({{ $key->purpose }})</strong> — created at {{ $key->created_at->format('Y-m-d H:i:s') }} — active: {{ $key->active ? 'yes' : 'no' }}
            </li>
        @endforeach
    </ul>
@endsection

@extends('layouts.app')

@section('content')
    <nav style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 16px; background: #f3f4f6; border-radius: 12px;">
        <h2 style="margin: 0; font-size: 20px; color: #1f2937;">Approved Reviews</h2>
        <div style="display: flex; gap: 12px;">
            <a href="/dashboard" class="button secondary" style="padding: 10px 16px; font-size: 14px;">Back to Dashboard</a>
        </div>
    </nav>

    <h1>Approved Reviews</h1>

    @if($reviews->isEmpty())
        <p>No approved reviews.</p>
    @else
        <div style="display: grid; gap: 16px;">
            @foreach($reviews as $review)
                <div class="card" style="padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff;">
                    <h3>{{ $review->user->name }} - {{ $review->restaurant_name }}</h3>
                    <p><strong>Title:</strong> {{ $review->title }}</p>
                    <p><strong>City:</strong> {{ $review->city }}</p>
                    <p><strong>Body:</strong> {{ $review->body }}</p>
                    <p><strong>Review Score:</strong> {{ $review->review_score }}/10</p>
                    <p><strong>Posted at:</strong> {{ $review->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            @endforeach
        </div>
    @endif
@endsection
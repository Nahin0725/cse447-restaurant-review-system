@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">All Approved Reviews</h1>

        @if($reviews->count() > 0)
            <div class="space-y-6">
                @foreach($reviews as $review)
                    <div class="bg-white shadow-md rounded px-6 py-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $review->restaurant_name }}</h3>
                                <p class="text-sm text-gray-600">{{ $review->location }}, {{ $review->city }}</p>
                                <p class="text-sm text-gray-500">By User ID: {{ $review->user_id }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-semibold">
                                    Score: {{ $review->review_score }}/10
                                </span>
                                <p class="text-xs text-gray-500 mt-1">{{ $review->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-4">{{ $review->review_text }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white shadow-md rounded px-6 py-8 text-center">
                <p class="text-gray-600">No approved reviews available yet.</p>
            </div>
        @endif

        <div class="mt-8 text-center">
            <a href="/dashboard" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
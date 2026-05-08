@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Review Details</h1>

        <div class="bg-white shadow-md rounded px-6 py-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $review->location }}</h3>
                    <p class="text-sm text-gray-600">{{ $review->city }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-semibold">
                        Score: {{ $review->review_score }}/10
                    </span>
                    <p class="text-xs text-gray-500 mt-1">{{ $review->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            <p class="text-gray-700 mb-4">{{ $review->review_text }}</p>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium
                    @if($review->status === 'approved') text-green-600
                    @elseif($review->status === 'pending') text-yellow-600
                    @elseif($review->status === 'rejected') text-red-600
                    @else text-gray-600
                    @endif">
                    Status: {{ ucfirst($review->status) }}
                </span>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="/reviews" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to My Reviews
            </a>
        </div>
    </div>
</div>
@endsection
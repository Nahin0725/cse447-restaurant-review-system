@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Submit a Review</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/reviews" method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf

            <div class="mb-4">
                <label for="restaurant_name" class="block text-gray-700 text-sm font-bold mb-2">Restaurant Name:</label>
                <input type="text" id="restaurant_name" name="restaurant_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter restaurant name" required>
            </div>

            <div class="mb-4">
                <label for="review_text" class="block text-gray-700 text-sm font-bold mb-2">Review Text:</label>
                <textarea id="review_text" name="review_text" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Write your review here..." required></textarea>
            </div>

            <div class="mb-4">
                <label for="review_score" class="block text-gray-700 text-sm font-bold mb-2">Review Score (1-10):</label>
                <input type="number" id="review_score" name="review_score" min="1" max="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="mb-4">
                <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Location:</label>
                <input type="text" id="location" name="location" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter location" required>
            </div>

            <div class="mb-6">
                <label for="city" class="block text-gray-700 text-sm font-bold mb-2">City:</label>
                <select id="city" name="city" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">Select a city</option>
                    @foreach($districts as $district)
                        <option value="{{ $district }}">{{ $district }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Review
                </button>
                <a href="/dashboard" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
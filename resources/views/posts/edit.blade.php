@extends('layouts.app')

@section('content')
    <h1>Edit Review</h1>
    <form method="POST" action="/posts/{{ $post->id }}/update">
        @csrf
        <div class="field">
            <label for="restaurant_name">Restaurant Name</label>
            <input type="text" id="restaurant_name" name="restaurant_name" value="{{ $post->restaurant_name }}" maxlength="200" required>
        </div>
        <div class="field">
            <label for="title">Address</label>
            <input type="text" id="title" name="title" value="{{ $post->title }}" maxlength="60" required>
        </div>
        <div class="field">
            <label for="city">City</label>
            <select id="city" name="city" required>
                <option value="">Select a district</option>
                @foreach($districts as $district)
                    <option value="{{ $district }}" {{ $post->city === $district ? 'selected' : '' }}>{{ $district }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="review_score">Review Score</label>
            <select id="review_score" name="review_score" required>
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}" {{ old('review_score', $post->review_score) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="field">
            <label for="body">Review</label>
            <textarea id="body" name="body" rows="5" maxlength="100" required>{{ $post->body }}</textarea>
        </div>
        <button type="submit" class="button">Update Review</button>
    </form>
@endsection

@extends('layouts.app')

@section('content')
    <h1>Create Review</h1>
    <form method="POST" action="/posts">
        @csrf
        <div class="field">
            <label for="restaurant_name">Restaurant Name</label>
            <input type="text" id="restaurant_name" name="restaurant_name" value="{{ old('restaurant_name') }}" maxlength="200" required>
            @error('restaurant_name')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="title">Address</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="60" required>
            @error('title')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="city">City</label>
            <select id="city" name="city" required>
                <option value="">Select a district</option>
                @foreach($districts as $district)
                    <option value="{{ $district }}" {{ old('city') === $district ? 'selected' : '' }}>{{ $district }}</option>
                @endforeach
            </select>
            @error('city')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="review_score">Review Score</label>
            <select id="review_score" name="review_score" required>
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}" {{ old('review_score', 5) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            @error('review_score')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="body">Review</label>
            <textarea id="body" name="body" rows="5" maxlength="100" required>{{ old('body') }}</textarea>
            @error('body')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="button">Save Review</button>
    </form>
@endsection

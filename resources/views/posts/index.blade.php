@extends('layouts.app')

@section('content')
    <h1>My Reviews</h1>

    @if($posts->isEmpty())
        <p>No secure reviews yet. Create a new one.</p>
    @else
        <ul>
            @foreach($posts as $post)
                <li style="margin-bottom: 16px; border: 1px solid #e5e7eb; padding: 16px; border-radius: 12px;">
                    <p><strong>{{ $post->restaurant_name }}</strong></p>
                    <p><strong>Address:</strong> {{ $post->title }}</p>
                    <p><strong>City:</strong> {{ $post->city }}</p>
                    <p><strong>Score:</strong> {{ $post->review_score }}/10</p>
                    <p>{{ $post->body }}</p>
                    <a href="/posts/{{ $post->id }}/edit" class="button secondary">Edit</a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection

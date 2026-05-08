@extends('layouts.app')

@section('content')
<style>
    .reviews-container {
        background: white;
        border-radius: 16px;
        padding: 30px;
        max-width: 1000px;
        margin: 20px auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .back-button {
        display: inline-block;
        background: #6b7280;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        margin-bottom: 20px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        font-weight: 600;
    }

    .back-button:hover {
        background: #4b5563;
        transform: translateY(-2px);
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        gap: 20px;
    }

    .page-header h1 {
        margin: 0;
        font-size: 32px;
        color: #1f2937;
    }

    .review-card {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .review-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        border-color: #059669;
        transform: translateY(-2px);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .review-title {
        font-size: 18px;
        font-weight: 700;
        color: #059669;
        margin: 0 0 8px 0;
    }

    .review-meta {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    .score-badge {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 700;
        text-align: center;
        min-width: 100px;
    }

    .review-content {
        color: #374151;
        line-height: 1.6;
        margin: 15px 0;
    }

    .review-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-wait {
        background: #dbeafe;
        color: #0c4a6e;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .edit-button {
        background: #f59e0b;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .edit-button:hover {
        background: #d97706;
        transform: translateY(-2px);
    }

    .edit-limit {
        color: #dc2626;
        font-weight: 600;
        font-size: 13px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #f0fdf4 0%, #f3fff4 100%);
        border-radius: 12px;
        border: 2px dashed #d1fae5;
    }

    .empty-state p {
        font-size: 18px;
        color: #059669;
        margin-bottom: 20px;
    }

    .submit-button {
        display: inline-block;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        padding: 12px 28px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .submit-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(5, 150, 105, 0.3);
    }

    .success-message {
        background: #d1fae5;
        border: 2px solid #10b981;
        color: #065f46;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .reviews-container {
            padding: 20px;
        }

        .review-header {
            flex-direction: column;
            gap: 10px;
        }

        .review-footer {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .action-buttons {
            width: 100%;
            flex-direction: column;
        }

        .edit-button {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="reviews-container">
    <div style="margin-bottom: 20px;">
        <a href="/dashboard" class="back-button">← Back to Dashboard</a>
    </div>

    <div class="page-header">
        <h1>📋 My Reviews</h1>
    </div>

    @if(session('status'))
        <div class="success-message">
            ✅ {{ session('status') }}
        </div>
    @endif

    @if($reviews->count() > 0)
        <div>
            @foreach($reviews as $review)
                <div class="review-card">
                    <div class="review-header">
                        <div style="flex: 1;">
                            <h3 class="review-title">{{ $review->restaurant_name }}</h3>
                            <p class="review-meta">📍 {{ $review->location }}, {{ $review->city }}</p>
                            <p class="review-meta">📅 {{ $review->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="score-badge">⭐ {{ $review->review_score }}/10</div>
                    </div>
                    <p class="review-content">{{ $review->review_text }}</p>
                    <div class="review-footer">
                        <span class="status-badge status-{{ $review->status }}">
                            {{ $review->status === 'pending' ? '⏳' : ($review->status === 'approved' ? '✅' : '❌') }} 
                            {{ ucfirst($review->status) }}
                        </span>
                        @if($review->status === 'approved')
                            <div class="action-buttons">
                                @if($review->edit_count < $review->max_edit_limit)
                                    <a href="/reviews/{{ $review->review_id }}/edit" class="edit-button">
                                        ✏️ Edit ({{ $review->edit_count }}/{{ $review->max_edit_limit }})
                                    </a>
                                @else
                                    <span class="edit-limit">🔒 Edit limit reached</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <p>🎉 No reviews yet! Share your first dining experience.</p>
            <a href="/reviews/create" class="submit-button">📝 Submit Your First Review</a>
        </div>
    @endif
</div>
@endsection
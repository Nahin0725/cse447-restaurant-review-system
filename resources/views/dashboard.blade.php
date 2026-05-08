@extends('layouts.app')

@section('content')
    <style>
        /* Override container for dashboard */
        .container {
            width: 100% !important;
            max-width: 1400px !important;
            padding: 20px !important;
            margin: 0 auto !important;
            display: flex;
            justify-content: center;
        }

        :root {
            --admin-primary: #1e40af;
            --admin-light: #eff6ff;
            --admin-accent: #0284c7;
            --user-primary: #059669;
            --user-light: #f0fdf4;
            --user-accent: #10b981;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-accent) 100%);
            color: white;
        }

        .user-header {
            background: linear-gradient(135deg, var(--user-primary) 0%, var(--user-accent) 100%);
            color: white;
        }

        .dashboard-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-button {
            padding: 10px 16px;
            font-size: 14px;
            border-radius: 8px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .nav-button:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-title {
            color: var(--admin-primary);
        }

        .user-title {
            color: var(--user-primary);
        }

        .title-icon {
            font-size: 28px;
        }
    </style>

    <div class="dashboard-container">
        <div class="dashboard-header {{ $user->isAdmin() ? 'admin-header' : 'user-header' }}">
            <h2>{{ $user->isAdmin() ? '👨‍💼 Admin Dashboard' : '🍽️ Your Reviews' }}</h2>
            <div class="nav-buttons">
                @if($user->isAdmin())
                    <a href="/admin/reviews" class="nav-button">📋 Reviews</a>
                @else
                    <a href="/reviews" class="nav-button">📝 My Reviews</a>
                    <a href="/user/reviews" class="nav-button">🌟 All Reviews</a>
                @endif
                <a href="/profile" class="nav-button">👤 Profile</a>
                <form method="POST" action="/logout" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-button" style="width: 100%;">🚪 Logout</button>
                </form>
            </div>
        </div>

        <h1 class="page-title {{ $user->isAdmin() ? 'admin-title' : 'user-title' }}">
            <span class="title-icon">{{ $user->isAdmin() ? '⚙️' : '✨' }}</span>
            {{ $user->isAdmin() ? 'Admin Dashboard' : 'User Dashboard' }}
        </h1>

    @if($user->isAdmin())
        <style>
            .admin-card {
                background: linear-gradient(135deg, var(--admin-light) 0%, #f0f9ff 100%);
                border: 2px solid var(--admin-primary);
                border-radius: 16px;
                padding: 24px;
                margin-bottom: 30px;
                box-shadow: 0 4px 20px rgba(30, 64, 175, 0.1);
            }

            .admin-card h2 {
                color: var(--admin-primary);
                margin-top: 0;
                margin-bottom: 20px;
                font-size: 20px;
                font-weight: 700;
            }

            .review-table {
                width: 100%;
                border-collapse: collapse;
                overflow-x: auto;
            }

            .review-table thead {
                background: var(--admin-primary);
                color: white;
            }

            .review-table th {
                padding: 14px;
                text-align: left;
                font-weight: 600;
            }

            .review-table td {
                padding: 14px;
                border-bottom: 1px solid #e5e7eb;
            }

            .review-table tbody tr {
                transition: background 0.3s;
            }

            .review-table tbody tr:hover {
                background: rgba(30, 64, 175, 0.05);
            }

            .action-buttons {
                display: flex;
                gap: 8px;
            }

            .btn-approve {
                background: #10b981;
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
            }

            .btn-approve:hover {
                background: #059669;
                transform: translateY(-2px);
            }

            .btn-reject {
                background: #ef4444;
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
            }

            .btn-reject:hover {
                background: #dc2626;
                transform: translateY(-2px);
            }

            .empty-state {
                text-align: center;
                padding: 40px;
                color: var(--admin-primary);
            }

            .star-rating {
                color: #fbbf24;
                font-size: 16px;
            }
        </style>

        <div class="admin-card">
            <h2>📋 Pending Reviews for Approval</h2>
            @if($pendingReviews->isEmpty())
                <div class="empty-state">
                    <p style="font-size: 18px; margin-bottom: 10px;">✅ All caught up!</p>
                    <p>No reviews are waiting for approval.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="review-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Restaurant</th>
                                <th>City</th>
                                <th>Location</th>
                                <th>Review</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReviews as $review)
                                <tr>
                                    <td><strong>#{{ $review->user_id }}</strong></td>
                                    <td><strong>{{ $review->restaurant_name }}</strong></td>
                                    <td>{{ $review->city }}</td>
                                    <td>{{ $review->location ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($review->review_text, 50) }}</td>
                                    <td><span class="star-rating">{{ str_repeat('⭐', $review->review_score / 2) }}</span> {{ $review->review_score }}/10</td>
                                    <td>
                                        <div class="action-buttons">
                                            <form method="POST" action="/admin/reviews/{{ $review->review_id }}/approve" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-approve">✓ Approve</button>
                                            </form>
                                            <form method="POST" action="/admin/reviews/{{ $review->review_id }}/reject" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-reject">✕ Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @else
        <style>
            .user-card {
                background: linear-gradient(135deg, var(--user-light) 0%, #f8faff 100%);
                border: 2px solid var(--user-primary);
                border-radius: 16px;
                padding: 24px;
                margin-bottom: 30px;
                box-shadow: 0 4px 20px rgba(5, 150, 105, 0.1);
            }

            .user-card h2 {
                color: var(--user-primary);
                margin-top: 0;
                margin-bottom: 20px;
                font-size: 20px;
                font-weight: 700;
            }

            .cta-button {
                display: inline-block;
                padding: 14px 28px;
                background: linear-gradient(135deg, var(--user-primary) 0%, var(--user-accent) 100%);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.3s;
                border: none;
                cursor: pointer;
            }

            .cta-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(5, 150, 105, 0.3);
            }

            .review-card {
                background: white;
                border: 2px solid #d1fae5;
                border-left: 6px solid var(--user-primary);
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 16px;
                transition: all 0.3s;
            }

            .review-card:hover {
                box-shadow: 0 8px 20px rgba(5, 150, 105, 0.15);
                transform: translateY(-2px);
            }

            .review-header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 12px;
            }

            .restaurant-name {
                color: var(--user-primary);
                font-size: 18px;
                font-weight: 700;
                margin: 0;
            }

            .review-score {
                background: var(--user-primary);
                color: white;
                padding: 4px 12px;
                border-radius: 20px;
                font-weight: 600;
                font-size: 14px;
            }

            .review-meta {
                color: #666;
                font-size: 14px;
                margin: 8px 0;
            }

            .review-text {
                color: #1f2937;
                line-height: 1.6;
                margin: 12px 0;
            }

            .empty-state {
                text-align: center;
                padding: 40px;
                color: var(--user-primary);
            }
        </style>

        <div class="user-card">
            <h2>🆕 Submit a New Review</h2>
            <p>Share your dining experience and help others find great restaurants!</p>
            <a href="/reviews/create" class="cta-button">📝 Write a Review</a>
        </div>
    @endif

    @if(!$user->isAdmin())
        <div class="user-card">
            <h2>⭐ Your Posted Reviews</h2>
            @if($approvedReviews->isEmpty())
                <div class="empty-state">
                    <p style="font-size: 18px; margin-bottom: 10px;">🎉 No reviews yet!</p>
                    <p>Start sharing your reviews to see them here.</p>
                </div>
            @else
                @foreach($approvedReviews as $review)
                    <div class="review-card">
                        <div class="review-header">
                            <h3 class="restaurant-name">{{ $review->restaurant_name }}</h3>
                            <span class="review-score">⭐ {{ $review->review_score }}/10</span>
                        </div>
                        <div class="review-meta">
                            📍 {{ $review->city }}@if($review->location) • {{ $review->location }}@endif
                        </div>
                        <p class="review-text">{{ $review->review_text }}</p>
                    </div>
                @endforeach
            @endif
        </div>
    @endif


    </div>
@endsection

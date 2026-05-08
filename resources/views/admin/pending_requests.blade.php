@extends('layouts.app')

@section('content')
    <nav style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 16px; background: #f3f4f6; border-radius: 12px;">
        <h2 style="margin: 0; font-size: 20px; color: #1f2937;">Pending Requests</h2>
        <div style="display: flex; gap: 12px;">
            <a href="/dashboard" class="button secondary" style="padding: 10px 16px; font-size: 14px;">Back to Dashboard</a>
        </div>
    </nav>

    <h1>Pending Requests</h1>

    @if(session('status'))
        <div class="card" style="background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46; margin-bottom: 20px; padding: 16px; border-radius: 12px;">
            {{ session('status') }}
        </div>
    @endif

    @if($pendingRequests->isEmpty())
        <p>No pending requests.</p>
    @else
        <div class="overflow-x-auto bg-white shadow sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restaurant Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingRequests as $request)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $request->user->name ?? 'N/A' }}
                                <div class="text-xs text-gray-500">ID: {{ $request->user->user_id ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->restaurant_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->city ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->review_score ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucfirst($request->status ?? 'pending') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <form method="POST" action="/admin/reviews/{{ $request->review_id }}/approve">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Approve</button>
                                    </form>
                                    <form method="POST" action="/admin/reviews/{{ $request->review_id }}/reject">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
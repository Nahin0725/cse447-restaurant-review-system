<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        $posts = Post::where('user_id', $user->id)->get();

        foreach ($posts as $post) {
            if (! $post->verifyIntegrity()) {
                abort(403, 'Post integrity verification failed.');
            }
        }

        return view('posts.index', compact('posts'));
    }

    public function dashboard(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        if ($user->isAdmin()) {
            $approvedReviews = Post::where('status', 'approved')
                ->latest()
                ->limit(10)
                ->get();

            $pendingReviews = Post::where('status', 'pending')
                ->latest()
                ->limit(10)
                ->get();
        } else {
            $approvedReviews = Post::where('user_id', $user->id)
                ->where('status', 'approved')
                ->latest()
                ->limit(10)
                ->get();

            $pendingReviews = collect();
        }

        return view('dashboard', [
            'user' => $user,
            'approvedReviews' => $approvedReviews,
            'pendingReviews' => $pendingReviews,
            'districts' => $this->getDistricts(),
        ]);
    }

    public function create(Request $request)
    {
        return view('posts.create', ['districts' => $this->getDistricts()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'restaurant_name' => 'required|string|max:200',
            'title' => 'required|string|max:60',
            'city' => 'required|string|in:' . implode(',', $this->getDistricts()),
            'body' => 'required|string|max:100',
            'review_score' => 'required|integer|min:1|max:10',
        ]);

        $user = $request->attributes->get('auth_user');

        // Check if there are already 20 unprocessed pending requests
        $pendingCount = Post::where('status', 'pending')->count();
        if ($pendingCount >= 20) {
            return back()->withErrors(['general' => 'Please try to post the review after some time.']);
        }

        Post::create([
            'user_id' => $user->id,
            'restaurant_name' => $request->input('restaurant_name'),
            'title' => $request->input('title'),
            'city' => $request->input('city'),
            'body' => $request->input('body'),
            'review_score' => (int) $request->input('review_score'),
            'status' => 'pending',
        ]);

        return redirect('/dashboard')->with('status', 'Review submitted and pending approval.');
    }

    public function edit(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');
        $post = Post::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        if (! $post->verifyIntegrity()) {
            abort(403, 'Post integrity verification failed.');
        }

        return view('posts.edit', [
            'post' => $post,
            'districts' => $this->getDistricts(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'restaurant_name' => 'required|string|max:200',
            'title' => 'required|string|max:60',
            'city' => 'required|string|in:' . implode(',', $this->getDistricts()),
            'body' => 'required|string|max:100',
            'review_score' => 'required|integer|min:1|max:10',
        ]);

        $user = $request->attributes->get('auth_user');
        $post = Post::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $post->restaurant_name = $request->input('restaurant_name');
        $post->title = $request->input('title');
        $post->city = $request->input('city');
        $post->body = $request->input('body');
        $post->review_score = (int) $request->input('review_score');
        $post->status = 'pending';
        $post->save();

        return redirect('/dashboard')->with('status', 'Review updated and resubmitted for approval.');
    }

    public function approveReview(Request $request, $id)
    {
        $post = Post::where('id', $id)->where('status', 'pending')->firstOrFail();
        $post->status = 'approved';
        $post->save();

        return redirect('/admin/pending-requests')->with('status', 'Review approved successfully.');
    }

    public function rejectReview(Request $request, $id)
    {
        $post = Post::where('id', $id)->where('status', 'pending')->firstOrFail();
        $post->status = 'rejected';
        $post->save();

        return redirect('/admin/pending-requests')->with('status', 'Review rejected.');
    }

    public function pendingRequests(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        if (!$user->isAdmin()) {
            abort(403);
        }

        $pendingRequests = Post::with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.pending_requests', compact('pendingRequests'));
    }

    public function userReviews(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        $reviews = Post::with('user')
            ->where('status', 'approved')
            ->latest()
            ->limit(50)
            ->get();

        return view('user.reviews', compact('reviews'));
    }

    public function adminReviews(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        if (!$user->isAdmin()) {
            abort(403);
        }

        $reviews = Post::with('user')
            ->where('status', 'approved')
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.reviews', compact('reviews'));
    }

    private function getDistricts(): array
    {
        return [
            'Bagerhat', 'Bandarban', 'Barguna', 'Barisal', 'Bhola', 'Jhalokathi', 'Patuakhali', 'Pirojpur',
            'Bogra', 'Chapainawabganj', 'Joypurhat', 'Naogaon', 'Natore', 'Pabna', 'Rajshahi', 'Sirajganj',
            'Dinajpur', 'Gaibandha', 'Kurigram', 'Lalmonirhat', 'Nilphamari', 'Panchagarh', 'Rangpur', 'Thakurgaon',
            'Dhaka', 'Faridpur', 'Gazipur', 'Gopalganj', 'Kishoreganj', 'Madaripur', 'Manikganj', 'Munshiganj',
            'Narayanganj', 'Narsingdi', 'Rajbari', 'Shariatpur', 'Tangail', 'Jamalpur', 'Mymensingh', 'Netrokona', 'Sherpur',
            'Bandarban', 'Brahmanbaria', 'Chandpur', 'Chattogram', 'Cumilla', "Cox's Bazar", 'Feni', 'Khagrachhari',
            'Lakshmipur', 'Noakhali', 'Rangamati', 'Habiganj', 'Moulvibazar', 'Sunamganj', 'Sylhet', 'Khulna',
            'Chuadanga', 'Jashore', 'Jhenaidah', 'Kushtia', 'Magura', 'Meherpur', 'Narail', 'Satkhira'
        ];
    }
}

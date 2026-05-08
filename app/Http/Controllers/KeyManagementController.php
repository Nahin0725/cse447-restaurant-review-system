<?php

namespace App\Http\Controllers;

use App\Models\KeyPair;
use App\Services\Crypto\KeyManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class KeyManagementController extends Controller
{
    public function index(Request $request)
    {
        $keys = KeyPair::orderBy('created_at', 'desc')->get();

        return view('admin.keys', compact('keys'));
    }

    public function rotate(Request $request)
    {
        app(KeyManager::class)->rotateKeys();

        return back()->with('status', 'Key material rotated and stored in encrypted form.');
    }
}

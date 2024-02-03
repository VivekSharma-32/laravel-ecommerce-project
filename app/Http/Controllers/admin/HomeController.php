<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    // This method will show the login page 
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        // echo 'Welcome ' . $admin->name . ' <a href="' . route('admin.logout') . '">Logout</a>';
        return view('admin.dashboard');
    }

    // This method will logout the user 
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}

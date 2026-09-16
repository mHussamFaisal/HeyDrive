<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.users.show', auth()->user()->id);
    }
}

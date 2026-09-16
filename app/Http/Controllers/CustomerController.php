<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function customer()
    {
        return view('customer.customer');
    }
}

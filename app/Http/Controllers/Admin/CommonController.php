<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'user' => auth()->user()
        ]);
    }

    public function discounts()
    {
        if (!auth()->user()->hasPermission('admin.discounts.index')) {
            return redirect_no_permission();
        }

        return view('admin.discounts', [
            'user' => auth()->user()
        ]);
    }

    public function fixedPrices()
    {
        if (!auth()->user()->hasPermission('admin.fixed_prices.index')) {
            return redirect_no_permission();
        }

        return view('admin.fixed-prices', [
            'user' => auth()->user()
        ]);
    }

    public function excludedRoutes()
    {
        if (!auth()->user()->hasPermission('admin.excluded_routes.index')) {
            return redirect_no_permission();
        }

        return view('admin.excluded-routes', [
            'user' => auth()->user()
        ]);
    }

    public function meetingPoints()
    {
        if (!auth()->user()->hasPermission('admin.meeting_points.index')) {
            return redirect_no_permission();
        }

        return view('admin.meeting-points', [
            'user' => auth()->user()
        ]);
    }

    public function categories()
    {
        if (!auth()->user()->hasPermission('admin.categories.index')) {
            return redirect_no_permission();
        }

        return view('admin.categories', [
            'user' => auth()->user()
        ]);
    }

    public function locations()
    {
        if (!auth()->user()->hasPermission('admin.locations.index')) {
            return redirect_no_permission();
        }

        return view('admin.locations', [
            'user' => auth()->user()
        ]);
    }

    public function customers()
    {
        if (!auth()->user()->hasPermission('admin.users.customer.index')) {
            return redirect_no_permission();
        }

        return view('admin.customers', [
            'user' => auth()->user()
        ]);
    }

    public function vehiclesTypes()
    {
        if (!auth()->user()->hasPermission('admin.vehicle_types.index')) {
            return redirect_no_permission();
        }

        return view('admin.vehicles-types', [
            'user' => auth()->user()
        ]);
    }

    public function payments()
    {
        if (!auth()->user()->hasPermission('admin.payments.index')) {
            return redirect_no_permission();
        }

        return view('admin.payments', [
            'user' => auth()->user()
        ]);
    }

    public function config()
    {
        return view('admin.config.index', [
            'user' => auth()->user()
        ]);
    }

    public function profiles()
    {
        return view('admin.profiles', [
            'user' => auth()->user()
        ]);
    }

    public function account()
    {
        return view('admin.account', [
            'user' => auth()->user()
        ]);
    }
}

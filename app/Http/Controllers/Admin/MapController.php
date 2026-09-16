<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MapController extends Controller
{
    public function index()
    {
        return view('admin.map.index');
    }

    public function drivers()
    {
        $minutes = 10;
        $users = [];
        $bounds = request('bounds');

        $query = \App\Models\User::with('profile')->role('driver.*')->where('last_seen_at', '>=', \Carbon\Carbon::now()->subSeconds($minutes * 60));

        // Check the bounds markers only
        // if ( !empty($bounds) ) {}
        // Disable drivers layer (tickbox)

        foreach($query->get() as $user) {
            if (!is_null($user->lat) && !is_null($user->lng)) {
                $users[] = (object)[
                    'id' => $user->id,
                    'name' => $user->getName(true),
                    'unique_id' => !empty($user->profile->unique_id) ? $user->profile->unique_id : '',
                    'lat' => $user->lat,
                    'lng' => $user->lng,
                    'accuracy' => $user->accuracy,
                    'heading' => $user->heading,
                    'url' => route('admin.users.show', $user->id)
                ];
            }
        }

        return $users;
    }
}

<?php

$searchType = ((string)$etoPost['searchType'] == 'from') ? 'from' : 'to';
$searchValue = trim((string)$etoPost['searchValue']);
$search = trim((string)$etoPost['search']);

$locations = [];

$query = \App\Models\ScheduledRoute::where('relation_type', 'site')
  ->where('relation_id', config('site.site_id'))
  ->where('status', 'active');

if (!$search && !$searchValue) {
    $query->where('is_featured', 1);
}

if ($searchType == 'from') {
    $query->with(['from' => function($query) {
        $query->orderBy('address');
        $query->distinct('address');
    }]);

    $query->whereHas('from', function($query) use ($search) {
        $query->where('address', 'LIKE', '%'. $search .'%');
    });

    if (!empty($searchValue)) {
        $query->whereHas('to', function($query) use ($searchValue) {
            $query->where('address', $searchValue);
        });
    }
}
else {
    $query->with(['to' => function($query) {
        $query->orderBy('address');
        $query->distinct('address');
    }]);

    $query->whereHas('to', function($query) use ($search) {
        $query->where('address', 'LIKE', '%'. $search .'%');
    });

    if (!empty($searchValue)) {
        $query->whereHas('from', function($query) use ($searchValue) {
            $query->where('address', $searchValue);
        });
    }
}

$scheduledRoutes = $query->orderBy('order')->get()->sortBy(function($item) use ($searchType) {
    return $item->{$searchType}->address;
});

foreach ($scheduledRoutes as $k => $v) {
    $address = $v->{$searchType}->address;

    if (!empty($address)) {
        $locations[] = [
            'id' => 0,
            'name' => trim($address),
            'cat_id' => 0,
            'cat_icon' => '<i class="fa fa-map-marker"></i>',
            'cat_type' => 'address',
        ];
    }
}

$data['showGoogleLogo'] = 0;
$data['locations'] = $locations;
$data['success'] = true;

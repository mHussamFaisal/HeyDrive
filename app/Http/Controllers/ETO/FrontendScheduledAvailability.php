<?php
use Carbon\Carbon;

$currentDate = request('currentDate') ? Carbon::parse(request('currentDate')) : Carbon::now();
$dateFrom = $currentDate->copy()->startOfMonth();
$dateTo = $currentDate->copy()->endOfMonth();

$searchFrom = trim(request('searchFrom'));
$searchTo = trim(request('searchTo'));

$query = \App\Models\ScheduledRoute::where('relation_type', 'site')
  ->where('relation_id', config('site.site_id'))
  ->where('status', 'active');

$query->with([
    'from' => function($query) {
        $query->orderBy('address');
        $query->distinct('address');
    },
    'to' => function($query) {
        $query->orderBy('address');
        $query->distinct('address');
    }
]);

$query->whereHas('from', function($query) use ($searchFrom) {
    $query->where('address', $searchFrom);
});

$query->whereHas('to', function($query) use ($searchTo) {
    $query->where('address', $searchTo);
});

$scheduledRoutes = $query->orderBy('order')->get();

$temp = [];

foreach ($scheduledRoutes as $scheduled) {
    if (!empty($scheduled->event->id)) {
        $events = $scheduled->event->availability('list', $dateFrom->toDateTimeString(), $dateTo->toDateTimeString());

        foreach ($events as $k => $v) {
            $sd = Carbon::parse($v['start']);
            $temp[$sd->toDateString()][] = $sd->format('H:i');
        }
    }
}

$availability = [];

foreach ($temp as $k => $v) {
    $availability[] = [$k, $v];
}

$data['availability'] = $availability;
$data['success'] = true;

<?php

namespace App\Http\Controllers;

use App\Models\News;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    protected static function checkNewsFromApi() {
        $data = \App\Http\Controllers\NewsController::connectApi('latest');
        if (isset($data->news)) {
            return $data->news;
        }
        return [];
    }

    protected static function setReadedToApi($id) {
        $response = \App\Http\Controllers\NewsController::connectApi('read/'.$id);
        if (!empty($response->status) && $response->status == 'OK') {
            return $response->status_data;
        }
        return false;
    }

    protected static function connectApi($uri = '', $headers = false) {
        $response = false;
        $request = request();

        $data = [
            'stream' => true,
            'connect_timeout' => 10,
            'read_timeout' => 10,
            "decode_content" => true,
            "verify" => false,
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
                'X-Forwarded-For' => $_SERVER['REMOTE_ADDR'],
                'X-referer' => $request->system->subscription->domain,
                'licensekey' => $request->system->subscription->license,
                'etogetall' => 1,
                'globalparent' => 'eto'
            ],
            'form_params' => [],
        ];

        if ($headers) {
            $data['headers'] = array_merge($data['headers'], $headers);
        }

        try {
            $client = new Client($data);
            $result = $client->post(config('app.api_news_url') . 'subscription/' . $uri);
        }
        catch (Exception $e) {
            $responseCode = $e->getCode();
            $error = $e->getMessage();
            \Log::error([$error,$responseCode]);
        }

        if (isset($result) && !empty($result)) {
            $body = $result->getBody();
            try {
                $json = $body->getContents();
                $response = json_decode($json);
            }
            catch (Exception $e) {
                $responseCode = $e->getCode();
                $error = $e->getMessage();
                \Log::error([$error, $responseCode]);
            }
        }

        if ($response === false) {
            return ['message'=>'Failed connection.'];
        }

        if ($response->status == 'OK') {
            return $response;
        }

        return ['message'=>trans('installer.'.$response->status)];
    }

    protected static function setNewsFromApi($news = [])
    {
        foreach ($news as $item) {
            if (!isset($item->id)) {
                continue;
            }

            $new = News::firstOrNew(['remote_id' => $item->id]);

            $new->remote_id = $item->id ?: '';
            $new->name = $item->name ?: '';
            $new->slug = $item->name ? \App\Helpers\SiteHelper::seoFriendlyUrl($item->name) . '_' . $item->id : \App\Helpers\SiteHelper::generateRandomString();
            $new->excerpt = $item->excerpt ?: null;
            $new->description = $item->description ?: null;
            $new->created_at = $item->created_at ?: null;
            $new->save();
        }
    }

    public function all()
    {
        if (!auth()->user()->hasPermission('admin.news.index')) {
            return redirect_no_permission();
        }

        return view('news.index');
    }

    public function search(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.news.index')) {
            return redirect_no_permission();
        }

        if ((int)config('eto.news.count') > 0) {
            $news = \App\Http\Controllers\NewsController::checkNewsFromApi();

            if ($news) {
                \App\Http\Controllers\NewsController::setNewsFromApi($news);
            }
        }

        $items = News::select('id', 'name', 'uuid', 'status', 'excerpt', 'read_at', 'created_at')->where('status', 1);
        $limit = $request->length ?: 10;
        $offset = $request->start ?: 0;

        if ($request->order) {
            foreach ($request->order as $order) {
                $dir = isset($order['dir']) ? $order['dir'] : 'desc';
                $column = isset($order['column']) ? $order['column'] : 'created_at';
                $items->orderBy($column, $dir);
            }
        }

        if (!empty($request->search['value'])) {
            $items->where(function($query) use ($request) {
//                $i = 0;
                $search = $request->search['value'];
//                foreach (explode(' ', $request->search['value']) as $search ) {
//                    $whereMethod = $i === 0 ? 'where' : 'orWhere';
                    $query->where(function($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                        $query->orWhere('slug', 'like', '%'.$search.'%');
                        $query->orWhere('excerpt', 'like', '%'.$search.'%');
                        $query->orWhere('description', 'like', '%'.$search.'%');
                    });
//                    $i++;
//                }
            });
        }

        $total = clone $items;
        $recordsFiltered = $total->get()->count();
        $items->offset($offset)->limit($limit);
        $data = $items->get();

        foreach ($data as $id=>$item) {
            $item->date = $item->created_at->format('Y-m-d');
        }

        return response()->json(['news'=>$data, 'recordsTotal'=>News::where('status', 1)->get()->count(), 'recordsFiltered'=>$recordsFiltered], 200);
    }

    public function get($slug) {
        if (!auth()->user()->hasPermission('admin.news.index')) {
            return redirect_no_permission();
        }

        $item = News::where('slug', $slug)->orWhere('uuid', $slug)->first();

        if ($item && is_null($item->read_at)) {
            $status_data = \App\Http\Controllers\NewsController::setReadedToApi($item->remote_id);

            if ($status_data) {
                $item->read_at = $status_data->read_at;
                $item->save();
            }

            $count = config('eto.news.count') - 1 > 0 ? config('eto.news.count') - 1 : 0;
            settings_save('eto.news.count', $count, 'subscription', request()->system->subscription->id, true);
        }

        return view('news.show', compact('item'));
    }
}

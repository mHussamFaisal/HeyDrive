<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('feedback.create');

        $feedback = [];

        return view('feedback.index', compact('feedback'));
    }

    public function create()
    {
        return view('feedback.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'type' => 'required',
            'name' => 'required|max:255',
            'email' => [
                'email',
                'max:255',
                'required'
            ],
            'ref_number' => 'required',
            'description' => 'required',
        ];

        $this->validate($request, $rules);

        $params = [
            // 'param' => $request->get('param', ''),
        ];

        \App\Models\Feedback::create([
            'relation_type' => 'site',
            'relation_id' => config('site.site_id'),
            'type' => $request->get('type', 'comment'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'ref_number' => $request->get('ref_number'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'params' => json_encode($params),
            'is_read' => 0,
            'order' => 0,
            'status' => 'inactive',
        ]);

        session()->flash('message', trans('feedback.message.store_success'));

        return redirect()->route('feedback.create');

        // return redirect()->route('feedback.create', request('type') ? ['type' => request('type')] : []);
        // return redirect()->back();
        // return redirect()->route('feedback.index');
    }
}

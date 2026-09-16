<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.transactions.index')) {
            return redirect_no_permission();
        }
    }

    public function show($id)
    {
        //
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('admin.transactions.create')) {
            return redirect_no_permission();
        }
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.transactions.create')) {
            return redirect_no_permission();
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('admin.transactions.edit')) {
            return redirect_no_permission();
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.transactions.edit')) {
            return redirect_no_permission();
        }
    }

    public function destroy($id, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.transactions.destroy')) {
            return redirect_no_permission();
        }

        $transaction = Transaction::withTrashed()->findOrFail($id);

        if ( !empty($transaction->id) ) {
            // $transaction->delete();
            $transaction->forceDelete();

            $status = true;
            $message = trans('admin/bookings.transaction.message.destroy_success');
        }
        else {
            $status = false;
            $message = trans('admin/bookings.transaction.message.destroy_failure');
        }

        if ( $request->ajax() ) {
            return [
                'status' => $status,
                'message' => $message,
            ];
        }
        else {
            session()->flash('message', $message);

            if ( url()->previous() != url()->full() ) {
                return redirect()->back();
            }
            else {
                // return redirect()->route('admin.transactions.index');
            }
        }
    }

    public function add($id)
    {
        if (!auth()->user()->hasPermission('admin.transactions.create')) {
            return redirect_no_permission();
        }

        $transaction = new Transaction;
        $transaction->relation_type = 'booking';
        $transaction->relation_id = $id;
        $transaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
        $transaction->name = 'Other';
        $transaction->payment_id = 0;
        $transaction->payment_method = 'none';
        $transaction->payment_name = 'None';
        $transaction->status = 'pending';
        $transaction->ip = null;
        $transaction->response = null;
        $transaction->save();

        session()->flash('message', trans('admin/bookings.transaction.message.add_success'));

        return redirect()->back();
    }

    public function copy($id)
    {
        if (!auth()->user()->hasPermission('admin.transactions.create')) {
            return redirect_no_permission();
        }

        $copiedTransaction = Transaction::withTrashed()->findOrFail($id);

        $transaction = $copiedTransaction->replicate();
        $transaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
        $transaction->status = 'pending';
        $transaction->ip = null;
        $transaction->response = null;
        $transaction->save();

        session()->flash('message', trans('admin/bookings.transaction.message.copy_success'));

        return redirect()->back();
    }

    public function inlineEditing($action, Request $request)
    {
        // $pk = $request->get('pk', 0);
        //
        // switch( $action ) {
        //     case '':
        //
        //     break;
        // }
    }
}

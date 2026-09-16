<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReminderController extends Controller
{
    protected static $remindSubscriptionDays = 14;

    public static function add($apiId = null, $type = 'general', $expire = null, $desc = null)
    {
        $item = new \App\Models\Reminder();
        $item->api_id = $apiId;
        $item->type = $type;
        $item->description = $desc;
        $item->expire_at = $expire;
        $item->save();
        \Cache::store('file')->forget('reminder_data');
        return $item;
    }

    public static function readed($id)
    {
        $item = \App\Models\Reminder::find($id);
        $item->read_at = Carbon::now();
        $item->disable = 1;
        $item->save();
        \Cache::store('file')->forget('reminder_data');
        return $item;
    }

    public static function disable($id)
    {
        $item = \App\Models\Reminder::find($id);
        $item->disable = 1;
        $item->save();
        \Cache::store('file')->forget('reminder_data');
        return $item;
    }

    public static function remind($id, $days)
    {
        $item = \App\Models\Reminder::find($id);
        $item->remind_at = Carbon::now()->addDays($days);
        $item->read_at = Carbon::now();
        $item->save();
        \Cache::store('file')->forget('reminder_data');
        return $item;
    }

    public static function get($id)
    {
        return \App\Models\Reminder::find($id);
    }

    public static function list($type = false, $disabled = 0, $readed = false)
    {
        $list = \App\Models\Reminder::where('disable', $disabled);

        if (!$readed) {
            $list->where(function($query) {
                $query->where(function($q) {
                    $q->where('remind_at', date('y-m-d'))
                        ->orWhereNull('remind_at');
                })
                ->where(function($q) {
                    $q->where('read_at', '!=', null)
                        ->orWhere(function($q2) {
                            $q2->where('remind_at', null)
                                ->where('read_at', null);
                        });
                });
            });
        }

        if ($type) {
            $list->where('type', $type);
        }

        return $list->get();
    }

    public function getListJson(Request $request, $getObject = false) {
        if (!$list = Cache::store('file')->get('reminder_data')) {
            $type = $request->type_remind ?: false;
            $disabled = $request->disabled ?: 0;
            $readed = $request->readed ?: false;
            $list = self::list($type, $disabled, $readed);
            Cache::store('file')->put('reminder_data', $list, \Carbon\Carbon::now()->addMinutes(15));
        }

        if ($getObject) {
            return $list;
        }

        return response()->json($list, 200);
    }

    public function notRemind(Request $request) {
        return response()->json(self::readed($request->id), 200);
    }

    public function remindWeek(Request $request) {
        return response()->json(self::remind($request->id, 7), 200);
    }

    public function reminTomorow(Request $request) {
        return response()->json(self::remind($request->id, 1), 200);
    }

    public static function checkSubscription($subscription) {
        $check = check_expire($subscription->update_at);
        $added = false;
        if (!$check->isExpire && $check->diff <= self::$remindSubscriptionDays) {
            $remind = \App\Models\Reminder::where('type', 'update')->where('disable', 0)->first();
            if (!$remind) {
                $added = self::add(null, 'update', $subscription->update_at);
            }
        }
        else {
            $update = \App\Models\Reminder::where('disable', 0 )->where('type', 'like', 'update%')->get();

            if ($check->isExpire && $check->diff < 30) {
                $remind = \App\Models\Reminder::where('type', 'update_expired')->where('disable', 0)->first();
                if (!$remind) {
                    $added = self::add(null, 'update_expired', $subscription->update_at);
                }
            }
            elseif ($check->isExpire && $check->diff >= 30 && $check->diff < 90) {
                $remind = \App\Models\Reminder::where('type', 'update_expired_1_month')->where('disable', 0)->first();
                if (!$remind) {
                    $added = self::add(null, 'update_expired_1_month', $subscription->update_at);
                }
            }
            elseif ($check->isExpire && $check->diff >= 90) {
                $remind = \App\Models\Reminder::where('type', 'update_expired_3_month')->where('disable', 0)->first();
                if (!$remind) {
                    $added = self::add(null, 'update_expired_3_month', $subscription->update_at);
                }
            }

            if ($added) {
                foreach ($update as $item) {
                    $item->disable = 1;
                    $item->save();
                }
            }
        }
    }
}

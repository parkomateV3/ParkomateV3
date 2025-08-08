<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class notificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        // http://localhost:8000/send-notification/?client_id=1&button=222
        $button = $request->input('button');

        if (!$button) {
            return response()->json(['status' => 0, 'msg' => "Button is required."], 400);
        }

        $notifications = DB::table('notification')->get();

        foreach ($notifications as $data) {
            $buttons = explode(',', $data->buttons);

            if (in_array($button, $buttons)) {
                $emails = explode(',', $data->email); // ✅ use $data here

                $check_log = DB::table('notification_email_log')->where('button', $button)->latest('updated_at')->first();

                if ($check_log) {
                    $diffInMinutesZ = Carbon::now()->diffInMinutes($check_log->updated_at);
                    if ($diffInMinutesZ >= 15) {

                        Mail::raw($data->message, function ($message) use ($emails) {
                            $message->from('no-reply@yourdomain.com', 'Alert Mail');
                            $message->to($emails)
                                ->subject('Alert Message');
                        });

                        DB::table('notification_email_log')->updateOrInsert(
                            ['button' => $button],
                            ['updated_at' => now()]
                        );
                    }
                } else {
                    Mail::raw($data->message, function ($message) use ($emails) {
                        $message->from('no-reply@yourdomain.com', 'Alert Mail');
                        $message->to($emails)
                            ->subject('Alert Message');
                    });

                    DB::table('notification_email_log')->updateOrInsert(
                        ['button' => $button],
                        ['updated_at' => now()]
                    );
                }


                return response()->json(['status' => 1, 'msg' => 'Email sent successfully.']);
            }
        }

        return response()->json(['status' => 0, 'msg' => "No matching record found."], 404);
    }
}

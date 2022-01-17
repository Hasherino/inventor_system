<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function gearCheck($gear) {
        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }
    }

    public function addLentGear($userGear, $user) {
        foreach ($userGear as $gear) {
            $gear['own'] = 1;
            if($gear['lent'] == 0) {
                $gear['current_holder'] = 1;
            } else {
                $gear['current_holder'] = 0;
            }
        }

        $requests = $user->request()->get();
        $validRequests = [];
        foreach($requests as $request) {
            if ($request->status == 1) {
                if($request == \App\Models\Request::where('gear_id', $request->gear_id)->latest()->get()->first()) {
                    $request['current_holder'] = 1;
                } else {
                    $request['current_holder'] = 0;
                }
                $validRequests[] = $request;
            } elseif($request->status == 2 and $request->gear()->get()->first()->user_id != $user->id) {
                $request['current_holder'] = 1;
                $validRequests[] = $request;
            }
        }

        foreach ($validRequests as $request) {
            $gear = $request->gear()->first();
            $gear['own'] = 0;
            if($request['current_holder'] == 1) {
                $gear['current_holder'] = 1;
            } else {
                $gear['current_holder'] = 0;
            }
            $userGear = $userGear->push($gear);
        }

        return $userGear;
    }
}

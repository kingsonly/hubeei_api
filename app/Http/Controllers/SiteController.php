<?php

namespace App\Http\Controllers;

use App\Models\Hubs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users',
            'firstname' => 'required',
            'lastname' => 'required',
            'password' => 'required',
            'hubDescription' => 'required',
            'url' => 'required|unique:hub',
            'name' => 'required|unique:hub',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $user = new User();
        $time = new \DateTime("Africa/Lagos");
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->name = ucwords($request->input('firstname')) . " " . ucwords($request->input('lastname'));
        $user->email_verified_at = $time->format("Y-m-d h:m:s");

        if ($user->save()) {
            //create a new fee hub
            $hub = new Hubs();
            $hub->name = $request->name;
            $hub->description = $request->hubDescription;
            $hub->url = $request->url;
            $hub->user_id = $user->id;
            $hub->status = 1;

            // note change the sending of email to become a queue
            if ($hub->save()) {
                try {
                    //Mail::to($user->email)->send(new Welcome($user));
                    return response()->json(['status' => 'success', 'message' => " created successfully", 'data' => $user], 201);
                } catch (\Exception $e) {

                    return response()->json(['status' => 'success', 'message' => "Sorry something went wrong"], 401);
                }
            }
        }
        return response()->json(['status' => 'error', 'message' => 'Sorry something went wrong'], 400);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "data" => $validator->errors()], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $authUser = Auth::user();
            $authUser['token'] = $authUser->createToken('MyAuthApp')->plainTextToken;

            return response()->json(['status' => 'success', 'message' => 'user logged in', 'data' => $authUser], 200);
        } else {
            return response()->json(["status" => "error", "message" => "Wrong Email or Password"], 400);
        }
    }

    public function makePayment()
    {
    }

    public function getPaymentPlans()
    {
    }

    public function dashboardCardsContent($id)
    {
        $model = Hubs::with(['categories' => function ($query) {
            $query->withSum('content', 'size');
        }])->find($id);

        $totalSumOfSize = $model->categories->sum(function ($category) {
            return $category->content_sum_size;
        });

        $totalCategories = $model->categories->count();

        $totalContents = $model->categories->sum(function ($category) {
            return $category->content->count(); // Assuming there's a content_count column
        });

        $data = ["totalCategories" => $totalCategories, "totalSize" => $totalSumOfSize, "totalContents" => $totalContents];

        return response()->json(["status" => "success", "data" => $data], 200);
    }

}

<?php

namespace App\Http\Controllers;

use App\Mail\Welcome;
use App\Models\Hub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    public function register(Request $request)
    {
        $userAuth = auth()->guard('sanctum')->user();
        if ($userAuth) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users',
                'firstname' => 'required',
                'lastname' => 'required',
                'password' => 'required',
                "name" => 'required',
                'hubDescription' => 'required',
                'url' => 'required|unique:hub',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => "ensure that all required filed are properly filled "], 400);
            }

            $user = new User();
            $time = new \DateTime("Africa/Lagos");
            $user->email = $request->input('email');
            $user->password = "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi";
            $user->firstname = ucwords($request->input('firstname'));
            $user->lastname = ucwords($request->input('lastname'));
            $user->email_verified_at = $time->format("Y-m-d h:m:s");
            $codex = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -3);
            $user->passwordresetcode = $codex . str_shuffle('1234567');

            if ($user->save()) {
                //create a new fee hub
                $hub = new Hub();
                $hub->name = $request->hubName;
                $hub->description = $request->hubDescription;
                $hub->url = $request->url;
                $hub->user_id = $user->id;
                $hub->status = 1;

                // note change the sending of email to become a queue
                try {
                    //$user->link = time().str_shuffle("01234567893ABCDEFGHIJKLMN01234567893ABCDEFGHIJKLMN").$user->emailresetcode;
                    Mail::to($user->email)->send(new Welcome($user));
                } catch (\Exception $e) {

                    return response()->json(['status' => 'success', 'message' => "Staff created successfully", 'data' => $user], 201);
                }

                return response()->json(['status' => 'success', 'message' => "Staff created successfully", 'data' => $user], 201);
            } else {
                return response()->json(['status' => 'error', 'message' => 'cannot create Staff', 'data' => $user], 400);
            }
        }
        return response()->json(['status' => 'error', 'message' => 'You must be An Admin Member to use this route'], 400);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(["status" => "error", "data" => $validator->errors()], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $authUser = Auth::user();
            $authUser['token'] = $authUser->createToken('MyAuthApp')->plainTextToken;

            return response()->json(['status' => 'success', 'message' => 'user logged in', 'data' => $authUser], 200);
        } else {
            return  response()->json(["status" => "error", "message" => "Wrong Email or Password"], 400);
        }
    }

    public function makePayment()
    {
    }

    public function getPaymentPlans()
    {
    }
}

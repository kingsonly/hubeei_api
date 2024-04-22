<?php

namespace App\Http\Controllers;

use App\Models\HubSubscription;
use App\Models\SubsribersHub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HubSubscriptionController extends Controller
{
    public function login(Request $request)
    {
        //return response()->json(['status' => 'success', 'message' => 'user logged in'], 200);
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'hub_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "data" => $validator->errors()], 400);
        }
        $user = HubSubscription::where(['email' => $request->email, 'hub_id' => $request->hub_id])->first();
        if ($user && Hash::check($request->password, $user->password)) {

            $user['token'] = $user->createToken('hubeeiApp')->plainTextToken;
            return response()->json(['status' => 'success', 'message' => 'user logged in', "data" => $user], 200);
        } else {
            return response()->json(["status" => "error", "message" => "Wrong Email or Password"], 400);
        }
    }

    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'hub_id' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "data" => $validator->errors()], 400);
        }

        $model = new HubSubscription();

        $model->email = $request->email;
        $model->password = bcrypt($request->input('password'));
        $model->hub_id = $request->hub_id;
        $model->name = $request->name;

        if ($model->save()) {
            return response()->json(["status" => "success", "data" => $model], 200);
        } else {
            return response()->json(["status" => "error", "data" => $model], 400);
        }
    }

    public function getHubSubscribers($id)
    {
        //$model =  SubsribersHub::with()->where([])->get();
        $model =  SubsribersHub::with(["subscriber", "subscriberData"])->where(["hub_id" => $id])->get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\HubSubscription;
use App\Models\HubSubsriberData;
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
        $user = HubSubscription::where(['email' => $request->email])->with(["subscribedHub"])->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // filter users subscriber to ensure that hub id exist and the use it to generate a token 
            $checkHubAccess = array_filter($user->subscribedHub->toArray(), function ($var) use ($request) {
                return ($var["hub_id"] == $request->hub_id);
            });
            if (count($checkHubAccess) > 0) {
                $user['token'] = $user->createToken('hubeeiApp')->plainTextToken;
                return response()->json(['status' => 'success', 'message' => 'user logged in', "data" => $user], 200);
            }
            return response()->json(["status" => "error", "message" => "You dont have access to this hub"], 400);
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
            'additional_data' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "data" => $validator->errors()], 400);
        }

        $checkUser = HubSubscription::where(["email" => $request->email])->first();
        if (!$checkUser) {
            $model = new HubSubscription();
            // check if this user already exist 

            $model->email = $request->email;
            $model->password = bcrypt($request->input('password'));
            $model->name = $request->name;
            $model->save();
            if ($model->save()) {
                //link the subscriber to a hub
                $this->linkUserToHubAndCreateOtherData($model->id, $request->hub_id, $request->additional_data);
                return response()->json(["status" => "success", "data" => $model], 200);
            } else {
                return response()->json(["status" => "error", "data" => $model], 400);
            }
        }
        // just create the rest user check user id
        $this->linkUserToHubAndCreateOtherData($checkUser->id, $request->hub_id, $request->additional_data);
        return response()->json(["status" => "success", "data" => $checkUser], 200);
    }

    private function linkUserToHubAndCreateOtherData($subscriberId, $hubId, $additionalData)
    {
        $subscriberHubLinkage = SubsribersHub::create(["subscribers_id" => $subscriberId, "hub_id" => $hubId]);
        // add other registration data 
        $otherData = json_decode($additionalData);
        if (count($otherData) > 0) {
            foreach ($otherData as $data) {
                $otherDataModel = new HubSubsriberData();
                $otherDataModel->subsribers_hubs_id = $subscriberHubLinkage->id;
                $otherDataModel->title = $data->name;
                $otherDataModel->value = $data->value;
                $otherDataModel->save();
            }
        }
    }

    public function getHubSubscribers($id)
    {
        //$model =  SubsribersHub::with()->where([])->get();
        $model =  SubsribersHub::with(["subscriber", "subscriberData"])->where(["hub_id" => $id])->get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }
}

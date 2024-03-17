<?php

namespace App\Http\Controllers;

use App\Models\Hubs;
use App\Models\HubSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HubController extends Controller
{
    //
    /**
     *
     *
     * @return void
     */
    public function index()
    {
        $model = Hubs::with(["user"])->get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    public function getUsersHubs($id)
    {
        $model = Hubs::where(["user_id" => $id])->with(["settings"])->get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    public function getUsersHubsByHubName($id)
    {
        $model = Hubs::where(["url" => $id])->with(["settings"])->first();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    public function view(Hubs $id)
    {
        return response()->json(["status" => "success", "data" => $id], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => 'required|unique:hub',
            'hubDescription' => 'required',
            'url' => 'required|unique:hub',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }
        $model = new Hubs();
        $model->name = $request->name;
        $model->description = $request->hubDescription;
        $model->url = $request->url;
        $model->user_id = auth()->guard('sanctum')->user()->id;
        $model->status = 1;
        if ($model->save()) {
            // create settings for the hub
            $data = [
                [
                    "name" => "logo",
                    "value" => "",
                ],
                [
                    "name" => "menu",
                    "value" => 1,
                ],
                [
                    "name" => "sportlight",
                    "value" => 0,
                ],
                [
                    "name" => "search",
                    "value" => 1,
                ],
                [
                    "name" => "content",
                    "value" => "#000",
                ],
                [
                    "name" => "category",
                    "value" => "#000",
                ],
                [
                    "name" => "background",
                    "value" => "#000",
                ],
                [
                    "name" => "registration",
                    "value" => 0,
                ],
                [
                    "name" => "topten",
                    "value" => 1,
                ],
            ];

            foreach ($data as $value) {
                $this->hubSettings($value, $model->id);
            }
            return response()->json(["status" => "sucess", "data" => $model], 200);
        }
        return response()->json(["status" => "error"], 400);
    }

    public function update(Request $request, Hubs $id)
    {
        $model = $id;
        if (!empty($request->hubName)) {
            $model->name = $request->hubName;
        }
        if (!empty($request->hubDescription)) {
            $model->description = $request->hubDescription;
        }
        if (!empty($request->url)) {
            $model->url = $request->url;
        }

        if (!empty($request->url) || !empty($request->hubDescription) || !empty($request->hubName)) {
            if ($model->save()) {
                return response()->json(["status" => "success"], 200);
            }
            return response()->json(["status" => "error", "message" => "could not update records"], 400);
        }
        return response()->json(["status" => "success", "message" => "You have not specified records to be updated"], 400);
    }

    public function hubSettings($data, $hubId)
    {
        $model = new HubSettings();
        $model->value = $data["value"];
        $model->name = $data["name"];
        $model->hub_id = $hubId;
        $model->status = 1;
        if ($model->save()) {
            return true;
        }
        return false;
    }

    public function delete(Hubs $id)
    {
        if ($id->delete()) {
            return response()->json(["status" => "success"], 200);
        }
        return response()->json(["status" => "error", "message" => "could not delete the selected recored"], 400);
    }
}

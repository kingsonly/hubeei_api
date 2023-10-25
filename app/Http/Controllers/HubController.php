<?php

namespace App\Http\Controllers;

use App\Models\Hub;
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
        $model = Hub::with(["user"])->get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    public function getUsersHubs($id)
    {
        $model = Hub::where(["user_id" => $id])->with(["user"])->get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    public function view(Hub $id)
    {
        return response()->json(["status" => "success", "data" => $id], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => 'required',
            'hubDescription' => 'required',
            'url' => 'required|unique:hub',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => "ensure that all required filed are properly filled "], 400);
        }
        $model = new Hub();
        $model->name = $request->hubName;
        $model->description = $request->hubDescription;
        $model->url = $request->url;
        $model->user_id = auth()->guard('sanctum')->user()->id;
        $model->status = 1;
        if ($model->save) {
            return response()->json(["status" => "sucess"], 200);
        }
        return response()->json(["status" => "error"], 400);
    }

    public function update(Request $request, Hub $id)
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

    public function delete(Hub $id)
    {
        if ($id->delete()) {
            return response()->json(["status" => "success"], 200);
        }
        return response()->json(["status" => "error", "message" => "could not delete the selected recored"], 400);
    }
}
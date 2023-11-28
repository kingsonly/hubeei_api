<?php

namespace App\Http\Controllers;

use App\Models\HubSettings;
use Illuminate\Http\Request;

class HubSettingsController extends Controller
{
    //
    public function updateSettings(Request $request)
    {
        $model = HubSettings::where(["hub_id" => $request->input("hub_id"), "name" => $request->input("type")])->first();
        if ($request->input("type") !== "logo") {
            $model->value = $request->input("value");
        } else {
            $logo = $this->logo($request);
            $model->value = $logo;

        }

        if ($model->save()) {
            return response()->json(["status" => "success"], 200);
        } else {
            return response()->json(["status" => "error", "message" => "update settings at this moment"], 200);

        }

    }

    private function logo($request): string
    {
        $logo = $request->file("value");
        $fileName = time() . '.' . $logo->getClientOriginalExtension();
        if ($logo->move(public_path('images/hub/logo'), $fileName)) {
            return '/images/hub/logo/' . $fileName;
        }
    }
}

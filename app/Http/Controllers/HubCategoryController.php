<?php

namespace App\Http\Controllers;

use App\Models\HubCategory;
use App\Models\Hubs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HubCategoryController extends Controller
{
    //
    public function index()
    {
        $model = HubCategory::with(["hub"])->get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    public function getHubCategory($id)
    {
        $model = HubCategory::where(["hub_id" => $id])->orderBy('position', 'asc')->get();
        if ($model) {
            return response()->json(["status" => "success", "data" => $model], 200);
        }
        return response()->json(["status" => "error", "message" => "No available record"], 400);
    }

    public function create(Request $request)
    {
        // if its not a paid hub check if they have reach their max also check what remains on their max and evaluate with he content size if it does not go above the max, create the content and update user uage 
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "hub_id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => "ensure that all required filed are properly filled "], 400);
        }
        $model = new HubCategory();
        $model->name = $request->name;
        $model->hub_id = $request->hub_id;
        if ($model->save()) {
            //rearange the possition
            $getAllCategories = HubCategory::where(["hub_id" => $request->hub_id])->orderBy('id', 'desc')->get();

            $counter = 1;
            foreach ($getAllCategories as $value) {
                if ($counter == 1) {
                    $value->position = $counter;
                } else {
                    $value->position += 1;
                }
                $value->save();
                $counter++;
            }

            $getAllCategories = HubCategory::where(["hub_id" => $request->hub_id])->orderBy('position', 'asc')->with("content")->get(); // order by possision
            return response()->json(["status" => "success", "data" => $getAllCategories], 200);
        }
        return response()->json(["status" => "error"], 400);
    }

    public function view(HubCategory $id)
    {
        $model = $id;
        if ($model) {
            return response()->json(["status" => "success", "data" => $model], 200);
        }
        return response()->json(["status" => "error", "message" => "could not find a record"], 400);
    }

    public function update(Request $request, HubCategory $id)
    {
        if ($request->name) {
            $id->name = $request->name;
        }
        if ($id->save()) {
            return response()->json(["status" => "success"], 200);
        }

        return response()->json(["status" => "error"], 400);
    }

    public function delete(HubCategory $id)
    {
        if ($id->delete()) {
            return response()->json(['status' => "success"], 200);
        }
        return response()->json(['status' => "error", "message" => "could not delete record at this time"], 400);
    }

    public function changeCatgoryPosition(Request $request)
    {

        $categorId = $request->category_id;
        $position = 1; // Start position

        foreach ($categorId as $cardId) {
            HubCategory::where('id', $cardId)->update(['position' => $position]);
            $position++;
        }

        return response()->json(["status" => "success", 'message' => 'Category order updated successfully']);
    }

    public function getCategoryWithContent($id)
    {
        // get hub with content and category 
        $model = HubCategory::where(["hub_id" => $id])->with("content")->get();
        if ($model) {
            return response()->json(["status" => "success", "data" => $model], 200);
        }
        return response()->json(["status" => "error", "message" => "could not find a record"], 400);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CreateHubRegistrationSettings;
use App\Models\Hubs;
use App\Models\HubSettings;
use App\Models\HubSubsribtionRequiredFields;
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
            'url' => 'required|unique:hubs',
            'name' => 'required',
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
                $data = [
                    [
                        "name" => "logo",
                        "value" => "",
                        "status" => 1
                    ],
                    [
                        "name" => "menu",
                        "value" => 1,
                        "status" => 1
                    ],
                    [
                        "name" => "sportlight",
                        "value" => 0,
                        "status" => 1
                    ],
                    [
                        "name" => "search",
                        "value" => 1,
                        "status" => 1
                    ],
                    [
                        "name" => "content",
                        "value" => "#ffffff",
                        "status" => 1
                    ],
                    [
                        "name" => "category",
                        "value" => "#ffffff",
                        "status" => 1
                    ],
                    [
                        "name" => "background",
                        "value" => "#000000",
                        "status" => 1
                    ],
                    [
                        "name" => "registration",
                        "value" => 0,
                        "status" => 0
                    ],
                    [
                        "name" => "topten",
                        "value" => 1,
                        "status" => 1,
                    ],
                ];

                foreach ($data as $value) {
                    $this->hubSettings($value, $hub->id);
                }

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

    /**
     * formatSizeUnits function
     *
     * this function is use to display a human readable size
     * 
     * @param [type] $bytes
     * @return void
     */
    private function  formatSizeUnits($bytes)
    {
        if ($bytes >= 1099511627776) {
            $size = number_format($bytes / 1099511627776, 2) . ' TB';
        } elseif ($bytes >= 1073741824) {
            $size = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $size = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $size = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $size = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $size = $bytes . ' byte';
        } else {
            $size = '0 bytes';
        }

        return $size;
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

        $data = [
            [
                "title" => "Total Categories",
                "count" => $totalCategories,
            ],

            [
                "title" => "Total Size",
                "count" => $this->formatSizeUnits($totalSumOfSize),
            ],

            [
                "title" => "Total Contents",
                "count" => $totalContents,
            ],

        ];

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    public function hubSettings($data, $hubId)
    {
        $model = new HubSettings();
        $model->value = $data["value"];
        $model->name = $data["name"];
        $model->status = $data["status"];
        $model->hub_id = $hubId;
        $model->status = 1;
        if ($model->save()) {
            return true;
        }
        return false;
    }


    public function hubRegistrationSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hub_id' => 'required',
            'structure' => 'required',
            "with_payment" => "required",
            "tenure" => "required",
            "primary_amount" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => "error", "message" => "Validation failed", "data" => $validator->errors()], 400);
        }
        if (!CreateHubRegistrationSettings::where(["hub_id" => $request->hub_id])->first()) {
            $model = new CreateHubRegistrationSettings();
            $createNewSetting = $model->create($request->all());
            $structure = json_decode($request->structure);
            //return response()->json(['status' => "success", "data" => $structure], 200);
            foreach ($structure as $requiredField) {
                $requiredFieldModel = new HubSubsribtionRequiredFields();
                $requiredFieldModel->hub_registration_settings_id = $createNewSetting->id;
                $requiredFieldModel->name = $requiredField->name;
                $requiredFieldModel->type = $requiredField->type;
                $requiredFieldModel->save();
            }
            // turn registration on in hub_settings
            $getHubSettingsRegistration = HubSettings::where(["hub_id" => $request->hub_id, "name" => "registration"])->first();
            $getHubSettingsRegistration->value = 1;
            $getHubSettingsRegistration->status = 1;
            $getHubSettingsRegistration->save();


            return response()->json(['status' => "success", "data" => $getHubSettingsRegistration], 200);
        }
        $getHubSettingsRegistration = HubSettings::where(["hub_id" => $request->hub_id, "name" => "registration"])->first();
        $getHubSettingsRegistration->value = $getHubSettingsRegistration->value == 1 ? 0 : 1;
        $getHubSettingsRegistration->save();
        return response()->json(['status' => "success", "data" => $getHubSettingsRegistration], 200);
    }

    public function getRegistrationSettingsStatus($id)
    {
        $response = HubSettings::where(["hub_id" => $id, "name" => "registration"])->first();
        return response()->json(['status' => "success", "data" => $response], 200);
    }

    public function getHubRegistrationSettings($id)
    {
        $model = Hubs::with(["createHubRegistrationSettings.hubRegistrationSettingFields"])->findOrFail($id);
        return response()->json(['status' => "success", "data" => $model], 200);
    }

    public function updateHubRegistrationSettings($id, Request $request)
    {
        $model = CreateHubRegistrationSettings::findOrFail($id);
        if ($model) {
            if ($model->update($request->all())) {
                return response()->json(['status' => "success", "message" => "created successfuly"], 200);
            }

            return response()->json(['status' => "error", "message" => "Something went wrong"], 400);
        }
        return response()->json(['status' => "error", "message" => "Something went wrong"], 400);
    }
}

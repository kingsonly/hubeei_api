<?php

namespace App\Http\Controllers;

use App\Models\ContentViews;
use App\Models\Engagementanswers;
use App\Models\EngagementOption;
use App\Models\Engagment;
use App\Models\HubCategoryContent;
use App\Models\Hubs;
use App\Models\UserLikedContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Image as SpecialImage;
use Illuminate\Support\Facades\Storage;


class HubCategoryContentController extends Controller
{
    //
    public function index()
    {
        $model = HubCategoryContent::get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    // ensure that we can pull for every senario
    public function view($id, Request $request)
    {
        $headerValue = $request->header('hub');
        if (!empty($headerValue)) {
            $model = HubCategoryContent::where(["id" => $id])->with(["category.hub"])->first();

            if ($model) {
                if ($model->category->hub->id == $headerValue) {
                    return response()->json(["status" => "success", "data" => $model], 200);
                }
                return response()->json(["status" => "error", "message" => "Content not in the same hub"], 400);
            }
        }
        return response()->json(["status" => "error", "message" => "Hub id is required "], 400);
    }

    public function create(Request $request)
    {
        //validate request here
        $validator = Validator::make($request->all(), [
            "name" => 'required|string',
            "content_type" => 'required|string',
            "content_description" => 'required|string',
            "content" => 'sometimes',
            "thumbnail" => 'sometimes',
            "sportlight" => 'required',
            "with_engagement" => 'required',
            "hub_category_id" => 'required',
            "hub_id" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }
        // note every item created should save have size counter and the size would be used to determine if a free account can add more content or not .
        DB::beginTransaction();
        try {
            switch ($request->content_type) {
                case "engagement":
                    DB::commit();
                    return $this->createEngagement($request);
                    break;
                default:
                    DB::commit();
                    return $this->uploadOtherFiles($request);
                    break;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function update(Request $request, HubCategoryContent $id)
    {

        $model = $id;
        if ($request->file("thumbnail") != null) {
            $file = $request->file('thumbnail');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            if ($file->move(public_path('images/application'), $fileName)) {
                $model->thumbnail = '/images/application/' . $fileName;
            }
        }

        if ($request->file("content") != null) {
            $file = $request->file('content');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            if ($file->move(public_path('images/application'), $fileName)) {
                $model->content = '/images/application/' . $fileName;
            }
        }

        if ($request->input("content") != null) {
            $model->content = $request->input("content");
        }

        $model->name = $request->name;
        $model->content_type = $request->content_type;
        $model->content_description = $request->content_description;
        $model->sportlight = $request->sportlight;

        if ($model->save()) {
            return response()->json(["status" => "success"], 200);
        }
        return response()->json(["status" => "error"], 400);
    }

    public function delete(HubCategoryContent $id)
    {
        //ensure that delete also delete the uploaded content
        if ($id->delete()) {
            return response()->json(["status" => "success"], 200);
        }
        return response()->json(["status" => "serror"], 400);
    }


    public function uploadOtherFiles(Request $request)
    {
        $engagmentModel = new Engagment();
        $hubId = $request->hub_id;
        if ($request->content_type == "video" or $request->content_type == "audio" or $request->content_type == "pdf") {
            $file = $request->file('content');
            $thumbNail = $request->file('thumbnail');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $fileSizeInBytes = $file->getSize();

            if ($file->move(public_path('images/application'), $fileName)) {
                $fileSizeInKB = $fileSizeInBytes / 1024;
                $fileSizeInMB = $fileSizeInKB / 1024;
                $thumbNailFileSizeInBytes = $thumbNail->getSize();
                $thumbNailFileSizeInKB = $fileSizeInBytes / 1024;
                $thumbNailFileSizeInMB = $fileSizeInKB / 1024;
                //$size = $thumbNailFileSizeInMB + $fileSizeInMB;
                $size = $thumbNailFileSizeInBytes + $fileSizeInBytes;

                $data = [
                    "name" => $request->name,
                    "content_type" => $request->content_type,
                    "content_description" => $request->content_description,
                    "content" => '/images/application/' . $fileName,
                    "thumbnail" => $this->uploadThumbnail($request),
                    "hub_category_id" => $request->hub_category_id,
                    "sportlight" => $request->sportlight,
                    "size" => $size,
                    "with_engagement" => $request->with_engagement,
                    "status" => 1,
                ];

                if ($createContent = $this->createNewContent($data)) {
                    if ($request->with_engagement == 1) {
                        $engagmentData = json_decode($request->input("engagment_data"));

                        $this->createActualEngagement($createContent, $engagmentModel, $engagmentData, $hubId);
                    }

                    return response()->json(["status" => "success", "data" => $createContent], 200);
                }
                return response()->json(["status" => "error"], 200);
            } else {

                $data = [
                    "name" => $request->name,
                    "content_type" => $request->content_type,
                    "content_description" => $request->content_description,
                    "content" => '/images/application/' . $fileName,
                    "thumbnail" => $this->uploadThumbnail($request),
                    "hub_category_id" => $request->hub_category_id,
                    "sportlight" => $request->sportlight,
                    "with_engagement" => $request->with_engagement,
                    "status" => 1,
                ];

                if ($createContent = $this->createNewContent($data)) {
                    return response()->json(["status" => "success", "data" => $createContent], 200);
                }
                return response()->json(["status" => "error"], 200);
            }

            //$file->move(public_path('images/application'), $fileName);

            //$resultDocument->file_path = '/images/application/' . $fileName;
        }

        if ($request->content_type == "link") {
            $file = $request->content;
            $thumbNail = $request->file('thumbnail');

            $sizeInBytes = mb_strlen($file, '8bit');
            $sizeInMB = $sizeInBytes / (1024 * 1024);

            $thumbNailFileSizeInBytes = $thumbNail->getSize();
            $thumbNailFileSizeInKB = $thumbNailFileSizeInBytes / 1024;
            $thumbNailFileSizeInMB = $thumbNailFileSizeInKB / 1024;

            //$size = $thumbNailFileSizeInMB + $sizeInMB;
            $size = $thumbNailFileSizeInBytes + $sizeInBytes;

            $data = [
                "name" => $request->name,
                "content_type" => $request->content_type,
                "content_description" => $request->content_description,
                "content" => $file,
                "thumbnail" => $this->uploadThumbnail($request),
                "hub_category_id" => $request->hub_category_id,
                "sportlight" => $request->sportlight,
                "with_engagement" => $request->with_engagement,
                "size" => $size,
                "status" => 1,
            ];

            if ($createContent = $this->createNewContent($data)) {
                if ($request->with_engagement == 1) {
                    $engagmentData = json_decode($request->input("engagment_data"));

                    $this->createActualEngagement($createContent, $engagmentModel, $engagmentData, $hubId);
                }

                return response()->json(["status" => "success", "data" => $createContent], 200);
            }
            return response()->json(["status" => "error"], 200);

            //$file->move(public_path('images/application'), $fileName);

            //$resultDocument->file_path = '/images/application/' . $fileName;
        }
    }

    // create a function for uploading thumbnail

    // create a function for creating just link upload

    // create a route to use to fetch sportlight content

    public function uploadThumbnail($request)
    {
        $file = $request->file('thumbnail');
        if ($file == null) {
            return 'public/images/thumbnail/hubieelogo.jpg';
        }
        if (!Storage::exists(public_path('images/thumbnail'))) {
            Storage::makeDirectory(public_path('images/thumbnail'), 0777, true); // You can adjust the permissions as needed
        }
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        // convert thumbnail for all card size on the hub page 
        $container1Image = SpecialImage::make($file)->resize(300, 200)->encode('jpg');
        $container1Image->save(public_path('images/thumbnail/300x200_' . $fileName));
        $container2Image = SpecialImage::make($file)->resize(150, 100)->encode('jpg');
        $container2Image->save(public_path('images/thumbnail/150x100_' . $fileName));

        $container3Image = SpecialImage::make($file)->resize(175, 130)->encode('jpg');
        $container3Image->save(public_path('images/thumbnail/150x200_' . $fileName));
        $container4Image = SpecialImage::make($file)->resize(75, 100)->encode('jpg');
        $container4Image->save(public_path('images/thumbnail/75x100_' . $fileName));


        if ($file->move(public_path('images/thumbnail'), $fileName)) {

            return '/images/thumbnail/' . $fileName;
        }
    }

    public function uploadVideo(Request $request, $vimeo)
    {
        // Check if a file was uploaded
        if ($request->hasFile('video')) {
            // Get the file from the request
            $videoFile = $request->file('content');

            // Upload the video to Vimeo
            $video = $vimeo->upload($videoFile, [
                'name' => $videoFile->getClientOriginalName(),
                'description' => $request->name,
            ]);

            // Get the Vimeo video ID
            //$vimeoVideoId = $video['body']['uri'];

            // Store the Vimeo video ID in your database or perform other actions
            if ($video) {
                $data = [
                    "name" => $request->name,
                    "content_type" => $request->content_type,
                    "content_description" => $request->content_description,
                    "content" => $video['body']['uri'],
                    "thumbnail" => $this->uploadThumbnail($request),
                    "hub_category_id" => $request->hub_category_id,
                    "status" => 1,
                ];
                if ($this->createNewContent($data)) {
                    return response()->json(["status" => "success"], 200);
                }
                return response()->json(["status" => "error"], 400);
            }
        }

        return response()->json(["status" => "error"], 400);
    }

    public function createNewContent($data)
    {
        $model = new HubCategoryContent();
        $model->name = $data["name"];
        $model->content_type = $data["content_type"];
        $model->content_description = $data["content_description"];
        $model->content = $data["content"];
        $model->thumbnail = $data["thumbnail"];
        $model->hub_category_id = $data["hub_category_id"];
        $model->status = $data["status"];
        $model->size = $data["size"];
        $model->sportlight = $data["sportlight"];
        $model->with_engagement = $data["with_engagement"];
        if ($model->save()) {
            // $getAllCategoriesContent = HubCategoryContent::where(["hub_category_id" => $data['hub_category_id']])->orderBy('id', 'desc')->get();
            // $counter = 1;
            // foreach ($getAllCategoriesContent as $value) {
            //     if ($counter == 1) {
            //         $value->position = $counter;
            //     } else {
            //         $value->position += 1;
            //     }
            //     $value->save();
            //     $counter++;
            // }

            return $model;
        } else {
            return false;
        }
    }

    public function getLikedContent($id)
    {
        $userLikedContent = UserLikedContent::where(["user_cookies_id" => $id])->with(["content.category"])->get(); // Replace $userId with the user's ID you want to fetch liked content for.

        $likedContentByCategory = $userLikedContent // Assuming you have defined the "likedContent" relationship in your User model.
            ->map(function ($likedContent) {
                return $likedContent->content->load('category');
            });
        return response()->json(["status" => "success", "data" => $likedContentByCategory], 200);
    }

    public function search($id, Request $search)
    {
        $data = $search->input("query");
        //HubCategoryContent::where(['id' => $content["id"]])->update(['position' => $position,"hub_category_id" => $value["id"]]);
        $hub = Hubs::with(['categories.content' => function ($query) use ($data) {
            $query->where('name', 'like', '%' . $data . '%')
                ->orWhere('content_description', 'like', '%' . $data . '%')
                ->orWhere('content_type', 'like', '%' . $data . '%')
                ->orderBy('position', 'desc');
        }])->find($id);

        // Access the contents
        $contents = $hub->categories;
        if (count($contents) > 0) {
            return response()->json(["status" => "success", 'data' => $contents]);
        } else {
            return response()->json(["status" => "error", 'message' => "there are no sportlight contents at the moment "]);
        }
    }

    public function createEngagement(Request $request)
    {

        $model = new Engagment();
        $validator = Validator::make($request->all(), [
            "engagment_data" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "data" => $validator->errors()], 400);
        }
        // start transaction from here
        $engagmentData = json_decode($request->input("engagment_data"));
        $file = $request->input("engagment_data");
        $thumbNail = $request->file('thumbnail');

        $sizeInBytes = mb_strlen($file, '8bit');
        $sizeInMB = $sizeInBytes / (1024 * 1024);

        $thumbNailFileSizeInBytes = $thumbNail->getSize();
        $thumbNailFileSizeInKB = $thumbNailFileSizeInBytes / 1024;
        $thumbNailFileSizeInMB = $thumbNailFileSizeInKB / 1024;

        //$size = $thumbNailFileSizeInMB + $sizeInMB;
        $size = $thumbNailFileSizeInBytes + $sizeInBytes;

        $data = [
            "hub_id" => $request->hub_id,
            "name" => $request->name,
            "content_type" => $request->content_type,
            "content_description" => $request->content_description,
            "content" => "Not Available",
            "thumbnail" => $this->uploadThumbnail($request),
            "hub_category_id" => $request->hub_category_id,
            "sportlight" => $request->sportlight,
            "size" => $size,
            "status" => 1,
            "with_engagement" => 0,
        ];

        DB::beginTransaction();
        try {
            if ($content = $this->createNewContent($data)) {
                $hubId = $request->hub_id;
                $this->createActualEngagement($content, $model, $engagmentData, $hubId);
                DB::commit();

                return response()->json(["status" => "success"], 200);
            }

            return response()->json(["status" => "error"], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Something whent wrong, Please try again later", "data" => $e], 400);
        }
    }

    private function createActualEngagement($content, $models, $engagmentData, $hubId)
    {
        foreach ($engagmentData as $value) {
            $model = new Engagment();
            $model->question = $value->question;
            $model->hub_content_id = $content->id;
            $model->engagement_type = $value->engagementType;
            $model->answer_type = $value->optionType;
            $model->hub_id = $hubId;
            $model->status = 1;
            if ($model->save()) {
                // save answers too

                foreach ($value->answers as $answers) {
                    $optionModel = new EngagementOption();
                    $optionModel->engagment_id = $model->id;
                    $optionModel->answer = $answers->answer;
                    $optionModel->answer_rank = $answers->status;
                    $optionModel->status = 1;
                    $optionModel->save();
                }
            }
        }
    }

    public function saveViews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'content_id' => 'required',
            'user_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "data" => $validator->errors()], 400);
        }

        $model = new ContentViews();
        $model->users_id = $request->user_id;
        $model->content_id = $request->content_id;
        $model->users_type = $request->user_type;
        $model->created_at = time();
        if ($model->save()) {
            return response()->json(["status" => "success", "data" => $model], 200);
        }
    }

    public function changeContentPosition(Request $request)
    {
        $data = $request->data;

        foreach ($data as $value) {
            $position = 1; // Start position
            foreach ($value["content"] as $content) {
                HubCategoryContent::where(['id' => $content["id"]])->update(['position' => $position, "hub_category_id" => $value["id"]]);
                $position++;
            }
        }

        return response()->json(["status" => "success", 'message' => 'Content order updated successfully']);
    }

    public function getSpotlightContent($id)
    {
        //HubCategoryContent::where(['id' => $content["id"]])->update(['position' => $position,"hub_category_id" => $value["id"]]);
        $hub = Hubs::with(['categories.content' => function ($query) {
            $query->where('sportlight', 1)->orderBy('position', 'desc');
        }])->find($id);

        // Access the contents
        $contents = $hub->categories->flatMap->content;
        if (count($contents) > 0) {
            return response()->json(["status" => "success", 'data' => $contents]);
        } else {
            return response()->json(["status" => "error", 'message' => "there are no sportlight contents at the moment "]);
        }
    }

    public function updateContentViews($id)
    {
        $model = ContentViews::where(["id" => $id])->first();
        if ($model) {
            $model->updated_at = time();
            if ($model->save()) {
                return response()->json(["status" => "success"], 200);
            } else {
                return response()->json(["status" => "error", "message" => "Could not update view"], 400);
            }
        } else {
            return response()->json(["status" => "error", "message" => "there is no view with the provided id"], 400);
        }
    }

    public function getTopTenViews($id)
    {
        //HubCategoryContent::where(['id' => $content["id"]])->update(['position' => $position,"hub_category_id" => $value["id"]]);
        // $hub = Hubs::with(['categories.content' => function ($query) {
        //     $query->orderBy('views', 'desc')->limit(10);
        // }])->find($id);

        // // Access the contents
        // $contents = $hub->categories->flatMap->content;
        $headerValue = request()->header('user');
        $contents = HubCategoryContent::whereHas('category.hub', function ($query) use ($id) {
            $query->where('id', $id);
        })
            ->with(['views', "liked"])
            ->withCount('views as total_views')
            ->orderByDesc('total_views')
            ->get();
        foreach ($contents as $key => $value) {
            foreach ($value->liked as $key2 => $contentLike) {
                if ($contentLike->user_cookies_id == $headerValue) {
                    $value->like = true;
                    break;
                } else {
                    $value->like = false;
                }
            }
        }

        if (count($contents) > 0) {
            return response()->json(["status" => "success", 'data' => $contents]);
        } else {
            return response()->json(["status" => "error", 'message' => "there are no sportlight contents at the moment "]);
        }
    }

    public function likeUnlike($id, Request $request)
    {
        $headerValue = $request->header('user');
        if (!empty($headerValue)) {
            $model = UserLikedContent::where(["content_id" => $id, "user_cookies_id" => $headerValue])->first();
            if (!empty($model)) {
                $model->delete();
                return response()->json(["status" => "you have unfollowed this content"], 200);
            } else {
                $creatLike = new UserLikedContent();
                $creatLike->user_cookies_id = $headerValue;
                $creatLike->content_id = $id;
                if ($creatLike->save()) {
                    return response()->json(["status" => "success"], 200);
                } else {
                    return response()->json(["status" => "error", "message" => "could not create a new like"], 400);
                }
            }
            return response()->json(["status" => "error", "message" => "Something went wrong"], 400);
        } else {
            return response()->json(["status" => "error", "message" => "user header is required"], 400);
        }
    }

    public function getEngagementContentUsers($id, Request $request)
    {
        $userCookiesId = $request->header('user');

        $engagments = Engagment::with(['options', 'answers', 'userAnswer' => function ($query) use ($userCookiesId) {
            $query->where('user_cookies_id', $userCookiesId);
        }])->where(["hub_content_id" => $id])->get();

        foreach ($engagments as $key => $engagment) {
            $engagments[$key]["stats"] = $engagment->optionAnswerCounts();

            // Use $optionAnswerCounts as needed
        }
        return response()->json(["status" => "success", "data" => $engagments], 200);
    }

    public function respondToEngagment($id, Request $request)
    {

        $answers = json_decode($request->answers);
        foreach ($answers as $value) {
            $model = new Engagementanswers();
            $model->engagment_id = $value->engagment_id;
            $model->user_cookies_id = $id;
            $model->option_id = $value->option_id;
            $model->save();
        }
        return response()->json(["status" => "success"], 200);
    }
}

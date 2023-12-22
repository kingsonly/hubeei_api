<?php

namespace App\Http\Controllers;

use App\Models\EngagementOption;
use App\Models\Engagment;
use App\Models\HubCategoryContent;
use App\Models\Hubs;
use App\Models\UserLikedContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Twitter;
use Vimeo\Laravel\Vimeo;

class HubCategoryContentController extends Controller
{
    //
    public function index()
    {
        $model = HubCategoryContent::get();
        return response()->json(["status" => "success", "data" => $model], 200);
    }

    // ensure that we can pull for every senario
    public function view(HubCategoryContent $id)
    {
        $model = $id;
        if ($model) {
            return response()->json(["status" => "success", "data" => $model], 200);
        }
        return response()->json(["status" => "error"], 400);
    }

    public function create(Request $request)
    {
        // note every item created should save have size counter and the size wouild be used to determine if a free account can add more content or not .
        switch ($request->content_type) {
            case "engagement":
                $this->createEngagement($request);
                break;
            case "video":
                $this->uploadOtherFiles($request);
                break;
            default:
                $this->uploadOtherFiles($request);
                break;
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
        if ($request->content_type == "video" or $request->content_type == "audio" or $request->content_type == "pdf") {
            $file = $request->file('content');
            $thumbNail = $request->file('thumbnail');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            if ($file->move(public_path('images/application'), $fileName)) {
                $fileSizeInBytes = $file->getSize();
                $fileSizeInKB = $fileSizeInBytes / 1024;
                $fileSizeInMB = $fileSizeInKB / 1024;

                $thumbNailFileSizeInBytes = $thumbNail->getSize();
                $thumbNailFileSizeInKB = $fileSizeInBytes / 1024;
                $thumbNailFileSizeInMB = $fileSizeInKB / 1024;
                $size = $thumbNailFileSizeInMB + $fileSizeInMB;

                $data = [
                    "name" => $request->name,
                    "content_type" => $request->content_type,
                    "content_description" => $request->content_description,
                    "content" => '/images/application/' . $fileName,
                    "thumbnail" => $this->uploadThumbnail($request),
                    "hub_category_id" => $request->hub_category_id,
                    "sportlight" => $request->sportlight,
                    "size" => $size,
                    "status" => 1,
                ];

                $this->createNewContent($data);
            } else {
                $data = [
                    "name" => $request->name,
                    "content_type" => $request->content_type,
                    "content_description" => $request->content_description,
                    "content" => $request->content,
                    "thumbnail" => $this->uploadThumbnail($request),
                    "hub_category_id" => $request->hub_category_id,
                    "status" => 1,
                ];
                $this->createNewContent($data);
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
            $thumbNailFileSizeInKB = $fileSizeInBytes / 1024;
            $thumbNailFileSizeInMB = $fileSizeInKB / 1024;

            $size = $thumbNailFileSizeInMB + $sizeInMB;

            $data = [
                "name" => $request->name,
                "content_type" => $request->content_type,
                "content_description" => $request->content_description,
                "content" => $file,
                "thumbnail" => $this->uploadThumbnail($request),
                "hub_category_id" => $request->hub_category_id,
                "sportlight" => $request->sportlight,
                "size" => $size,
                "status" => 1,
            ];

            $this->createNewContent($data);

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
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        if ($file->move(public_path('images/thumbnail'), $fileName)) {
            return '/images/thumbnail/' . $fileName;
        }
    }

    public function uploadVideo(Request $request, Vimeo $vimeo)
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
        if ($model->save()) {
            $getAllCategoriesContent = HubCategoryContent::where(["hub_category_id" => $data['hub_category_id']])->orderBy('id', 'desc')->get();
            $counter = 1;
            foreach ($getAllCategoriesContent as $value) {
                if ($counter == 1) {
                    $value->position = $counter;
                } else {
                    $value->position += 1;
                }
                $value->save();
                $counter++;
            }

            //$getAllCategories = HubCategoryContent::where(["hub_category_id" => $data['hub_category_id']])->orderBy('position', 'asc')->get(); // order by possision
            return $model;
        }
        return false;
    }

    public function getLikedContent($id)
    {
        $userLikedContent = UserLikedContent::where(["user_cookies_id" => $id])->get(); // Replace $userId with the user's ID you want to fetch liked content for.

        $likedContentByCategory = $userLikedContent // Assuming you have defined the "likedContent" relationship in your User model.
        ->map(function ($likedContent) {
            return $likedContent->content->load('category');
        })
            ->groupBy(function ($content) {
                return $content->category->name;
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

        $size = $thumbNailFileSizeInMB + $sizeInMB;

        $data = [
            "name" => $request->name,
            "content_type" => $request->content_type,
            "content_description" => $request->content_description,
            "content" => "Not Available",
            "thumbnail" => $this->uploadThumbnail($request),
            "hub_category_id" => $request->hub_category_id,
            "sportlight" => $request->sportlight,
            "size" => $size,
            "status" => 1,
        ];

        DB::beginTransaction();
        try {
            if ($content = $this->createNewContent($data)) {
                foreach ($engagmentData as $value) {
                    $model->question = $value->question;
                    $model->hub_content_id = $content->id;
                    $model->engagementType = $value->engagementType;
                    $model->answer_type = $value->optionType;
                    $model->status = 1;
                    if ($model->save()) {
                        // save answers too
                        $optionModel = new EngagementOption();
                        foreach ($value->answers as $answers) {
                            $optionModel->engagment_id = $model->id;
                            $optionModel->answer = $answers->answer;
                            $optionModel->answer_rank = $answers->status;
                            $optionModel->status = 1;
                            $optionModel->save();
                        }
                    }
                }
                return response()->json(["status" => "success"], 200);

            }

            return response()->json(["status" => "error"], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Something whent wrong, Please try again later", "data" => $e], 400);
        }
    }

    public function saveViews()
    {
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
        $model = HubCategoryContent::where(['id' => $id]);
        if ($model->update(['view' => $model->view + 1])) {
            return response()->json(["status" => "success", 'message' => 'number of views has been updated']);
        }
        return response()->json(["status" => "error", 'message' => 'could not update  views']);

    }

    public function getTopTenViews($id)
    {
        //HubCategoryContent::where(['id' => $content["id"]])->update(['position' => $position,"hub_category_id" => $value["id"]]);
        // $hub = Hubs::with(['categories.content' => function ($query) {
        //     $query->orderBy('views', 'desc')->limit(10);
        // }])->find($id);

        // // Access the contents
        // $contents = $hub->categories->flatMap->content;
        $contents = HubCategoryContent::whereHas('category.hub', function ($query) use ($id) {
            $query->where('id', $id);
        })
            ->with('views')
            ->withCount('views as total_views')
            ->orderByDesc('total_views')
            ->get();

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

    public function getEngagementContentUsers($id)
    {
        // the id here is the content id // please take note.
        $model = Engagment::where(["hub_content_id" => $id])->with(["options.answers"])->get();
        // we can instantly get the engagment count for each answer

    }

    public function getEngagementContentHub()
    {

    }

    public function tweet()
    {
        $tweets = Twitter::forApiV1()->getDms();
        return response()->json(["status" => $tweets], 200);
        dd($tweets);
    }

}

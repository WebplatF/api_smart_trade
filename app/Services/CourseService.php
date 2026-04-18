<?php

namespace App\Services;

use App\Models\CourseDetails;
use App\Models\CourseMaster;
use App\Models\CourseVideos;
use App\Models\UserSubscription;
use App\Resources\CourseLessonResources;
use App\Resources\CourseMasterResources;
use App\Resources\CourseWithLessonResources;
use App\Resources\VideoListResources;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CourseService
{
    /**
     * @param string $title
     * @param string expert
     * @param int $imageId
     * @return array $courseDetails
     * @throws Exception
     */
    public function courseCreation(string $title, string $expert, int $imageId)
    {
        try {
            $course = CourseMaster::create([
                'title' => $title,
                'expert' => $expert,
                'thumbnail_id' => $imageId
            ]);
            $return = CourseMaster::with('image')->find($course->id);
            return [
                "id" => $return->id,
                'expert' => $return->expert,
                'thumbnail_id' => $return->image->media_url ?? ""
            ];
        } catch (QueryException $e) {
            throw new Exception("Course Creation Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Creation Failed :" . $e->getMessage());
        }
    }
    /**
     * @param string $title
     * @param string expert
     * @param int $imageId
     * @return array $courseDetails
     * @throws Exception
     */
    public function courseEdit(int $courseId, string $title, string $expert, int $imageId)
    {
        try {
            $course = CourseMaster::find($courseId);
            if (!$course) {
                throw new Exception('Invalid course details');
            }
            $course->update([
                'title' => $title,
                'expert' => $expert,
                'thumbnail_id' => $imageId
            ]);
            $return = CourseMaster::with('image')->find($course->id);
            return [
                "id" => $return->id,
                'expert' => $return->expert,
                'thumbnail_id' => $return->image->media_url ?? ""
            ];
        } catch (QueryException $e) {
            throw new Exception("Course Creation Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Creation Failed :" . $e->getMessage());
        }
    }
    /**
     * @param string $title
     * @param int $courseId
     * @return array $courseDetails
     * @throws Exception
     */
    public function courseDetailsCreation(string $title, int $courseId)
    {
        try {
            $course = CourseDetails::create([
                'course_id' => $courseId,
                'title' => $title
            ]);
            return [
                "id" => $course->id,
                "course_id" => $course->course_id,
                'title' => $course->title
            ];
        } catch (QueryException $e) {
            throw new Exception("Course Details Creation Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Details Creation Failed :" . $e->getMessage());
        }
    }
    /**
     * @param string $title
     * @return array $courseDetails
     * @throws Exception
     */
    public function courseDetailsEdit(string $title, int $detailsId)
    {
        try {
            $course = CourseDetails::findOrFail($detailsId);
            if (!$course) {
                throw new Exception('Invalid course details');
            }
            $course->update([
                'title' => $title
            ]);
            return [
                "id" => $course->id,
                'title' => $course->title
            ];
        } catch (QueryException $e) {
            throw new Exception("Course Detail Edit Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Detail Edit Failed :" . $e->getMessage());
        }
    }
    /**
     * @param int $detailId
     * @param int $videoId
     * @return array $courseDetails
     * @throws Exception
     */
    public function mapCourseVideo(int $detailId, int $videoId, int $thumbnailId,string $title)
    {
        try {
            $course = CourseVideos::create([
                'detail_id' => $detailId,
                'video_id' => $videoId,
                 'title' => $title,
                'thumbnail_id' => $thumbnailId
            ]);
            $return = CourseVideos::with('image', 'video')->find($course->id);
            return [
                'id' => $course->id,
                'detail_id' => $detailId,
                 'title' => $title,
                'video_id' => $videoId ?? "",
                'video_path' => $return->video != null ?  $return->video->video_id ?? "" : "",
                'thumbnail_id' => $thumbnailId,
                'thumbnail_url' => $return->image->media_url ?? "",
            ];
        } catch (QueryException $e) {
            throw new Exception("Course Video Creation Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Video Creation Failed :" . $e->getMessage());
        }
    }
    /**
     * @return $courseList
     * @throws Exception
     */
    public function courseList()
    {
        try {
            $course = CourseMaster::with('image')->get();
            return CourseMasterResources::collection($course);
        } catch (QueryException $e) {
            throw new Exception("Course List Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course List Failed :" . $e->getMessage());
        }
    }
    /**
     * @return $courseList
     * @throws Exception
     */
    public function courseLessonList(int $courseId)
    {
        try {
            $course = CourseDetails::withCount('videos')->where('course_id', $courseId)->get();
            return CourseLessonResources::collection($course);
        } catch (QueryException $e) {
            throw new Exception("Course List Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course List Failed :" . $e->getMessage());
        }
    }
    /**
     * @return $courseList
     * @throws Exception
     */
    public function lessonVideoList(int $detailId)
    {
        try {
            $course = CourseVideos::with('image', 'video')->where('detail_id', $detailId)->get();
            return VideoListResources::collection($course);
        } catch (QueryException $e) {
            throw new Exception("Course List Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course List Failed :" . $e->getMessage());
        }
    }
    /**
     * @param string $tag
     * @return string $message
     * @throws Exception
     */
    public function courseAcion(string $tag, int $id, bool $status)
    {
        $returnMsg = "";
        try {
            switch (strtolower($tag)) {
                case 'course':
                    $cm = CourseMaster::find($id);
                    if (!$cm) {
                        throw new Exception('Invalid Course');
                    }
                    $cm->update([
                        'is_delete' => $status
                    ]);
                    $returnMsg = "Course " . (!$status ? "activated" : "deactivated") . " Successfully...!";
                    break;
                case 'detail':
                    $cm = CourseDetails::find($id);
                    if (!$cm) {
                        throw new Exception('Invalid Lesson');
                    }
                    $cm->update([
                        'is_delete' => $status
                    ]);
                    $returnMsg = "Lesson " . (!$status ? "activated" : "deactivated") . " Successfully...!";
                    break;
            }
            return $returnMsg;
        } catch (QueryException $e) {
            throw new Exception("Course Action Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Action Failed :" . $e->getMessage());
        }
    }
    public function courseWithLesson(int $userId)
    {
        try {
            $subscription = UserSubscription::where('user_id', $userId)
                ->where('is_delete', 0)
                ->latest()
                ->first();
            $subscriptionId = $subscription->id;
            $data = CourseMaster::with([
                'image',
                'lesson.videos.image',
                'lesson.videos.video',
                'lesson.videos.watchHistory' => function ($query) use ($userId, $subscriptionId) {
                    $query->where('user_id', $userId)
                        ->where('subscription_id', $subscriptionId);
                }
            ])->first();
            if (!$data) {
                throw new Exception("No Course is available...!");
            }
            return new CourseWithLessonResources($data);
        } catch (QueryException $e) {
            throw new Exception("Course Action Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Action Failed :" . $e->getMessage());
        }
    }
    public function courseDetails()
    {
        try {
            $data = DB::table('coursemaster as cm')

                ->join('imageupload as iu_cm', 'iu_cm.id', '=', 'cm.thumbnail_id')

                ->join('coursedetails as cd', 'cm.id', '=', 'cd.course_id')

                ->join('coursevideos as cv', 'cd.id', '=', 'cv.detail_id')

                ->join('imageupload as iu_cv', 'iu_cv.id', '=', 'cv.thumbnail_id')

                ->join('videoupload as vu', 'vu.id', '=', 'cv.video_id')

                ->leftJoin('watchhistory as wh', function ($join) {
                    $join->on('cv.id', '=', 'wh.video_id')
                        ->where('wh.user_id', '=', 1);
                })

                ->select([
                    'cm.*',
                    'cd.*',
                    'cv.*',
                    'wh.*',
                    'iu_cm.*',
                    'iu_cv.*',
                    'vu.*'
                ])

                ->get();
            return $data;
            // return new CourseWithLessonResources($data);
        } catch (QueryException $e) {
            throw new Exception("Course Action Failed :" . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Course Action Failed :" . $e->getMessage());
        }
    }
}

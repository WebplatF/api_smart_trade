<?php

namespace App\Services;

use App\Models\HomePageMaster;
use App\Models\WeeklyMeeting;
use App\Traits\CommonTraits;
use Exception;
use Illuminate\Database\QueryException;

class HomePageService
{
    use CommonTraits;
    /**
     * @param string $title
     * @param int $imageId
     * @return string
     * @throws Exception
     */
    public function addBanner(string $title, int $imageId)
    {
        try {
            HomePageMaster::create(
                [
                    'title' => $title,
                    'source_id' => $imageId,
                    'type' => 'image'
                ]
            );
            return "";
        } catch (Exception $e) {
            throw new Exception("Banner add Failed:" . $e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
    /**
     * @param string $title
     * @param string $videoId
     * @return string
     * @throws Exception
     */
    public function addDemoVideo(string $title, string $videoId)
    {
        try {
            HomePageMaster::create(
                [
                    'title' => $title,
                    'source_id' => $videoId,
                    'type' => 'video'
                ]
            );
            return "";
        } catch (Exception $e) {
            throw new Exception("Banner add Failed:" . $e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
    /**
     * @param string $title
     * @param string $videoId
     * @return string
     * @throws Exception
     */
    public function addWeeklyMeeting(string $title, string $videoId)
    {
        try {
            WeeklyMeeting::create(
                [
                    'title' => $title,
                    'source_id' => $videoId,
                ]
            );
            return "";
        } catch (Exception $e) {
            throw new Exception("Weekly meeting add Failed:" . $e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
    /**
     * @return array $banner & videos urls 
     * @throws Exception
     */
    public function intialData()
    {
        try {
            $queryData = HomePageMaster::where('is_delete', 0)->with([
                'image',
                'video',
                'video.thumbnail'
            ])->get();
            $response = [
                'banner' => [],
                'demo_videos' => []
            ];
            foreach ($queryData as $item) {
                if ($item->type === 'image' && $item->image) {
                    $image_path = $this->signCdnUrl(path: $item->image->media_url, ttl: 300,);
                    $response['banner'][] = [
                        'title' => $item->title,
                        'path' => $image_path
                    ];
                }
                if ($item->type === 'video' && $item->video) {
                    $image_path = $this->signCdnUrl(path: $item->video->thumbnail->media_url, ttl: 300,);
                    $response['demo_videos'][] = [
                        'title' => $item->title,
                        'video_id' => $item->video->video_id,
                        'thumbnail' => $image_path
                    ];
                }
            }
            return $response;
        } catch (Exception $e) {
            throw new Exception("Banner add Failed:" . $e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
    /**
     * @return array $banner & videos urls 
     * @throws Exception
     */
    public function getWeeklyMeeting()
    {
        try {
            $queryData = WeeklyMeeting::where('is_delete', 0)->with([
                'video',
                'video.thumbnail'
            ])->get();
            $response = [
                'weekly_meeting' => []
            ];
            foreach ($queryData as $item) {
                    $image_path = $this->signCdnUrl(path: $item->video->thumbnail->media_url, ttl: 300,);
                    $response['weekly_meeting'][] = [
                        'title' => $item->title,
                        'path' => $item->video->video_id,
                        'thumbnail' => $image_path
                    ];
            }
            return $response;
        } catch (Exception $e) {
            throw new Exception("Weekly meeting Failed:" . $e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
    /**
     * @return array $banner & videos urls 
     * @throws Exception
     */
    public function homebuilderData()
    {
        try {
            $queryData = HomePageMaster::where('is_delete', 0)->with([
                'image',
                'video',
                'video.thumbnail'
            ])->get();
            $weeklyMeeting = WeeklyMeeting::where('is_delete',0)->with([
                'video',
                'video.thumbnail'
            ])->get();
            $response = [
                'banner' => [],
                'demo_videos' => [],
                'weekly_meeting' => [],
            ];
            foreach ($queryData as $item) {
                if ($item->type === 'image' && $item->image) {
                    $response['banner'][] = [
                        'title' => $item->title,
                        'path' => $item->image->media_url
                    ];
                }
                if ($item->type === 'video' && $item->video) {
                    $response['demo_videos'][] = [
                        'title' => $item->title,
                        'video_id' => $item->video->video_id,
                        'thumbnail' => $item->video->thumbnail->media_url
                    ];
                }
            }
            foreach ($weeklyMeeting as $item) {
                    $response['weekly_meeting'][] = [
                        'title' => $item->title,
                        'video_id' => $item->video->video_id,
                        'thumbnail' => $item->video->thumbnail->media_url
                    ];
                
            }
            return $response;
        } catch (Exception $e) {
            throw new Exception("Banner add Failed:" . $e->getMessage());
        } catch (QueryException $e) {
            throw new Exception('Database error' . $e->errorInfo[2] ?? $e->getMessage());
        }
    }
}

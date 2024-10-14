<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'youtube_id' => 'required|string|max:255',
        ]);

        $youtubeId = $request->input('youtube_id');
        
        $response = Http::get("https://www.googleapis.com/youtube/v3/videos", [
            'id' => $youtubeId,
            'key' => env('YOUTUBE_API_KEY'),
            'part' => 'snippet,statistics',
        ]);

        if ($response->failed() || empty($response['items'])) {
            return response()->json(['error' => 'Invalid or not found YouTube ID'], 404);
        }

        $videoData = $response['items'][0]['snippet'];
        $videoStats = $response['items'][0]['statistics'];

        $video = Video::create([
            'youtube_id' => $youtubeId,
            'title' => $videoData['title'],
            'description' => $videoData['description'],
            'embed_url' => "https://www.youtube.com/embed/$youtubeId",
            'views' => $videoStats['viewCount'] ?? 0,
            'likes' => $videoStats['likeCount'] ?? 0,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Video successfully registered',
            'video' => $video,
        ], 201);
    }

    public function index()
    {
        $videos = Video::all(['youtube_id', 'title', 'embed_url']);

        $videosList = $videos->map(function ($video) {
            return [
                'title' => $video->title,
                'thumbnail_url' => "https://img.youtube.com/vi/{$video->youtube_id}/hqdefault.jpg",
                'embed_url' => $video->embed_url,
            ];
        });

        return response()->json($videosList);
    }

    public function show($id)
    {
        $video = Cache::remember("video_details_{$id}", 60 * 5, function () use ($id) {
            return Video::findOrFail($id);
        });

        $youtubeId = $video->youtube_id;

        $response = Http::get("https://www.googleapis.com/youtube/v3/videos", [
            'id' => $youtubeId,
            'key' => env('YOUTUBE_API_KEY'),
            'part' => 'snippet,statistics',
        ]);

        if ($response->failed() || empty($response['items'])) {
            return response()->json(['error' => 'Could not retrieve video data from YouTube'], 500);
        }

        $videoStats = $response['items'][0]['statistics'];

        $video->views = $videoStats['viewCount'] ?? 0;
        $video->likes = $videoStats['likeCount'] ?? 0;

        $video->increment('views');

        return response()->json([
            'title' => $video->title,
            'description' => $video->description,
            'embed_url' => $video->embed_url,
            'views' => $video->views,
            'likes' => $video->likes,
        ]);
    }
}

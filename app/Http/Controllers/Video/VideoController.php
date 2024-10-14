<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Http;

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
            'part' => 'snippet',
        ]);

        if ($response->failed() || empty($response['items'])) {
            return response()->json(['error' => 'Invalid or not found YouTube ID'], 404);
        }

        $videoData = $response['items'][0]['snippet'];

        $video = Video::create([
            'youtube_id' => $youtubeId,
            'title' => $videoData['title'],
            'description' => $videoData['description'],
            'embed_url' => "https://www.youtube.com/embed/$youtubeId",
            'views' => 0, 
            'likes' => 0,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Video successfully registered',
            'video' => $video,
        ], 201);
    }

    public function index()
    {
        $videos = Video::all(['title', 'embed_url']);

        $videosList = $videos->map(function ($video) {
            return [
                'title' => $video->title,
                'thumbnail_url' => "https://img.youtube.com/vi/{$video->youtube_id}/hqdefault.jpg", 
            ];
        });

        return response()->json($videosList);
    }
}

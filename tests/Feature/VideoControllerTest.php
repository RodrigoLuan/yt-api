<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class VideoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_video_with_valid_youtube_id()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        Http::fake([
            'https://www.googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'snippet' => [
                            'title' => 'Test Video',
                            'description' => 'Test Description',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->post('/api/videos', [
                             'youtube_id' => 'validYoutubeId',
                             'user_id' => $user->id, 
                         ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('videos', [
            'youtube_id' => 'validYoutubeId',
            'title' => 'Test Video',
            'user_id' => $user->id, 
        ]);
    }


    public function test_store_video_with_invalid_youtube_id()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        Http::fake([
            'https://www.googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => []
            ], 200), 
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->post('/api/videos', ['youtube_id' => 'invalidYoutubeId']);

        $response->assertStatus(404); 
    }

    public function test_unauthenticated_user_cannot_list_videos()
    {
        $response = $this->getJson('/api/videos');

        $response->assertStatus(401);
    }

    public function test_list_videos()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        Video::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->get('/api/videos');

        $response->assertStatus(200);
        $response->assertJsonCount(3); 
    }

   public function test_show_video_details()
   {
       $user = User::factory()->create();
       $token = JWTAuth::fromUser($user);

       $video = Video::factory()->create([
           'youtube_id' => 'validYoutubeId',
           'title' => 'Test Video',
           'user_id' => $user->id, 
       ]);

       Http::fake([
           'https://www.googleapis.com/youtube/v3/videos*' => Http::response([
               'items' => [
                   [
                       'snippet' => [
                           'title' => 'Test Video',
                           'description' => 'Test Description',
                       ],
                       'statistics' => [
                           'viewCount' => 1000,
                           'likeCount' => 100,
                       ]
                   ]
               ]
           ], 200),
       ]);

       $response = $this->withHeader('Authorization', "Bearer $token")
                        ->get("/api/videos/{$video->id}");

       $response->assertStatus(200);
       $response->assertJson([
           'title' => 'Test Video',
           'views' => 1000,
           'likes' => 100,
       ]);
   }

    public function test_show_non_existent_video()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson('/api/videos/999999');

        $response->assertStatus(404);
    }

    public function test_store_video_with_invalid_data()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/videos', [
                             'youtube_id' => ''
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('youtube_id');
    }


    public function test_store_video_youtube_api_unavailable()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        Http::fake([
            'https://www.googleapis.com/youtube/v3/videos*' => Http::response([], 500)
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/videos', [
                             'youtube_id' => 'validYoutubeId'
        ]);

        $response->assertStatus(500)
                 ->assertJson(['error' => 'Could not retrieve video data from YouTube']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $instagramPosts = $this->getInstagramPosts();
        // abort(404);
        // Log::info('Test log message with data', ['id' => 123]);
        return view('index', compact('instagramPosts'));
    }

    protected function getInstagramPosts(int $postsCount = 6)
    {
        // Cache::forget('instagram_posts');
        return Cache::remember('instagram_posts', 10800, function () use ($postsCount) { // 3h
            $posts = [];
            $response = $this->getInstagramData();
            if ($response->failed()) {
                return []; // Request error
            }
            $data = explode('window._sharedData = ', $response->body())[1] ?? '';
            $data = substr(trim(mb_strstr($data, '</script>', true, 'UTF-8')), 0, -1);
            if (empty($data)) {
                return []; // доступ к профилю видимо закрыт
            }
            $data = json_decode($data, true);
            $data = $data['entry_data']['ProfilePage'] ?? null; // or LoginAndSignupPage
            if (is_null($data)) {
                return []; // many requests
            }
            $data =  $data[0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ?? [];
            $data = array_slice($data, 0, $postsCount);

            foreach ($data as $key => $post) {
                $posts[$key] = [
                    'url' => 'https://www.instagram.com/p/' . ($post['node']['shortcode'] ?? ''),
                    'image' => $post['node']['thumbnail_resources'][3]['src'] ?? '',
                    'likes' => intval($post['node']['edge_liked_by']['count'] ?? 0)
                ];
            }
            return $posts;
        });
    }
    /**
     * Получить данные из инстаграмма
     *
     * @return \Illuminate\Http\Client\Response
     */
    protected function getInstagramData()
    {
        // $host = 'https://www.instagram.com/';
        // $username = 'barocco.by';
        // return Http::get($host . $username . '/');

        $proxyServer = 'https://modny.by/yml/parse-insta-HjcvyT7n4.php';
        return Http::post($proxyServer, ['token' => 'fhYvHhfd74Gn4K9Fb08J']);
    }
}

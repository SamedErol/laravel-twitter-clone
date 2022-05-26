<?php

namespace App\Services;

use App\Http\Requests\TweetRequest;
use App\Models\Like;
use App\Models\Reply;
use App\Models\Tweet;
use Illuminate\Http\Request;

class TweetService {

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function createTweet(TweetRequest $request) 
    {
        $tweet = Tweet::create([
            'content' => $request->content,
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id
        ]);

        return $tweet;
    }

    public function getUserTweet($tweet_id) 
    {
        return Tweet::where('id', $tweet_id)
            ->where('user_id', $this->request->user()->id)
            ->first();
    }

    public function getTweetReplies($tweet_id) 
    {
        return Reply::where('tweet_id', $tweet_id)
            ->whereHas('tweet', function($query) {
                $query->where('user_id', $this->request->user()->id);
            });
    }

    public function replyTweet($tweet_id) 
    {
        $reply = Reply::create([
            'content' => $this->request->content,
            'tweet_id' => $tweet_id,
            'user_id' => $this->request->user()->id
        ]);

        return $reply;
    }

    public function isLiked($userId, $tweet_id) 
    {
        return in_array($userId, Tweet::findOrFail($tweet_id)->likes->pluck('user_id')->toArray());
    }

    public function likeTweet($tweet_id) 
    {
        if(!$this->isLiked($this->request->user()->id, $tweet_id)) {
            $like = Like::create([
                'tweet_id' => $tweet_id,
                'user_id' => $this->request->user()->id
            ]);
        } else {
            return response()->json('Already liked!');
        }

        return $like;
    }

    public function unlikeTweet($tweet_id) 
    {
        if($this->isLiked($this->request->user()->id, $tweet_id)) {
            $unlike = Like::where('tweet_id', $tweet_id)
                ->where('user_id', $this->request->user()->id)
                ->delete();
        } else {
            return response()->json('Can\'t unlike!');
        }

        return response()->json($unlike);
    }

}
<?php

namespace App\Http\Livewire;

use App\Models\Favourite;
use App\Models\Like;
use App\Models\Reply;
use App\Models\Tweet;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class TweetList extends Component
{
    public $content;

    public $tweet;

    public $perPageReplies = 3;

    protected $listeners = [
        'storeReply' => 'render',
        'perPageRepliesIncrease' => 'render'
    ];

    protected $rules = [
        'content' => 'required|max:280'
    ];

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function deleteTweet() {
        Tweet::findOrFail($this->tweet->id)
            ->delete();

        $this->emit('deleteTweet');
    }

    public function deleteReply($replyId) {
        Reply::findOrFail($replyId)
            ->delete();

        $this->emit('deleteReply');
    }

    public function storeReply() {
        $this->validate();

        Reply::create([
            'content' => $this->content,
            'tweet_id' => $this->tweet->id,
            'user_id' => auth()->user()->id
        ]);

        if($this->tweet->user_id !== auth()->user()->id) {
            Notification::send(Tweet::find($this->tweet->id)->user, new UserNotification(auth()->user(), auth()->user()->name.' replied to your tweet.', $this->tweet->id));
        }

        $this->reset('content');
    }

    public function likeTweet() {
        if(!in_array(auth()->user()->id, $this->tweet->likes->pluck('user_id')->toArray())) {
            Like::create([
                'tweet_id' => $this->tweet->id,
                'user_id' => auth()->user()->id
            ]);

            if($this->tweet->user_id !== auth()->user()->id) {
                Notification::send(Tweet::find($this->tweet->id)->user, new UserNotification(auth()->user(), auth()->user()->name.' liked your tweet.', $this->tweet->id));
            }
        } else {
            Like::where('tweet_id', $this->tweet->id)
                ->where('user_id', auth()->user()->id)
                ->delete();

            if($this->tweet->user_id !== auth()->user()->id) {
                Notification::send(Tweet::find($this->tweet->id)->user, new UserNotification(auth()->user(), auth()->user()->name.' unliked your tweet.', $this->tweet->id));
            }
        }

        $this->emit('likeTweet');
    }

    public function addToFavourites() {
        if(!in_array(auth()->user()->id, $this->tweet->favourites->pluck('user_id')->toArray())) {
            Favourite::create([
                'tweet_id' => $this->tweet->id,
                'user_id' => auth()->user()->id
            ]);
        } else {
            Favourite::where('tweet_id', $this->tweet->id)
                ->where('user_id', auth()->user()->id)
                ->delete();
        }

        $this->emit('addToFavourites');
    }

    public function perPageRepliesIncrease() {
        $this->perPageReplies += 3;
    }

    public function render()
    {
        return view('livewire.tweet-list', [
            'likes' => Like::where('tweet_id', $this->tweet->id),
            'favourites' => Favourite::where('tweet_id', $this->tweet->id),
            'replies' => Reply::where('tweet_id', $this->tweet->id)
                ->orderBy('created_at', 'desc')
                ->cursorPaginate($this->perPageReplies),
            'repliesCount' => Reply::where('tweet_id', $this->tweet->id)
                ->count()
        ]);
    }
}

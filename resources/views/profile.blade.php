@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mt-2 mb-3">
        <livewire:user-profile :user="$user" />
    </div>
    <div class="col-md-8 @auth my-2 @endauth">
        <livewire:create-tweet />
        <livewire:tweet-feed :feed="false" :userId="$user->id" />
    </div>
    <!-- Sidebar -->
    <div class="col-md-4 my-2">
        <aside class="aside">
            @auth
            <livewire:follow-users :limit="5" />
            @endauth
            <x-the-copyright />
        </aside>
    </div>
    <!-- /Sidebar -->
</div>
@endsection
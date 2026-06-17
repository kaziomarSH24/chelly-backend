<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user !== null && (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);


Broadcast::channel('conversations.{conversation}', function ($user, Conversation $conversation) {
    return $conversation->users->contains($user);
});

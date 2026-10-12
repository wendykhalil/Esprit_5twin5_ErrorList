<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are used
| to check if an authenticated user can listen to the channel.
|
*/

/*
|--------------------------------------------------------------------------
| Admin Channels - Private channels for admin notifications
|--------------------------------------------------------------------------
*/

Broadcast::channel('admin.reservations', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.payments', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.deliveries', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.contracts', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.reclamations', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.compensation-codes', function ($user) {
    return $user->role === 'admin';
});

/*
|--------------------------------------------------------------------------
| Client Channels - Private channels for individual user notifications
|--------------------------------------------------------------------------
*/

Broadcast::channel('user.{userId}.payments', function ($user, $userId) {
    // Only the owner or an admin can listen to this channel
    return (int) $userId === $user->id || $user->role === 'admin';
});

Broadcast::channel('user.{userId}.deliveries', function ($user, $userId) {
    return (int) $userId === $user->id || $user->role === 'admin';
});

Broadcast::channel('user.{userId}.contracts', function ($user, $userId) {
    return (int) $userId === $user->id || $user->role === 'admin';
});

Broadcast::channel('user.{userId}.reclamations', function ($user, $userId) {
    return (int) $userId === $user->id || $user->role === 'admin';
});

Broadcast::channel('user.{userId}.compensation-codes', function ($user, $userId) {
    return (int) $userId === $user->id || $user->role === 'admin';
});

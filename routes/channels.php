<?php

use Illuminate\Support\Facades\Broadcast;

// 1. PUBLIC CHANNEL: Para sa Admin Dashboard (Lahat ng admins makakarinig)
Broadcast::channel('admin-dashboard', function ($user) {
    return $user->role === 'admin';
});

// 2. PRIVATE CHANNEL: Para sa mismong Residente (Kanya-kanyang channel)
Broadcast::channel('resident.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

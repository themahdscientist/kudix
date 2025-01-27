<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('updates', function ($user) {
    return true;
});

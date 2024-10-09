<?php

namespace App\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class PharmacyObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Model $model): void
    {
        if (filament()->auth()->check()) {
            if (filament()->auth()->user()->isAdmin()) {
                $model->user_id = filament()->auth()->id();
                $model->saveQuietly();
            } else {
                $model->user_id = filament()->auth()->user()->user_id;
                $model->saveQuietly();
            }
        }
    }
}

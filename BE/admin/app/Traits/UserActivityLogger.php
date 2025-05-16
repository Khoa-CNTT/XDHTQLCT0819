<?php

namespace App\Traits;

use App\Models\Story;
use Illuminate\Support\Facades\Auth;

trait UserActivityLogger
{
    public function logAction(string $action, $userId = null): void
    {
        Story::create([
            'user_id' => $userId ?? Auth::id(),
            'action'  => $action,
        ]);
    }
}

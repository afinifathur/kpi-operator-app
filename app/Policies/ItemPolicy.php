<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function delete(User $user, Item $item): bool
    {
        return ! $item->jobs()->exists();
    }

    public function forceDelete(User $user, Item $item): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\Operator;
use App\Models\User;

class OperatorPolicy
{
    public function delete(User $user, Operator $operator): bool
    {
        return ! $operator->jobs()->exists();
    }

    public function forceDelete(User $user, Operator $operator): bool
    {
        return false;
    }
}

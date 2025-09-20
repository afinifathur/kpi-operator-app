<?php

namespace App\Policies;

use App\Models\Machine;
use App\Models\User;

class MachinePolicy
{
    public function delete(User $user, Machine $machine): bool
    {
        return ! $machine->jobs()->exists();
    }

    public function forceDelete(User $user, Machine $machine): bool
    {
        return false;
    }
}

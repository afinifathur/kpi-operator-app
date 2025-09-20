<?php

namespace App\Providers;

use App\Models\Job;
use App\Models\Item;
use App\Models\Machine;
use App\Models\Operator;
use App\Policies\JobPolicy;
use App\Policies\ItemPolicy;
use App\Policies\MachinePolicy;
use App\Policies\OperatorPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Job::class => JobPolicy::class,
		Item::class => ItemPolicy::class,
        Machine::class => MachinePolicy::class,
        Operator::class => OperatorPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

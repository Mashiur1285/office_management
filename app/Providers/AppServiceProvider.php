<?php

namespace App\Providers;

use App\Models\Agent;
use App\Models\BdCompany;
use App\Models\ForeignCompany;
use App\Models\OfficeStaff;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Relation::enforceMorphMap([
            'agency_user' => OfficeStaff::class,
            'agency' => OfficeStaff::class,
            'agent' => Agent::class,
            'bd_company' => BdCompany::class,
            'foreign_company' => ForeignCompany::class,
            'user' => User::class,
        ]);
    }
}

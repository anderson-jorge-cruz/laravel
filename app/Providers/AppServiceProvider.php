<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

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
        $this->configModels();
        $this->configCommands();
        $this->configUrl();
        $this->configDate();
        $this->setupLogViewer();
    }

    public function setupLogViewer(): void
    {
        LogViewer::auth(function ($request) {
            return $request->user() && in_array($request->user()->email, [
                'ti.contagem@simaslog.com.br',
            ]);
        });
    }

    public function configModels(): void
    {
        Model::unguard();
    }

    public function configCommands(): void
    {
        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );
    }

    public function configUrl(): void
    {
        URL::forceHttps();
    }

    public function configDate(): void
    {
        Date::use(CarbonImmutable::class);
    }
}

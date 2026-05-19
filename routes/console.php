<?php

use Illuminate\Foundation\Inspiring;
use App\Console\Commands\MinimaikaChatCommand;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::starting(function ($artisan) {
    $artisan->resolveCommands([
        MinimaikaChatCommand::class,
    ]);
});

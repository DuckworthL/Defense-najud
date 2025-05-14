<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckTimezone extends Command
{
    protected $signature = 'system:timezone';
    
    protected $description = 'Check the current system timezone settings';
    
    public function handle()
    {
        $this->info('Current PHP Default Timezone: ' . date_default_timezone_get());
        $this->info('Current Carbon Timezone: ' . Carbon::now()->timezone->getName());
        $this->info('Current App Config Timezone: ' . config('app.timezone'));
        $this->info('Current Time (UTC): ' . Carbon::now('UTC')->format('Y-m-d H:i:s'));
        $this->info('Current Time (App Timezone): ' . Carbon::now()->format('Y-m-d H:i:s'));
        
        return Command::SUCCESS;
    }
}
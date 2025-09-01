<?php

namespace App\Console\Commands;

use App\Jobs\ImageFilter;
use Illuminate\Console\Command;

class TestImageFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-image-filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ImageFilter::dispatchSync('red', 'red');
         $this->info("OK");
    }
}

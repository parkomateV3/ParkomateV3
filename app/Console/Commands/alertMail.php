<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class alertMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alertmail:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'alert mail every 15 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}

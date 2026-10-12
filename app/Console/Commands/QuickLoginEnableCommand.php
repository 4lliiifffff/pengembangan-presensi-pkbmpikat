<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QuickLoginEnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quick-login:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aktifkan fitur Quick Login pengujian di sistem';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->call('quick-login', ['action' => 'enable']);
    }
}

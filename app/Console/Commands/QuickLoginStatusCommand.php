<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QuickLoginStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quick-login:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tampilkan status aktivasi fitur Quick Login pengujian';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->call('quick-login', ['action' => 'status']);
    }
}

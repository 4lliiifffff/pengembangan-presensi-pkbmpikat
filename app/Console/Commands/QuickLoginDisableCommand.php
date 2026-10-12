<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QuickLoginDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quick-login:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nonaktifkan / sembunyikan fitur Quick Login pengujian dari sistem';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->call('quick-login', ['action' => 'disable']);
    }
}

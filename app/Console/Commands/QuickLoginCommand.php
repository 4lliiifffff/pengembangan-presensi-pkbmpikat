<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class QuickLoginCommand extends Command
{
    /**
     * Nama dan signature perintah console.
     *
     * @var string
     */
    protected $signature = 'quick-login
                            {action? : Tindakan yang ingin dijalankan (enable|disable|toggle|status)}';

    /**
     * Deskripsi perintah console.
     *
     * @var string
     */
    protected $description = 'Kelola aktivasi fitur Quick Login pengujian (aktifkan, nonaktifkan, toggle, atau cek status)';

    /**
     * Eksekusi perintah console.
     */
    public function handle(): int
    {
        $action = strtolower((string) ($this->argument('action') ?? ''));

        if (empty($action)) {
            $this->displayStatus();
            $action = $this->choice(
                'Pilih tindakan untuk fitur Quick Login:',
                ['status', 'enable', 'disable', 'toggle'],
                'status'
            );
        }

        return match ($action) {
            'enable', 'on', 'aktif' => $this->enableQuickLogin(),
            'disable', 'off', 'nonaktif', 'remove', 'hapus' => $this->disableQuickLogin(),
            'toggle' => $this->toggleQuickLogin(),
            'status', 'info' => $this->displayStatus(),
            default => $this->invalidAction($action),
        };
    }

    /**
     * Mengaktifkan fitur Quick Login dan menyimpannya di file .env.
     */
    protected function enableQuickLogin(): int
    {
        $this->updateEnvFile('APP_QUICK_LOGIN', 'true');
        $this->clearConfigCache();

        $this->newLine();
        $this->components->info('Fitur Quick Login berhasil DIAKTIFKAN.');
        $this->line('  • Panel Pengujian di halaman login sekarang <fg=green;options=bold>TERLIHAT</>');
        $this->line('  • Dropdown Quick Role Switcher di header navigasi sekarang <fg=green;options=bold>AKTIF</>');
        $this->line('  • Endpoint rute /quick-login siap digunakan untuk pengujian peran');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Menonaktifkan/menghapus akses fitur Quick Login dari tampilan dan rute.
     */
    protected function disableQuickLogin(): int
    {
        $this->updateEnvFile('APP_QUICK_LOGIN', 'false');
        $this->clearConfigCache();

        $this->newLine();
        $this->components->warn('Fitur Quick Login berhasil DINONAKTIFKAN.');
        $this->line('  • Panel Pengujian di halaman login sekarang <fg=red;options=bold>TERSEMBUNYI / DIHAPUS</>');
        $this->line('  • Dropdown Quick Role Switcher di header navigasi <fg=red;options=bold>DINONAKTIFKAN</>');
        $this->line('  • Endpoint rute /quick-login diblokir (403 Forbidden / Akses Ditolak)');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Mengalihkan status fitur Quick Login ke kebalikan status saat ini.
     */
    protected function toggleQuickLogin(): int
    {
        if (User::isQuickLoginEnabled()) {
            return $this->disableQuickLogin();
        }

        return $this->enableQuickLogin();
    }

    /**
     * Menampilkan status terkini fitur Quick Login dan konfigurasi environment.
     */
    protected function displayStatus(): int
    {
        $isEnabled = User::isQuickLoginEnabled();
        $envRaw = env('APP_QUICK_LOGIN');
        $configVal = config('auth.quick_login');

        $statusBadge = $isEnabled
            ? '<fg=green;options=bold>AKTIF (ENABLED)</>'
            : '<fg=red;options=bold>NONAKTIF (DISABLED)</>';

        $this->newLine();
        $this->components->twoColumnDetail('Status Fitur Quick Login', $statusBadge);
        $this->components->twoColumnDetail('Nilai config("auth.quick_login")', var_export($configVal, true));
        $this->components->twoColumnDetail('Nilai env("APP_QUICK_LOGIN")', var_export($envRaw, true));
        $this->components->twoColumnDetail('Environment Aplikasi (APP_ENV)', (string) config('app.env'));
        $this->components->twoColumnDetail('Debug Mode (APP_DEBUG)', config('app.debug') ? 'true' : 'false');
        $this->newLine();

        $this->line('Petunjuk Penggunaan:');
        $this->line('  • Aktifkan fitur      : <fg=cyan>php artisan quick-login enable</>  atau  <fg=cyan>php artisan quick-login:enable</>');
        $this->line('  • Nonaktifkan fitur   : <fg=cyan>php artisan quick-login disable</> atau  <fg=cyan>php artisan quick-login:disable</>');
        $this->line('  • Beralih status      : <fg=cyan>php artisan quick-login toggle</>');
        $this->line('  • Cek status terkini  : <fg=cyan>php artisan quick-login status</>  atau  <fg=cyan>php artisan quick-login:status</>');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Memperbarui atau menambahkan variabel di file .env root project.
     */
    protected function updateEnvFile(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->components->error("File .env tidak ditemukan pada lokasi: {$envPath}");

            return;
        }

        $envContent = (string) file_get_contents($envPath);

        if (preg_match("/^{$key}=.*/m", $envContent)) {
            $envContent = (string) preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
        } else {
            // Tempatkan di bawah APP_DEBUG jika ada, atau di akhir file
            if (preg_match('/^APP_DEBUG=.*$/m', $envContent, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1] + strlen($matches[0][0]);
                $envContent = substr_replace($envContent, "\n{$key}={$value}", $pos, 0);
            } else {
                $envContent = rtrim($envContent)."\n{$key}={$value}\n";
            }
        }

        file_put_contents($envPath, $envContent);

        // Mutasikan juga environment runtime saat ini
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    /**
     * Membersihkan cache konfigurasi agar perubahan .env langsung berefek.
     */
    protected function clearConfigCache(): void
    {
        try {
            $this->callSilently('config:clear');
        } catch (\Throwable $e) {
            // Ignore if config:clear fails
        }
    }

    /**
     * Menangani aksi tidak valid.
     */
    protected function invalidAction(string $action): int
    {
        $this->components->error("Tindakan '{$action}' tidak dikenali.");
        $this->line('Gunakan: <comment>enable</comment>, <comment>disable</comment>, <comment>toggle</comment>, atau <comment>status</comment>.');

        return self::FAILURE;
    }
}

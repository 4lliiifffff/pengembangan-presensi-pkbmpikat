<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid {--show : Display the keys instead of modifying files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate WebPush VAPID public and private keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keys = null;

        // Ensure OPENSSL_CONF is set for Windows environment
        $opensslConf = 'C:\laragon\bin\php\php-8.5.1\extras\ssl\openssl.cnf';
        if (file_exists($opensslConf)) {
            putenv("OPENSSL_CONF={$opensslConf}");
            $_ENV['OPENSSL_CONF'] = $opensslConf;
            $_SERVER['OPENSSL_CONF'] = $opensslConf;
        }

        // Try using minishlink VAPID helper if available
        try {
            if (class_exists(VAPID::class)) {
                $keys = VAPID::createVapidKeys();
            }
        } catch (\Throwable $e) {
            $this->warn('Minishlink VAPID: '.$e->getMessage());
        }

        // Fallback: Generate ECDSA P-256 keys natively via openssl
        if (! $keys || empty($keys['publicKey']) || empty($keys['privateKey'])) {
            $config = [
                'curve_name' => 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'config' => $opensslConf,
            ];

            // If openssl.cnf is not set in Windows env, try locating php/laragon openssl.cnf
            $opensslConfigPath = getenv('OPENSSL_CONF');
            if (! $opensslConfigPath || ! file_exists($opensslConfigPath)) {
                $possiblePaths = [
                    'C:/laragon/bin/php/php-8.5.1-Win32-x64/extras/ssl/openssl.cnf',
                    'C:/laragon/bin/apache/httpd-2.4.62-win64-VS17/conf/openssl.cnf',
                    'C:/laragon/usr/ssl/openssl.cnf',
                ];
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $config['config'] = $path;
                        break;
                    }
                }
            }

            $res = openssl_pkey_new($config);
            if (! $res) {
                // If OpenSSL fails due to missing config file, generate standard valid VAPID pair safely
                $this->error('OpenSSL EC key generation error: '.openssl_error_string());

                return self::FAILURE;
            }

            openssl_pkey_export($res, $privKey, null, $config);
            $pubKeyDetails = openssl_pkey_get_details($res);

            // Extract x and y coordinates from EC public key
            $x = $pubKeyDetails['ec']['x'];
            $y = $pubKeyDetails['ec']['y'];
            $d = $pubKeyDetails['ec']['d'];

            // Format uncompressed point (0x04 + x + y) in Base64URL
            $uncompressedPoint = "\x04".$x.$y;
            $publicKey = self::base64UrlEncode($uncompressedPoint);
            $privateKey = self::base64UrlEncode($d);

            $keys = [
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ];
        }

        $this->info('VAPID Public Key:  '.$keys['publicKey']);
        $this->info('VAPID Private Key: '.$keys['privateKey']);

        if ($this->option('show')) {
            return self::SUCCESS;
        }

        // Save to .env file
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            $this->setOrUpdateEnv($envContent, 'VAPID_PUBLIC_KEY', $keys['publicKey']);
            $this->setOrUpdateEnv($envContent, 'VAPID_PRIVATE_KEY', $keys['privateKey']);
            $this->setOrUpdateEnv($envContent, 'VAPID_SUBJECT', 'mailto:admin@pkbmpikat.sch.id');

            file_put_contents($envPath, $envContent);
            $this->info('VAPID keys successfully stored in .env!');
        }

        return self::SUCCESS;
    }

    private function setOrUpdateEnv(string &$content, string $key, string $value): void
    {
        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}";
        }
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

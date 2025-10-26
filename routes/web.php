<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return redirect()->to('/panel');

});



/*
|--------------------------------------------------------------------------
| Endpoint: /deploy
|--------------------------------------------------------------------------
| Webhook do automatycznego deploya z GitHuba.
| GitHub wysyła żądanie POST po każdym puszu.
| Endpoint zabezpieczony tokenem w nagłówku: X-Deploy-Token
| Token ustaw w pliku .env jako DEPLOY_TOKEN=...
|
| Ścieżka projektu:
| /home/admin/web/serwer.relotec.pl/public_html
|--------------------------------------------------------------------------
*/




Route::post('/deploy', function () {
    $secret = env('DEPLOY_TOKEN', null);

    // 1. autoryzacja tokena
    $provided = request()->header('X-Deploy-Token');
    if (!$secret || $provided !== $secret) {
        Log::warning('Unauthorized deploy attempt', [
            'ip' => request()->ip(),
            'provided_token' => $provided,
        ]);
        abort(403, 'Unauthorized.');
    }

    $output = [];

    // 2. git pull
    exec('cd /home/admin/web/serwer.relotec.pl/public_html && git reset --hard && git pull 2>&1', $output);

    // 3. composer install
    exec('cd /home/admin/web/serwer.relotec.pl/public_html && /usr/bin/php8.2 /usr/local/bin/composer install --no-dev --optimize-autoloader 2>&1', $output);

    // 4. artisan commands
    exec('cd /home/admin/web/serwer.relotec.pl/public_html && php artisan migrate --force 2>&1', $output);
    exec('cd /home/admin/web/serwer.relotec.pl/public_html && php artisan cache:clear 2>&1', $output);
    exec('cd /home/admin/web/serwer.relotec.pl/public_html && php artisan config:cache 2>&1', $output);
    exec('cd /home/admin/web/serwer.relotec.pl/public_html && php artisan route:cache 2>&1', $output);
    exec('cd /home/admin/web/serwer.relotec.pl/public_html && php artisan view:clear 2>&1', $output);

    Log::info('✅ Deploy completed successfully', [
        'ip' => request()->ip(),
        'output' => $output,
    ]);

    return response()->json([
        'status' => 'ok',
        'output' => $output
    ]);
});
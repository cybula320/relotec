<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::get('deploy', function () {
    // 🔐 Sekret z pliku .env
    $secret = env('DEPLOY_TOKEN', null);

    // 🧩 Sprawdzenie poprawności tokena
    $provided = request()->header('X-Deploy-Token');
    if (!$secret || $provided !== $secret) {
        Log::warning('❌ Unauthorized deploy attempt', [
            'ip' => request()->ip(),
            'provided_token' => $provided,
        ]);
        abort(403, 'Unauthorized.');
    }

    // 📂 Ścieżka projektu (zmień, jeśli inna)
    $path = '/home/admin/web/serwer.relotec.pl/public_html';
    $output = [];

    // 🚀 1. Git pull
    exec("cd {$path} && git reset --hard && git pull 2>&1", $output);

    // 📦 2. Composer install
    exec("cd {$path} && /usr/bin/php8.2 /usr/local/bin/composer install --no-dev --optimize-autoloader 2>&1", $output);

    // 🛠️ 3. Artisan commands
    exec("cd {$path} && php artisan migrate --force 2>&1", $output);
    exec("cd {$path} && php artisan cache:clear 2>&1", $output);
    exec("cd {$path} && php artisan config:cache 2>&1", $output);
    exec("cd {$path} && php artisan route:cache 2>&1", $output);
    exec("cd {$path} && php artisan view:clear 2>&1", $output);

    // 🪵 Logowanie wyniku
    Log::info('✅ Deploy completed successfully', [
        'ip' => request()->ip(),
        'output' => $output,
    ]);

    // 📤 Odpowiedź JSON
    return response()->json([
        'status' => 'ok',
        'output' => $output,
    ]);
});
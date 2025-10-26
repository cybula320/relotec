<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::get('deploy', function () {
    // 🔍 Zbierz podstawowe dane requestu
    $headers = request()->headers->all();
    $payload = file_get_contents('php://input');
    $ip = request()->ip();

    // 🪵 Zapisz wszystko do loga dla debugowania
    Log::info('🐙 GitHub Webhook received', [
        'ip' => $ip,
        'headers' => $headers,
        'payload_raw' => $payload,
    ]);

    // 🔐 Sekret z .env
    $secret = env('DEPLOY_TOKEN', null);

    // ✍️ Weryfikacja podpisu GitHuba (X-Hub-Signature-256)
    $signature = request()->header('X-Hub-Signature-256');
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret ?? '');

    if (!hash_equals($expected, (string) $signature)) {
        Log::warning('❌ Unauthorized GitHub webhook attempt', [
            'ip' => $ip,
            'expected' => $expected,
            'provided' => $signature,
        ]);

        return response()->json(['error' => 'Invalid signature'], 403);
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

    // 🪵 Logowanie wyniku deploya
    Log::info('✅ Deploy completed successfully', [
        'ip' => $ip,
        'output' => $output,
    ]);

    return response()->json([
        'status' => 'ok',
        'output' => $output,
    ]);
});




Route::post('deploy', function () {
    // 🔍 Zbierz podstawowe dane requestu
    $headers = request()->headers->all();
    $payload = file_get_contents('php://input');
    $ip = request()->ip();

    // 🪵 Zapisz wszystko do loga dla debugowania
    Log::info('🐙 GitHub Webhook received', [
        'ip' => $ip,
        'headers' => $headers,
        'payload_raw' => $payload,
    ]);

    // 🔐 Sekret z .env
    $secret = env('DEPLOY_TOKEN', null);

    // ✍️ Weryfikacja podpisu GitHuba (X-Hub-Signature-256)
    $signature = request()->header('X-Hub-Signature-256');
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret ?? '');

    if (!hash_equals($expected, (string) $signature)) {
        Log::warning('❌ Unauthorized GitHub webhook attempt', [
            'ip' => $ip,
            'expected' => $expected,
            'provided' => $signature,
        ]);

        return response()->json(['error' => 'Invalid signature'], 403);
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

    // 🪵 Logowanie wyniku deploya
    Log::info('✅ Deploy completed successfully', [
        'ip' => $ip,
        'output' => $output,
    ]);

    return response()->json([
        'status' => 'ok',
        'output' => $output,
    ]);
});

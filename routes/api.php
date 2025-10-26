<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('deploy', function (Request $request) {
    // 📁 Ścieżki
    $repoDir = '/home/admin/web/serwer.relotec.pl/public_html';
    $logFile = '/home/admin/web/serwer.relotec.pl/private/deploy.log';
    $phpBin  = '/usr/bin/php8.2';
    $composerBin = '/usr/bin/composer';

    // 🔐 Sekret (ten sam co w GitHub Webhook → "Secret")
    $secret = 'twoj_super_tajny_token_123';

    // 🧾 Pobierz payload
    $payload = $request->getContent();
    if (!$payload) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ❌ Brak payload\n", FILE_APPEND);
        return response('Brak payload', 400);
    }

    // 📬 Sprawdź podpis
    $signatureHeader = $request->header('X-Hub-Signature-256') ?? $request->header('X-Hub-Signature');
    if (!$signatureHeader) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ❌ Brak podpisu w nagłówkach\n", FILE_APPEND);
        return response('Brak podpisu', 403);
    }

    [$algo, $signature] = array_pad(explode('=', $signatureHeader, 2), 2, '');
    $expectedHash = hash_hmac($algo, $payload, $secret);

    if (!hash_equals($expectedHash, $signature)) {
        file_put_contents(
            $logFile,
            "[" . date('Y-m-d H:i:s') . "] ❌ Niepoprawny podpis\nOczekiwany: $expectedHash\nOtrzymany: $signature\n\n",
            FILE_APPEND
        );
        return response('Niepoprawny podpis', 403);
    }

    // 🔍 Rozkoduj payload i sprawdź branch
    $data = json_decode($payload, true);
    if (!isset($data['ref']) || $data['ref'] !== 'refs/heads/main') {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ℹ️ Push nie na main, pomijam\n", FILE_APPEND);
        return response('Nie main', 200);
    }

    // 🪵 Start logowania
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 🚀 Deploy start\n", FILE_APPEND);

    // 🧭 Przejdź do repo
    chdir($repoDir);

    // 🔄 Git aktualizacja
    exec('git fetch --all 2>&1', $outFetch);
    exec('git reset --hard origin/main 2>&1', $outReset);
    exec('git clean -fd 2>&1', $outClean);

    file_put_contents($logFile, "🧩 GIT:\n" . implode("\n", array_merge($outFetch, $outReset, $outClean)) . "\n", FILE_APPEND);

    // 💾 Composer
    exec("$composerBin install --no-dev --optimize-autoloader 2>&1", $outComposer, $retComposer);
    file_put_contents($logFile, "💾 COMPOSER:\n" . implode("\n", $outComposer) . "\n", FILE_APPEND);

    // ⚙️ Laravel Artisan
    $artisanCommands = [
        "$phpBin artisan migrate --force",
        "$phpBin artisan config:cache",
        "$phpBin artisan route:cache",
        "$phpBin artisan view:clear",
        "$phpBin artisan cache:clear",
    ];

    foreach ($artisanCommands as $cmd) {
        exec("$cmd 2>&1", $output);
        file_put_contents($logFile, "🔧 $cmd\n" . implode("\n", $output) . "\n", FILE_APPEND);
        $output = []; // reset
    }

    // ✅ Koniec
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ✅ Deploy zakończony\n\n", FILE_APPEND);

    return response('Deploy OK', 200);
});
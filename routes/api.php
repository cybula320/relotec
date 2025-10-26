<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('deploy', function () {
    // 📁 Ścieżki
    $repoDir = '/home/admin/web/serwer.relotec.pl/public_html';
    $logFile = '/home/admin/web/serwer.relotec.pl/private/deploy.log';

    // 🔐 Sekret lokalny (taki sam jak w "Secret" w GitHub Webhook)
    $secret = 'twoj_super_tajny_token_123';

    // 🧾 Pobierz payload
    $payload = file_get_contents('php://input');
    if (!$payload) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Brak payload\n", FILE_APPEND);
        return response('Brak payload', 400);
    }

    // 📬 Sprawdź podpis
    $signatureHeader = request()->header('X-Hub-Signature-256') ?? request()->header('X-Hub-Signature') ?? '';
    if (!$signatureHeader) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Brak podpisu w nagłówkach\n", FILE_APPEND);
        return response('Brak podpisu', 403);
    }

    [$algo, $signature] = array_pad(explode('=', $signatureHeader, 2), 2, '');
    $payloadHash = hash_hmac($algo, $payload, $secret);

    if (!hash_equals($signature, $payloadHash)) {
        file_put_contents(
            $logFile,
            "[" . date('Y-m-d H:i:s') . "] Niepoprawny podpis\nOczekiwany: $payloadHash\nOtrzymany: $signature\n\n",
            FILE_APPEND
        );
        return response('Niepoprawny podpis', 403);
    }

    // 🔍 Rozkoduj payload
    $data = json_decode($payload, true);
    if (!isset($data['ref']) || $data['ref'] !== 'refs/heads/main') {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Push nie na main, pomijam\n", FILE_APPEND);
        return response('To nie jest push na main', 200);
    }

    // 🪵 Start logowania
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Deploy start\n", FILE_APPEND);

    // 🚀 Sprawdź, czy exec() działa
    if (!function_exists('exec')) {
        file_put_contents($logFile, "BŁĄD: Funkcja exec() wyłączona w PHP\n", FILE_APPEND);
        return response('exec() wyłączone – sprawdź konfigurację PHP', 200);
    }

    // 🧭 Przejdź do repo i wykonaj polecenia
    chdir($repoDir);

    exec('git fetch --all 2>&1', $output1, $return1);
    file_put_contents($logFile, "git fetch:\n" . implode("\n", $output1) . "\n", FILE_APPEND);

    exec('git reset --hard origin/main 2>&1', $output2, $return2);
    file_put_contents($logFile, "git reset:\n" . implode("\n", $output2) . "\n", FILE_APPEND);

    exec('git clean -fd 2>&1', $output3, $return3);
    file_put_contents($logFile, "git clean:\n" . implode("\n", $output3) . "\n", FILE_APPEND);

    // 💾 Composer + Laravel
    exec('cd /home/jancybulski/web/relotec && /usr/bin/composer install --no-dev --optimize-autoloader 2>&1', $composerOutput, $composerReturn);
    file_put_contents($logFile, "composer install:\n" . implode("\n", $composerOutput) . "\n", FILE_APPEND);

    exec('php artisan migrate --force 2>&1', $mig, $migRet);
    exec('php artisan config:cache 2>&1', $cfg, $cfgRet);
    exec('php artisan route:cache 2>&1', $rt, $rtRet);
    exec('php artisan view:clear 2>&1', $vc, $vcRet);
    exec('php artisan cache:clear 2>&1', $cc, $ccRet);

    file_put_contents($logFile, "artisan:\n" . implode("\n", array_merge($mig, $cfg, $rt, $vc, $cc)) . "\n", FILE_APPEND);

    // ✅ Koniec
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Deploy done\n\n", FILE_APPEND);

    return response('Deploy wykonany', 200);
});
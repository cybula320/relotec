<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nowe logowanie</title>
</head>
<body>
    <h2>Nowe logowanie do systemu RELOTEC</h2>

    <ul>
        @foreach($logInfo as $key => $value)
            <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
        @endforeach
    </ul>
</body>
</html>
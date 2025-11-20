<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oferta {{ $oferta->numer }}</title>
    <style>
        @page {
            margin: 20mm 15mm 15mm 15mm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 5px;
        }
        
        .logo-subtitle {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-info {
            font-size: 11px;
            color: #666;
        }
        
        .date-location {
            text-align: right;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .client-section {
            margin-bottom: 15px;
        }
        
        .client-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .offer-title {
            text-align: center;
            margin: 20px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .offer-number {
            text-align: center;
            color: #e74c3c;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 20px;
        }
        
        .description-section {
            margin: 20px 0;
        }
        
        .description-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .details-section {
            margin: 20px 0;
            font-size: 11px;
            line-height: 1.6;
        }
        
        .details-section h3 {
            font-size: 12px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .details-section ul {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        
        .price-section {
            margin: 20px 0;
            font-size: 13px;
            font-weight: bold;
        }
        
        .terms-section {
            margin: 15px 0;
            font-size: 11px;
            line-height: 1.6;
        }
        
        .terms-section p {
            margin-bottom: 8px;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #666;
        }
        
        .footer-content {
            display: table;
            width: 100%;
        }
        
        .footer-column {
            display: table-cell;
            width: 25%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .footer h4 {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #333;
        }
        
        .highlight {
            background-color: #fff2cc;
            padding: 2px 4px;
        }
        
        .total-price {
            font-size: 16px;
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border: 2px solid #e74c3c;
        }
    </style>
</head>
<body>
    {{-- NAGŁÓWEK --}}
    <div class="header">
        <div class="header-left">
            <div class="logo">FREZTECH</div>
            <div class="logo-subtitle">TOCZENIE • FREZOWANIE • SPAWANIE</div>
        </div>
        <div class="header-right">
            <div class="company-info">
                <strong>P.U.H. FREZTECH</strong><br>
                Adam Jankowicz
            </div>
        </div>
    </div>

    {{-- DATA I MIEJSCE --}}
    <div class="date-location">
        Krzęcin, {{ $oferta->created_at->format('Y-m-d') }}
    </div>

    {{-- DANE KLIENTA --}}
    <div class="client-section">
        <div class="client-name">
            {{ $oferta->firma->nazwa ?? 'Sz. P. Imię i Nazwisko' }}
        </div>
        @if($oferta->firma->adres)
            <div style="font-size: 11px; color: #666;">
                {{ $oferta->firma->adres }}
                @if($oferta->firma->miasto), {{ $oferta->firma->miasto }}@endif
            </div>
        @endif
        @if($oferta->firma->nip)
            <div style="font-size: 11px; color: #666;">NIP: {{ $oferta->firma->nip }}</div>
        @endif
    </div>

    {{-- TYTUŁ OFERTY --}}
    <div class="offer-title">
        {{ $oferta->firma->nazwa ?? 'Oferta cenowa' }}
    </div>

    {{-- NUMER OFERTY --}}
    <div class="offer-number">
        Oferta cenowa {{ $oferta->numer }}
    </div>

    {{-- OPIS GŁÓWNY --}}
    <div class="description-section">
        <div class="description-title">OPIS:</div>
        <div>
            @if($oferta->uwagi)
                {{ $oferta->uwagi }}
            @else
                Wykonanie elementów zgodnie z specyfikacją
            @endif
        </div>
    </div>

    {{-- TABELA POZYCJI --}}
    @if($oferta->pozycje->count() > 0)
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;">Lp.</th>
                <th style="width: 50%;">Nazwa</th>
                <th style="width: 20%;">Materiał</th>
                <th style="width: 20%;">Ilość</th>
            </tr>
        </thead>
        <tbody>
            @foreach($oferta->pozycje as $index => $pozycja)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $pozycja->nazwa }}</td>
                <td>
                    @if($pozycja->opis)
                        {{ $pozycja->opis }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $pozycja->ilosc }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- SZCZEGÓŁY OFERTY --}}
    <div class="details-section">
        <h3>Półfabrykat dostarczony przez klienta</h3>
        
        <h3>W skład ofert wchodzi:</h3>
        <ul>
            @if($oferta->pozycje->count() > 0)
                @foreach($oferta->pozycje as $pozycja)
                <li>{{ $pozycja->nazwa }}@if($pozycja->opis) - {{ $pozycja->opis }}@endif</li>
                @endforeach
            @else
                <li>Toczenie elementów zgodnie ze specyfikacją</li>
                <li>Kontrola jakości wykonanych elementów</li>
                <li>Pakowanie i przygotowanie do odbioru</li>
            @endif
        </ul>
    </div>

    {{-- CENA --}}
    <div class="total-price">
        Koszt wykonania {{ $oferta->pozycje->sum('ilosc') ?? '1' }}szt wynosi – {{ number_format($oferta->total_net, 2, ',', ' ') }} {{ $oferta->waluta ?? 'PLN' }} NETTO
    </div>

    {{-- WARUNKI --}}
    <div class="terms-section">
        <p><em>Podstawą do rozpoczęcia prac jest przesłanie pisemnego zamówienia.</em></p>
        
        <p><strong>Warunki płatności:</strong> 
            @if($oferta->paymentMethod)
                {{ $oferta->paymentMethod->nazwa }}
                @if($oferta->paymentMethod->termin)
                    {{ $oferta->paymentMethod->termin }} dni
                @endif
            @else
                Przelew 7 dni
            @endif
        </p>
        
        <p><strong>Oferta ważna 7dni</strong></p>
        
        <p><strong>Termin realizacji: ok. 3 tygodnie</strong></p>
        
        <p><strong>Ofertę przygotował:</strong> {{ $oferta->user->name ?? 'Dawid Jankowicz' }}</p>
        
        <p><strong>Numer kontaktowy:</strong> {{ $oferta->user->phone ?? '+48 728 400 808' }}</p>
        
        <p><strong>E-mail:</strong> 
            @if($oferta->user && $oferta->user->email)
                {{ $oferta->user->email }}
            @else
                <a href="mailto:dawid.jankowicz@freztech.com.pl">dawid.jankowicz@freztech.com.pl</a>
            @endif
        </p>
    </div>

    {{-- STOPKA --}}
    <div class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h4>Kontakt:</h4>
                +48 609 483 019<br>
                biuro@freztech.com.pl
            </div>
            <div class="footer-column">
                <h4>Oddział:</h4>
                ul. Topolowa 10<br>
                32-050 Borek Szlachecki
            </div>
            <div class="footer-column">
                <h4>Siedziba:</h4>
                ul. Działowa 21<br>
                32-051 Krzęcin
            </div>
            <div class="footer-column">
                <h4>NIP 6791897141</h4>
                REGON: 369802015<br>
                www.freztech.com.pl
            </div>
        </div>
    </div>
</body>
</html>

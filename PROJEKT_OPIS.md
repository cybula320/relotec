# Relotec - Opis Projektu dla Prompt Engineering

## Przegląd Projektu

**Relotec** to aplikacja webowa typu CRM/ERP stworzona w technologii Laravel 12 z interfejsem administracyjnym Filament 4. System służy do zarządzania ofertami handlowymi, zamówieniami, firmami klientów oraz handlowcami.

### Technologie
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Filament 4 (admin panel), Vite, TailwindCSS 4
- **Baza danych**: MySQL/SQLite
- **Dodatkowe pakiety**:
  - `filament/filament` - nowoczesny panel administracyjny
  - `spatie/laravel-activitylog` - logowanie aktywności użytkowników
  - `laravel/sanctum` - autentykacja API

## Architektura Aplikacji

### Struktura Katalogów
```
/app
  /Filament          # Zasoby Filament (Resources, Widgets, Pages)
  /Models            # Modele Eloquent
  /Http              # Kontrolery i Middleware
  /Helpers           # Klasy pomocnicze
  /Mail              # Klasy mailowe
  /Listeners         # Event listeners
  /Providers         # Service providers
/database
  /migrations        # Migracje bazy danych
  /seeders           # Seedery
/resources           # Widoki, assets
/routes              # Pliki routingu (web.php, api.php)
/tests               # Testy PHPUnit
```

## Główne Moduły Biznesowe

### 1. Firmy (Firmas)
**Model**: `App\Models\Firma`
**Tabela**: `firmy`

Reprezentuje firmy klientów z podstawowymi danymi:
- Dane podstawowe: nazwa, NIP, email, telefon
- Dane adresowe: adres, miasto
- Pola pomocnicze: uwagi
- Relacja z metodą płatności (`payment_method_id`)
- Soft deletes (możliwość przywracania usuniętych rekordów)

**Relacje**:
- `hasMany(Handlowiec)` - firma ma wielu handlowców
- `belongsTo(PaymentMethod)` - firma ma domyślną metodę płatności

**Funkcjonalności**:
- Activity logging (śledzenie zmian)
- CRUD przez Filament Resource: `FirmaResource`

### 2. Handlowcy (Handlowiecs)
**Model**: `App\Models\Handlowiec`
**Tabela**: `handlowcy`

Osoby kontaktowe w firmach klientów:
- Dane osobowe: imię, nazwisko, email, telefon
- Powiązanie z firmą (`firma_id`)
- Soft deletes

**Relacje**:
- `belongsTo(Firma)` - handlowiec należy do firmy
- Accessor: `getFullNameAttribute()` - zwraca pełne imię i nazwisko

**Funkcjonalności**:
- Activity logging
- CRUD przez Filament Resource: `HandlowiecResource`

### 3. Oferty (Ofertas)
**Model**: `App\Models\Oferta`
**Tabela**: `oferty`

Główny moduł systemu - zarządzanie ofertami handlowymi:

**Pola**:
- `numer` - automatycznie generowany numer oferty (format: OF-YYYY-XXXX)
- `firma_id` - powiązanie z firmą
- `handlowiec_id` - osoba kontaktowa
- `user_id` - opiekun oferty (użytkownik systemu)
- `waluta` - waluta oferty
- `payment_terms_days` - termin płatności (dni)
- `due_date` - data płatności
- `uwagi` - dodatkowe uwagi
- `total_net` - suma netto
- `total_gross` - suma brutto
- `status` - status oferty
- `converted_order_id` - ID zamówienia po konwersji
- `payment_method_id` - metoda płatności

**Relacje**:
- `belongsTo(Firma)` - oferta dla firmy
- `belongsTo(Handlowiec)` - osoba kontaktowa
- `belongsTo(User)` - opiekun oferty
- `belongsTo(PaymentMethod)` - metoda płatności
- `hasMany(OfertaPozycja)` - pozycje oferty

**Kluczowe Metody**:
- `generateNumber()` - generuje unikalny numer oferty
- `recalcTotals()` / `recalculateTotals()` - przelicza sumy z pozycji
- Event hook `creating()` - automatyczne generowanie numeru przy tworzeniu

**Funkcjonalności**:
- Activity logging
- CRUD przez Filament Resource: `OfertaResource`
- Relation Manager: `OfertaPozycjeRelationManager`
- Widgets: `OfertyPodsumowanieWidget`, `OfertyWTokuWidget`

### 4. Pozycje Ofert (OfertaPozycjas)
**Model**: `App\Models\OfertaPozycja`
**Tabela**: `oferta_pozycjas`

Pojedyncze pozycje w ofercie:
- Opis produktu/usługi
- Ilość, cena jednostkowa
- Wartości netto i brutto (`total_net`, `total_gross`)
- Powiązanie z ofertą (`oferta_id`)

**Relacje**:
- `belongsTo(Oferta)` - pozycja należy do oferty

### 5. Zamówienia (Zamowienies)
**Model**: `App\Models\Zamowienie`
**Tabela**: `zamowienia`

Zamówienia utworzone z ofert:

**Pola** (podobne do Oferty):
- `numer` - format: ZAM-YYYY-XXXX
- `firma_id`, `handlowiec_id`
- `waluta`, `payment_terms_days`, `due_date`, `uwagi`
- `total_net`, `total_gross`, `status`

**Relacje**:
- `belongsTo(Firma)`
- `belongsTo(Handlowiec)`
- `hasMany(ZamowieniePozycja)` - pozycje zamówienia

**Metody**:
- `generateNumber()` - generuje numer zamówienia
- `recalcTotals()` - przelicza sumy

**Funkcjonalności**:
- Activity logging
- CRUD przez Filament Resource: `ZamowienieResource`

### 6. Pozycje Zamówień (ZamowieniePozycjas)
**Model**: `App\Models\ZamowieniePozycja`
**Tabela**: `zamowienie_pozycjas`

Analogiczne do pozycji ofert, ale dla zamówień.

### 7. Metody Płatności (PaymentMethods)
**Model**: `App\Models\PaymentMethod`

Słownik metod płatności używanych w systemie:
- Przykłady: przelew, gotówka, karta, raty, itp.

**Relacje**:
- Używane przez Firmy i Oferty jako domyślna metoda płatności

**Funkcjonalności**:
- CRUD przez Filament Resource: `PaymentMethodResource`

### 8. Użytkownicy (Users)
**Model**: `App\Models\User`
**Tabela**: `users`

Użytkownicy systemu z rolami:
- Dodatkowe pola: `position` (stanowisko)
- Role użytkowników (prawdopodobnie: admin, viewer, itp.)

**Funkcjonalności**:
- CRUD przez Filament Resource: `UserResource`
- Autentykacja przez Filament Auth
- Przypisywanie jako opiekunowie ofert

### 9. Activity Logs
**Model**: `App\Models\ActivityLog`
**Tabela**: `activity_log`

Logi aktywności użytkowników:
- Śledzenie zmian w modelach (Firma, Oferta, Zamówienie, Handlowiec)
- Rejestrowanie logowań użytkowników

**Funkcjonalności**:
- Integracja z pakietem Spatie Activity Log
- Przeglądanie przez Filament Resource: `ActivityLogResource`
- Listener: `LogSuccessfulLogin` - logowanie udanych logowań
- Mail: `LogowanieNotification` - powiadomienia o logowaniu

## Helpers i Utilities

### OfferNumberHelper
**Plik**: `app/Helpers/OfferNumberHelper.php`

Klasa pomocnicza do generowania numerów ofert i zamówień.

### helpers.php
**Plik**: `app/Helpers/helpers.php`

Globalne funkcje pomocnicze (autoloadowane przez composer).

## Filament Panel Configuration

**Provider**: `App\Providers\Filament\PanelPanelProvider`

Konfiguracja panelu administracyjnego Filament:
- Customowe logo
- Nawigacja
- Widgety na dashboard
- Ustawienia panelu

## Widgety Dashboard

### CustomInfoWidget
Informacje ogólne na dashboardzie.

### OfertyPodsumowanieWidget
Podsumowanie ofert (statystyki, wykresy).

### OfertyWTokuWidget
Lista ofert w toku.

## Baza Danych

### Kluczowe Migracje
1. **Użytkownicy**: podstawowa tabela + dodatkowe pola (stanowisko)
2. **Firmy**: tabela firm z soft deletes
3. **Handlowcy**: osoby kontaktowe z soft deletes
4. **Oferty**: główna tabela ofert
5. **Pozycje Ofert**: szczegóły ofert
6. **Zamówienia**: tabela zamówień
7. **Pozycje Zamówień**: szczegóły zamówień
8. **Activity Log**: śledzenie zmian (3 migracje Spatie)
9. **Settings**: ustawienia aplikacji
10. **Payment Methods**: metody płatności

### Kluczowe Relacje
```
Firma (1) ---> (N) Handlowiec
Firma (1) ---> (N) Oferta
Firma (1) ---> (N) Zamowienie
Handlowiec (1) ---> (N) Oferta
Handlowiec (1) ---> (N) Zamowienie
User (1) ---> (N) Oferta (jako opiekun)
Oferta (1) ---> (N) OfertaPozycja
Zamowienie (1) ---> (N) ZamowieniePozycja
PaymentMethod (1) ---> (N) Firma
PaymentMethod (1) ---> (N) Oferta
```

## Workflow Biznesowy

### Proces Ofertowy
1. **Dodanie Firmy** - utworzenie karty klienta z danymi podstawowymi
2. **Dodanie Handlowca** - osoba kontaktowa w firmie
3. **Utworzenie Oferty** - wybór firmy, handlowca, opiekuna
4. **Dodanie Pozycji** - produkty/usługi w ofercie
5. **Automatyczne Przeliczanie** - suma netto/brutto
6. **Zarządzanie Statusem** - śledzenie etapu oferty
7. **Konwersja na Zamówienie** - opcjonalnie po zaakceptowaniu

### Numeracja
- **Oferty**: `OF-{ROK}-{NUMER}` np. OF-2025-0001
- **Zamówienia**: `ZAM-{ROK}-{NUMER}` np. ZAM-2025-0001

### Statusy
System obsługuje różne statusy ofert i zamówień (szczegóły w kodzie Resources).

## Bezpieczeństwo i Audyt

### Activity Logging
Wszystkie kluczowe modele logują zmiany:
- Kto wprowadził zmianę
- Kiedy
- Jakie pola zostały zmienione
- Stare i nowe wartości

### Logowanie Użytkowników
- Listener `LogSuccessfulLogin` rejestruje udane logowania
- Email notification `LogowanieNotification` wysyłany przy logowaniu

## Development & Deployment

### Skrypty Composer
- `composer setup` - instalacja i konfiguracja projektu
- `composer dev` - uruchomienie środowiska deweloperskiego (serwer, queue, pail, vite)
- `composer test` - testy PHPUnit

### Skrypty NPM
- `npm run dev` - Vite dev server
- `npm run build` - build produkcyjny
- `npm run release` - standard-version (semantic versioning)

### Wersjonowanie
Projekt używa **standard-version** do automatycznego wersjonowania:
- Obecna wersja: **0.0.13**
- Changelog automatycznie generowany
- Konwencja commit messages: feat, fix, refactor, docs

### CI/CD
Projekt ma skonfigurowany deployment (szczegóły w historii commitów).

## Rozszerzalność

### Dodawanie Nowych Modułów
1. Utworzenie modelu Eloquent w `app/Models`
2. Utworzenie migracji w `database/migrations`
3. Utworzenie Filament Resource w `app/Filament/Resources`
4. Opcjonalnie: dodanie activity logging
5. Opcjonalnie: utworzenie relation managers

### Customizacja Filament
- Resources w `app/Filament/Resources/{Model}/*Resource.php`
- Formularze w `Schemas/*Form.php`
- Tabele w `Tables/*Table.php`
- Widgety w `app/Filament/Widgets`

## Najlepsze Praktyki w Projekcie

1. **Soft Deletes**: Firma i Handlowiec używają soft deletes
2. **Activity Logging**: Wszystkie kluczowe operacje są logowane
3. **Automatyczna Numeracja**: Oferty i zamówienia mają auto-generowane numery
4. **Przeliczanie Sum**: Metody `recalcTotals()` utrzymują spójność danych
5. **Relacje Eloquent**: Używanie relacji zamiast join'ów w zapytaniach
6. **Fillable Fields**: Określenie pól dostępnych do mass assignment
7. **Event Hooks**: Używanie `booted()` do automatyzacji (np. generowanie numerów)

## Struktura Filament Resources

Typowy Filament Resource składa się z:
- **{Model}Resource.php** - główna klasa resource
- **Schemas/{Model}Form.php** - definicja formularza
- **Tables/{Model}sTable.php** - definicja tabeli
- **Pages/List{Model}s.php** - strona listy
- **Pages/Create{Model}.php** - strona tworzenia
- **Pages/Edit{Model}.php** - strona edycji
- **RelationManagers/** - zarządzanie relacjami

## Kolejne Kroki Rozwoju

Na podstawie historii commitów, typowe obszary rozwoju:
1. Dodawanie nowych widgetów analitycznych
2. Rozbudowa filtrów i sortowań
3. Dodawanie nowych pól do istniejących modeli
4. Integracje z systemami zewnętrznymi
5. Rozbudowa powiadomień email
6. Eksport/import danych
7. Raporty i wydruki (PDF)

## Uwagi dla Prompt Engineering

### Styl Kodu
- Polski naming dla zmiennych biznesowych (oferta, firma, handlowiec)
- Angielski dla zmiennych technicznych
- Laravel conventions (PSR-12)
- Filament conventions dla Resources

### Konwencje Nazewnicze
- Tabele: liczba mnoga po polsku (firmy, handlowcy, oferty, zamowienia)
- Modele: liczba pojedyncza (Firma, Handlowiec, Oferta, Zamowienie)
- Kontrolery: standardowe Laravel naming
- Resources: {Model}Resource

### Typowe Zadania
- Dodawanie pól do formularzy
- Tworzenie nowych relacji
- Dodawanie filtrów do tabel
- Tworzenie nowych widgetów
- Rozbudowa logiki biznesowej w modelach
- Dodawanie validacji
- Tworzenie nowych Resources

### Stack Technologiczny do Uwzględnienia
- Laravel 12 features
- Filament 4 components
- TailwindCSS 4 dla stylowania
- Vite dla asset bundling
- PHPUnit dla testów
- Spatie packages dla funkcjonalności dodatkowych

---

**Data utworzenia**: 2025-11-18
**Wersja aplikacji**: 0.0.13
**Autor dokumentacji**: AI Assistant (GitHub Copilot)

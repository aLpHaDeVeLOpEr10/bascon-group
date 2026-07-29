<?php

/*
|--------------------------------------------------------------------------
| Money formatting
|--------------------------------------------------------------------------
|
| Every figure in this application is Pakistani rupees, but the legacy schema
| stores them as bare numbers and the views printed them that way — "644000"
| reads as a quantity, not a price, and is unscannable at six or seven digits.
|
| This is the single definition on the PHP side. It is reachable three ways,
| all of which come through here:
|
|   money($value)      in views, controllers and services
|   @money($value)     Blade directive (registered in AppServiceProvider)
|   window.money()     the client-side twin in resources/js/ui.js
|
| The JavaScript copy exists because roughly half of these totals are painted
| by AJAX callbacks that PHP never sees. The two must produce identical output;
| if you change the format here, change it there too.
|
*/

if (! function_exists('money')) {
    /**
     * Formats an amount as Pakistani rupees: money(644000) === 'PKR 644,000'.
     *
     * Values reach this function from the legacy schema as ints, floats,
     * numeric strings, and occasionally null or ''. Anything non-numeric
     * formats as zero rather than throwing: these figures are rendered in
     * views that have no error path, where a suspicious-looking total is a bug
     * report but a fatal is a broken page.
     */
    function money(mixed $value): string
    {
        $number = is_numeric($value) ? (float) $value : 0.0;

        // Whole rupees unless the figure genuinely carries paisa.
        $decimals = fmod($number, 1.0) === 0.0 ? 0 : 2;

        return 'PKR ' . number_format($number, $decimals);
    }
}

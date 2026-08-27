<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    | When true, the package auto-registers a reportable() callback on the
    | app's exception handler so every unhandled exception is captured
    | without the host app touching bootstrap/app.php.
    */
    'enabled' => env('ERROR_MONITOR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | AI Analysis
    |--------------------------------------------------------------------------
    | When true, a new/repeat-eligible error is sent to the bound
    | ErrorAnalyzer before the notification email is built.
    */
    'ai_analysis_enabled' => env('ERROR_MONITOR_AI_ANALYSIS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    | The Eloquent model used to store captured errors. Override this in your
    | app's published config to point to your own model, which should extend
    | \HeritageApps\ErrorMonitor\Models\SystemError and add whichever
    | landlord-connection trait your app uses (e.g. UsesLandlordConnection).
    | Leave pointing at the package model if your app's default database
    | connection already is the landlord connection.
    */
    'models' => [
        'system_error' => \HeritageApps\ErrorMonitor\Models\SystemError::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Recipients & Throttling
    |--------------------------------------------------------------------------
    | Used by the default ConfigErrorMonitorSettings implementation. Bind your
    | own \HeritageApps\ErrorMonitor\Contracts\ErrorMonitorSettings in your
    | app's service provider if recipients/threshold need to be editable at
    | runtime (e.g. from a landlord settings screen).
    */
    'recipients' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ERROR_MONITOR_RECIPIENTS', ''))
    ))),

    'repeat_threshold_minutes' => (int) env('ERROR_MONITOR_REPEAT_THRESHOLD_MINUTES', 60),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    | Used by the default OpenAiFaultAnalyzer implementation. Bind your own
    | \HeritageApps\ErrorMonitor\Contracts\ErrorAnalyzer if your app needs to
    | route the call through its own cost-tracked AI client.
    */
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('ERROR_MONITOR_OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('ERROR_MONITOR_OPENAI_TIMEOUT', 30),
    ],

];

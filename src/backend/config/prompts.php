<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Prompts Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines the paths to all AI prompt templates used throughout
    | the application. Prompts are stored as markdown files in resources/prompts.
    |
    | Placeholder syntax: {{variable_name}} will be replaced with actual values.
    |
    */

    'lead_analysis' => [
        'path' => resource_path('prompts/lead-analysis.md'),
        'max_tokens' => 2048,
        'description' => 'Analyzes lead data to provide insights, recommendations, and risks.',
    ],

    'lead_history_analysis' => [
        'path' => resource_path('prompts/lead-history-analysis.md'),
        'max_tokens' => 4096,
        'description' => 'Analyzes complete HubSpot engagement history for pattern detection and predictions.',
    ],

];

<?php

return [
    'endpoint' => rtrim((string) env('OPENAI_ENDPOINT', env('OPENAI_BASE_URL', 'https://api.openai.com/v1')), '/'),
    'base_url' => rtrim((string) env('OPENAI_BASE_URL', env('OPENAI_ENDPOINT', 'https://api.openai.com/v1')), '/'),
    'key' => env('OPENAI_KEY', env('OPENAI_API_KEY', '')),
    'api_key' => env('OPENAI_API_KEY', env('OPENAI_KEY', '')),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
];

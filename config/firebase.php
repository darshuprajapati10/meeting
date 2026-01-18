<?php

return [
    'credentials' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase/service-account.json')),
    'credentials_json' => env('FIREBASE_CREDENTIALS_JSON'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
];



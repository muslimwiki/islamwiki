<?php

use App\Http\Controllers\PageController;

return [
    'GET /' => [PageController::class, 'home'],
    'GET /pages/{slug}' => [PageController::class, 'show'],
];

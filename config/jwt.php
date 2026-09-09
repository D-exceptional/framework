<?php

declare(strict_types=1);

return [
    
    'secret'    => env('JWT_SECRET', ''),

    'expire_at' => (int) env('JWT_EXPIRE_AT', 7200),
    
];
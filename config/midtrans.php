<?php

return [
  'serverKey' => env('MIDTRANS_SERVER_KEY','SB-Mid-server-QzfVTtanOdS-2mk5NQm7Wirc'),
  'clientKey' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-0oxCuwYrCcfcfhM_'),
  'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),
  'isSanitized' => env('MIDTRANS_IS_SANITIZED', true),
  'is3ds' => env('MIDTRANS_IS_3DS', true),
];

<?php

return [
    'carriers'=>[
      'ups'=>[
        'enabled'=>true,
        'sandbox'=>env('UPS_SANDBOX_MODE', true),
        'api_credentials'=>[
          'client_id'=>env('UPS_CLIENT_ID'),
          'client_secret'=>env('UPS_CLIENT_SECRET'),
        ],
      ],
      'usps'=>[
        'enabled'=>false,
        'api_credentials'=>[
          //
        ],
      ],
      'fedex'=>[
        'enabled'=>false,
        'api_credentials'=>[
          //
        ],
      ],
    ],
];

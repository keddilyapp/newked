<?php

use App\Models\Admin;

return [
    'app_name' => 'Nazmart',
    'super_admin_role_id' => 1,
    'admin_model' => Admin::class,
    'admin_table' => 'admins',
    'multi_tenant' => true,
    'author' => 'xgenious',
    'product_key' => '5debb671064213be9c0db593d094da0df3c4e2dd',
    'php_version' => '8.1',
    'extensions' => ['BCMath', 'Ctype', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'cURL', 'fileinfo'],
    'website' => 'https://xgenious.com',
    'email' => 'support@xgenious.com',
    'env_example_path' => public_path('env-sample.txt'),
    'broadcast_driver' => 'log',
    'cache_driver' => 'array',
    'queue_connection' => 'database',
    'mail_port' => '587',
    'mail_encryption' => 'tls',
    'model_has_roles' => true,
    'bundle_pack' => true,
    'bundle_pack_key' => '6adc506fc0e376bfd2d148663cda94c0cfa8a215',
];

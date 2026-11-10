<?php

return [
    'path' => env('BEASISWA_BACKUP_PATH', storage_path('app/private-backups')),
    'retention_days' => (int) env('BEASISWA_BACKUP_RETENTION_DAYS', 7),
    'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
];

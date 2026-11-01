<?php

return [
    'offsite_disk' => env('GROWPOS_BACKUP_DISK'),
    'prefix' => 'growpos-backups',
    'retention_days' => (int) env('GROWPOS_BACKUP_RETENTION_DAYS', 14),
];

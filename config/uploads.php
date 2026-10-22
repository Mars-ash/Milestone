<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Private Storage Disk
    |--------------------------------------------------------------------------
    |
    | Upload lampiran disimpan pada storage disk privat terisolasi tanpa URL publik.
    |
    */
    'disk' => env('UPLOADS_DISK', 'private'),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Upload Foto (JPG/JPEG, PNG, WebP)
    |--------------------------------------------------------------------------
    */
    'photos' => [
        'max_size_kb' => (int) env('UPLOAD_PHOTO_MAX_KB', 5120), // 5 MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Upload Dokumen (OP03 Disepakati)
    |--------------------------------------------------------------------------
    |
    | Batas ukuran 10 MB per dokumen, format PDF, Office, PPT, TXT, CSV, ZIP.
    |
    */
    'documents' => [
        'max_size_kb' => (int) env('UPLOAD_DOCUMENT_MAX_KB', 10240), // 10 MB
        'allowed_extensions' => [
            'pdf',
            'doc', 'docx',
            'xls', 'xlsx',
            'ppt', 'pptx',
            'txt', 'csv',
            'zip',
        ],
        'allowed_mimes' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'application/zip',
            'application/x-zip-compressed',
        ],
    ],
];

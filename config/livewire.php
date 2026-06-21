<?php
// FILE: config/livewire.php

return [
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:12288'],
        'directory' => 'livewire-tmp',
        'middleware' => ['web'],
        'preview_mimes' => [
            'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],
];

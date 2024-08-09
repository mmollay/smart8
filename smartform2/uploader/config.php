<?php
// config.php

// Stellen Sie sicher, dass diese Datei nicht direkt aufgerufen werden kann
if (!defined('SECURE_ACCESS')) {
    die('Direkter Zugriff nicht erlaubt');
}

return [
    'MAX_FILE_SIZE' => 10 * 1024 * 1024, // 10 MB
    'MAX_FOLDER_SIZE' => 10000 * 1024 * 1024,
    'ALLOWED_FORMATS' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'wav'],
    'MAX_FILE_COUNT' => 10,
    'UPLOAD_DIR' => '../uploads/',
    'LANGUAGE' => 'de',
    'dropZoneId' => 'drop-zone',
    'fileInputId' => 'file-input',
    'fileListId' => 'file-list',
    'deleteAllButtonId' => 'delete-all',
    'progressContainerId' => 'progress-container',
    'progressBarId' => 'progress',
];

// // Upload-Konfigurationen
// return [
//     // Maximale Dateigröße in Bytes (hier: 5 MB)
//     'MAX_FILE_SIZE' => 10000 * 1024 * 1024,

//     // Maximale Gesamtgröße des Upload-Ordners in Bytes (hier: 100 MB)
//     'MAX_FOLDER_SIZE' => 10000 * 1024 * 1024,

//     // Erlaubte Dateiformate
//     'ALLOWED_FORMATS' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'wav'],

//     // Upload-Verzeichnis
//     'UPLOAD_DIR' => 'uploads/',

//     // Maximale Anzahl von Dateien, die hochgeladen werden können
//     'MAX_FILE_COUNT' => 50,

//     // Zeitlimit für temporäre Dateien in Sekunden (z.B. 24 Stunden)
//     'TEMP_FILE_LIFETIME' => 86400,

//     // Minimale Dateigröße in Bytes (z.B. 1 KB)
//     'MIN_FILE_SIZE' => 1024,

//     // Erlaubte MIME-Typen
//     'ALLOWED_MIME_TYPES' => [
//         'application/pdf',
//         'application/msword',
//         'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
//         'application/vnd.ms-excel',
//         'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
//         'image/jpeg',
//         'image/png',
//         'image/gif'
//     ],

//     // Maximale Bildauflösung (für Bilder)
//     'MAX_IMAGE_RESOLUTION' => 1920 * 1080,

//     // Ob Dateinamen beim Upload geändert werden sollen (z.B. für Eindeutigkeit)
//     'RENAME_FILES' => true,

//     // Präfix für umbenannte Dateien
//     'FILE_NAME_PREFIX' => 'upload_',

//     // Ob eine Vorschau generiert werden soll (für Bilder)
//     'GENERATE_THUMBNAILS' => true,

//     // Thumbnail-Größe
//     'THUMBNAIL_SIZE' => 200,

//     // Logging-Level (z.B. ERROR, WARNING, INFO, DEBUG)
//     'LOG_LEVEL' => 'ERROR',

//     // Pfad zur Log-Datei
//     'LOG_FILE' => 'logs/upload.log',

//     'LANGUAGE' => 'en' // Setzen Sie hier die Standardsprache
// ];
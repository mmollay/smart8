<?php
function getEditorConfig($userId, $update_id)
{


    return [
        'minHeight' => 300,
        'maxHeight' => 600,
        'placeholder' => 'Geben Sie hier Ihre Nachricht ein...',
        'image' => [
            'upload' => [
                'types' => ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp', 'tiff'],
                'maxFileSize' => 5 * 1024 * 1024,
                'path' => "/users/$userId/newsletters/$update_id/"
            ]
        ],
        'toolbar' => [
            'items' => [
                'heading',
                '|',
                'bold',
                'italic',
                'underline',
                'strikethrough',
                '|',
                'fontSize',
                'fontColor',
                'backgroundColor',
                '|',
                'alignment',
                'bulletedList',
                'numberedList',
                '|',
                'indent',
                'outdent',
                '|',
                'link',
                'imageUpload',
                'blockQuote',
                'insertTable',
                '|',
                'undo',
                'redo'
            ]
        ]
    ];
}
<?php
require_once __DIR__ . '/../FormGenerator.php';

function createFormGenerator($formId, $action)
{
    $formGenerator = new FormGenerator();
    $formGenerator->setFormData([
        'id' => $formId,
        'action' => $action,
        'class' => 'ui form',
        'method' => 'POST',
        'responseType' => 'normal',
        'success' => "showToast('Formular erfolgreich gesendet!', 'success');"
    ]);

    $fields = [
        [
            'type' => 'input',
            'name' => $formId . '_name',
            'label' => 'Name',
            'placeholder' => 'Ihr Name',
            'required' => true
        ],
        [
            'type' => 'input',
            'name' => $formId . '_email',
            'label' => 'E-Mail',
            'placeholder' => 'Ihre E-Mail-Adresse',
            'required' => true,
            'email' => true
        ],
        [
            'type' => 'ckeditor5',
            'name' => $formId . '_message',
            'id' => $formId . '_ckeditor',  // Eindeutige ID für CKEditor
            'label' => 'Nachricht',
            'required' => true,
            'config' => [
                'minHeight' => 200,
                'maxHeight' => 400,
                'placeholder' => 'Ihre Nachricht hier...'
            ]
        ],
        [
            'type' => 'button',
            'name' => 'submit',
            'value' => 'Absenden',
            'class' => 'ui primary button'
        ]
    ];

    foreach ($fields as $field) {
        $formGenerator->addField($field);
    }

    return $formGenerator;
}

$formGenerator1 = createFormGenerator('form1', 'process_form.php');
$formGenerator2 = createFormGenerator('form2', 'process_form.php');
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zwei Formulare</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <?= $formGenerator1->generateCSS() ?>
</head>

<body>
    <div class="ui container" style="padding-top: 50px;">
        <h1 class="ui header">Zwei unabhängige Formulare</h1>
        <div class="ui two column stackable grid">
            <div class="column">
                <h2 class="ui header">Formular 1</h2>
                <?= $formGenerator1->generateForm() ?>
            </div>
            <div class="column">
                <h2 class="ui header">Formular 2</h2>
                <?= $formGenerator2->generateForm() ?>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/29.0.0/decoupled-document/ckeditor.js"></script>
    <?= $formGenerator1->generateJS() ?>
    <?= $formGenerator2->generateJS() ?>
</body>

</html>
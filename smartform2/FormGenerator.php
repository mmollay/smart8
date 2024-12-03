<?php
//Column anlegen aber er liest noch nich die Daten ein 
error_reporting(E_ALL);
ini_set('display_errors', 1);
class FormGenerator
{
    private $customBasePath = null;
    private static $toastContainerAdded = false;
    private static $ckeditorConfigs = [];
    private $hasCKEditor = false;

    private $formData = [];

    private $fields = [];
    private $requiredFields = [];
    private $hasFileUploader = false;
    private $fileUploaderConfig = [];

    private $translations;
    private $language;

    private $config;

    private $currentLayout = null;
    private $layoutFieldCount = 0;

    private $values = [];

    private $tabs = [];
    private $currentTab = null;

    private $tabFields = [];

    private $fieldGroups = [];


    public function addButtonElement($buttons, $options = [])
    {
        $defaultOptions = [
            'layout' => 'default', // 'default', 'grouped', 'spaced', 'inline', 'vertical'
            'alignment' => 'left', // 'left', 'center', 'right'
            'spacing' => '10px',   // Für 'spaced' Layout
            'size' => '',
            'color' => '',
            'basic' => false,
            'icon' => false,
            'labeled' => false,
            'fluid' => false,
            'compact' => false,
            'toggle' => false,
            'positive' => false,
            'negative' => false,
            'circular' => false,
        ];

        $options = array_merge($defaultOptions, $options);

        // Wenn ein einzelner Button übergeben wird, wandeln wir ihn in ein Array um
        if (!isset($buttons[0]) || !is_array($buttons[0])) {
            $buttons = [$buttons];
        }

        $this->fields[] = [
            'type' => 'buttonElement',
            'buttons' => $buttons,
            'options' => $options
        ];
    }

    private function generateButtonElement($element)
    {
        $buttons = $element['buttons'];
        $options = $element['options'];

        $containerClass = 'field';
        if ($options['layout'] === 'grouped')
            $containerClass .= ' ui buttons';
        if ($options['layout'] === 'vertical')
            $containerClass .= ' vertical';
        if ($options['fluid'])
            $containerClass .= ' fluid';

        $html = "<div class='$containerClass' style='text-align: {$options['alignment']};'>";

        foreach ($buttons as $button) {
            $type = $button['type'] ?? 'button';
            $value = $button['value'] ?? '';
            $icon = $button['icon'] ?? '';
            $class = $button['class'] ?? 'ui button';
            $onclick = $button['onclick'] ?? '';

            // Füge globale Button-Optionen hinzu
            if ($options['basic'])
                $class .= ' basic';
            if ($options['size'])
                $class .= ' ' . $options['size'];
            if ($options['color'])
                $class .= ' ' . $options['color'];
            if ($options['compact'])
                $class .= ' compact';
            if ($options['toggle'])
                $class .= ' toggle';
            if ($options['positive'])
                $class .= ' positive';
            if ($options['negative'])
                $class .= ' negative';
            if ($options['circular'])
                $class .= ' circular';

            $buttonAttributes = '';
            if ($type === 'submit') {
                $buttonAttributes .= ' type="submit"';
            } else {
                $buttonAttributes .= ' type="button"';
            }

            // Füge onclick-Attribut hinzu
            if ($onclick) {
                $buttonAttributes .= " onclick=\"$onclick\"";
            } elseif ($type === 'close') {
                // Automatisches Schließen für 'close'-Buttons ohne explizites onclick
                $buttonAttributes .= " onclick=\"$('.ui.modal').modal('hide');\"";
            }

            $html .= "<button$buttonAttributes class=\"$class\">";

            if ($options['icon'] || $icon) {
                $iconClass = $icon ?: $options['icon'];
                if ($options['labeled']) {
                    $html .= "<i class='$iconClass icon'></i>";
                    $html .= $value;
                } else {
                    $html .= "<i class='$iconClass icon'></i>" . ($value ? $value : '');
                }
            } else {
                $html .= $value;
            }

            $html .= "</button>";

            if ($options['layout'] === 'spaced' && !end($buttons) === $button) {
                $html .= "<span style='margin-right: {$options['spacing']};'></span>";
            }
        }

        $html .= "</div>";

        return $html;
    }

    private function generateButton($button)
    {
        $type = $button['type'] ?? 'button';
        $name = $button['name'] ?? 'button_' . uniqid();
        $value = $button['value'] ?? '';
        $class = $button['class'] ?? 'ui button';
        $icon = $button['icon'] ?? '';
        $onclick = $button['onclick'] ?? '';

        $attributes = "";
        if (!empty($button['confirmation'])) {
            $attributes .= " data-confirm=\"" . htmlspecialchars($button['confirmation'], ENT_QUOTES, 'UTF-8') . "\"";
        }
        if ($onclick) {
            $attributes .= " onclick=\"" . htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8') . "\"";
        }

        if (!empty($button['popup'])) {
            if (is_array($button['popup'])) {
                $attributes .= " data-tooltip='" . htmlspecialchars($button['popup']['content'] ?? '', ENT_QUOTES, 'UTF-8') . "'";
                $attributes .= " data-position='" . htmlspecialchars($button['popup']['position'] ?? 'top center', ENT_QUOTES, 'UTF-8') . "'";
                $attributes .= " data-variation='" . htmlspecialchars($button['popup']['variation'] ?? '', ENT_QUOTES, 'UTF-8') . "'";
                $attributes .= " data-inverted='" . ($button['popup']['inverted'] ?? false) . "'";
            } else {
                $attributes .= " data-tooltip=\"" . htmlspecialchars($button['popup'], ENT_QUOTES, 'UTF-8') . "\"";
            }
        }

        if (!empty($button['disabled'])) {
            $attributes .= " disabled";
        }

        $buttonContent = '';
        if ($icon) {
            $buttonContent .= "<i class='{$icon} icon'></i>";
        }
        $buttonContent .= $value;

        return "<button type='{$type}' name='{$name}' class='{$class}'{$attributes}>{$buttonContent}</button>\n";
    }

    public function setBasePath($path)
    {
        $this->customBasePath = rtrim($path, '/');
    }

    private function getBasePath()
    {
        // Wenn ein benutzerdefinierter Pfad gesetzt wurde, verwende diesen
        if ($this->customBasePath !== null) {
            return $this->customBasePath;
        }

        // Ansonsten verwende die Standard-Logik
        $classDir = dirname((new ReflectionClass($this))->getFileName());
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $classDir);
        $relativePath = '/' . trim($relativePath, '/');
        return $relativePath;
    }


    public function loadValuesFromDatabase($db, $sql, $params = [], $mappings = [], $additionalValues = [])
    {
        $stmt = $db->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $this->values = $result->fetch_assoc();

        if ($this->values) {
            foreach ($mappings as $formField => $dbField) {
                if (isset($this->values[$dbField])) {
                    $this->values[$formField] = $this->values[$dbField];
                }
            }

            // Füge zusätzliche Werte hinzu
            $this->values = array_merge($this->values, $additionalValues);

            $this->setFieldValues($this->values);
        }
        $stmt->close();
    }

    public function setFieldValues($values)
    {
        $this->values = array_merge($this->values, $values);

        $this->updateFieldValues($this->fields);
        foreach ($this->tabFields as &$tabFields) {
            $this->updateFieldValues($tabFields);
        }
    }

    private function updateFieldValues(&$fields)
    {
        foreach ($fields as &$field) {
            if ($field['type'] === 'group') {
                $this->updateFieldValues($field['fields']);
            } elseif (isset($field['name']) && isset($this->values[$field['name']])) {
                $field['value'] = $this->values[$field['name']];
            }

            if ($field['type'] === 'grid') {
                foreach ($field['fields'] as &$gridField) {
                    if (isset($gridField['name']) && isset($this->values[$gridField['name']])) {
                        $gridField['value'] = $this->values[$gridField['name']];
                    }
                }
            }
        }
    }


    public function __construct()
    {
        $this->language = 'en'; // Standardsprache auf Englisch setzen
        $this->loadConfig();
        $this->loadTranslations();
    }

    private function loadConfig()
    {
        if (!defined('SECURE_ACCESS'))
            define('SECURE_ACCESS', true);

        $this->config = require_once __DIR__ . '/uploader/config.php';
    }

    private function loadTranslations()
    {
        $translationFile = __DIR__ . '/uploader/translations.php';
        if (file_exists($translationFile)) {
            include $translationFile;
            $this->translations = $translations[$this->language] ?? $translations['en']; // Fallback auf Englisch, wenn die gewählte Sprache nicht verfügbar ist
        } else {
            $this->translations = []; // Leeres Array, falls die Datei nicht existiert
        }
    }

    public function setFormData($data)
    {
        $this->formData = $data;
        if (!isset($this->formData['responseType'])) {
            $this->formData['responseType'] = 'json';
        }
        if (!isset($this->formData['success'])) {
            // $this->formData['success'] = "showToast('Formular erfolgreich gesendet!', 'success');";
        }
    }

    public function addTab($tabId, $tabLabel)
    {
        $this->tabs[$tabId] = $tabLabel;
    }

    public function setCurrentTab($tabId)
    {
        $this->currentTab = $tabId;
    }

    private function handleTabField($field)
    {
        foreach ($field['tabs'] as $tabId => $tabLabel) {
            $this->addTab($tabId, $tabLabel);
        }
        if (isset($field['active'])) {
            $this->setCurrentTab($field['active']);
        }
    }

    public function addField($field)
    {
        if ($field['type'] === 'tab') {
            $this->handleTabField($field);
            return;
        }

        // If tabs are being used, assign the current tab to the field
        if ($this->currentTab !== null && !isset($field['tab'])) {
            $field['tab'] = $this->currentTab;
        }

        if (!isset($field['name'])) {
            $field['name'] = 'field_' . uniqid();
        }

        if (isset($this->values[$field['name']])) {
            $field['value'] = $this->values[$field['name']];
        }

        if ($field['type'] === 'hidden') {
            $this->fields[] = $field;
            return;
        }

        if ($field['type'] === 'grid') {
            $this->addGridField($field);
            return;
        }

        if ($field['type'] === 'ckeditor5' && !empty($field['config']['image']['upload'])) {
            self::$ckeditorConfigs[$field['name']]['imageUpload'] = $field['config']['image']['upload'];
        }

        if ($field['type'] === 'ckeditor5') {
            $field['id'] = $this->generateUniqueId($field['name']);
            $this->hasCKEditor = true;
            if (!empty($field['config'])) {
                self::$ckeditorConfigs[$field['name']] = $field['config'];
            }
        }

        if ($field['type'] === 'textarea') {
            $field['rows'] = $field['rows'] ?? 3;
            $field['cols'] = $field['cols'] ?? 50;
            $field['minlength'] = $field['minlength'] ?? null;
            $field['maxlength'] = $field['maxlength'] ?? null;
            $field['resize'] = $field['resize'] ?? 'both';
        }

        if ($field['type'] === 'uploader') {
            $this->hasFileUploader = true;
            $this->fileUploaderConfig = array_merge($this->config, $field['config'] ?? []);
            $this->language = $this->fileUploaderConfig['LANGUAGE'] ?? 'en';
            $this->loadTranslations();
            $this->fileUploaderConfig['translations'] = $this->translations;
            $this->fileUploaderConfig['basePath'] = $this->getBasePath() . '/uploader/';
        }

        if (
            !empty($field['required']) || !empty($field['email']) || !empty($field['number']) ||
            isset($field['minLength']) || isset($field['maxLength']) || !empty($field['regex']) ||
            isset($field['minlength']) || isset($field['maxlength'])
        ) {
            $this->requiredFields[$field['name']] = $this->buildValidationRules($field);
        }

        // Handle layout fields
        if ($this->currentLayout !== null) {
            $this->currentLayout['fields'][] = $field;
            $this->layoutFieldCount += $field['width'] ?? 1;

            if ($this->layoutFieldCount >= $this->currentLayout['columns']) {
                $layoutField = $this->generateLayoutHTML($this->currentLayout);
                $this->addFieldToAppropriateArray($layoutField);
                $this->currentLayout = null;
                $this->layoutFieldCount = 0;
            }
        } else {
            $this->addFieldToAppropriateArray($field);
        }
    }
    private function addFieldToAppropriateArray($field)
    {
        if (isset($field['tab']) && isset($this->tabs[$field['tab']])) {
            if (!isset($this->tabFields[$field['tab']])) {
                $this->tabFields[$field['tab']] = [];
            }
            $this->tabFields[$field['tab']][] = $field;
        } else {
            $this->fields[] = $field;
        }
    }

    public function createFieldGroup($groupName, $fields, $options = [])
    {
        $this->fieldGroups[$groupName] = [
            'fields' => $fields,
            'options' => $options
        ];
        return $this;
    }

    public function addFieldGroup($groupName, $fields = null, $options = [], $tabId = null)
    {
        // Wenn $fields null ist, nehmen wir an, dass die Gruppe bereits erstellt wurde
        if ($fields === null) {
            if (!isset($this->fieldGroups[$groupName])) {
                throw new Exception("Field group '{$groupName}' does not exist.");
            }
            $group = $this->fieldGroups[$groupName];
            $fields = $group['fields'];
            $options = array_merge($group['options'], $options);
        } else {
            // Erstelle eine neue Gruppe
            $this->fieldGroups[$groupName] = [
                'fields' => $fields,
                'options' => $options
            ];
        }

        $groupField = [
            'type' => 'group',
            'name' => $groupName,
            'fields' => [],
            'tab' => $tabId
        ];

        // Füge Wrapper hinzu, wenn vorhanden
        if (!empty($options['wrapper'])) {
            $groupField['fields'][] = [
                'type' => 'html',
                'content' => "<div class='field-group {$options['wrapper']}'>"
            ];
        }

        // Füge Titel hinzu, wenn vorhanden
        if (!empty($options['title'])) {
            $groupField['fields'][] = [
                'type' => 'html',
                'content' => "<h4 class='ui dividing header'>{$options['title']}</h4>"
            ];
        }

        // Füge Felder hinzu und erstelle Validierungsregeln
        foreach ($fields as $field) {
            $groupField['fields'][] = $field;
            $this->addValidationRules($field);
        }

        // Schließe Wrapper
        if (!empty($options['wrapper'])) {
            $groupField['fields'][] = [
                'type' => 'html',
                'content' => "</div>"
            ];
        }

        $this->addFieldToAppropriateArray($groupField);

        return $this; // Für Method Chaining
    }



    private function generateLayoutHTML($layout)
    {
        $html = "<div class='ui grid'>";
        foreach ($layout['fields'] as $field) {
            $width = $field['width'] ?? 1;
            $columnClass = $this->getColumnClass($width, $layout['columns']);
            $html .= "<div class='{$columnClass} column'>";
            $html .= $this->generateField($field);
            $html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }

    private function getColumnClass($width, $totalColumns)
    {
        $widthMap = [
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen'
        ];

        $widthRatio = ($width / $totalColumns) * 16;
        $roundedWidth = round($widthRatio);
        return $widthMap[$roundedWidth] . ' wide';
    }

    private function buildValidationRules($field)
    {
        $rules = [];
        $ruleTypes = [
            'required' => ['type' => 'empty', 'message' => 'error_message'],
            'email' => ['type' => 'email', 'message' => 'email_error'],
            'number' => ['type' => 'number', 'message' => 'number_error'],
            'minLength' => ['type' => 'minLength[{value}]', 'message' => 'minLength_error'],
            'maxLength' => ['type' => 'maxLength[{value}]', 'message' => 'maxLength_error'],
            'regex' => ['type' => 'regExp[{value}]', 'message' => 'regex_error']
        ];

        foreach ($ruleTypes as $key => $rule) {
            if (!empty($field[$key])) {
                $type = str_replace('{value}', $field[$key], $rule['type']);
                $prompt = $field[$rule['message']] ?? "Bitte überprüfen Sie dieses Feld.";
                $rules[] = ['type' => $type, 'prompt' => $prompt];
            }
        }

        return $rules;
    }
    public function generateForm()
    {
        $formId = $this->formData['id'];
        $formHtml = "<form id='{$formId}' action='{$this->formData['action']}' method='{$this->formData['method']}' class='{$this->formData['class']}'>\n";

        if (!empty($this->tabs)) {
            $formHtml .= $this->generateTabs();
            $formHtml .= $this->generateTabContent();
        }

        // Fügen Sie immer die Felder aus $this->fields hinzu
        $formHtml .= $this->generateFields($this->fields);

        $formHtml .= "</form>";

        $this->addToastContainerIfNeeded($formHtml);

        return $formHtml;
    }

    private function addValidationRules($field)
    {
        if (isset($field['name'])) {
            if (
                !empty($field['required']) || !empty($field['email']) || !empty($field['number']) ||
                isset($field['minLength']) || isset($field['maxLength']) || !empty($field['regex']) ||
                isset($field['minlength']) || isset($field['maxlength'])
            ) {
                $this->requiredFields[$field['name']] = $this->buildValidationRules($field);
            }
        }
    }

    private function generateTabs()
    {
        $tabsHtml = "<div class='ui top attached tabular menu'>";
        foreach ($this->tabs as $tabId => $tabLabel) {
            $activeClass = ($tabId === $this->currentTab) ? 'active' : '';
            $tabsHtml .= "<a class='item {$activeClass}' data-tab='{$tabId}'>{$tabLabel}</a>";
        }
        $tabsHtml .= "</div>";
        return $tabsHtml;
    }

    private function generateFields($fields)
    {
        $fieldsHtml = "";
        foreach ($fields as $field) {
            if (is_string($field)) {
                $fieldsHtml .= $field;
            } elseif ($field['type'] === 'buttonElement') {
                $fieldsHtml .= $this->generateButtonElement($field);
            } else {
                $fieldsHtml .= $this->generateField($field);
            }
        }
        return $fieldsHtml;
    }

    private function generateTabContent()
    {
        $tabContentHtml = "";
        foreach ($this->tabs as $tabId => $tabLabel) {
            $activeClass = ($tabId === $this->currentTab) ? 'active' : '';
            $tabContentHtml .= "<div class='ui bottom attached tab segment {$activeClass}' data-tab='{$tabId}'>";
            if (isset($this->tabFields[$tabId])) {
                $tabContentHtml .= $this->generateFields($this->tabFields[$tabId]);
            }
            $tabContentHtml .= "</div>";
        }
        return $tabContentHtml;
    }

    private function addToastContainerIfNeeded(&$formHtml)
    {
        if (!self::$toastContainerAdded) {
            $formHtml .= "<div class='ui toast-container'></div>";
            self::$toastContainerAdded = true;
        }
    }

    private function generateCheckbox($field)
    {
        $name = $field['name'] ?? '';
        $id = $field['id'] ?? $name;
        $label = $field['label'] ?? '';
        $checked = !empty($field['checked']) ? 'checked' : '';

        $fieldClass = $field['class'] ?? '';
        $style = $field['style'] ?? 'standard'; // standard, toggle, slider, radio, invisible

        $checkboxClass = 'ui checkbox';
        switch ($style) {
            case 'toggle':
                $checkboxClass .= ' toggle';
                break;
            case 'slider':
                $checkboxClass .= ' slider';
                break;
            case 'invisible':
                $checkboxClass .= ' hidden';
                break;
        }

        return "
            <div class='{$checkboxClass}'>
                <input type='checkbox' name='{$name}' id='{$id}' value='1' {$checked} class='{$fieldClass}'>
                <label for='{$id}'>{$label}</label>
            </div>";
    }

    private function generateRadioButtons($field)
    {
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? '';
        $options = $field['options'] ?? [];
        $inline = isset($field['inline']) && $field['inline'];
        $fieldClass = $field['class'] ?? '';

        $fieldHtml = "<div class='field {$fieldClass}'><label>{$label}</label>";
        $fieldHtml .= "<div class='" . ($inline ? 'inline' : '') . " fields'>";

        foreach ($options as $value => $optionLabel) {
            $id = $this->generateUniqueId("{$name}_{$value}");
            $fieldHtml .= "
            <div class='field'>
                <div class='ui radio checkbox'>
                    <input type='radio' name='{$name}' id='{$id}' value='{$value}'>
                    <label for='{$id}'>{$optionLabel}</label>
                </div>
            </div>";
        }

        $fieldHtml .= "</div></div>";
        return $fieldHtml;
    }

    private function generateGroupedCheckboxes($field)
    {
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? '';
        $options = $field['options'] ?? [];
        $fieldClass = $field['class'] ?? '';

        $fieldHtml = "<div class='field {$fieldClass}'>";
        $fieldHtml .= "<label>{$label}</label>";
        $fieldHtml .= "<div class='ui celled relaxed list'>";

        foreach ($options as $groupName => $groupOptions) {
            $groupId = $this->generateUniqueId("{$name}_{$groupName}");
            $fieldHtml .= "
                <div class='item'>
                    <div class='ui master checkbox'>
                        <input type='checkbox' name='{$name}[{$groupName}]' id='{$groupId}' value='{$groupName}'>
                        <label for='{$groupId}'>{$groupName}</label>
                    </div>
                    <div class='list'>";

            foreach ($groupOptions as $value => $optionLabel) {
                $id = $this->generateUniqueId("{$name}_{$groupName}_{$value}");
                $fieldHtml .= "
                    <div class='item'>
                        <div class='ui child checkbox'>
                            <input type='checkbox' name='{$name}[]' id='{$id}' value='{$value}'>
                            <label for='{$id}'>{$optionLabel}</label>
                        </div>
                    </div>";
            }

            $fieldHtml .= "
                    </div>
                </div>";
        }

        $fieldHtml .= "</div></div>";
        return $fieldHtml;
    }

    private function generateField($field, $inSplitGroup = false)
    {

        if ($field['type'] === 'grid') {
            return $this->generateGridField($field);
        }

        if ($field['type'] === 'group') {
            return $this->generateGroupFields($field['fields']);
        }

        if (isset($field['name']) && $field['name'] !== '') {
            // Überprüfen Sie, ob ein entsprechender Wert in $this->values existiert
            if (isset($this->values[$field['name']])) {
                $field['value'] = $this->values[$field['name']];
            }
        }

        $fieldType = $field['type'] ?? '';
        $label = $field['label'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $value = $field['value'] ?? '';

        $fieldClass = $field['class'] ?? '';
        $width = !empty($field['width']) ? "wide {$field['width']}" : '';
        $required = !empty($field['required']) ? 'required' : '';

        $tabAttribute = isset($field['tab']) ? "data-tab='{$field['tab']}'" : '';
        $fieldHtml = $inSplitGroup ? "<div class='$width field {$required}' {$tabAttribute}>" : "<div class='field {$required}' {$tabAttribute}>";


        if ($fieldType === 'button') {
            $name = $field['name'] ?? 'button_' . uniqid();  // Generiere einen eindeutigen Namen, falls keiner angegeben ist
        } else {
            $name = $field['name'] ?? '';
        }

        $id = $field['id'] ?? $name;

        $fieldHtml = $inSplitGroup ? "<div  class='$width field {$required}'>" : "<div class='field {$required}'>";

        switch ($fieldType) {
            case 'grid':
                return $this->generateGridField($field);
            case 'gridColumn':
                return $this->generateGridColumn($field);
            case 'hidden':
                $fieldHtml = "<input type='hidden' name='{$field['name']}' value='" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "'>";
                break;
            case 'input':
                $fieldHtml .= "<label>{$label}</label><input type='text' id='$id' name='{$field['name']}' placeholder='{$placeholder}' value='{$value}' class='{$fieldClass}'>";
                break;
            case 'textarea':
                $rows = $field['rows'] ?? 3;
                $cols = $field['cols'] ?? 50;
                $minlength = isset($field['minlength']) ? "minlength='{$field['minlength']}'" : '';
                $maxlength = isset($field['maxlength']) ? "maxlength='{$field['maxlength']}'" : '';
                $resize = $field['resize'] ?? 'both';
                $style = "resize: {$resize};";

                $fieldHtml .= "<label>{$label}</label><textarea name='{$field['name']}' placeholder='{$placeholder}' rows='{$rows}' cols='{$cols}' {$minlength} {$maxlength} class='{$fieldClass}' style='{$style}'>{$value}</textarea>";
                break;
            case 'calendar':
                $calendarType = $field['calendarType'] ?? 'date';
                $format = $field['format'] ?? 'DD.MM.YYYY';
                $minDate = isset($field['minDate']) ? "data-min-date='{$field['minDate']}'" : '';
                $maxDate = isset($field['maxDate']) ? "data-max-date='{$field['maxDate']}'" : '';

                $fieldHtml .= "<label>{$label}</label>
                                   <div class='ui calendar' id='{$id}_calendar' data-type='{$calendarType}' data-format='{$format}' {$minDate} {$maxDate}>
                                       <div class='ui input left icon'>
                                           <i class='calendar icon'></i>
                                           <input type='text' name='{$name}' id='{$id}' placeholder='{$placeholder}' value='{$value}' class='{$fieldClass}'>
                                       </div>
                                   </div>";
                break;
            case 'checkbox':
                if ($value) {
                    $field['checked'] = true;
                }
                $fieldHtml .= $this->generateCheckbox($field);
                break;
            case 'radio':
                $fieldHtml .= $this->generateRadioButtons($field);
                break;
            case 'grouped_checkbox':
            case 'checkbox_group':
                $fieldHtml .= $this->generateGroupedCheckboxes($field);
                break;
            case 'dropdown':
            case 'select':

                $selectedValues = isset($field['value']) ? (is_array($field['value']) ? $field['value'] : [$field['value']]) : [];
                $multiple = !empty($field['multiple']);
                $nameAttr = $multiple ? "{$field['name']}[]" : $field['name'];
                $placeholder = $field['placeholder'] ?? '';
                $dropdownSettings = $field['dropdownSettings'] ?? [];

                // Setze Standardwerte
                $dropdownSettings['fullTextSearch'] = $dropdownSettings['fullTextSearch'] ?? true;
                $dropdownSettings['clearable'] = $dropdownSettings['clearable'] ?? true;
                $dropdownSettings['multiple'] = $multiple;

                // Behandle das onChange-Event separat
                $onChangeFunction = '';
                if (isset($dropdownSettings['onChange'])) {
                    $onChangeFunction = $dropdownSettings['onChange'];
                    unset($dropdownSettings['onChange']); // Entferne es aus den Einstellungen, die als JSON übergeben werden
                }

                $dropdownSettingsForJson = array_filter($dropdownSettings, function ($value) {
                    return !is_string($value) || !preg_match('/^function\s*\(/', $value);
                });
                $dropdownSettingsJson = htmlspecialchars(json_encode($dropdownSettingsForJson), ENT_QUOTES, 'UTF-8');

                $dropdownId = $field['name'];
                $searchClass = $dropdownSettings['fullTextSearch'] ? 'search' : '';
                $clearableClass = $dropdownSettings['clearable'] ? 'clearable' : '';
                $multipleClass = $multiple ? 'multiple' : '';

                $fieldHtml .= "<label>{$label}</label>";
                $fieldHtml .= "<div id='{$dropdownId}' class='ui fluid {$searchClass} {$clearableClass} {$multipleClass} selection dropdown {$fieldClass}' data-settings='{$dropdownSettingsJson}' data-onchange='" . htmlspecialchars($onChangeFunction, ENT_QUOTES, 'UTF-8') . "'>";
                $fieldHtml .= "<input type='hidden' name='{$nameAttr}' value='" . implode(',', $selectedValues) . "'>";
                $fieldHtml .= "<i class='dropdown icon'></i>";
                $fieldHtml .= "<div class='default text'>" . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . "</div>";
                $fieldHtml .= "<div class='menu'>";

                if (!empty($field['options']))
                    $field['array'] = $field['options'];

                if ($field['array']) {
                    foreach ($field['array'] as $optionValue => $optionLabel) {
                        $selected = in_array($optionValue, $selectedValues) ? ' selected' : '';
                        $fieldHtml .= "<div class='item{$selected}' data-value=\"" . htmlspecialchars($optionValue, ENT_QUOTES, 'UTF-8') . "\">{$optionLabel}</div>";
                    }
                }
                $fieldHtml .= "</div></div>";
                break;

            case 'slider':
                $max = $field['max'] ?? 100;
                $step = $field['step'] ?? 1;
                $fieldHtml .= "<label>{$label}</label><input type='range' name='{$field['name']}' min='0' max='{$max}' step='{$step}' value='{$value}' class='{$fieldClass}'>";
                break;
            case 'color':
                $fieldHtml .= "<label>{$label}</label><input type='color' name='{$field['name']}' value='{$value}' class='{$fieldClass}'>";
                break;
            case 'button':
                $buttonHtml = $this->generateButton($field);
                if (!empty($field['split_start'])) {
                    $fieldHtml = "<div class='ui buttons'>\n";
                    $fieldHtml .= $buttonHtml;
                } else if (!empty($field['split_end'])) {
                    $fieldHtml = $buttonHtml;
                    $fieldHtml .= "</div>\n";
                } else {
                    $fieldHtml = "<div class='field'>{$buttonHtml}</div>\n";
                }
                return $fieldHtml;
            case 'content':
                $fieldHtml .= "<div class='$fieldClass'>{$field['value']}</div>\n";
                break;
            case 'custom':
                $fieldHtml = '';
                if (!empty($field['label'])) {
                    $fieldHtml .= "<label>{$field['label']}</label>";
                }
                if (!empty($field['html'])) {
                    $fieldHtml .= $field['html'];
                }
                return $fieldHtml;
            case 'uploader':
                $config = $field['config'];
                $uploadDirFieldName = $config['uploadDirFieldName'] ?? 'upload_dir';

                $fieldHtml = "<div id='file-uploader'    >";
                $fieldHtml .= "<input type='hidden' name='{$uploadDirFieldName}' id='{$uploadDirFieldName}' value='{$config['UPLOAD_DIR']}'>";
                $fieldHtml .= "<div id='{$config['dropZoneId']}' class='ui  segment'>
                        <div align=center class='ui icon header'>
                            <i class='file alternate outline icon'></i>
                            {$this->translations['dropzone_text']}
                        </div>
                    </div>";
                $fieldHtml .= "<input type='file' id='{$config['fileInputId']}' multiple style='display: none;' accept='" . implode(',', array_map(function ($format) {
                    return '.' . $format;
                }, $config['ALLOWED_FORMATS'])) . "'>";
                $fieldHtml .= "<div id='{$config['progressContainerId']}' style='display: none;'>
                        <div class='ui progress' data-percent='0' id='{$config['progressBarId']}'>
                            <div class='bar'>
                                <div class='progress'></div>
                            </div>
                        </div>
                    </div>";
                $fieldHtml .= "<div class='ui relaxed divided list' id='{$config['fileListId']}'></div>";
                if ($config['showDeleteAllButton']) {
                    $fieldHtml .= "<button id='{$config['deleteAllButtonId']}' class='ui red button' style='display: none; margin-bottom: 1em;'>{$this->translations['delete_all']}</button>";
                    $fieldHtml .= "<div style='clear: both;'></div>";
                }
                $fieldHtml .= "</div>";

                // JavaScript to initialize the FileUploader
                // $this->additionalJS .= "
                // document.addEventListener('DOMContentLoaded', function() {
                //     new FileUploader(" . json_encode($config) . ");
                // });";

                return $fieldHtml;
            case 'ckeditor5':
                $fieldId = $field['id'] ?? $field['name'];
                $fieldHtml .= "<label>{$label}</label>
                        <div id='{$fieldId}-toolbar'></div>
                        <div id='{$fieldId}-container' style='border:solid #e5e5e5; border-width:0 1px 1px 1px; border-radius:0 0 3px 3px;'>
                            <div id='{$fieldId}' class='ckeditor-content' data-name='{$field['name']}'>
                                {$value}
                            </div>
                        </div>";

                // CKEditor Konfiguration
                if (!empty($field['config'])) {
                    self::$ckeditorConfigs[$fieldId] = array_merge([
                        'minHeight' => 300,
                        'maxHeight' => 600,
                        'placeholder' => 'Text eingeben...',
                        'image' => [
                            'upload' => [
                                'types' => ['jpeg', 'jpg', 'png', 'gif'],
                                'maxFileSize' => 5 * 1024 * 1024,
                                'path' => 'uploads/'
                            ]
                        ]
                    ], $field['config']);
                }
                break;
            case 'password':
                $fieldHtml .= $this->generatePasswordField($field);
                break;
            case 'html':
                return $field['content'];
            case 'table':
                $fieldHtml .= $this->generateTableField($field);
                break;
            case 'segment':
                $html = "<div class='{$field['class']}'>";
                if (isset($field['fields']) && is_array($field['fields'])) {
                    foreach ($field['fields'] as $subField) {
                        $html .= $this->generateField($subField);
                    }
                }
                $html .= "</div>";
                return $html;

            default:
                $fieldHtml .= "Unbekannter Feldtyp: {$fieldType}";
        }

        $fieldHtml .= "</div>\n";
        return $fieldHtml;
    }

    private function generatePasswordField($field)
    {
        $minLength = $field['minLength'] ?? 8;
        $maxLength = $field['maxLength'] ?? '';
        $minLengthAttr = $minLength ? "minlength='{$minLength}'" : '';
        $maxLengthAttr = $maxLength ? "maxlength='{$maxLength}'" : '';
        $required = !empty($field['required']) ? 'required' : '';
        $placeholder = $field['placeholder'] ?? 'Passwort eingeben';
        $fieldId = $field['name'] . '_' . uniqid(); // Nutze name statt id
        $class = $field['class'] ?? ''; // Default leerer String
        $label = $field['label'] ?? 'Passwort';

        $html = "<label>{$label}</label>
        <div class='ui action input'>
            <input type='password' 
                id='{$fieldId}' 
                name='{$field['name']}' 
                placeholder='{$placeholder}' 
                {$minLengthAttr} 
                {$maxLengthAttr} 
                {$required} 
                class='{$class}'>";

        // Show/Hide Password Button mit Tooltip
        $html .= "<button type='button' 
                        class='ui icon button toggle-password' 
                        onclick='togglePasswordVisibility(\"{$fieldId}\")' 
                        data-tooltip='Passwort anzeigen/verbergen' 
                        data-position='top right'>
            <i class='eye icon'></i>
        </button>";

        $html .= "</div>";

        // Passwort-Stärke Anzeige
        $html .= "<div class='ui tiny progress' data-value='0' data-total='100' id='{$fieldId}_strength'>
            <div class='bar'>
                <div class='progress'></div>
            </div>
            <div class='label'>Passwort-Stärke: <span class='strength-text'>Nicht bewertet</span></div>
        </div>";

        // Passwort-Regeln anzeigen
        $html .= "
        <div class='ui small message password-rules'>
            <div class='header'>Passwort-Anforderungen:</div>
            <ul class='ui list'>
                <li class='rule-length'>Mindestens {$minLength} Zeichen</li>
                <li class='rule-uppercase'>Mindestens ein Großbuchstabe</li>
                <li class='rule-lowercase'>Mindestens ein Kleinbuchstabe</li>
                <li class='rule-number'>Mindestens eine Zahl</li>
                <li class='rule-special'>Mindestens ein Sonderzeichen</li>
            </ul>
        </div>";

        // JavaScript für die Passwort-Funktionalität
        $html .= $this->getPasswordJS($fieldId, $minLength);

        // CSS für die Passwort-Regeln
        $html .= $this->getPasswordCSS();

        return $html;
    }

    private function getPasswordJS($fieldId, $minLength)
    {
        return "
        <script>
        (function() {
            function togglePasswordVisibility(fieldId) {
                const field = document.getElementById(fieldId);
                const button = field.nextElementSibling;
                const icon = button.querySelector('i');
                
                if (field.type === 'password') {
                    field.type = 'text';
                    icon.classList.remove('eye');
                    icon.classList.add('eye', 'slash');
                    button.setAttribute('data-tooltip', 'Passwort verbergen');
                } else {
                    field.type = 'password';
                    icon.classList.remove('eye', 'slash');
                    icon.classList.add('eye');
                    button.setAttribute('data-tooltip', 'Passwort anzeigen');
                }
            }
     
            function calculatePasswordStrength(password) {
                let strength = 0;
                let feedback = '';
     
                // Mindestlänge
                if (password.length >= {$minLength}) {
                    strength += 20;
                    feedback = 'Schwach';
                }
     
                // Enthält Kleinbuchstaben
                if (/[a-z]/.test(password)) {
                    strength += 20;
                    feedback = 'Mäßig';
                }
     
                // Enthält Großbuchstaben
                if (/[A-Z]/.test(password)) {
                    strength += 20;
                    feedback = 'Gut';
                }
     
                // Enthält Zahlen
                if (/\d/.test(password)) {
                    strength += 20;
                    feedback = 'Stark';
                }
     
                // Enthält Sonderzeichen
                if (/[^A-Za-z0-9]/.test(password)) {
                    strength += 20;
                    feedback = 'Sehr stark';
                }
     
                return { strength, feedback };
            }
     
            // Passwortfeld überwachen
            const passwordField = document.getElementById('{$fieldId}');
            if (passwordField) {
                passwordField.addEventListener('input', function() {
                    const password = this.value;
                    const rules = this.closest('.field').querySelector('.password-rules');
                    const strengthProgress = $('#{$fieldId}_strength');
                    const strengthResult = calculatePasswordStrength(password);
                    
                    // Update Passwort-Stärke
                    strengthProgress.progress({
                        value: strengthResult.strength
                    });
                    strengthProgress.find('.strength-text').text(strengthResult.feedback);
     
                    // Farbe der Fortschrittsleiste anpassen
                    const bar = strengthProgress.find('.bar');
                    if (strengthResult.strength <= 20) {
                        bar.css('background-color', '#DB2828'); // Rot
                    } else if (strengthResult.strength <= 40) {
                        bar.css('background-color', '#F2711C'); // Orange
                    } else if (strengthResult.strength <= 60) {
                        bar.css('background-color', '#FBBD08'); // Gelb
                    } else if (strengthResult.strength <= 80) {
                        bar.css('background-color', '#B5CC18'); // Olivgrün
                    } else {
                        bar.css('background-color', '#21BA45'); // Grün
                    }
                    
                    // Update Regeln
                    if (rules) {
                        // Explizite Längenprüfung
                        const lengthRule = rules.querySelector('.rule-length');
                        if (password.length >= {$minLength}) {
                            lengthRule.classList.add('green');
                        } else {
                            lengthRule.classList.remove('green');
                        }
                        
                        // Andere Regeln prüfen
                        rules.querySelector('.rule-uppercase').classList.toggle('green', /[A-Z]/.test(password));
                        rules.querySelector('.rule-lowercase').classList.toggle('green', /[a-z]/.test(password));
                        rules.querySelector('.rule-number').classList.toggle('green', /[0-9]/.test(password));
                        rules.querySelector('.rule-special').classList.toggle('green', /[^A-Za-z0-9]/.test(password));
                    }
                });
            }
     
            // Funktion global verfügbar machen
            window.togglePasswordVisibility = togglePasswordVisibility;
        })();
        </script>";
    }

    private function getPasswordCSS()
    {
        return "
        <style>
        .password-rules .green {
            color: #21ba45;
        }
        .password-rules .green:before {
            content: '✓ ';
        }
        .ui.progress .bar {
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .password-rules .ui.list {
            margin-top: 0.5em;
        }
        .password-rules .ui.list li {
            margin-bottom: 0.3em;
        }
        </style>";
    }

    public function setFieldValue($fieldName, $value)
    {
        // Array für grouped_checkbox Felder
        if (is_array($value)) {
            $this->values[$fieldName] = $value;
        } else {
            $this->values[$fieldName] = (string) $value;
        }

        // Aktualisiere Werte in den bestehenden Feldern
        foreach ($this->fields as &$field) {
            if (isset($field['name']) && $field['name'] === $fieldName) {
                $field['value'] = $value;
            }
        }

        // Aktualisiere auch Werte in Tab-Feldern
        foreach ($this->tabFields as &$tabFields) {
            foreach ($tabFields as &$field) {
                if (isset($field['name']) && $field['name'] === $fieldName) {
                    $field['value'] = $value;
                }
            }
        }
    }

    private function generateTableField($field)
    {
        $name = $field['name'];
        $label = $field['label'];
        $columns = $field['columns'];

        $html = "<div class='field'>";
        $html .= "<label>{$label}</label>";
        $html .= "<table class='ui celled table' id='{$name}'>";

        // Header
        $html .= "<thead><tr>";
        foreach ($columns as $columnName => $columnLabel) {
            $html .= "<th>{$columnLabel}</th>";
        }
        $html .= "<th>Actions</th></tr></thead>";

        // Body
        $html .= "<tbody>";
        // Hier könnten Sie vorhandene Daten einfügen, falls welche vorhanden sind
        $html .= "</tbody>";

        // Footer (für neue Einträge)
        $html .= "<tfoot><tr>";
        foreach ($columns as $columnName => $columnLabel) {
            $html .= "<td><input type='text' name='{$name}[new][{$columnName}]' /></td>";
        }
        $html .= "<td><button type='button' class='ui button' onclick='addTableRow(\"{$name}\")'>Add</button></td>";
        $html .= "</tr></tfoot>";

        $html .= "</table>";
        $html .= "</div>";

        return $html;
    }

    private function generateGroupFields($fields)
    {
        $html = '';
        foreach ($fields as $field) {
            if ($field['type'] === 'html') {
                $html .= $field['content'];
            } else {
                $html .= $this->generateField($field);
            }
        }
        return $html;
    }

    public function generateCSS()
    {
        $css = "
        <style>
            .ui.form .field.required label:after { content: ' *'; color: red; }
            .editor-container { border: 1px solid #ddd; border-radius: 4px; overflow: hidden; }
            .editor-toolbar { background-color: #f7f7f7; border-bottom: 1px solid #ddd; padding: 5px; }
            .editor-toolbar .ck-toolbar { border: none !important; background: transparent !important; }
            .editor-content { padding: 10px; min-height: 200px; background-color: #fff; }
            .editor-content .ck-editor__editable { border: none !important; box-shadow: none !important; min-height: 200px; }
            .editor-content .ck-editor__editable:focus { outline: none !important; }
        ";

        if (!self::$toastContainerAdded) {
            $css .= "
            .ui.toast-container { position: fixed; top: 20px; right: 20px; z-index: 1000; }
            ";
        }

        $css .= "</style>";

        return $css;
    }

    public function generateJS()
    {
        $uploadUrl = $this->getUploadUrl();
        $formId = $this->formData['id'];
        $rulesJson = json_encode($this->requiredFields, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        $responseType = $this->formData['responseType'];
        $successFunction = $this->formData['success'];
        $basePath = $this->getBasePath();

        //$js = "<script src='{$basePath}/js/formGenerator.js'></script>\n";
        //if ($this->hasCKEditor) { $js .= "<script src='{$basePath}/js/ckeditor-init.js'></script>\n"; }
        $js = '';
        if (!empty($this->tabs)) {
            $js .= "
            <script>
            $(document).ready(function() {
                $('.menu .item').tab();
            });
            </script>
            ";
        }
        if ($this->hasFileUploader) {
            $js .= "
            <script>
            (function() {
                // Globales Objekt für FileUploader-Management
                if (!window.FileUploaderManager) {
                    window.FileUploaderManager = {
                        instances: {},
                        configs: {},
                        
                        // Initialisiert oder aktualisiert eine Uploader-Instanz
                        initOrUpdate: function(formId, config) {
                            // Wenn eine alte Instanz existiert, diese zuerst bereinigen
                            if (this.instances[formId]) {
                                this.cleanup(formId);
                            }
                            
                            // Konfiguration speichern
                            this.configs[formId] = config;
                            
                            // Neue Instanz erstellen
                            this.instances[formId] = new FileUploader(config);
                            console.log('FileUploader initialized for form:', formId);
                        },
                        
                        // Bereinigt eine Uploader-Instanz
                        cleanup: function(formId) {
                            if (this.instances[formId]) {
                                // Event-Listener entfernen
                                const instance = this.instances[formId];
                                if (instance.dropZone) {
                                    instance.dropZone.removeEventListener('dragover', instance.handleDragOver);
                                    instance.dropZone.removeEventListener('dragleave', instance.handleDragLeave);
                                    instance.dropZone.removeEventListener('drop', instance.handleDrop);
                                    instance.dropZone.removeEventListener('click', instance.handleClick);
                                }
                                
                                if (instance.fileInput) {
                                    instance.fileInput.removeEventListener('change', instance.handleFileSelect);
                                }
                                
                                // Instanz löschen
                                delete this.instances[formId];
                                console.log('FileUploader cleaned up for form:', formId);
                            }
                        }
                    };
                }
                
                // Modal-Event-Listener für Cleanup
                $('.ui.modal').on('hide', function() {
                    const formId = $(this).find('form').attr('id');
                    if (formId && window.FileUploaderManager) {
                        window.FileUploaderManager.cleanup(formId);
                    }
                });
                
                // FileUploader direkt initialisieren
                window.FileUploaderManager.initOrUpdate('" . $formId . "', " . json_encode($this->fileUploaderConfig) . ");
            })();
            </script>
            ";
        }

        $js .= "
        <script>
        (function($) {
            var formId = '{$formId}';
            var formRules = {$rulesJson};
            var responseType = '{$responseType}';
            var successFunction = function(response) { {$successFunction} };
            var uploadUrl = '{$uploadUrl}';
    
            $(document).ready(function() {
                initializeForm(formId, formRules, responseType, successFunction);
    
                " . ($this->hasCKEditor ? "
                $('.ckeditor-content').each(function() {
                    var editorId = $(this).attr('id');
                    var config = " . json_encode(self::$ckeditorConfigs) . "[editorId] || {};
                    initializeCKEditor(editorId, config, '" . $this->getUploadUrl() . "');
                });
                " : "") . "
    
                // Initialisiere Dropdowns
                initializeDropdowns();
            });
        })(jQuery);
    
        function addTableRow(tableId) {
            var table = document.getElementById(tableId);
            var newRow = table.insertRow(-1);
            var columns = table.rows[0].cells.length - 1; // -1 wegen der Aktionsspalte
            
            for (var i = 0; i < columns; i++) {
                var cell = newRow.insertCell(i);
                var input = document.createElement('input');
                input.type = 'text';
                input.name = tableId + '[]' + table.rows[0].cells[i].innerText;
                cell.appendChild(input);
            }
            
            var actionCell = newRow.insertCell(-1);
            var deleteButton = document.createElement('button');
            deleteButton.innerHTML = 'Delete';
            deleteButton.className = 'ui button red';
            deleteButton.onclick = function() { table.deleteRow(newRow.rowIndex); };
            actionCell.appendChild(deleteButton);
        }
        </script>
        ";

        return $js;
    }

    private $usedIds = [];

    private function generateUniqueId($baseId)
    {
        $id = $baseId;
        $counter = 1;
        while (in_array($id, $this->usedIds)) {
            $id = $baseId . '_' . $counter;
            $counter++;
        }
        $this->usedIds[] = $id;
        return $id;
    }


    //wird für CKeditor verwendet
    private function getUploadUrl()
    {
        $classDir = dirname((new ReflectionClass($this))->getFileName());
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $classDir);
        $relativePath = '/' . trim($relativePath, '/');
        return $relativePath . '/upload_image.php';
    }

    private function addGridField($gridField)
    {
        if (!isset($gridField['columns']) || !isset($gridField['fields']) || !is_array($gridField['fields'])) {
            throw new InvalidArgumentException("Grid field must have 'columns' and 'fields' array defined.");
        }

        $gridFields = [];
        foreach ($gridField['fields'] as $field) {
            if (!isset($field['width'])) {
                throw new InvalidArgumentException("Each field in a grid must have a 'width' defined.");
            }

            $columnClass = $this->getColumnClass($field['width'], $gridField['columns']);

            // Setze den Wert, falls vorhanden
            if (isset($field['name']) && isset($this->values[$field['name']])) {
                $field['value'] = $this->values[$field['name']];
            }

            // Füge das Tab-Attribut hinzu, wenn es im Grid-Feld definiert ist
            if (isset($gridField['tab'])) {
                $field['tab'] = $gridField['tab'];
            }

            $gridFields[] = [
                'type' => 'gridColumn',
                'class' => $columnClass,
                'content' => $field,
                'tab' => $field['tab'] ?? null
            ];
        }

        $this->addFieldToAppropriateArray([
            'type' => 'grid',
            'columns' => $gridField['columns'],
            'fields' => $gridFields,
            'tab' => $gridField['tab'] ?? null
        ]);
    }


    private function generateGridField($gridField)
    {
        $html = "<div class='ui form grid-container'>";
        $html .= "<div class='ui {$gridField['columns']} column grid'>";
        foreach ($gridField['fields'] as $field) {
            $html .= "<div class='{$field['class']} column'>";
            $html .= $this->generateField($field['content'], true);
            $html .= "</div>";
        }
        $html .= "</div>";
        $html .= "</div><br>";
        return $html;
    }

    private function generateGridColumn($columnField)
    {
        $html = "<div class='{$columnField['class']} column'>";
        $html .= $this->generateField($columnField['content']);
        $html .= "</div>";
        return $html;
    }

}
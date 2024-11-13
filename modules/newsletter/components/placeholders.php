<?php
function getPlaceholdersHTML()
{
    return '
    <div class="ui segment">
        <div class="ui tiny buttons">
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{anrede}}\')">Anrede</button>
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{titel}}\')">Titel</button>
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{vorname}}\')">Vorname</button>
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{nachname}}\')">Nachname</button>
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{firma}}\')">Firma</button>
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{email}}\')">E-Mail</button>
        </div>
        <div class="ui tiny buttons" style="margin-top: 5px;">
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{datum}}\')">Datum</button>
            <button type="button" class="ui button" onclick="EditorUtils.insertPlaceholder(\'{{uhrzeit}}\')">Uhrzeit</button>
            <button type="button" class="ui primary button" onclick="EditorUtils.previewWithPlaceholders()">Vorschau</button>
        </div>
    </div>
    
    <div class="ui large modal" id="previewModal">
    <i class="close icon"></i>
    <div class="header">Template Vorschau</div>
    <div class="content">
        <div id="previewContent" class="preview-container"></div>
    </div>
    <div class="actions">
        <div class="ui deny button">Schließen</div>
    </div>
    </div>
    ';
}
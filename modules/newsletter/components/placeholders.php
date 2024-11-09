<?php
function getPlaceholdersHTML()
{
    return '
    <div class="ui segment">
        <div class="ui tiny buttons">
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{anrede}}\')">Anrede</button>
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{titel}}\')">Titel</button>
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{vorname}}\')">Vorname</button>
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{nachname}}\')">Nachname</button>
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{firma}}\')">Firma</button>
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{email}}\')">E-Mail</button>
        </div>
        <div class="ui tiny buttons" style="margin-top: 5px;">
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{datum}}\')">Datum</button>
            <button type="button" class="ui button" onclick="insertPlaceholder(\'{{uhrzeit}}\')">Uhrzeit</button>
            <button type="button" class="ui primary button" onclick="previewWithPlaceholders()">Vorschau</button>
        </div>
    </div>';
}
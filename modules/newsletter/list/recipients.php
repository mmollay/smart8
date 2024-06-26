<?
$arr['mysql'] = array(
    'table' => "recipients r
                LEFT JOIN (
                    SELECT recipient_id, GROUP_CONCAT(CONCAT('<div class=\"ui mini basic compact label ', g.color, '\">', g.name, '</div>') SEPARATOR ' ') as group_labels
                    FROM recipient_group rg
                    JOIN groups g ON rg.group_id = g.id
                    GROUP BY recipient_id
                ) rg ON r.id = rg.recipient_id",
    'field' => "r.id, r.first_name, r.last_name, r.company, r.email, r.gender, r.title, r.comment, IFNULL(rg.group_labels, '<div class=\"ui mini compact label\">Keine Gruppen</div>') as group_labels",
    'order' => 'r.id desc',
    'limit' => 25,
    'group' => 'r.id',
    'like' => 'r.first_name, r.last_name, r.email, r.company',
);

$arr['list'] = array('id' => 'recipients', 'width' => '1200px', 'align' => '', 'size' => 'small', 'class' => 'compact celled striped definition');

$arr['th']['first_name'] = array('title' => "<i class='user icon'></i>Vorname");
$arr['th']['last_name'] = array('title' => "<i class='user icon'></i>Nachname");
$arr['th']['company'] = array('title' => "<i class='building icon'></i>Firma");
$arr['th']['email'] = array('title' => "<i class='mail icon'></i>Empfänger-Email");
$arr['th']['group_labels'] = array('title' => "Gruppennamen"); // Neue Spalte für die Gruppennamen
$arr['th']['comment'] = array('title' => "Kommentar");

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');
$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen');

$arr['modal']['modal_form'] = array('title' => 'Empfänger bearbeiten', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form_delete'] = array('title' => 'Empfänger entfernen', 'class' => 'small', 'url' => 'form_delete.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['top']['button']['modal_form'] = array('title' => 'Neuen Empfänger anlegen', 'icon' => 'plus', 'class' => 'blue circular', 'popup' => 'Neuen Empfänger anlegen');

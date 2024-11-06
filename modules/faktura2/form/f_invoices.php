<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'invoiceForm',
    'action' => 'save/process_invoice_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_invoice(response);"
]);

// Bestimmen des Modus (Bearbeiten oder Neu)
$update_id = $_POST['update_id'] ?? null;

if (!$update_id) {
    $invoice_date = date("Y-m-d");
    $invoice_number = getNextInvoiceNumber($GLOBALS['db']);
} else {
    $formGenerator->loadValuesFromDatabase($GLOBALS['db'], "SELECT * FROM invoices WHERE invoice_id = ?", [$update_id]);
}

// Tabs definieren
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        '1' => 'Rechnungsdaten',
        '2' => 'Positionen',
        '3' => 'Zusatzinformationen'
    ],
    'active' => '1'
]);

// Felder für Tab 1: Rechnungsdaten
$formGenerator->addField([
    'type' => 'grid',
    'columns' => 2,
    'tab' => '1',
    'fields' => [
        [
            'type' => 'input',
            'name' => 'invoice_number',
            'label' => 'Rechnungsnummer',
            'value' => $invoice_number ?? '',
            'required' => true,
            'width' => 1
        ],
        [
            'type' => 'calendar',
            'name' => 'invoice_date',
            'label' => 'Rechnungsdatum',
            'value' => $invoice_date ?? '',
            'required' => true,
            'width' => 1
        ]
    ]
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'customer_id',
    'label' => 'Kunde auswählen',
    'array' => getCustomerArray($GLOBALS['db']),
    'class' => 'search',
    'placeholder' => '--bitte wählen--',
    'clearable' => true,
    'required' => true,
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'calendar',
    'name' => 'due_date',
    'label' => 'Fälligkeitsdatum',
    'tab' => '1'
]);

// Felder für Tab 2: Positionen
$formGenerator->addField([
    'type' => 'custom',
    'content' => '
    <div class="field">
        <label for="select_article">Artikel auswählen</label>
        <select id="select_article" name="select_article" class="ui search dropdown">
            <option value="">-- Bitte wählen --</option>
        </select>
    </div>
    ',
    'tab' => '2'
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 3,
    'tab' => '2',
    'fields' => [
        [
            'type' => 'input',
            'name' => 'article_number',
            'label' => 'Artikelnummer',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'article_title',
            'label' => 'Artikelname',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'net_price',
            'label' => 'Netto',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'quantity',
            'label' => 'Anzahl',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'unit',
            'label' => 'Einheit',
            'width' => 1
        ],
        [
            'type' => 'dropdown',
            'name' => 'account',
            'label' => 'Konto',
            'required' => true,
            'array' => getAccountArray($GLOBALS['db']),
            'class' => 'search',
            'width' => 1
        ]
    ]
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'article_description',
    'label' => 'Beschreibung',
    'tab' => '2'
]);

$formGenerator->addButtonElement([
    [
        'type' => 'button',
        'name' => 'copy_article',
        'value' => 'Artikel überschreiben',
        'icon' => 'copy',
        'class' => 'ui button'
    ],
    [
        'type' => 'button',
        'name' => 'add_new_article',
        'value' => 'Artikel anlegen',
        'icon' => 'plus',
        'class' => 'ui primary button'
    ],
    [
        'type' => 'button',
        'name' => 'add_position',
        'value' => 'Position übernehmen',
        'icon' => 'check',
        'class' => 'ui positive button'
    ],
    [
        'type' => 'button',
        'name' => 'cancel_position',
        'value' => 'Abbrechen',
        'icon' => 'cancel',
        'class' => 'ui negative button'
    ]
], [
    'layout' => 'inline',
    'alignment' => 'left',
    'tab' => '2'
]);

$formGenerator->addField([
    'type' => 'custom',
    'content' => '
    <div id="no_articles_message" class="ui message">
        <p>Noch keine Artikel hinzugefügt.</p>
    </div>
    <table id="invoice_items" class="ui celled table" style="display:none;">
        <thead>
            <tr>
                <th>Artikelnr.</th>
                <th>Bezeichnung</th>
                <th>Menge</th>
                <th>Einheit</th>
                <th>Einzelpreis</th>
                <th>Gesamtpreis</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    ',
    'tab' => '2'
]);

$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'invoice_items_data',
    'tab' => '2'
]);

// Felder für Tab 3: Zusatzinformationen
$formGenerator->addField([
    'type' => 'input',
    'name' => 'total_amount',
    'label' => 'Gesamtbetrag',
    'format' => 'euro',
    'readonly' => true,
    'tab' => '3'
]);

$formGenerator->addField([
    'type' => 'checkbox',
    'name' => 'paid',
    'label' => 'Bezahlt',
    'tab' => '3'
]);

// Buttons
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button'
    ],
    [
        'type' => 'button',
        'name' => 'cancel',
        'value' => 'Abbrechen',
        'icon' => 'cancel',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);

// JavaScript für die Funktionalität
$formGenerator->addField([
    'type' => 'custom',
    'content' => '
    <div class="field">
        <label for="select_article">Artikel auswählen</label>
        <select id="select_article" name="select_article" class="ui search dropdown">
            <option value="">-- Bitte wählen --</option>
        </select>
    </div>
    <script>
    $(document).ready(function () {
        let articleData = {};

        function loadArticleData() {
            $.ajax({
                url: "data/get_article_data.php",
                method: "GET",
                dataType: "json",
                success: function (data) {
                    articleData = data;
                    updateArticleDropdown();
                },
                error: function (xhr, status, error) {
                    console.error("Fehler beim Laden der Artikeldaten:", error);
                }
            });
        }

        function updateArticleDropdown() {
            let $select = $("#select_article");
            $select.empty().append("<option value=\'\'>-- Bitte wählen --</option>");
            $.each(articleData, function (id, article) {
                $select.append($("<option>", {
                    value: id,
                    text: article.article_number + " - " + article.name
                }));
            });
            $select.dropdown({
                fullTextSearch: true,
                onChange: function(value, text, $selectedItem) {
                    if (value && articleData[value]) {
                        let article = articleData[value];
                        $("#article_number").val(article.article_number);
                        $("#article_title").val(article.name);
                        $("#article_description").val(article.description);
                        $("#net_price").val(article.price);
                        $("#unit").val(article.unit);
                        $("#quantity").val(1);
                    } else {
                        clearArticleFields();
                    }
                }
            });
        }

        function clearArticleFields() {
            $("#article_number, #article_title, #article_description, #net_price, #unit, #quantity").val("");
            $("#account").dropdown("clear");
        }

        loadArticleData();
    });
    </script>
    ',
    'tab' => '2'
]);

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();

function getNextInvoiceNumber($db)
{
    $query = "SELECT MAX(CAST(SUBSTRING(invoice_number, 5) AS UNSIGNED)) as max_number 
              FROM invoices 
              WHERE invoice_number LIKE CONCAT(YEAR(CURDATE()), '-%')";
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $nextNumber = ($row['max_number'] ?? 0) + 1;
    return date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

function getCustomerArray($db)
{
    $customers = [];
    $query = "SELECT customer_id, CONCAT(company_name, ' (', contact_person, ')') AS name FROM customers ORDER BY company_name";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $customers[$row['customer_id']] = $row['name'];
    }
    return $customers;
}



// Stellen Sie sicher, dass Sie eine get_article_data.php Datei erstellen, die etwa so aussieht:
/*
<?php
include(__DIR__ . '/../f_config.php');

$articles = getArticleArray($db);

header('Content-Type: application/json');
echo json_encode($articles);
?>
*/

?>
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
        $select.empty().append('<option value="">-- Bitte wählen --</option>');
        $.each(articleData, function (id, article) {
            $select.append($('<option>', {
                value: id,
                text: article.display_text
            }));
        });
        $select.dropdown("refresh");
    }

    function clearArticleFields() {
        $("#article_number, #article_title, #article_description, #net_price, #unit, #quantity").val("");
        $("#account").dropdown("clear");
    }

    function addArticleToTable() {
        let articleNumber = $("#article_number").val();
        let title = $("#article_title").val();
        let description = $("#article_description").val();
        let quantity = $("#quantity").val();
        let unit = $("#unit").val();
        let netPrice = $("#net_price").val();
        let account = $("#account option:selected").text();
        let totalPrice = (quantity * netPrice).toFixed(2);

        if (!articleNumber || !title || !quantity || !netPrice) {
            alert('Bitte füllen Sie alle erforderlichen Felder aus.');
            return;
        }

        let newRow = `
            <tr>
                <td>${articleNumber}</td>
                <td>${title}<br>${description}</td>
                <td>${quantity}</td>
                <td>${unit}</td>
                <td>${netPrice}</td>
                <td>${totalPrice}</td>
                <td>
                    <button type="button" class="ui icon button edit-row"><i class="edit icon"></i></button>
                    <button type="button" class="ui icon button delete-row"><i class="trash icon"></i></button>
                </td>
            </tr>
        `;

        $('#invoice_items tbody').append(newRow);
        updateArticleVisibility();
        clearArticleFields();
        $('#select_article').dropdown("clear");
        calculateTotalAmount();
    }

    function updateArticleVisibility() {
        if ($('#invoice_items tbody tr').length > 0) {
            $('#no_articles_message').hide();
            $('#invoice_items').show();
        } else {
            $('#no_articles_message').show();
            $('#invoice_items').hide();
        }
    }

    function calculateTotalAmount() {
        let total = 0;
        $('#invoice_items tbody tr').each(function () {
            total += parseFloat($(this).find('td:eq(5)').text());
        });
        $('#total_amount').val(total.toFixed(2));
    }

    // Event Listeners
    loadArticleData();

    $('#select_article').change(function () {
        let articleId = $(this).val();
        if (articleId && articleData[articleId]) {
            let article = articleData[articleId];
            $('#article_number').val(article.article_number);
            $('#article_title').val(article.name);
            $('#article_description').val(article.description);
            $('#net_price').val(article.price);
            $('#unit').val(article.unit);
            $('#quantity').val(1);
        } else {
            clearArticleFields();
        }
    });

    $('#copy_article').click(function () {
        let articleId = $('#select_article').val();
        if (articleId && articleData[articleId]) {
            let article = articleData[articleId];
            $('#article_number').val(article.article_number);
            $('#article_title').val(article.name);
            $('#article_description').val(article.description);
            $('#net_price').val(article.price);
            $('#unit').val(article.unit);
        }
    });

    $('#add_new_article').click(function () {
        alert('Funktion zum Anlegen eines neuen Artikels wird geöffnet');
        // Hier können Sie den Code zum Öffnen eines Modals für die Erstellung eines neuen Artikels einfügen
    });

    $('#add_position').click(function () {
        addArticleToTable();
    });

    $('#cancel_position').click(function () {
        clearArticleFields();
        $('#select_article').dropdown("clear");
    });

    $(document).on('click', '.delete-row', function () {
        $(this).closest('tr').remove();
        updateArticleVisibility();
        calculateTotalAmount();
    });

    $(document).on('click', '.edit-row', function () {
        let $row = $(this).closest('tr');
        $('#article_number').val($row.find('td:eq(0)').text());
        $('#article_title').val($row.find('td:eq(1)').text().split('<br>')[0]);
        $('#article_description').val($row.find('td:eq(1)').text().split('<br>')[1]);
        $('#quantity').val($row.find('td:eq(2)').text());
        $('#unit').val($row.find('td:eq(3)').text());
        $('#net_price').val($row.find('td:eq(4)').text());
        // Setze das Konto, wenn es in der Tabelle enthalten ist
        $row.remove();
        updateArticleVisibility();
        calculateTotalAmount();
    });

    $('#invoiceForm').submit(function (e) {
        e.preventDefault();
        let invoiceItems = [];
        $('#invoice_items tbody tr').each(function () {
            let row = $(this);
            invoiceItems.push({
                article_number: row.find('td:eq(0)').text(),
                description: row.find('td:eq(1)').text(),
                quantity: row.find('td:eq(2)').text(),
                unit: row.find('td:eq(3)').text(),
                net_price: row.find('td:eq(4)').text(),
                total_price: row.find('td:eq(5)').text()
            });
        });
        $('#invoice_items_data').val(JSON.stringify(invoiceItems));
        this.submit();
    });

    // Initialisierung
    updateArticleVisibility();
});
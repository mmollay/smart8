$(document).ready(function () {
    $("#button_generate_password").click(function () {
        $.post('inc/random_password.php', function (data) {
            $('#password').val(data);
        });
    });
});

function after_form_client(id) {
    // Hilfsfunktion, um Toast-Nachrichten anzuzeigen
    function showToast(message, type) {
        $('body').toast({
            class: type, // success oder error
            message: message,
            position: 'top center',
            displayTime: 4000
        });
    }

    switch (id) {
        case 'empty_client_number':
        case 'double_client_number':
            showToast('Kundennummer existiert bereits', 'error');
            $('#client_number').focus();
            break;
        case 'email_exists':
            showToast('Email bereits vergeben', 'error');
            $('#email').focus();
            break;
        case 'double_company_name':
            showToast('Firmenname existiert bereits', 'error');
            $('#company_1').focus();
            break;
        default:
            if (Number.isInteger(parseInt(id))) {
                showToast('Kundendaten wurden gespeichert', 'success');
                $('#modal_form').modal('hide');
                table_reload();
            } else {
                showToast('Fehler im System: ' + id, 'error');
            }
            break;
    }
}

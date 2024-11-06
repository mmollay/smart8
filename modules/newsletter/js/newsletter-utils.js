// newsletter-utils.js
function checkPendingEmails() {
    $.ajax({
        url: 'ajax/check_pending_emails.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.hasPendingEmails) {
                if ($('#testSendEmail').length === 0) {
                    $('.ui.left.fixed.menu').append(
                        '<div class="item"><button class="ui blue icon button" id="testSendEmail">' +
                        '<i class="paper small plane icon"></i></button></div>'
                    );
                    initializeSendButton();
                }
            } else {
                $('#testSendEmail').closest('.item').remove();
            }
        }
    });
}
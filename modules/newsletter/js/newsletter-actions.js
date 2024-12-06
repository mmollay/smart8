// Newsletter Aktionen
const NewsletterActions = {
    sendNewsletter: function (id) {
        NewsletterUI.showConfirmDialog('Möchten Sie diesen Newsletter jetzt versenden?', () => {
            $.ajax({
                url: 'ajax/send_newsletter.php',
                method: 'POST',
                data: { content_id: id },
                dataType: 'json',
                success: this.handleSendResponse,
                error: NewsletterCore.handleError
            });
        });
    },

    handleSendResponse: function (response) {
        if (response.success === true) {
            NewsletterUI.showSuccessToast(response.message || 'Newsletter wird versendet');
            setTimeout(NewsletterUI.reloadTable, 2100);
        } else {
            NewsletterUI.showErrorToast(response.message || 'Fehler beim Versenden');
        }
    },

    sendTestMail: function (params) {
        $.ajax({
            url: 'exec/send_test_mail.php',
            method: 'POST',
            data: { content_id: params.content_id },
            dataType: 'json',
            success: this.handleTestMailResponse,
            error: NewsletterCore.handleError
        });
    },

    handleTestMailResponse: function (data) {
        if (data.success) {
            NewsletterUI.showSuccessToast(data.message || 'Test-Mail wurde gesendet');
            NewsletterUI.reloadTable();
        } else {
            NewsletterUI.showErrorToast(data.message || 'Fehler beim Senden der Test-Mail');
        }
    },

    cloneNewsletter: function (params) {
        $.ajax({
            url: 'ajax/clone_newsletter.php',
            method: 'POST',
            data: { content_id: params.content_id },
            dataType: 'json',
            success: this.handleCloneResponse,
            error: NewsletterCore.handleError
        });
    },

    handleCloneResponse: function (data) {
        if (data.status === 'success') {
            NewsletterUI.showSuccessToast(data.message || 'Newsletter erfolgreich dupliziert');
            NewsletterUI.reloadTable();
        } else {
            NewsletterUI.showErrorToast(data.message || 'Fehler beim Duplizieren des Newsletters');
        }
    }
};
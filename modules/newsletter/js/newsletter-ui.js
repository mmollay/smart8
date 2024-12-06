// Newsletter UI Funktionalitäten
const NewsletterUI = {
    showSuccessToast: function (message) {
        $('body').toast({
            class: 'success',
            message: message,
            showProgress: 'bottom',
            displayTime: 2000
        });
    },

    showErrorToast: function (message) {
        $('body').toast({
            class: 'error',
            message: message,
            showProgress: 'bottom',
            displayTime: 3000
        });
    },

    reloadTable: function () {
        NewsletterCore.stopUpdates();

        if (typeof standardReloadTable === 'function') {
            standardReloadTable();
        }

        setTimeout(() => NewsletterCore.startUpdates(), 1000);
    },

    showConfirmDialog: function (message, callback) {
        if (confirm(message)) {
            callback();
        }
    }
};

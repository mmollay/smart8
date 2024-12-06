// Newsletter Initialisierung
$(document).ready(function () {
    // Globale Funktionen für Button-Callbacks
    window.sendNewsletter = function (id) {
        NewsletterActions.sendNewsletter(id);
    };

    window.sendTestMail = function (params) {
        NewsletterActions.sendTestMail(params);
    };

    window.cloneNewsletter = function (params) {
        NewsletterActions.cloneNewsletter(params);
    };

    // Initialisiere Core-Funktionalitäten
    NewsletterCore.init();
});

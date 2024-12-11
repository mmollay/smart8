const Newsletter = {
    Core: {
        updateInterval: null,

        init: function () {
            // Initialisiere Popups
            $('.ui.popup').popup();

            // Starte Updates
            this.startUpdates();

            // Progress Bar Initialisierung
            $('.ui.progress').progress({
                showActivity: false
            });
        },

        startUpdates: function () {
            // Initial laden
            this.loadAllNewsletterStats();
            this.updateProgress();

            // Regelmäßiges Update
            this.updateInterval = setInterval(() => {
                this.loadAllNewsletterStats();
                this.updateProgress();
            }, 10000);
        },

        loadAllNewsletterStats: function () {
            $('.newsletter-stats').each((i, el) => {
                const contentId = $(el).data('content-id');
                this.loadNewsletterStats(contentId);
            });
        },

        loadNewsletterStats: function (contentId) {
            $.ajax({
                url: 'ajax/get_newsletter_stats.php',
                data: { content_id: contentId },
                success: (response) => {
                    if (response.success) {
                        this.updateStatsDisplay(contentId, response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading stats:', error);
                    $(`.newsletter-stats[data-content-id="${contentId}"]`)
                        .html('<span class="ui red text">Fehler beim Laden</span>');
                }
            });
        },

        updateProgress: function () {
            $('.ui.active.progress').each((i, el) => {
                const contentId = $(el).closest('tr').find('[data-content-id]').data('content-id');
                if (contentId) {
                    this.checkProgress(contentId);
                }
            });
        },

        checkProgress: function (contentId) {
            $.ajax({
                url: 'ajax/get_send_progress.php',
                data: { content_id: contentId },
                success: (data) => {
                    if (data.success) {
                        const progress = Math.round((data.processed / data.total) * 100);
                        this.updateProgressBar(contentId, progress, data.processed, data.total);
                    }
                }
            });
        },

        updateProgressBar: function (contentId, progress, processed, total) {
            const $progress = $(`.ui.progress[data-content-id="${contentId}"]`);
            $progress
                .progress('set percent', progress)
                .find('.label')
                .text(`${processed} von ${total} versendet`);

            if (progress >= 100) {
                setTimeout(() => window.reloadTable(), 1000);
            }
        }
    },

    Actions: {
        cloneNewsletter: function (params) {
            $.ajax({
                url: 'ajax/clone_newsletter.php',
                method: 'POST',
                data: { content_id: params.content_id },
                dataType: 'json',
                success: (response) => {
                    if (response.status === 'success') {
                        $('body').toast({
                            class: 'success',
                            message: response.message || 'Newsletter erfolgreich dupliziert'
                        });
                        window.reloadTable();
                    } else {
                        $('body').toast({
                            class: 'error',
                            message: response.message || 'Fehler beim Duplizieren'
                        });
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Clone error:', error);
                    $('body').toast({
                        class: 'error',
                        message: 'Fehler beim Duplizieren des Newsletters'
                    });
                }
            });
        },

        sendNewsletter: function (contentId) {
            if (confirm('Möchten Sie diesen Newsletter jetzt versenden?')) {
                $.ajax({
                    url: 'ajax/send_newsletter.php',
                    method: 'POST',
                    data: { content_id: contentId },
                    dataType: 'json',
                    success: (response) => {
                        if (response.success) {
                            $('body').toast({
                                class: 'success',
                                message: response.message || 'Newsletter wird versendet'
                            });
                            setTimeout(() => window.reloadTable(), 1000);
                        } else {
                            $('body').toast({
                                class: 'error',
                                message: response.message || 'Fehler beim Versenden'
                            });
                        }
                    }
                });
            }
        }
    }
};

// Initialisierung
$(document).ready(function () {
    // Globale Funktionen für Button-Callbacks
    window.cloneNewsletter = function (params) {
        Newsletter.Actions.cloneNewsletter(params);
    };

    window.sendNewsletter = function (contentId) {
        Newsletter.Actions.sendNewsletter(contentId);
    };

    // Core initialisieren
    Newsletter.Core.init();
});
// Globaler Namespace für Newsletter-Funktionalitäten
window.Newsletter = {
    // Core Funktionalitäten
    Core: {
        updateInterval: null,

        init: function () {
            this.initializeComponents();
            this.startUpdates();
        },

        initializeComponents: function () {
            $('.ui.popup').popup();
            $('.ui.tooltip').popup();
            $('.ui.label').popup();
            $('.ui.progress').progress({
                precision: 1,
                showActivity: false
            });
        },

        startUpdates: function () {
            if (this.updateInterval !== null) {
                console.log('Stopping existing updates before starting new ones');
                this.stopUpdates();
            }

            if ($('.ui.progress:not(.success)').length > 0 || $('[data-stats-id]').length > 0) {
                console.log('Starting new updates');
                this.updateNewsletterData();
                this.updateInterval = setInterval(() => this.updateNewsletterData(), 5000);
            } else {
                console.log('No elements to update found');
            }
        },

        stopUpdates: function () {
            if (this.updateInterval !== null) {
                clearInterval(this.updateInterval);
                this.updateInterval = null;
                console.log('Stopped updates');
            }
        },

        updateNewsletterData: function () {
            $('.ui.progress:not(.success)').each((index, element) => {
                const $progress = $(element);
                const contentId = $progress.data('content-id');
                if (contentId) {
                    this.checkProgress(contentId, $progress);
                }
            });

            $('[data-stats-id]').each((index, element) => {
                const $statsContainer = $(element);
                const contentId = $statsContainer.data('stats-id');
                if (contentId) {
                    this.updateDeliveryStats(contentId, $statsContainer);
                }
            });
        },

        checkProgress: function (contentId, $progress) {
            $.ajax({
                url: 'ajax/check_sending_status.php',
                method: 'GET',
                data: { content_id: contentId },
                dataType: 'json',
                success: (response) => this.handleProgressResponse(response, contentId, $progress),
                error: this.handleError
            });
        },

        handleProgressResponse: function (response, contentId, $progress) {
            if (response.success && response.total > 0) {
                const percent = Math.round((response.sent / response.total) * 100);
                this.updateProgressBar($progress, percent, response.sent, response.total);

                if (percent >= 100) {
                    $progress.addClass('success');
                    setTimeout(() => Newsletter.UI.reloadTable(), 1000);
                }
            }
        },

        updateProgressBar: function ($progress, percent, sent, total) {
            $progress.find('.bar').css('width', percent + '%');
            $progress.find('.label').text(`${sent} von ${total} versendet`);
            $progress.attr('data-percent', percent);
        },

        createStatLabel: function (icon, color, tooltip, percent, count) {
            return `
                <div class="ui tiny ${color} label" data-tooltip="${tooltip}">
                    <i class="${icon} icon"></i> ${percent}% (${count})
                </div>
            `;
        },

        updateDeliveryStats: function (contentId, $statsContainer) {
            $.ajax({
                url: 'ajax/get_delivery_stats.php',
                method: 'GET',
                data: { content_id: contentId },
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        const stats = [];
                        const total = response.total_recipients;

                        if (response.sent_count > 0) {
                            const percent = Math.min(100, Math.round((response.sent_count / total) * 100));
                            stats.push(this.createStatLabel('check', 'gray', 'Versendet', percent, response.sent_count));
                        }

                        const total_opened = response.opened_count + response.clicked_count;
                        if (total_opened > 0) {
                            const percent = Math.min(100, Math.round((total_opened / total) * 100));
                            stats.push(this.createStatLabel('eye', 'blue', 'Newsletter geöffnet (inkl. Klicks)', percent, total_opened));
                        }

                        if (response.clicked_count > 0) {
                            const percent = Math.min(100, Math.round((response.clicked_count / total) * 100));
                            stats.push(this.createStatLabel('mouse pointer', 'teal', 'Links angeklickt', percent, response.clicked_count));
                        }

                        if (response.unsub_count > 0) {
                            const percent = Math.min(100, Math.round((response.unsub_count / total) * 100));
                            stats.push(this.createStatLabel('user times', 'orange', 'Abgemeldet', percent, response.unsub_count));
                        }

                        if (response.failed_count > 0) {
                            const percent = Math.min(100, Math.round((response.failed_count / total) * 100));
                            stats.push(this.createStatLabel('exclamation triangle', 'red', 'Fehler oder Bounces', percent, response.failed_count));
                        }

                        this.updateStatsContainer($statsContainer, stats);
                    }
                },
                error: this.handleError
            });
        },

        updateStatsContainer: function ($container, stats) {
            $container.html(
                stats.length > 0
                    ? '<div class="ui labels">' + stats.join(' ') + '</div>'
                    : '<span class="ui grey text">Keine Statistiken verfügbar</span>'
            );
            $container.find('.ui.label').popup();
        },

        handleError: function (xhr, status, error) {
            console.error('Ajax Error:', { status, error });
            Newsletter.UI.showErrorToast('Fehler bei der Anfrage: ' + error);
        }
    },

    // UI Funktionalitäten
    UI: {
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
            Newsletter.Core.stopUpdates();

            if (Newsletter.Core.updateInterval) {
                clearInterval(Newsletter.Core.updateInterval);
                Newsletter.Core.updateInterval = null;
            }

            if (typeof standardReloadTable === 'function') {
                standardReloadTable();
            }

            setTimeout(() => {
                if (Newsletter.Core.updateInterval === null) {
                    Newsletter.Core.startUpdates();
                }
            }, 1000);
        },

        showConfirmDialog: function (message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
    },

    // Action Funktionalitäten
    Actions: {
        sendNewsletter: function (id) {
            Newsletter.UI.showConfirmDialog('Möchten Sie diesen Newsletter jetzt versenden?', () => {
                $.ajax({
                    url: 'ajax/send_newsletter.php',
                    method: 'POST',
                    data: { content_id: id },
                    dataType: 'json',
                    success: this.handleSendResponse,
                    error: Newsletter.Core.handleError
                });
            });
        },

        handleSendResponse: function (response) {
            if (response.success === true) {
                Newsletter.UI.showSuccessToast(response.message || 'Newsletter wird versendet');
                setTimeout(Newsletter.UI.reloadTable, 2100);
            } else {
                Newsletter.UI.showErrorToast(response.message || 'Fehler beim Versenden');
            }
        },

        sendTestMail: function (params) {
            $.ajax({
                url: 'exec/send_test_mail.php',
                method: 'POST',
                data: { content_id: params.content_id },
                dataType: 'json',
                success: this.handleTestMailResponse,
                error: Newsletter.Core.handleError
            });
        },

        handleTestMailResponse: function (data) {
            if (data.success) {
                Newsletter.UI.showSuccessToast(data.message || 'Test-Mail wurde gesendet');
                Newsletter.UI.reloadTable();
            } else {
                Newsletter.UI.showErrorToast(data.message || 'Fehler beim Senden der Test-Mail');
            }
        },

        cloneNewsletter: function (params) {
            $.ajax({
                url: 'ajax/clone_newsletter.php',
                method: 'POST',
                data: { content_id: params.content_id },
                dataType: 'json',
                success: this.handleCloneResponse,
                error: Newsletter.Core.handleError
            });
        },

        handleCloneResponse: function (data) {
            if (data.status === 'success') {
                Newsletter.UI.showSuccessToast(data.message || 'Newsletter erfolgreich dupliziert');
                Newsletter.UI.reloadTable();
            } else {
                Newsletter.UI.showErrorToast(data.message || 'Fehler beim Duplizieren des Newsletters');
            }
        }
    }
};

// Initialisierung beim Laden der Seite
$(document).ready(function () {
    // Globale Funktionen für Button-Callbacks
    window.sendNewsletter = function (id) {
        Newsletter.Actions.sendNewsletter(id);
    };

    window.sendTestMail = function (params) {
        Newsletter.Actions.sendTestMail(params);
    };

    window.cloneNewsletter = function (params) {
        Newsletter.Actions.cloneNewsletter(params);
    };

    // Initialisiere Core-Funktionalitäten
    Newsletter.Core.init();
});
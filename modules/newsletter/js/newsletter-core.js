// Newsletter Core Funktionalitäten
const NewsletterCore = {
    // Update Management
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

        // Attachment-Informationen initial laden
        $('.attachment-info').each(function () {
            var $this = $(this);
            var contentId = $this.data('content-id');
            NewsletterCore.handleAttachmentInfo(contentId, $this);
        });
    },

    handleAttachmentInfo: function (contentId, $container) {
        $.ajax({
            url: 'ajax/get_attachment_info.php',
            data: { content_id: contentId },
            success: function (response) {
                if (response.count > 0) {
                    $container
                        .html(response.count + ' Datei(en) (' + response.size + ' MB)')
                        .removeClass('grey red')
                        .addClass('ui blue text');
                } else {
                    $container
                        .html('Keine Anhänge')
                        .removeClass('blue red')
                        .addClass('ui grey text');
                }
            },
            error: function () {
                $container
                    .html('Fehler beim Laden')
                    .removeClass('blue grey')
                    .addClass('ui red text');
            }
        });
    },

    startUpdates: function () {
        if (this.updateInterval !== null) {
            console.log('Updates already running');
            return;
        }

        if ($('.ui.progress:not(.success)').length > 0 || $('[data-stats-id]').length > 0) {
            this.updateNewsletterData();
            this.updateInterval = setInterval(() => this.updateNewsletterData(), 5000);
            console.log('Started updates');
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
        $('.ui.progress:not(.success)').each(function () {
            var $progress = $(this);
            var contentId = $progress.data('content-id');
            if (contentId) {
                NewsletterCore.checkProgress(contentId, $progress);
            }
        });

        $('[data-stats-id]').each(function () {
            var $statsContainer = $(this);
            var contentId = $statsContainer.data('stats-id');
            if (contentId) {
                NewsletterCore.updateDeliveryStats(contentId, $statsContainer);
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
                setTimeout(() => NewsletterUI.reloadTable(), 1000);
            }
        }
    },

    updateProgressBar: function ($progress, percent, sent, total) {
        $progress.find('.bar').css('width', percent + '%');
        $progress.find('.label').text(sent + ' von ' + total + ' versendet');
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

                    // Versandstatistik
                    if (response.sent_count > 0) {
                        const percent = Math.min(100, Math.round((response.sent_count / total) * 100));
                        stats.push(this.createStatLabel('check', 'gray', 'Versendet', percent, response.sent_count));
                    }

                    // Öffnungsstatistik
                    const total_opened = response.opened_count + response.clicked_count;
                    if (total_opened > 0) {
                        const percent = Math.min(100, Math.round((total_opened / total) * 100));
                        stats.push(this.createStatLabel('eye', 'blue', 'Newsletter geöffnet (inkl. Klicks)', percent, total_opened));
                    }

                    // Klickstatistik
                    if (response.clicked_count > 0) {
                        const percent = Math.min(100, Math.round((response.clicked_count / total) * 100));
                        stats.push(this.createStatLabel('mouse pointer', 'teal', 'Links angeklickt', percent, response.clicked_count));
                    }

                    // Abmeldungsstatistik
                    if (response.unsub_count > 0) {
                        const percent = Math.min(100, Math.round((response.unsub_count / total) * 100));
                        stats.push(this.createStatLabel('user times', 'orange', 'Abgemeldet', percent, response.unsub_count));
                    }

                    // Fehlerstatistik
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
        NewsletterUI.showErrorToast('Fehler bei der Anfrage: ' + error);
    }
};
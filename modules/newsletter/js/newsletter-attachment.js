// newsletter-attachment.js
window.Newsletter = window.Newsletter || {};
window.Newsletter.AttachmentManager = {
    attachmentInfoElements: null,

    init() {
        this.attachmentInfoElements = $('.attachment-info');
        this.initializeAttachments();
    },

    initializeAttachments() {
        if (this.attachmentInfoElements.length === 0) {
            console.log('Keine Attachment-Elemente gefunden');
            return;
        }

        this.attachmentInfoElements.each((index, element) => {
            this.initializeSingleAttachment(element);
        });
    },

    initializeSingleAttachment(element) {
        const $element = $(element);
        const contentId = $element.data('content-id');

        if (!contentId) {
            console.warn('Content-ID fehlt für Attachment-Element');
            return;
        }

        this.updateAttachmentInfo(contentId, $element);
    },

    updateAttachmentInfo(contentId, $container) {
        $.ajax({
            url: 'ajax/get_attachment_info.php',
            data: { content_id: contentId },
            success: (response) => this.handleAttachmentSuccess(response, $container),
            error: () => this.handleAttachmentError($container)
        });
    },

    handleAttachmentSuccess(response, $container) {
        if (response.count > 0) {
            $container
                .html(`${response.count} Datei(en) (${response.size} MB)`)
                .removeClass('grey red')
                .addClass('ui blue text');
        } else {
            $container
                .html('Keine Anhänge')
                .removeClass('blue red')
                .addClass('ui grey text');
        }
    },

    handleAttachmentError($container) {
        $container
            .html('Fehler beim Laden')
            .removeClass('blue grey')
            .addClass('ui red text');
    }
};

// Initialisierung wenn das Dokument geladen ist
$(document).ready(function () {
    Newsletter.AttachmentManager.init();
});
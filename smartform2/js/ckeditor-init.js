function initializeCKEditor(editorId, config, uploadUrl) {
    var editorElement = document.querySelector('#' + editorId);
    if (!editorElement || editorElement.dataset.editorInitialized) {
        console.log('CKEditor already initialized or element not found:', editorId);
        return;
    }

    DecoupledEditor
        .create(editorElement, {
            placeholder: config.placeholder || 'Geben Sie hier Ihren Text ein...',
            toolbar: config.toolbar || {
                items: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    '|',
                    'imageUpload',
                    'imageTextAlternative',
                    'blockQuote',
                    'insertTable',
                    'undo',
                    'redo'
                ]
            },
            image: {
                toolbar: [
                    'imageTextAlternative',
                    '|',
                    'imageStyle:alignLeft',
                    'imageStyle:alignCenter',
                    'imageStyle:alignRight',
                    '|',
                    'imageStyle:full',
                    'imageStyle:side',
                    '|',
                    'imageResize:custom',  // Ermöglicht freie Skalierung
                    'imageResize'
                ],
                styles: [
                    'full',
                    'side',
                    'alignLeft',
                    'alignCenter',
                    'alignRight'
                ],
                resizeUnit: 'px',  // Änderung von '%' zu 'px'
                resizeOptions: [
                    {
                        name: 'imageResize:original',
                        value: null,
                        label: 'Original'
                    },
                    {
                        name: 'imageResize:200',
                        value: '200',
                        label: '200px'
                    },
                    {
                        name: 'imageResize:400',
                        value: '400',
                        label: '400px'
                    },
                    {
                        name: 'imageResize:600',
                        value: '600',
                        label: '600px'
                    },
                    {
                        name: 'imageResize:800',
                        value: '800',
                        label: '800px'
                    }
                ],
                upload: {
                    types: config.image && config.image.upload && config.image.upload.types || ['jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'],
                    maxFileSize: config.image && config.image.upload && config.image.upload.maxFileSize || 5 * 1024 * 1024
                }
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            },
            language: 'de',
            licenseKey: '',
        })
        .then(function (editor) {
            console.log('CKEditor instance created:', editorId);
            editorElement.dataset.editorInitialized = 'true';

            var formId = $(editorElement).closest('form').attr('id');
            if (!window.editorInstances) {
                window.editorInstances = {};
            }
            if (!window.editorInstances[formId]) {
                window.editorInstances[formId] = {};
            }
            window.editorInstances[formId][editorId] = editor;

            var toolbarContainer = document.querySelector('#' + editorId + '-toolbar');
            toolbarContainer.appendChild(editor.ui.view.toolbar.element);
            $(toolbarContainer).addClass('editor-toolbar');

            var editorContainer = $('#' + editorId + '-container');
            editorContainer.addClass('editor-container');

            editor.plugins.get('FileRepository').createUploadAdapter = function (loader) {
                console.log('Upload config:', config.image.upload);
                return new UploadAdapter(loader, uploadUrl, config.image.upload);
            };

            var adjustHeight = function () {
                adjustEditorHeight($(editorElement), editorContainer, config);
            };
            adjustHeight();
            $(window).on('resize', adjustHeight);
            editor.model.document.on('change:data', function () {
                adjustHeight();
                $(editorElement).trigger('input');
            });

            editor.model.document.on('change:data', function () {
                var content = editor.getData();
                var minLength = config.minLength || 0;
                var maxLength = config.maxLength || Infinity;

                if (content.length < minLength) {
                    showFieldError(editorId, config.minLength_error || 'Mindestlänge nicht erreicht');
                } else if (content.length > maxLength) {
                    showFieldError(editorId, config.maxLength_error || 'Maximallänge überschritten');
                } else {
                    clearFieldError(editorId);
                }
            });
        })
        .catch(function (error) {
            console.error('CKEditor5 error:', error);
        });
}

function UploadAdapter(loader, uploadUrl, config) {
    this.loader = loader;
    this.uploadUrl = uploadUrl;
    this.config = config;
}

UploadAdapter.prototype.upload = function () {
    return this.loader.file
        .then(file => new Promise((resolve, reject) => {
            const data = new FormData();
            data.append('upload', file);

            // Nur die ursprüngliche Konfiguration senden
            const cleanConfig = {
                types: this.config.types,
                maxFileSize: this.config.maxFileSize,
                path: this.config.path
            };
            data.append('ckEditorConfig', JSON.stringify(cleanConfig));

            fetch(this.uploadUrl, {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(result => {
                    if (result.uploaded) {
                        resolve({ default: result.url });
                    } else {
                        reject(result.error);
                    }
                })
                .catch(error => reject(error));
        }));
};

function adjustEditorHeight(editorElement, editorContainer, config) {
    var minHeight = config.minHeight || 200;
    var maxHeight = config.maxHeight || 600;

    var contentHeight = editorElement[0].scrollHeight;

    var newHeight = Math.min(Math.max(contentHeight, minHeight), maxHeight);

    editorContainer.height(newHeight);
    editorElement.height(newHeight).css('max-height', newHeight);

    editorElement.css('overflow', newHeight === maxHeight ? 'auto' : 'hidden');
}

function showFieldError(fieldId, errorMessage) {
    var field = $('#' + fieldId).closest('.field');
    field.addClass('error');
    var errorElement = $('#' + fieldId + '-error');
    if (errorElement.length === 0) {
        $('<div id="' + fieldId + '-error" class="ui basic red pointing prompt label transition visible">' + errorMessage + '</div>').insertAfter($('#' + fieldId));
    } else {
        errorElement.text(errorMessage);
    }
}

function clearFieldError(fieldId) {
    var field = $('#' + fieldId).closest('.field');
    field.removeClass('error');
    $('#' + fieldId + '-error').remove();
}
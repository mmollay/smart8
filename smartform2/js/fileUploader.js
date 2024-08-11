class FileUploader {

    constructor(config) {
        this.config = config;
        this.translations = config.translations;
        this.basePath = config.basePath || '';
        this.uploadDir = config.UPLOAD_DIR || '../uploads/'; // Verwenden Sie das übergebene UPLOAD_DIR
        this.filesToUpload = [];
        this.initStyles();
        this.initElements();
        this.setAcceptedFormats();
        this.bindEvents();
        this.loadFileList();
        this.onFileListChange = config.onFileListChange;
    }

    triggerFileListChange() {

        // if (typeof this.onFileListChange === 'function') {
        alert('triggerFileListChange');
        const fileList = this.filesToUpload.map(file => ({
            name: file.split('/').pop(),
            type: file.split('.').pop().toLowerCase(),
            size: this.getFileSize(file.split('/').pop())
        }));
        this.onFileListChange(fileList);

        // }
    }

    // Fügen Sie diese neue Methode hinzu
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        // Hier können Sie auch andere Teile aktualisieren, die von der Konfiguration abhängen
        this.setAcceptedFormats();
        // Möglicherweise müssen Sie auch andere Methoden aufrufen, um die UI zu aktualisieren
    }


    setAcceptedFormats() {
        const acceptedFormats = this.config.ALLOWED_FORMATS.map(format => '.' + format).join(',');
        this.fileInput.setAttribute('accept', acceptedFormats);
    }

    initStyles() {
        const style = document.createElement('style');
        style.textContent = `
            #${this.config.dropZoneId} {
                border: 2px dashed #2185d0;
                padding: 40px; 
                background-color: #f9fafb;
                text-align: center;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            #${this.config.dropZoneId}.dragover {
                background-color: #f8f8f8;
            }
            .file-list-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                margin-bottom: 10px;
            }
            .file-list-item .file-icon {
                font-size: 24px;
                width: 40px;
                text-align: center;
            }
            .file-list-item .file-name {
                flex-grow: 1;
                padding: 0 10px;
                word-break: break-all;
                color: #2185d0;
                text-decoration: none;
            }
            .file-list-item .file-name:hover {
                text-decoration: underline;
            }
            .file-list-item .file-size {
                margin: 0 10px;
                color: #666;
                font-size: 0.9em;
            }
            .file-list-item .delete-button {
                padding: 5px 10px;
                font-size: 0.9em;
            }
            .total-size {
                margin-top: 20px;
                font-weight: bold;
                text-align: right;
            }
            #${this.config.deleteAllButtonId} {
                float: right;
                font-size: 0.9em;
                padding: 5px 10px;
                margin-top: 0px;
            }
        `;
        document.head.appendChild(style);
    }

    initElements() {
        this.dropZone = document.getElementById(this.config.dropZoneId);
        this.fileInput = document.getElementById(this.config.fileInputId);
        this.fileList = document.getElementById(this.config.fileListId);
        this.deleteAllButton = document.getElementById(this.config.deleteAllButtonId);
        this.progressContainer = document.getElementById(this.config.progressContainerId);
        this.progressBar = document.getElementById(this.config.progressBarId);
    }

    bindEvents() {
        this.dropZone.addEventListener('dragover', (e) => this.handleDragOver(e));
        this.dropZone.addEventListener('dragleave', () => this.handleDragLeave());
        this.dropZone.addEventListener('drop', (e) => this.handleDrop(e));
        this.dropZone.addEventListener('click', () => this.fileInput.click());
        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        if (this.deleteAllButton) {
            this.deleteAllButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.deleteAllFiles();
            });
        }
    }

    handleDragOver(e) {
        e.preventDefault();
        this.dropZone.classList.add('dragover');
    }

    handleDragLeave() {
        this.dropZone.classList.remove('dragover');
    }

    handleDrop(e) {
        e.preventDefault();
        this.dropZone.classList.remove('dragover');
        this.handleFiles(e.dataTransfer.files);
    }

    handleFileSelect(e) {
        this.handleFiles(e.target.files);
    }

    async handleFiles(files) {
        if (this.filesToUpload.length + files.length > this.config.MAX_FILE_COUNT) {
            this.showToast('error', this.translations.max_files_reached.replace('{0}', this.config.MAX_FILE_COUNT));
            return;
        }

        for (const file of files) {
            if (!this.isAllowedFileType(file.name)) {
                this.showToast('error', `${file.name} ${this.translations.invalid_format}`);
                continue;
            }

            if (file.size > this.config.MAX_FILE_SIZE) {
                this.showToast('error', `${file.name} ${this.translations.file_too_large} ${this.formatFileSize(this.config.MAX_FILE_SIZE)}.`);
                continue;
            }

            await this.uploadFile(file);
        }

        this.loadFileList();
        this.triggerFileListChange();
    }

    isAllowedFileType(fileName) {
        const fileExtension = fileName.split('.').pop().toLowerCase();
        return this.config.ALLOWED_FORMATS.includes(fileExtension);
    }

    uploadFile(file) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            formData.append('file', file);
            formData.append('config', JSON.stringify(this.config));

            xhr.open('POST', this.basePath + 'upload.php', true);

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    this.updateProgressBar(percentComplete);
                }
            };

            xhr.onload = () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        this.showToast('success', response.message);
                    } else {
                        this.showToast('error', response.message);
                    }
                } else {
                    this.showToast('error', this.translations.upload_error + xhr.statusText);
                }
                this.hideProgressBar();
                resolve();
            };

            xhr.onerror = () => {
                this.showToast('error', this.translations.network_error);
                this.hideProgressBar();
                reject();
            };

            this.showProgressBar();
            xhr.send(formData);
        });
    }

    deleteFile(fileName) {
        const formData = new FormData();
        formData.append('file', fileName);
        formData.append('config', JSON.stringify(this.config));

        fetch(this.basePath + 'delete_file.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.loadFileList();
                    this.showToast('success', data.message);
                } else {
                    this.showToast('error', data.message);
                }
            })
            .catch(error => {
                this.showToast('error', this.translations.delete_error + error);
            })

            .then(() => {
                this.triggerFileListChange();
            });


    }

    deleteAllFiles() {
        if (confirm(this.translations.delete_confirm)) {
            const formData = new FormData();
            formData.append('config', JSON.stringify(this.config));

            fetch(this.basePath + 'delete_all_files.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' || data.status === 'partial') {
                        this.loadFileList();
                        this.showToast(data.status === 'success' ? 'success' : 'warning', data.message);
                    } else {
                        this.showToast('error', data.message);
                    }
                })
                .catch(error => {
                    this.showToast('error', this.translations.delete_all_error + error);
                })

                .then(() => {
                    this.triggerFileListChange();
                });
        }
    }

    loadFileList() {
        const formData = new FormData();
        formData.append('config', JSON.stringify(this.config));

        fetch(this.basePath + 'load_files.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    this.showToast('error', data.error);
                } else {
                    this.filesToUpload = data.files;
                    this.renderFileList();
                }
            })
            .catch(error => {
                this.showToast('error', this.translations.load_error + error);
            });
    }

    async renderFileList() {
        this.fileList.innerHTML = '';
        let totalSize = 0;

        for (const file of this.filesToUpload) {
            const fileName = file.split('/').pop();
            const filePath = file;
            const fileType = fileName.split('.').pop().toLowerCase();

            let fileSize = await this.getFileSize(fileName);
            totalSize += fileSize;

            const listItem = document.createElement('div');
            listItem.className = 'file-list-item';

            const fileIcon = document.createElement('i');
            fileIcon.className = this.getFileIcon(fileType) + ' icon file-icon';

            const fileLink = document.createElement('a');
            fileLink.className = 'file-name';
            fileLink.href = filePath;
            fileLink.target = '_blank';
            fileLink.textContent = fileName;

            const fileSizeSpan = document.createElement('span');
            fileSizeSpan.className = 'file-size';
            fileSizeSpan.textContent = ` (${this.formatFileSize(fileSize)})`;

            const deleteButton = document.createElement('button');
            deleteButton.className = 'ui red button delete-button';
            deleteButton.textContent = this.translations.delete;
            deleteButton.onclick = (e) => {
                e.preventDefault();
                this.deleteFile(fileName);
            };

            listItem.appendChild(fileIcon);
            listItem.appendChild(fileLink);
            listItem.appendChild(fileSizeSpan);
            listItem.appendChild(deleteButton);
            this.fileList.appendChild(listItem);
        }

        const totalSizeElement = document.createElement('div');
        totalSizeElement.className = 'total-size';
        totalSizeElement.textContent = `${this.translations.total_size}: ${this.formatFileSize(totalSize)} (${(totalSize / (1024 * 1024)).toFixed(2)} MB)`;

        this.fileList.appendChild(totalSizeElement);

        this.showDeleteAllButton();
    }

    getFileSize(fileName) {
        const formData = new FormData();
        formData.append('config', JSON.stringify(this.config));
        formData.append('file', fileName);
        return fetch(this.basePath + 'get_file_size.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    this.showToast('error', data.error);
                    return 0;
                }
                return data.size;
            })
            .catch(error => {
                this.showToast('error', this.translations.load_error + error);
                return 0;
            });
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    getFileIcon(fileType) {
        switch (fileType) {
            case 'pdf': return 'file pdf outline';
            case 'doc':
            case 'docx': return 'file word outline';
            case 'xls':
            case 'xlsx': return 'file excel outline';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif': return 'file image outline';
            default: return 'file outline';
        }
    }

    showToast(status, message) {
        $('body').toast({
            message: message,
            class: status,
            showProgress: 'bottom',
            classProgress: status === 'success' ? 'green' : 'red'
        });
    }

    showProgressBar() {
        this.progressContainer.style.display = 'block';
        this.updateProgressBar(0);
    }

    updateProgressBar(percent) {
        this.progressBar.style.width = percent + '%';
    }

    hideProgressBar() {
        this.progressContainer.style.display = 'none';
    }

    showDeleteAllButton() {
        if (this.config.showDeleteAllButton && this.deleteAllButton) {
            if (this.filesToUpload.length > 0) {
                this.deleteAllButton.style.display = 'block';
                this.deleteAllButton.textContent = this.translations.delete_all;
            } else {
                this.deleteAllButton.style.display = 'none';
            }
        }
    }
}

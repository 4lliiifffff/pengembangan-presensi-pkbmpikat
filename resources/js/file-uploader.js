/**
 * Standardized File Uploader Module
 * PKBM Pintar Berbakat
 * 
 * Provides unified drag-and-drop, real-time image thumbnail preview,
 * file-type badge indicators (Spreadsheet, PDF, Image), file validation,
 * and clear actions across all views.
 */

(function () {
    'use strict';

    /**
     * Format bytes into human readable string (KB / MB)
     */
    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        if (bytes < k * 1024) {
            return Math.round(bytes / k) + ' KB';
        }
        return (bytes / (k * k)).toFixed(2) + ' MB';
    }

    /**
     * Determine file category based on extension
     */
    function getFileCategory(filename) {
        const ext = (filename.split('.').pop() || '').toLowerCase();
        if (['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(ext)) {
            return 'image';
        }
        if (['xlsx', 'xls', 'csv'].includes(ext)) {
            return 'excel';
        }
        if (ext === 'pdf') {
            return 'pdf';
        }
        if (['doc', 'docx'].includes(ext)) {
            return 'word';
        }
        return 'general';
    }

    /**
     * Render the standardized feedback card inside a target element
     */
    function renderFeedbackCard(container, file, inputElement, isError, errorMessage) {
        if (!container) return;

        if (isError) {
            container.className = 'fileUploadFeedback fileUploadFeedback--error';
            container.innerHTML = `
                <div class="fileUploadFeedback__inner">
                    <div class="fileUploadFeedback__iconBadge fileUploadFeedback__iconBadge--error">
                        <ion-icon name="alert-circle"></ion-icon>
                    </div>
                    <div class="fileUploadFeedback__meta">
                        <div class="fileUploadFeedback__name text-danger font-bold">Gagal Mengunggah Berkas</div>
                        <div class="fileUploadFeedback__desc">${errorMessage || 'Format atau ukuran berkas tidak valid.'}</div>
                    </div>
                    <button type="button" class="fileUploadFeedback__clearBtn" title="Tutup / Ganti Berkas" aria-label="Tutup">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
            `;
            container.style.display = 'block';

            const clearBtn = container.querySelector('.fileUploadFeedback__clearBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (inputElement) inputElement.value = '';
                    container.style.display = 'none';
                    container.innerHTML = '';
                    container.className = 'fileUploadFeedback';
                });
            }
            return;
        }

        const category = getFileCategory(file.name);
        const formattedSize = formatFileSize(file.size);

        container.className = 'fileUploadFeedback fileUploadFeedback--success';

        let previewBadgeHtml = '';
        if (category === 'excel') {
            previewBadgeHtml = `
                <div class="fileUploadFeedback__iconBadge fileUploadFeedback__iconBadge--excel">
                    <ion-icon name="document-text"></ion-icon>
                </div>
            `;
        } else if (category === 'pdf') {
            previewBadgeHtml = `
                <div class="fileUploadFeedback__iconBadge fileUploadFeedback__iconBadge--pdf">
                    <ion-icon name="document-attach"></ion-icon>
                </div>
            `;
        } else if (category === 'image') {
            previewBadgeHtml = `
                <div class="fileUploadFeedback__thumbWrapper">
                    <img class="fileUploadFeedback__thumb" src="" alt="Pratinjau" id="thumb_${Math.random().toString(36).substr(2, 9)}" />
                </div>
            `;
        } else {
            previewBadgeHtml = `
                <div class="fileUploadFeedback__iconBadge fileUploadFeedback__iconBadge--general">
                    <ion-icon name="document-outline"></ion-icon>
                </div>
            `;
        }

        container.innerHTML = `
            <div class="fileUploadFeedback__inner">
                ${previewBadgeHtml}
                <div class="fileUploadFeedback__meta">
                    <div class="fileUploadFeedback__name" title="${file.name}">${file.name}</div>
                    <div class="fileUploadFeedback__details">
                        <span class="fileUploadFeedback__sizePill">${formattedSize}</span>
                        <span class="fileUploadFeedback__statusPill">
                            <ion-icon name="checkmark-circle"></ion-icon> Siap diunggah
                        </span>
                    </div>
                </div>
                <button type="button" class="fileUploadFeedback__clearBtn" title="Hapus / Pilih Berkas Lain" aria-label="Hapus Berkas">
                    <ion-icon name="close-circle-outline"></ion-icon>
                </button>
            </div>
        `;
        container.style.display = 'block';

        // Bind image preview reader if category is image
        if (category === 'image') {
            const thumbImg = container.querySelector('.fileUploadFeedback__thumb');
            if (thumbImg && typeof FileReader !== 'undefined') {
                const reader = new FileReader();
                reader.onload = function (e) {
                    thumbImg.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Bind clear button
        const clearBtn = container.querySelector('.fileUploadFeedback__clearBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (inputElement) {
                    inputElement.value = '';
                    inputElement.dispatchEvent(new Event('change', { bubbles: true }));
                }
                container.style.display = 'none';
                container.innerHTML = '';
                container.className = 'fileUploadFeedback';
            });
        }
    }

    /**
     * Core handler for handling file input change events
     */
    function handleFileSelection(input, feedbackId, customMaxMb) {
        const feedback = typeof feedbackId === 'string' ? document.getElementById(feedbackId) : feedbackId;
        if (!input || !input.files) return;

        if (input.files.length === 0) {
            if (feedback) {
                feedback.style.display = 'none';
                feedback.innerHTML = '';
                feedback.className = 'fileUploadFeedback';
            }
            return;
        }

        const file = input.files[0];
        const category = getFileCategory(file.name);

        // Calculate max allowed size (default 2MB for images/photos, 5MB for excel/docs)
        let maxMb = customMaxMb;
        if (!maxMb) {
            maxMb = (category === 'excel' || category === 'pdf') ? 5 : 2;
        }

        const maxBytes = maxMb * 1024 * 1024;
        if (file.size > maxBytes) {
            const currentSize = formatFileSize(file.size);
            renderFeedbackCard(feedback, file, input, true, `Ukuran berkas (${currentSize}) melebihi batas maksimal ${maxMb} MB.`);
            input.value = '';
            return;
        }

        renderFeedbackCard(feedback, file, input, false);
    }

    /**
     * Auto-bind drag and drop states and events on all .fileUploadBox elements
     */
    function bindFileUploadBox(box) {
        if (!box || box.dataset.uploaderBound === 'true') return;
        box.dataset.uploaderBound = 'true';

        const fileInput = box.querySelector('input[type="file"]');
        if (!fileInput) return;

        // Visual Drag & Drop interactions
        ['dragenter', 'dragover'].forEach(eventName => {
            box.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                box.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'dragend', 'drop'].forEach(eventName => {
            box.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                box.classList.remove('dragover');
            }, false);
        });

        box.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            box.classList.remove('dragover');

            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    /**
     * Initialize all file upload components in DOM
     */
    function initFileUploader() {
        document.querySelectorAll('.fileUploadBox').forEach(bindFileUploadBox);
    }

    // Expose global functions to window for backward compatibility and dynamic calls
    window.handleFileSelected = function (input, feedbackId, maxMb) {
        handleFileSelection(input, feedbackId, maxMb || 2);
    };

    window.handleExcelFileSelected = function (input, feedbackId, maxMb) {
        handleFileSelection(input, feedbackId, maxMb || 5);
    };

    window.initFileUploader = initFileUploader;

    // Auto initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFileUploader);
    } else {
        initFileUploader();
    }
})();

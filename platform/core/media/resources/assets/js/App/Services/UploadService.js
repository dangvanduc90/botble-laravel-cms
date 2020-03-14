import {MediaService} from '../Services/MediaService';
import {Helpers} from '../Helpers/Helpers';

export class UploadService {
    constructor() {
        this.$body = $('body');

        this.dropZone = null;

        this.uploadUrl = RV_MEDIA_URL.upload_file;

        this.uploadProgressBox = $('.rv-upload-progress');

        this.uploadProgressContainer = $('.rv-upload-progress .rv-upload-progress-table');

        this.uploadProgressTemplate = $('#rv_media_upload_progress_item').html();

        this.totalQueued = 1;

        this.MediaService = new MediaService();

        this.totalError = 0;
    }

    init() {
        if (_.includes(RV_MEDIA_CONFIG.permissions, 'files.create') && $('.rv-media-items').length > 0) {
            this.setupDropZone();
        }
        this.handleEvents();
    }

    setupDropZone() {
        let _self = this;
        _self.dropZone = new Dropzone(document.querySelector('.rv-media-items'), {
            url: _self.uploadUrl,
            thumbnailWidth: false,
            thumbnailHeight: false,
            parallelUploads: 1,
            autoQueue: true,
            clickable: '.js-dropzone-upload',
            previewTemplate: false,
            previewsContainer: false,
            uploadMultiple: true,
            sending: (file, xhr, formData) => {
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('folder_id', Helpers.getRequestParams().folder_id);
                formData.append('view_in', Helpers.getRequestParams().view_in);
            },
        });

        _self.dropZone.on('addedfile', file => {
            file.index = _self.totalQueued;
            _self.totalQueued++;
        });

        _self.dropZone.on('sending', file => {
            _self.initProgress(file.name, file.size);
        });

        _self.dropZone.on('complete', file => {
            _self.changeProgressStatus(file);
        });

        _self.dropZone.on('queuecomplete', () => {
            Helpers.resetPagination();
            _self.MediaService.getMedia(true);
            if (_self.totalError === 0) {
                setTimeout(() => {
                    $('.rv-upload-progress .close-pane').trigger('click');
                }, 5000);
            }
        });
    }

    handleEvents() {
        let _self = this;
        /**
         * Close upload progress pane
         */
        _self.$body.off('click', '.rv-upload-progress .close-pane').on('click', '.rv-upload-progress .close-pane', (event) => {
            event.preventDefault();
            $('.rv-upload-progress').addClass('hide-the-pane');
            _self.totalError = 0;
            setTimeout(() => {
                $('.rv-upload-progress li').remove();
                _self.totalQueued = 1;
            }, 300);
        });
    }

    initProgress($fileName, $fileSize) {
        let template = this.uploadProgressTemplate
            .replace(/__fileName__/gi, $fileName)
            .replace(/__fileSize__/gi, UploadService.formatFileSize($fileSize))
            .replace(/__status__/gi, 'warning')
            .replace(/__message__/gi, 'Uploading')
        ;
        this.uploadProgressContainer.append(template);
        this.uploadProgressBox.removeClass('hide-the-pane');
        this.uploadProgressBox.find('.panel-body')
            .animate({scrollTop: this.uploadProgressContainer.height()}, 150);
    }

    changeProgressStatus(file) {
        let _self = this;
        let $progressLine = _self.uploadProgressContainer.find('li:nth-child(' + file.index + ')');
        let $label = $progressLine.find('.label');
        $label.removeClass('label-success label-danger label-warning');

        let response = Helpers.jsonDecode(file.xhr.responseText || '', {});

        _self.totalError = _self.totalError + (response.error === true || file.status === 'error' ? 1 : 0);

        $label.addClass(response.error === true || file.status === 'error' ? 'label-danger' : 'label-success');
        $label.html(response.error === true || file.status === 'error' ? 'Error' : 'Uploaded');
        if (file.status === 'error') {
            if (file.xhr.status === 422) {
                let error_html = '';
                $.each(response.errors, (key, item) => {
                    error_html += '<span class="text-danger">' + item + '</span><br>';
                });
                $progressLine.find('.file-error').html(error_html);
            } else if (file.xhr.status === 500) {
                $progressLine.find('.file-error').html('<span class="text-danger">' + file.xhr.statusText + '</span>');
            }
        } else if (response.error) {
            $progressLine.find('.file-error').html('<span class="text-danger">' + response.message + '</span>');
        } else {
            Helpers.addToRecent(response.data.id);
            Helpers.setSelectedFile(response.data.id);
        }
    }

    static formatFileSize(bytes, si = false) {
        let thresh = si ? 1000 : 1024;
        if (Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        let units = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        let u = -1;
        do {
            bytes /= thresh;
            ++u;
        } while (Math.abs(bytes) >= thresh && u < units.length - 1);
        return bytes.toFixed(1) + ' ' + units[u];
    }
}

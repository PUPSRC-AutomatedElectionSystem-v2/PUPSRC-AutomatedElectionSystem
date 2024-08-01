$(document).ready(function() {
    const maxFileSize = 25 * 1024 * 1024; // 25MB in bytes
    const allowedExtensions = ['csv', 'xls', 'xlsx'];
    const $importButton = $('#import-voters');
    const $fileInput = $('#voters-list');

    // Disable the Import Voters button initially
    $importButton.prop('disabled', true);

    function validateFile(file) {
        if (!file) {
            return false;
        }

        const extension = file.name.split('.').pop().toLowerCase();
        
        if (file.size > maxFileSize || !allowedExtensions.includes(extension)) {
            showInvalidFileFormatModal();
            return false;
        }
        
        return true;
    }

    function handleFileSelect(e) {
        const file = e.target.files[0];
        if (validateFile(file)) {
            $importButton.prop('disabled', false);
        } else {
            $fileInput.val(''); // Clear the file input
            $importButton.prop('disabled', true);
        }
    }

    $fileInput.on('change', handleFileSelect);

    // Drag and drop handling
    $fileInput.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $fileInput.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const file = e.originalEvent.dataTransfer.files[0];
        if (validateFile(file)) {
            // Manually set the file to the input
            $fileInput[0].files = e.originalEvent.dataTransfer.files;
            $importButton.prop('disabled', false);
        } else {
            $fileInput.val(''); // Clear the file input
            $importButton.prop('disabled', true);
        }
    });

    $importButton.on('click', function() {
        var $button = $(this);
        var originalText = $button.text();

        // Change button state to loading
        setButtonLoading($button);

        var formData = new FormData();
        formData.append('file', $fileInput[0].files[0]);

        $.ajax({
            url: 'submission_handlers/import.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(response) {
                console.log("Import response:", response);
                if (response.status === 'success') {
                    $('#importDoneModal').modal('show');
                } else if (response.status === 'error') {
                    if (response.errorType === 'duplicates') {
                        console.log("Duplicates:", response.duplicates);
                        console.log("Invalid IDs:", response.invalidIds);
                        showDuplicatesModal(response);
                    } else if (response.errorType === 'invalid_content') {
                        showInvalidContentModal(response.message);
                    } else {
                        showInvalidContentModal('An unexpected error occurred during the import process.');
                    }
                } else {
                    showInvalidContentModal('An unexpected response was received from the server.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
                showInvalidContentModal('An error occurred while processing your request.');
            }
        });
    });

    function setButtonLoading($button) {
        $button.prop('disabled', true)
               .text('Importing...')
               .addClass('btn-secondary')
               .removeClass('btn-main-primary');
    }

    function resetButton($button, originalText) {
        $button.text(originalText)
               .removeClass('btn-secondary')
               .addClass('btn-main-primary')
               .prop('disabled', true);  // Always disable the button
    }

    function showInvalidFileFormatModal() {
        $('#dangerTitle').text('Invalid file format!');
        $('#dangerSubtitle').text('Excel and CSV files are only allowed. Please also ensure the file is no larger than 25 mb and the file headers are correct. Let\'s try that again!');
        $('#onlyPDFAllowedModal').modal('show');
    }

    function showDuplicatesModal(response) {
        console.log("showDuplicatesModal called with:", response);
        let title = 'Import Failed';
        let content = `<p class="fw-medium spacing-5 pt-2">${response.message}</p>`;

        if (response.duplicates && response.duplicates.length > 0) {
            content += '<h5>Duplicates:</h5><ul>';
            response.duplicates.forEach(duplicate => {
                content += `<li>${duplicate}</li>`;
            });
            content += '</ul>';
        }

        if (response.invalidIds && response.invalidIds.length > 0) {
            content += '<h5>Invalid Column:</h5><ul>';
            response.invalidIds.forEach(id => {
                content += `<li>${id}</li>`;
            });
            content += '</ul>';
        }

        $('#duplicatesTitle').text(title);
        $('#duplicatesList').html(content);
        $('#duplicatesModal').modal('show');
    }

    function showInvalidContentModal(message) {
        $('#invalidContentTitle').text('Import Failed');
        $('#invalidContentSubtitle').html(message);
        $('#invalidContentModal').modal('show');
    }

    // Close button for modals
    $('#importDoneClose, #onlyPDFClose, #invalidContentClose, #duplicatesClose').on('click', function() {
        $(this).closest('.modal').modal('hide');
    });

    // Ensure all modals are initialized
    $('#importDoneModal, #onlyPDFAllowedModal, #invalidContentModal, #duplicatesModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    // Disable button when any modal is shown
    $('.modal').on('show.bs.modal', function () {
        $importButton.prop('disabled', true);
    });

    // Reset button state when any modal is closed
    $('.modal').on('hidden.bs.modal', function () {
        resetButton($importButton, 'Import Voters');
        $fileInput.val(''); // Clear the file input
        $importButton.prop('disabled', true); // Ensure the button is disabled
    });
});
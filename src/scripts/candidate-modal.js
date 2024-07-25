//Discard Modal
$(document).ready(function () {
    feather.replace();

    // Detect changes in form fields
    var isDirty = false;
    var targetUrl = '';

    $('#candidate-form input, #candidate-form select').on('change', function () {
        isDirty = true;
        $('.submit-btn').prop('disabled', false);
    });

    // Handle form submission
    $('#candidate-form').on('submit', function () {
        isDirty = false;
    });

    // Handle cancel button click
    $('.cancel-button').on('click', function (e) {
        if (isDirty) {
            e.preventDefault();
            $('#warningModal').modal('show');
        } else {
            window.location.href = 'add-candidate.php';
        }
    });

    // Handle leave button click in the modal
    $('#leaveButton').on('click', function () {
        isDirty = false;
        window.removeEventListener('beforeunload', showWarningModal);
        window.location.href = targetUrl;
    });

    $('.modal .cancel').on('click', function () {
        $('#warningModal').modal('hide');
    });

    function showWarningModal(e) {
        if (isDirty) {
            e.preventDefault();
            $('#warningModal').modal('show');
            return ''; // Required for some browsers to show the modal
        }
    }

    window.addEventListener('beforeunload', showWarningModal);

    $(document).on('click', 'a', function (e) {
        if (isDirty) {
            e.preventDefault();
            targetUrl = $(this).attr('href');
            $('#warningModal').modal('show');
        }
    });
});
//reset form modal
document.addEventListener("DOMContentLoaded", function () {
    let isFormDirty = false;
    const form = document.querySelector("form");
    const resetButton = document.querySelector("button[type='reset']");
    const resetModal = new bootstrap.Modal(document.getElementById('resetFormModal'));

    // Mark the form as dirty if any input changes
    form.addEventListener("input", function () {
        isFormDirty = true;
    });

    // Show the reset modal on reset button click
    resetButton.addEventListener("click", function (event) {
        if (isFormDirty) {
            event.preventDefault();
            resetModal.show();
        }
    });

    // Handle form reset confirmation
    document.getElementById("confirmReset").addEventListener("click", function () {
        isFormDirty = false;
        form.reset();
        resetModal.hide();
    });
});
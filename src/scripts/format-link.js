$(document).ready(function() {
    // Format link functionality
    const formatLink = document.getElementById('formatLink');
    if (formatLink) {
        formatLink.addEventListener('click', function(event) {
            event.preventDefault();
            $('#formatModal').modal('show');
        });
    }

    // Close button for format modal
    $('#formatModalClose').on('click', function() {
        $('#formatModal').modal('hide');
    });

    // Ensure the format modal is initialized
    $('#formatModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });
});
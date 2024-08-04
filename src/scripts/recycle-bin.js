$(document).ready(function() {
    // Utility function to check if any checkbox is checked
    
    var deleteModeActive = false;
    var restoreModeActive = false;
    function isAnyCheckboxChecked() {
        return $('.select-checkbox:checked').length > 0;
    }

    // Utility function to toggle checkbox visibility
    function toggleCheckboxesVisibility(show) {
        var checkboxes = $('.select-checkbox');
        var selectAllCheckbox = $('#selectAllCheckbox');
        if (show) {
            checkboxes.show();
        } else {
            checkboxes.hide();
            checkboxes.prop('checked', false); // Uncheck all checkboxes when hiding
            selectAllCheckbox.prop('checked', false); // Uncheck the "Select All" checkbox
        }
    }

    // Function to hide delete elements
    function hideDeleteElements() {
        $('#cancelDelete').hide();
        $('#deleteSelectedbtn').hide();
        $('#deleteBtn').removeClass('btn-gray');
        $('.select-checkbox').removeClass('checkbox-red');
        $('#selectAllCheckbox').removeClass('checkbox-red');
        $('#deleteSelectedbtn').prop('disabled', true);
    }

    // Function to hide restore elements
    function hideRestoreElements() {
        $('#cancelRestore').hide();
        $('#restoreSelectedbtn').hide();
        $('#restoreBtn').removeClass('btn-gray');
        $('.select-checkbox').removeClass('checkbox-blue');
        $('#selectAllCheckbox').removeClass('checkbox-blue');
        $('#restoreSelectedbtn').prop('disabled', true);
    }

    // Function to show delete elements
    function showDeleteElements() {
        $('#cancelDelete').show();
        $('#deleteSelectedbtn').show();
        $('#deleteBtn').addClass('btn-gray');
        $('.select-checkbox').addClass('checkbox-red');
        $('#selectAllCheckbox').addClass('checkbox-red');
    }

    // Function to show restore elements
    function showRestoreElements() {
        $('#cancelRestore').show();
        $('#restoreSelectedbtn').show();
        $('#restoreBtn').addClass('btn-gray');
        $('.select-checkbox').addClass('checkbox-blue');
        $('#selectAllCheckbox').addClass('checkbox-blue');
    }

    // Function to handle cancel actions
    function handleCancelActions() {
        deleteModeActive = false;
        restoreModeActive = false;
        hideDeleteElements();
        hideRestoreElements();
        toggleCheckboxesVisibility(false);
        $('.status-dropdown').prop('disabled', false);
        $('#selectAllCheckbox').hide();
    }

    // Function to show warning modal
    function showWarningModal() {
        $('#warningModal').modal('show');
    }

    // Function to update button visibility
    function updateButtonVisibility() {
        if (isAnyCheckboxChecked()) {
            if (deleteModeActive) {
                $('#deleteSelectedbtn').prop('disabled', false);
            }
            if (restoreModeActive) {
                $('#restoreSelectedbtn').prop('disabled', false);
            }
        } else {
            $('#deleteSelectedbtn').prop('disabled', true);
            $('#restoreSelectedbtn').prop('disabled', true);
        }
    }

    // Event handler for checkbox change
    $('.select-checkbox').on('change', function() {
        updateButtonVisibility();
    });

    // Event handler for delete button click
    $('#deleteBtn').on('click', function() {
        if (restoreModeActive) {
            showWarningModal();
            return;
        }

        // Toggle delete mode
        deleteModeActive = !deleteModeActive;

        if (deleteModeActive) {
            showDeleteElements();
            hideRestoreElements();
        } else {
            hideDeleteElements();
        }

        toggleCheckboxesVisibility(deleteModeActive || restoreModeActive);
        $('#selectAllCheckbox').toggle(deleteModeActive || restoreModeActive);
        $('#selectAllCheckbox').data('checked', false);
        $('.status-dropdown').prop('disabled', deleteModeActive || restoreModeActive);

        if (!deleteModeActive && !restoreModeActive) {
            $('#selectAllCheckbox').hide();
        }
    });

    // Event handler for restore button click
    $('#restoreBtn').on('click', function() {
        if (deleteModeActive) {
            showWarningModal();
            return;
        }

        // Toggle restore mode
        restoreModeActive = !restoreModeActive;

        if (restoreModeActive) {
            showRestoreElements();
            hideDeleteElements();
        } else {
            hideRestoreElements();
        }

        toggleCheckboxesVisibility(deleteModeActive || restoreModeActive);
        $('#selectAllCheckbox').toggle(deleteModeActive || restoreModeActive);
        $('#selectAllCheckbox').data('checked', false);
        $('.status-dropdown').prop('disabled', deleteModeActive || restoreModeActive);

        if (!deleteModeActive && !restoreModeActive) {
            $('#selectAllCheckbox').hide();
        }
    });

    // Event handler for cancel delete button click
    $('#cancelDelete').on('click', function() {
        handleCancelActions();
    });

    // Event handler for cancel restore button click
    $('#cancelRestore').on('click', function() {
        handleCancelActions();
    });

    // Event handler for select all checkbox change
    $('#selectAllCheckbox').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.select-checkbox:visible').prop('checked', isChecked);
        updateButtonVisibility();
    });

    function handleDelete() {
        var selectedIds = [];
        $('.select-checkbox:checked:visible').each(function() {
            selectedIds.push($(this).val());
        });
        if (selectedIds.length > 0) {
            $.ajax({
                type: 'POST',
                url: 'submission_handlers/delete-selected-voter.php',
                data: { ids: selectedIds },
                dataType: 'json',
                success: function(response) {
                    console.log('Selected items deleted successfully');
                    $('#deleteSuccessModal').modal('show');
                    $.each(selectedIds, function(index, id) {
                        $('.select-checkbox[value="' + id + '"]').closest('tr').remove();
                    });
                    handleCancelActions();
                    hideDeleteElements();
                    updatePaginationAndDisplay();
                    $('#confirmDeleteInput').val('');
                },
                error: function() {
                    console.error('An error occurred while deleting selected items');
                }
            });
        }
    }

    // Event handler for confirm delete button click
    $('#confirmDeleteButton').on('click', function() {
        handleDelete();
        $('#deleteConfirmationModal').modal('hide');
    });

    // Function to handle restoration of selected items
    function handleRestore() {
        var selectedIds = [];
        $('.select-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        if (selectedIds.length > 0) {
            $.ajax({
                type: 'POST',
                url: 'submission_handlers/restore-selected-voter.php',
                data: { ids: selectedIds },
                dataType: 'json',
                success: function(response) {
                    console.log('Selected items restored successfully');
                    $('#restoreSuccessModal').modal('show');
                    $.each(selectedIds, function(index, id) {
                        $('.select-checkbox[value="' + id + '"]').closest('tr').remove();
                    });

                    handleCancelActions();

                    hideRestoreElements();
                    updatePaginationAndDisplay();
                },
                error: function() {
                    console.error('An error occurred while restoring selected items');
                }
            });
       
        }
    }

    // Event handler for confirm restore button click
    $('#confirmRestoreBtn').on('click', function() {
        handleRestore();
        $('#restoreConfirmationModal').modal('hide');
    });

    // Function to show empty state
    function showEmptyState() {
        var tableContainer = $('.table-responsive');
        var emptyStateHtml = `
            <div class="col-md-12 no-registration text-center">
                <img src="images/resc/folder-empty.png" class="illus">
                <p class="fw-bold spacing-6 black">No accounts deleted</p>
                <p class="spacing-3 pt-1 black fw-medium">Check again once you've deleted an account!</p>
            </div>`;
        tableContainer.html(emptyStateHtml);
    }

    // Function to hide empty state
    function hideEmptyState() {
        $('.no-registration').remove();
    }

    // Pagination settings
    var rowsPerPage = 5;  // Number of rows to show per page
    var paginationControls = $('#pagination-controls');

    // Function to update pagination
    function updatePagination(rows) {
        var rowsCount = rows.length;
        var pageCount = Math.ceil(rowsCount / rowsPerPage);

        paginationControls.empty();

        for (var i = 1; i <= pageCount; i++) {
            paginationControls.append('<li class="page-item"><a href="#" class="page-link">' + i + '</a></li>');
        }

        paginationControls.find('li').eq(0).addClass('active');

        paginationControls.find('a.page-link').on('click', function(e) {
            e.preventDefault();
            var pageNum = $(this).text();
            if (!$(this).parent().hasClass('disabled')) {
                displayPage(parseInt(pageNum), rows);
            }
        });
        $('#previous-page').on('click', function(e) {
            e.preventDefault();
            var activePage = paginationControls.find('.active');
            var activePageNum = parseInt(activePage.text());
            if (activePageNum > 1) {
                displayPage(activePageNum - 1, rows);
            }
        });

        $('#next-page').on('click', function(e) {
            e.preventDefault();
            var activePage = paginationControls.find('.active');
            var activePageNum = parseInt(activePage.text());
            if (activePageNum < pageCount) {
                displayPage(activePageNum + 1, rows);
            }
        });
    }

    // Function to display a specific page
    function displayPage(pageNum, rows) {
        var start = (pageNum - 1) * rowsPerPage;
        var end = start + rowsPerPage;

        $('#selectAllCheckbox').prop('checked', false);
        $('.select-checkbox').prop('checked', false);

        rows.hide().slice(start, end).show();

        paginationControls.find('li').removeClass('active');
        paginationControls.find('li').eq(pageNum - 1).addClass('active');
        $('#previous-page').parent().toggleClass('disabled', pageNum === 1);
        $('#next-page').parent().toggleClass('disabled', end >= rows.length);
    }

    // Function to update pagination and display
    function updatePaginationAndDisplay() {
        var rows = $('#accountTable tbody tr');

        if (rows.length === 0) {
            showEmptyState();
            paginationControls.hide();
        } else {
            hideEmptyState();
            paginationControls.show();
            updatePagination(rows);
            displayPage(1, rows);
        }
    }


    updatePaginationAndDisplay();

    // Function to update buttons state based on the current page
    function updateButtonsState(currentPage, rowsCount) {
        var pageCount = Math.ceil(rowsCount / rowsPerPage);
        $('#previous-page').toggleClass('disabled', currentPage <= 1);
        $('#next-page').toggleClass('disabled', currentPage >= pageCount);
    }

    // Function to filter rows based on search input
    function filterRows() {
        var searchValue = $('#searchInput').val().toLowerCase();
        var allRows = $('.table tbody tr');
        var filteredRows = allRows.filter(function() {
            return $(this).text().toLowerCase().indexOf(searchValue) > -1;
        });

        allRows.hide(); // Hide all rows by default

        if (filteredRows.length === 0) {
            var noRecordsFoundMessage = $('<tr class="no-records-found text-center"><td colspan="4">No records found</td></tr>');
            $('.table tbody').append(noRecordsFoundMessage);
        } else {
            $('.no-records-found').remove();
            filteredRows.show();
            displayPage(1, filteredRows);
            updatePagination(filteredRows);
        }
    }

    // Event handler for search input keyup
    $('#searchInput').on('keyup', function() {
        filterRows();
    });

    // Function to sort table rows based on selected option
    function sortTable(sortOption) {
        var $table = $('.table');
        var rows = $table.find('tbody tr').get();

        rows.sort(function(a, b) {
            var keyA, keyB;

            switch (sortOption) {
                case 'Most to Fewest Days':
                    keyA = parseInt($(a).children('td').eq(2).text().split(' ')[0]);
                    keyB = parseInt($(b).children('td').eq(2).text().split(' ')[0]);
                    return keyB - keyA;
                case 'Fewest to Most Days':
                    keyA = parseInt($(a).children('td').eq(2).text().split(' ')[0]);
                    keyB = parseInt($(b).children('td').eq(2).text().split(' ')[0]);
                    return keyA - keyB;
                case 'A to Z (Ascending)':
                    keyA = $(a).children('td').eq(1).text().toUpperCase();
                    keyB = $(b).children('td').eq(1).text().toUpperCase();
                    return keyA.localeCompare(keyB);
                case 'Z to A (Descending)':
                    keyA = $(a).children('td').eq(1).text().toUpperCase();
                    keyB = $(b).children('td').eq(1).text().toUpperCase();
                    return keyB.localeCompare(keyA);
                default:
                    return 0;
            }
        });

        $.each(rows, function(index, row) {
            $table.children('tbody').append(row);
        });
    }

    // Event handler for dropdown item click
    $('.dropdown-item').on('click', function() {
        var sortOption = $(this).text().trim(); // Get the text of the clicked option
        sortTable(sortOption);
        filterRows(); // Apply search filter after sorting
    });

    // Event handler for delete button click in the confirmation modal
    $('#deleteSelectedbtn').on('click', function() {
        $('#deleteConfirmationModal').modal('show');
    });

    // Event handler for restore button click in the confirmation modal
    $('#restoreSelectedbtn').on('click', function() {
        $('#restoreConfirmationModal').modal('show');
    });

    // Event handler for confirm delete button click
    $('#confirmDeleteButton').on('click', function() {
        handleDelete();
        $('#deleteConfirmationModal').modal('hide');
    });

    // Event handler for confirm restore button click
    $('#confirmRestoreBtn').on('click', function() {
        handleRestore();
        $('#restoreConfirmationModal').modal('hide');
    });

    // Validation for confirm delete input field
    var isHovered = false;

    function updateDeleteButtonState() {
        var inputValue = $('#confirmDeleteInput').val().trim();
        if (inputValue === 'Confirm Delete') {
            enableDeleteButton();
        } else {
            disableDeleteButton();
        }
    }

    function enableDeleteButton() {
        $('#confirmDeleteButton').prop('disabled', false);
    }

    function disableDeleteButton() {
        $('#confirmDeleteButton').prop('disabled', true);
    }

    updateDeleteButtonState();

    $('#confirmDeleteButton-container').hover(function() {
        isHovered = true;
        updateDeleteButtonState();
    }, function() {
        isHovered = false;
        updateDeleteButtonState();
    });

    $('#confirmDeleteInput').on('input', function() {
        if (!isHovered) {
            updateDeleteButtonState();
        }
    });

    function showValidationMessage() {
        $('#confirmDeleteInput').addClass('input-error');
        $('.validation-message').show();
    }

    function hideValidationMessage() {
        $('#confirmDeleteInput').removeClass('input-error');
        $('.validation-message').hide();
    }

    $('#confirmDeleteButton-container').hover(function() {
        var inputValue = $('#confirmDeleteInput').val().trim();
        if (inputValue !== 'Confirm Delete') {
            showValidationMessage();
        }
    }, function() {
        if (!isHovered) {
            hideValidationMessage();
        }
    });

    $('#showDeleteSuccessModal').on('click', function() {
        $('#warningModal').modal('show');
    });

    // Initialize
    $('#deleteSelectedbtn, #cancelDelete, #restoreSelectedbtn, #cancelRestore').hide();
    $('#deleteSelectedbtn, #restoreSelectedbtn').prop('disabled', true);
    $('#selectAllCheckbox').hide();
});

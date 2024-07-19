var toastElement = document.getElementById('myToast');
var myToast = new bootstrap.Toast(toastElement);

// toggle button for voting guidelines
document.getElementById("toggleButton").addEventListener("click", function() {
    this.classList.add("clicked");
    document.getElementById("title").classList.add("main-bg-color");
    setTimeout(function() {
        // Remove the 'clicked' class from the button
        document.getElementById("toggleButton").classList.remove("clicked");
        // Remove the 'main-bg-color' class from the title
        document.getElementById("title").classList.remove("main-bg-color");
    }, 1000);
});

document.addEventListener('DOMContentLoaded', function() {
    // Function to limit checkbox selection based on max_votes
    function limitCheckboxSelection() {
        // Select all candidate checkboxes for voting
        var candidateCheckboxes = document.querySelectorAll('input[type="checkbox"][name^="position["][value]');
        // Select all abstain checkboxes
        var abstainCheckboxes = document.querySelectorAll('.abstain-checkbox');

        candidateCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var positionId = this.name.match(/\[(.*?)\]/)[1]; // Extract position_id from checkbox name
                var maxVotes = parseInt(this.getAttribute('data-max-votes')); // Get max_votes for the position
                var checkedCandidateCheckboxes = document.querySelectorAll('input[type="checkbox"][name="position[' + positionId + '][]"]:checked'); // Checked candidate checkboxes for this position
                var abstainCheckbox = document.querySelector('#abstain_' + positionId); // Abstain checkbox for this position
        
                // If a candidate checkbox is checked, uncheck the abstain checkbox
                if (abstainCheckbox && this.checked) {
                    abstainCheckbox.checked = false;
                }
        
                // Check if the number of checked checkboxes exceeds max_votes
                if (checkedCandidateCheckboxes.length > maxVotes) {
                    this.checked = false; // Uncheck the current checkbox
        
                    myToast.show();
                }
            });
        });

        abstainCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('click', function() {
                var positionId = this.getAttribute('data-position-id'); // Extract position_id from data attribute
                var candidateCheckboxes = document.querySelectorAll('input[type="checkbox"][name="position[' + positionId + '][]"][value]'); // Candidate checkboxes for this position

                // Uncheck all candidate checkboxes if abstain is checked
                if (this.checked) {
                    candidateCheckboxes.forEach(function(candidateCheckbox) {
                        candidateCheckbox.checked = false;
                    });
                }
            });
        });
    }

    // Execute the function to limit checkbox selection once the document is loaded
    limitCheckboxSelection();
});

// Add event listeners to checkboxes and "ABSTAIN" radio button to update error messages
var reminders = document.querySelectorAll('.reminder');
reminders.forEach(function(reminder) {
    var checkboxes = reminder.querySelectorAll('input[type="checkbox"]');
    var abstainRadio = reminder.querySelector('input[type="radio"].abstain-checkbox');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            removeErrorState(reminder); // Just remove error state on change
        });
    });

    if (abstainRadio) {
        abstainRadio.addEventListener('change', function() {
            removeErrorState(reminder); // Just remove error state on change
        });
    }
});

function removeErrorState(reminder) {
    var reminderError = reminder.querySelector('.text-danger');
    if (reminderError) {
        reminder.removeChild(reminderError);
    }
    reminder.classList.remove('border', 'border-danger');
}

function updateErrorState(reminder) {
    var checkboxes = reminder.querySelectorAll('input[type="checkbox"]');
    var abstainRadio = reminder.querySelector('input[type="radio"].abstain-checkbox');
    var isChecked = false;

    checkboxes.forEach(function(cb) {
        if (cb.checked) {
            isChecked = true;
        }
    });

    if (isChecked || (abstainRadio && abstainRadio.checked)) {
        var reminderError = reminder.querySelector('.text-danger');
        if (reminderError) {
            reminder.removeChild(reminderError);
        }
        reminder.classList.remove('border', 'border-danger');
    } else {
        var reminderError = reminder.querySelector('.text-danger');
        if (!reminderError) {
            var errorText = document.createElement('div');
            errorText.classList.add('text-danger', 'mt-4', 'ps-lg-4', 'ps-sm-2', 'ps-2', 'ms-2', 'ms-lg-4', 'ms-sm-2', 'me-4');
            errorText.innerHTML = "<span style='font-size:13px'><i>This field is required. Please select one (1) candidate or click ABSTAIN.</i></span>";
            reminder.insertBefore(errorText, reminder.firstChild);
        }
        reminder.classList.add('border', 'border-danger');
    }
}



function validateForm(event) {
  event.preventDefault(); // Prevent form submission
  
  var voteForm = document.getElementById('voteForm');
  var reminders = voteForm.querySelectorAll('.reminder');
  var isValid = true;
  var scrollToReminder = null;
  var selectedCandidateHTML = '';


  // Validate each position
  reminders.forEach(function(reminder) {
      updateErrorState(reminder);

      var positionTitle = reminder.getAttribute('data-position-title');
      var candidateHTML = '';
      var pairCounter = 0;

     // Build HTML for selected candidates for each position
      var checkboxes = reminder.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(function(checkbox) {
          if (checkbox.checked) {
              var candidateName = checkbox.parentNode.querySelector('div.ps-4 > div.font-weight2').textContent.trim();
              var imageSrc = checkbox.getAttribute('data-img-src');

              // Create HTML for each candidate with vertically centered content
              candidateHTML += '<div class="col-lg-6 col-md-6 col-sm-12 mb-3">' +
                  '<div class="d-flex align-items-center">' + // Ensure vertical alignment
                  '<div class="me-3">' + // Add margin to the right for spacing
                  '<img src="' + imageSrc + '" width="80px" height="80px" style="display: inline-block; vertical-align: middle; border-radius: 10px; border: 2px solid #ccc;">' +
                  '</div>' +
                  '<div style="font-size:13px">' +
                  '<div class="ps-3"><b>' + candidateName + '</b></div>' +
                  '</div>' +
                  '</div>' +
                  '</div>';

              pairCounter++;
          }
      });

      // Handle ABSTAIN option
      var abstainRadio = reminder.querySelector('input[type="radio"].abstain-checkbox');
      if (abstainRadio && abstainRadio.checked) {
          // Create HTML for abstain option with vertically centered content
          candidateHTML += '<div class="col-12 mb-3">' +
              '<div class="d-flex  align-items-center">' + // Ensure vertical and horizontal alignment
              '<div class="me-3">' + // Add margin to the right for spacing
              '<img src="images/resc/Abstained.png" width="80px" height="80px" style="display: inline-block; vertical-align: middle; border-radius: 10px; border: 2px solid #ccc;">' +
              '</div>' +
              '<div>' +
              '<div class="ps-3" style="font-size:12.5px"><b>ABSTAINED</b></div>' +
              '</div>' +
              '</div>' +
              '</div>';

          pairCounter++;
      }
      // If candidates were selected, add them to selectedCandidateHTML
      if (pairCounter > 0) {
          selectedCandidateHTML += '<div>' +
              '<hr><div class="main-color px-5" style="padding-bottom:25px"><center><b>' + positionTitle.toUpperCase() + '</b></center></div>' +
              '<div class="row">' + candidateHTML + '</div>' +
              '</div>';
      } else {
          isValid = false;
          if (!scrollToReminder) {
              scrollToReminder = reminder;
          }
      }
  });

  // Handle form submission
  if (!isValid) {
      if (scrollToReminder) {
          scrollToReminder.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
  } else {
    console.log('Triggering modal');
      $('#confirmationModal').modal('show');
      document.getElementById('selectedCandidate').innerHTML = selectedCandidateHTML;
  }
}

// Ensure validateForm is bound to the form submit event
document.getElementById('voteForm').addEventListener('submit', validateForm);

// Handle the confirmation of the vote
document.getElementById('submitModalButton').addEventListener('click', function() {
    var voteForm = document.getElementById('voteForm');
    var formData = $(voteForm).serialize();

    $.ajax({
        type: 'POST',
        url: 'includes/insert-vote.php',
        data: formData,
        success: function(response) {
            // Hide the confirmation modal
            $('#confirmationModal').modal('hide');
            // Show the success modal
            $('#voteSubmittedModal').modal('show');
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr.responseText);
        }
    });
});

//Still the same script when user tries to reload the page
// Add alert when user tries to refresh the page or close the browser
let formChanged = false;

document.getElementById('voteForm').addEventListener('change', function() {
  formChanged = true;
});

window.addEventListener('beforeunload', function (e) {
  if (formChanged) {
    const confirmationMessage = ' ';
    e.returnValue = confirmationMessage;
    return confirmationMessage;
  }
});

// Add an event listener to the "Give Feedback" button to remove the beforeunload event
document.getElementById('giveFeedbackbtn').addEventListener('click', function() {
  formChanged = false; // Reset the flag
  window.removeEventListener('beforeunload', function (e) {
    if (formChanged) {
      const confirmationMessage = ' ';
      e.returnValue = confirmationMessage;
      return confirmationMessage;
    }
  });
}); 

/* let formChanged = false;
let shouldLeave = false;

document.getElementById('voteForm').addEventListener('change', function() {
  formChanged = true;
});

window.addEventListener('beforeunload', function (e) {
  if (formChanged && !shouldLeave) {
    $('#leavePageModal').modal('show');
    e.preventDefault();
    return false; // This prevents the default browser alert
  }
});

document.getElementById('leaveButton').addEventListener('click', function() {
  shouldLeave = true;
  formChanged = false;
  $('#leavePageModal').modal('hide');
  window.location.reload(); // Reload the page
});

// Add an event listener to the "Give Feedback" button to remove the beforeunload event
document.getElementById('giveFeedbackbtn').addEventListener('click', function() {
  formChanged = false; // Reset the flag
  window.removeEventListener('beforeunload', function (e) {
    if (formChanged) {
      const confirmationMessage = ' ';
      e.returnValue = confirmationMessage;
      return confirmationMessage;
    }
  });
});
*/

function showResetConfirmation() {
    // Check if there are any inputs that need resetting
    if (shouldResetForm()) {
      $('#resetFormModal').modal('show'); // Show the modal using jQuery
    } else {
      resetForm(); // If no inputs to reset, reset immediately
    }
  }

  function confirmReset() {
    resetForm(); // Call resetForm() if user confirms
    $('#resetFormModal').modal('hide'); // Hide the modal using jQuery
  }

  function resetForm() {
    document.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
      checkbox.checked = false;
    });
    document.querySelectorAll('input[type="radio"]').forEach((radio) => {
      radio.checked = false;
    });
    document.querySelectorAll('input[type="text"]').forEach((textInput) => {
      textInput.value = '';
    });
  }

  function shouldResetForm() {
    let anyInputs = false;

    // Check if there are any inputs that need resetting
    document.querySelectorAll('input[type="checkbox"], input[type="radio"], input[type="text"]').forEach((input) => {
      if (input.type === 'checkbox' || input.type === 'radio') {
        if (input.checked) {
          anyInputs = true;
        }
      } else if (input.type === 'text') {
        if (input.value.trim() !== '') {
          anyInputs = true;
        }
      }
    });

    return anyInputs;
  }
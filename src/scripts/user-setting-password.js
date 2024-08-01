$(document).ready(function () {

    $("#reset-password-toggle-2").click(function () {
        togglePasswordVisibility("#password_current", $(this));
    });

    function togglePasswordVisibility(inputSelector, toggleElement) {
        var passwordInput = $(inputSelector);
        var eyeIcon = toggleElement.find("i");

        var type = passwordInput.attr("type") === "password" ? "text" : "password";
        passwordInput.attr("type", type);

        eyeIcon.toggleClass("fa-eye-slash fa-eye");
    }

    function avoidSpace(event) {
        var input = $(event.target);
        var value = input.val().replace(/\s/g, "");
        input.val(value);
    }

    $("#password_current").on("input", function (event) {
        avoidSpace(event);
    });

    // JavaScript to enable/disable submit button based on password input
    document.getElementById('password_current').addEventListener('input', function () {
        var passwordInput = this.value.trim();
        var submitButton = document.getElementById('new-password-submit');

        if (passwordInput.length > 0) {
            submitButton.removeAttribute('disabled');
        } else {
            submitButton.setAttribute('disabled', 'disabled');
        }
    });

    // AJAX verify password
    $('#new-password-submit').click(function (e) {
        e.preventDefault();
    
        var currentPassword = $('#password_current').val();
    
        $.ajax({
            type: 'POST',
            url: 'includes/verify-password.php',
            data: {
                password: currentPassword
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Password verification successful
                    window.location.href = "setting-password-reset";
                } else {
                    $('#error').text(response.message).show();
                    $('#reset-password').addClass('error-border');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error: ' + status + ', ' + error);
            }
        });
    });
    
    $('#password_current, #reset-password-toggle-2').on('input', function() {
        $('#reset-password').removeClass('error-border');
        $('#error').hide();
    });
    

});

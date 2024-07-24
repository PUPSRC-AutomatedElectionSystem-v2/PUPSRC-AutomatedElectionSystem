$(document).ready(function () {
  const submitBtn = $("#SCO-login-button");

  // Toggle password visibility
  $("#reset-password-toggle-1").click(function () {
    togglePasswordVisibility("#password", $(this));
  });

  $("#reset-password-toggle-2").click(function () {
    togglePasswordVisibility("#password_confirmation", $(this));
  });

  function togglePasswordVisibility(inputSelector, toggleElement) {
    var passwordInput = $(inputSelector);
    var type = passwordInput.attr("type") === "password" ? "text" : "password";
    passwordInput.attr("type", type);
    toggleElement.find("i").toggleClass("fa-eye-slash fa-eye"); // Toggle eye icon classes
    checkInputs(); // Check password requirements after toggling visibility
  }

  // Function to prevent spaces from input fields
  function preventSpaces(event) {
    var input = $(event.target);
    var value = input.val().replace(/\s/g, "");
    input.val(value);
  }

  // Disallow whitespaces from input fields
  $("#password, #password_confirmation").on("input", function (event) {
    preventSpaces(event);
    checkInputs(); // Check password requirements on input change
    updateCheckIconVisibility(); // Update check icon visibility
  });

  // Truncate password if exceeds 20 characters
  function truncatePasswordIfExceedsMax(input) {
    var value = input.val().trim();
    if (value.length > 20) {
      value = value.slice(0, 20);
      input.val(value);
    }
  }

  function checkInputs() {
    truncatePasswordIfExceedsMax($("#password"));
    truncatePasswordIfExceedsMax($("#password_confirmation"));
    var passwordValue = $("#password").val().trim();
    var passwordConfirmationValue = $("#password_confirmation").val().trim();
    var errorText = $("#password-mismatch-error");

    // Password requirements
    var hasValidLength =
      passwordValue.length >= 8 && passwordValue.length <= 20;
    var hasUpperCase = /[A-Z]/.test(passwordValue);
    var hasLowerCase = /[a-z]/.test(passwordValue);
    var hasNumber = /\d/.test(passwordValue);
    var hasSpecialChar = /[\W_]/.test(passwordValue);

    // Check if passwords match and all requirements are met
    if (
      passwordConfirmationValue === "" ||
      !hasValidLength ||
      !hasUpperCase ||
      !hasLowerCase ||
      !hasNumber ||
      !hasSpecialChar
    ) {
      submitBtn.prop("disabled", true);
      errorText.hide();
      updateCheckIconVisibility(); // Update check icon visibility
      $(".password-requirements").removeClass("show"); // Hide password requirements
    } else if (passwordValue === passwordConfirmationValue) {
      submitBtn.prop("disabled", false);
      errorText.hide();
      updateCheckIconVisibility(); // Update check icon visibility
      $(".password-requirements").removeClass("show"); // Hide password requirements
    } else {
      submitBtn.prop("disabled", true);
      errorText.show();
      updateCheckIconVisibility(); // Update check icon visibility
      $(".password-requirements").addClass("show"); // Show password requirements
    }
  }

  // Update check icon visibility based on password requirements
  function updateCheckIconVisibility() {
    var passwordValue = $("#password").val().trim();
    var passwordConfirmationValue = $("#password_confirmation").val().trim();
    var checkIcons = $(
      "#reset-password-toggle-1 .fa-check-circle, #reset-password-toggle-2 .fa-check-circle"
    );

    if (
      passwordConfirmationValue === "" ||
      passwordValue.length < 8 ||
      passwordValue.length > 20 ||
      !/[A-Z]/.test(passwordValue) ||
      !/[a-z]/.test(passwordValue) ||
      !/\d/.test(passwordValue) ||
      !/[\W_]/.test(passwordValue)
    ) {
      checkIcons.hide(); // Hide check icon if requirements not met
    } else if (passwordValue === passwordConfirmationValue) {
      checkIcons.show(); // Show check icon if requirements are met
    } else {
      checkIcons.hide(); // Hide check icon if passwords do not match
    }
  }

  // Initial check
  checkInputs();
  updateCheckIconVisibility();

  // Show password requirements on password input
  $("#password").on("input", function () {
    var value = $(this).val().trim();
    var passwordRequirements = $(".password-requirements");

    if (value) {
      passwordRequirements.addClass("show");
    } else {
      passwordRequirements.removeClass("show");
    }

    var passwordRequirementsList = $(".requirement");

    // Check password length
    passwordRequirementsList
      .eq(0)
      .toggleClass("met", value.length >= 8 && value.length <= 20);
    passwordRequirementsList
      .eq(0)
      .toggleClass("unmet", !(value.length >= 8 && value.length <= 20));

    // Check for uppercase letter
    passwordRequirementsList.eq(1).toggleClass("met", /[A-Z]/.test(value));
    passwordRequirementsList.eq(1).toggleClass("unmet", !/[A-Z]/.test(value));

    // Check for lowercase letter
    passwordRequirementsList.eq(2).toggleClass("met", /[a-z]/.test(value));
    passwordRequirementsList.eq(2).toggleClass("unmet", !/[a-z]/.test(value));

    // Check for number
    passwordRequirementsList.eq(3).toggleClass("met", /\d/.test(value));
    passwordRequirementsList.eq(3).toggleClass("unmet", !/\d/.test(value));

    // Check for special character
    passwordRequirementsList.eq(4).toggleClass("met", /[\W_]/.test(value));
    passwordRequirementsList.eq(4).toggleClass("unmet", !/[\W_]/.test(value));
  });

  // Process new password
  submitBtn.click(function (event) {
    event.preventDefault();
    submitBtn.prop("disabled", true);
    var password = $("#password").val();
    var passwordConfirmation = $("#password_confirmation").val();
    var verificationToken = $("#verificationToken").val();
    $.ajax({
      url: "includes/process-new-password.php",
      type: "POST",
      data: {
        password: password,
        passwordConfirmation: passwordConfirmation,
        verificationToken: verificationToken,
      },
      success: function (response) {
        $("#successPasswordResetModal").modal("show");
        submitBtn.prop("disabled", false);
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText, status, error);
        submitBtn.prop("disabled", false);
      },
    });
  });
});

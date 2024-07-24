$(document).ready(function () {
  let termsAndPolicyData = null;
  let dataLoaded = false;
  const studentNumber = $("#student_number");
  const firstName = $("#first_name");
  const middleName = $("#middle_name");
  const lastName = $("#last_name");
  const suffix = $("#suffix");
  const email = $("#email");
  const organization = $("#org");
  const password = $("#password");
  const confirmPassword = $("#retype-pass");
  const privacyAndTerms = $("#privacyTerms");
  const submitButton = $("#sign-up");
  const studentNumberRegex = /^\d{4}-\d{5}-[A-Z]{2}-\d$/;
  const passwordRegex =
    /^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])[^\s]{8,20}$/;
  const nameRegex = /^[a-zA-ZñÑ]+([ ,.'-][a-zA-ZñÑ]+)*$/;


  let isDirty = false;
  let targetUrl = "";

  // top.window.onbeforeunload = null;

  $("#register-form input, #register-form select").on("change", function () {
    isDirty = true;
  });

  $("#confirmLeaveBtn").on("click", function () {
    isDirty = false;
    closeModal("pendingChangesModal");
    if (targetUrl) {
      window.location.href = targetUrl;
    } else {
      window.location.reload();
    }
  });

  $(window).on("beforeunload", function (event) {
    if (isDirty) {
      const message = "Changes you made may not be saved.";

      event.returnValue = message;
      return message;
    }
  });

  $(document).on("click", "a", function (e) {
    if (isDirty) {
      e.preventDefault();
      targetUrl = $(this).attr("href");
      showModal("pendingChangesModal");
    }
  });

  function loadTermsAndPrivacyPolicy() {
    $.getJSON("includes/misc/terms-and-privacy.json", function (data) {
      termsAndPolicyData = data;
      dataLoaded = true;
    });
  }

  // Fetches the JSON on page load
  loadTermsAndPrivacyPolicy();

  const fields = {
    studentNumber: { touched: false },
    firstName: { touched: false },
    middleName: { touched: false },
    lastName: { touched: false },
    suffix: { touched: false },
    email: { touched: false },
    org: { touched: false },
    password: { touched: false },
    retypePass: { touched: false },
    // cor: { touched: false },
  };

  function preventSpaces(event) {
    let input = event.target;
    let value = $(input).val();
    value = value.replace(/\s/g, "");
    $(input).val(value);
  }

  function preventLeadingSpace(event) {
    const input = event.target;
    if (input.value.startsWith(" ")) {
      input.value = input.value.trim();
    }
    input.value = input.value.replace(/\s{2,}/g, " ");
  }

  // Checks for valid student number
  function validateStudentNumber(input, showErrorMessages = false) {
    let studentNumberValue = input.val().trim();
    const errorElement = input.next();

    if (!studentNumberRegex.test(studentNumberValue)) {
      if (showErrorMessages && fields.studentNumber.touched)
        showError(
          input,
          errorElement,
          "Please follow the proper format for student number."
        );
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  function sliceStringLength(input, max) {
    let inputValue = input.val().trim();
    if (inputValue.length > max) {
      inputValue = inputValue.slice(0, max);
      input.val(inputValue);
    }
    return inputValue;
  }

  function validateFirstName(input, showErrorMessages = false) {
    let firstNameValue = input.val().trim();
    const errorElement = input.next();

    if (!nameRegex.test(firstNameValue)) {
      if (showErrorMessages && fields.firstName.touched)
        showError(input, errorElement, "Please use a valid first name.");
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  function validateMiddleName(input, showErrorMessages = false) {
    let middleNameNameValue = input.val().trim();
    const errorElement = input.next();

    if (middleNameNameValue && !nameRegex.test(middleNameNameValue)) {
      if (showErrorMessages && fields.middleName.touched)
        showError(input, errorElement, "Please use a valid middle name.");
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  function validateLastName(input, showErrorMessages = false) {
    let lastNameNameValue = input.val().trim();
    const errorElement = input.next();

    if (!nameRegex.test(lastNameNameValue)) {
      if (showErrorMessages && fields.lastName.touched)
        showError(input, errorElement, "Please use a valid last name.");
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  function validateSuffix(input, showErrorMessages = false) {
    let suffixValue = input.val().trim();
    const errorElement = input.next();

    if (suffixValue && !nameRegex.test(suffixValue)) {
      if (showErrorMessages && fields.suffix.touched)
        showError(input, errorElement, "Please use a valid suffix.");
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  // Check for valid email and if one already exists in the voter table
  function validateEmail(input, showErrorMessages = false) {
    let emailValue = input.val().trim();
    const errorElement = input.next();

    const isValidFormat = validateEmailFormat(emailValue);
    const isExistingEmail = emails.includes(emailValue);

    if (!isValidFormat) {
      if (showErrorMessages && fields.email.touched)
        showError(input, errorElement, "Please provide a valid email address.");
      return false;
    } else if (isExistingEmail) {
      if (showErrorMessages && fields.email.touched)
        showError(input, errorElement, "This email address is already taken.");
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  // Email regex format validation
  function validateEmailFormat(email) {
    return /^[a-zA-Z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,4}$/.test(email);
  }

  function validateOrg(select, showErrorMessages = false) {
    const orgValue = select.val();
    const errorElement = select.next();

    if (orgValue === "") {
      if (showErrorMessages && fields.org.touched)
        showError(select, errorElement, "Please select an organization.");
      return false;
    } else {
      clearError(select, errorElement);
      return true;
    }
  }

  function validatePassword(input, showErrorMessages = false) {
    let passwordValue = input.val();
    const errorElement = input.next();

    if (!passwordRegex.test(passwordValue)) {
      if (showErrorMessages && fields.password.touched)
        showError(
          input,
          errorElement,
          "Password must be 8-20 characters with a number, an uppercase letter, a lowercase letter, and a special character."
        );
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  function validateRetypePassword(
    input,
    originalPasswordInput,
    showErrorMessages = false
  ) {
    let retypePassValue = input.val();
    const errorElement = input.next();

    if (retypePassValue !== originalPasswordInput.val()) {
      if (showErrorMessages && fields.retypePass.touched)
        showError(input, errorElement, "Passwords do not match.");
      return false;
    } else {
      clearError(input, errorElement);
      return true;
    }
  }

  // function validateCOR(showErrorMessages = false) {
  //   const file = $("#cor").prop("files")[0];
  //   const errorElement = $("#cor").next();

  //   if (!file) {
  //     if (showErrorMessages && fields.cor.touched)
  //       showError(
  //         $("#cor"),
  //         errorElement,
  //         "Please upload your Certificate of Registration."
  //       );
  //     return false;
  //   }

  //   const fileName = file.name;
  //   const fileExtension = fileName.split(".").pop().toLowerCase();

  //   // Check if file is PDF extension
  //   if (fileExtension !== "pdf") {
  //     $("#cor").val("");
  //     showModal("onlyPDFAllowedModal");
  //     return false;
  //   }

  //   // Check if file size exceeds 25mb
  //   const fileSizeInMB = file.size / (1024 * 1024);
  //   if (fileSizeInMB > 25) {
  //     $("#cor").val("");
  //     showModal("onlyPDFAllowedModal");
  //     return false;
  //   }

  //   clearError($("#cor"), errorElement);
  //   return true;
  // }

  // Check if terms and conditions checkbox ticked?
  function validateTermsCheckbox() {
    return $("#privacyTerms").is(":checked");
  }

  // Display error message below an input field
  function showError(input, errorElement, message) {
    if (!errorElement.length) {
      const newErrorElement = $("<div>").addClass("error-message");
      input.parent().append(newErrorElement);
      errorElement = newErrorElement;
    }
    errorElement.text(message);
    input.addClass("error-border");
  }

  // Remove error messages below an input field
  function clearError(input, errorElement) {
    if (errorElement.length) {
      errorElement.text("");
      input.removeClass("error-border");
    }
  }

  // Functions on modal toggle
  function showModal(modalId) {
    $("#" + modalId).modal("show");
  }

  function closeModal(modalId) {
    $("#" + modalId).modal("hide");
  }

  // Content loading for Terms and Privacy Policy
  function showContentModal(content, modalId) {
    $(modalId).find(".modal-body .text-start.fs-7").html(content);
    $(modalId).modal("show");
  }

  // PDF
  $("#onlyPDFClose").click(function () {
    closeModal("onlyPDFAllowedModal");
  });

  // Terms and Conditions Modal
  $("#termsConditionsLink").click(function (event) {
    isDirty = false;
    event.preventDefault();
    if (dataLoaded) {
      showContentModal(
        termsAndPolicyData.termsAndConditions[0].content,
        "#termsConditionsModal"
      );
    }
  });

  $("#closeTermsConditions").click(function () {
    closeModal("termsConditionsModal");
  });

  // Privacy Policy Modal
  $("#privacyTermsLink").click(function (event) {
    isDirty = false;
    event.preventDefault();
    if (dataLoaded) {
      showContentModal(
        termsAndPolicyData.privacyPolicy[0].content,
        "#privacyPolicyModal"
      );
    }
  });

  $("#closePrivacyPolicy").click(function () {
    closeModal("privacyPolicyModal");
  });

  // MAIN FORM VALIDATOR
  function checkFormValidity() {
    const firstNameValid = validateFirstName(firstName, false);
    const middleNameValid = validateMiddleName(middleName, false);
    const lastNameValid = validateLastName(lastName, false);
    const suffixValid = validateSuffix(suffix, false);
    const studentNumberValid = validateStudentNumber(studentNumber, false);
    const emailValid = validateEmail(email, false);
    const orgValid = validateOrg(organization, false);
    const passwordValid = validatePassword(password, false);
    const retypePassValid = validateRetypePassword(
      confirmPassword,
      password,
      false
    );
    // const corValid = validateCOR(false);
    const termsChecked = validateTermsCheckbox();

    if (
      firstNameValid &&
      middleNameValid &&
      lastNameValid &&
      suffixValid &&
      studentNumberValid &&
      emailValid &&
      orgValid &&
      passwordValid &&
      retypePassValid &&
      // corValid &&
      termsChecked
    ) {
      submitButton.removeAttr("disabled");
    } else {
      submitButton.attr("disabled", "disabled");
    }
  }

  /* ----------------------------------------------------
                START: ON INPUT EVENTS 
  ------------------------------------------------------- */

  studentNumber.on("input", function (event) {
    sliceStringLength(studentNumber, 15);
    preventSpaces(event);
    checkFormValidity();
  });

  firstName.on("input", function (event) {
    sliceStringLength(firstName, 100);
    preventLeadingSpace(event);
    checkFormValidity();
  });

  middleName.on("input", function (event) {
    sliceStringLength(middleName, 100);
    preventLeadingSpace(event);
    checkFormValidity();
  });

  lastName.on("input", function (event) {
    sliceStringLength(lastName, 100);
    preventLeadingSpace(event);
    checkFormValidity();
  });

  suffix.on("input", function (event) {
    sliceStringLength(suffix, 10);
    preventLeadingSpace(event);
    checkFormValidity();
  });

  email.on("input", function (event) {
    sliceStringLength(email, 255);
    preventSpaces(event);
    checkFormValidity();
  });

  password.on("input", function (event) {
    sliceStringLength(password, 20);
    preventSpaces(event);
    fields.password.touched = true;
    validatePassword($(this), true);
    checkFormValidity();
  });

  confirmPassword.on("input", function (event) {
    sliceStringLength(confirmPassword, 20);
    preventSpaces(event);
    fields.retypePass.touched = true;
    validateRetypePassword($(this), password, true);
    checkFormValidity();
  });

  privacyAndTerms.on("input", function () {
    checkFormValidity();
  });

  /* ----------------------------------------------------
                END: ON INPUT EVENTS 
  ------------------------------------------------------- */

  /* ----------------------------------------------------
                START: ON CHANGE EVENTS 
  ------------------------------------------------------- */
  studentNumber.on("change", function () {
    fields.studentNumber.touched = true;
    validateStudentNumber($(this), true);
    checkFormValidity();
  });

  firstName.on("change", function () {
    fields.firstName.touched = true;
    validateFirstName($(this), true);
    checkFormValidity();
  });

  middleName.on("change", function () {
    fields.middleName.touched = true;
    validateMiddleName($(this), true);
    checkFormValidity();
  });

  lastName.on("change", function () {
    fields.lastName.touched = true;
    validateLastName($(this), true);
    checkFormValidity();
  });

  suffix.on("change", function () {
    fields.suffix.touched = true;
    validateSuffix($(this), true);
    checkFormValidity();
  });

  email.on("change", function () {
    fields.email.touched = true;
    validateEmail($(this), true);
    checkFormValidity();
  });

  organization.on("change", function () {
    fields.org.touched = true;
    validateOrg($(this), true);
    checkFormValidity();
  });

  $("form").on("submit", function () {
    isDirty = false;
    setTimeout(function () {
      $("#sign-up").attr("disabled", true);
      $("#sign-up").html(
        `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Please wait...`
      );
    }, 50);
  });

  // Show success modal if registration is successful
  if (registrationSuccess) {
    showModal("registerSuccessModal");
  }
});

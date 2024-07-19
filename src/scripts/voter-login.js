$(document).ready(function () {

  const sendButton = $("#" + ORG_NAME);
  const cancelButton = $("#cancelReset");
  const origSendBtnState = sendButton.html();

  // Client-side validation for empty and invalid email and password
  $("#loginForm").on("submit", function (event) {
    let email = $("#Email").val().trim();
    let password = $("#Password").val().trim();
    let isValid = true;

    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    if (!emailPattern.test(email) || email === "") {
      isValid = false;
      $("#email-login-error").text("Please provide a valid email.");
      $("#Email").addClass("is-invalid border border-danger");
    } else {
      $("#email-login-error").text("");
      $("#Email").removeClass("is-invalid border border-danger");
      $("#Email").addClass("is-valid border border-success");
    }

    if (password === "") {
      isValid = false;
      $("#password-login-error").text("Please provide a valid password.");
      $("#Password").addClass("is-invalid border border-danger");
      $("#password-toggle").addClass("is-invalid border border-danger");
    } else {
      $("#password-login-error").text("");
      $("#Password, #password-toggle").removeClass(
        "is-invalid border border-danger"
      );
      $("#Password, #password-toggle").addClass(
        "is-valid border border-success"
      );
    }

    if (!isValid) {
      event.preventDefault();
    } else {
      setTimeout(function () {
        $("#loginSubmitBtn").prop("disabled", true);
      }, 50);
    }
  });

  const avoidSpace = (event) => {
    if (event.key === " ") {
      event.preventDefault();
    }
  };

  // Clears displayed messages from server-side
  $("#loginSubmitBtn").on("click", function () {
    const serverSideErrorMessage = document.querySelector(
      "#serverSideErrorMessage"
    );
    const serverSideInfoMessage = document.querySelector(
      "#serverSideInfoMessage"
    );

    if (serverSideErrorMessage) {
      serverSideErrorMessage.remove();
    }

    if (serverSideInfoMessage) {
      serverSideInfoMessage.remove();
    }
  });

  const preventSpaces = (event) => {
    let input = event.target;
    let maxLength = input.id === "Password" ? 20 : 255;
    let value = $(input).val();
    if (value.length > maxLength) {
      value = value.substring(0, maxLength);
      $(input).val(value);
    }
    value = value.replace(/\s/g, "");
    $(input).val(value);
  };

  $("input").on("keydown", avoidSpace);
  $("input").on("input", preventSpaces);

  // Function to reset forgot password form
  const resetForgotPasswordForm = () => {
    $("#email-error").text("");
    $("#email").removeClass(
      "is-invalid is-valid was-validated border border-danger border-success"
    );
    $("#email-valid").text("");
    sendButton.prop("disabled", true);
    cancelButton.prop("disabled", false);
    sendButton.html(origSendBtnState);
    $("#forgot-password-form")[0].reset();
  };

  $("#cancelReset").on("click", resetForgotPasswordForm);

  // Toggle password visibility
  $("#password-toggle").on("click", function () {
    const type =
      $("#Password").attr("type") === "password" ? "text" : "password";
    $("#Password").attr("type", type);
    $(this).text(type === "password" ? "Show" : "Hide");
  });

  sendButton.prop("disabled", true);

  $("#Password").on("change", function (event) {
    let password = $(this).val();
    if (password === "") {
      event.preventDefault();
      $("#password-login-error").text("Please provide a valid password.");
      $("#Password").addClass("is-invalid border border-danger");
      $("#password-toggle").addClass("is-invalid border border-danger");
    } else {
      $("#password-login-error").text("");
      $("#Password, #password-toggle").removeClass(
        "is-invalid border border-danger"
      );
      $("#Password, #password-toggle").addClass(
        "is-valid border border-success"
      );
    }
  });

  const validateEmail = (
    email,
    emailErrorElement,
    emailValidElement,
    isLogin = false
  ) => {
    const emailValue = email.val().trim();
    const isValid = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/.test(
      emailValue
    );
    // const user = user_data[emailValue];

    if (!isValid) {
      email
        .removeClass("is-valid was-validated")
        .addClass("is-invalid border border-danger");
      emailErrorElement.text("Please provide a valid email.");
      emailValidElement.text("");
      // } else if (!isLogin && !user) {
      //   email.removeClass("is-valid was-validated").addClass("is-invalid");
      //   emailErrorElement.text("We couldn't find your email address.");
      //   emailValidElement.text("");
      // } else if (!isLogin && user === "invalid") {
      //   email.removeClass("is-valid was-validated").addClass("is-invalid");
      //   emailErrorElement.text("We couldn't find your account.");
      //   emailValidElement.text("");
    } else {
      email
        .removeClass("is-invalid border border-danger")
        .addClass("is-valid was-validated border border-success");
      emailErrorElement.text("");
    }

    if (!isLogin) {
      sendButton.prop("disabled", !isValid);
    }
  };

  $("#email").on("input", function () {
    validateEmail($(this), $("#email-error"), $("#email-valid"));
  });

  $("#Email").on("change", function () {
    validateEmail(
      $(this),
      $("#email-login-error"),
      $("#email-login-valid"),
      true
    );
  });

  if (maxLoginAttempts) {
    // $("#blockTime").text(`${blockTime} minutes`);
    $("#maxLimitReachedModal").modal("show");
  }

  sendButton.on("click", function (event) {
    event.preventDefault();
    const email = $("#email").val();
    const emailError = $("#email-error");

    if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/.test(email)) {
      emailError.text("Please provide a valid email address.");
      $("#email").addClass("is-invalid border border-danger");
      return;
    }

    // $("#forgot-password-modal").modal("hide");
    // $("#emailSending").modal("show");
    cancelButton.prop("disabled", true);
    sendButton.html(
      `<span class="spinner-border spinner-border-sm me-2 text-white" role="status" aria-hidden="true"></span>Please wait...`
    );
    sendButton.prop("disabled", true);

    $.ajax({
      url: "includes/send-password-reset.php",
      type: "POST",
      data: { email: email },
      dataType: "json",
      success: function (response) {
        // $("#emailSending").modal("hide");

        if (response.success) {
          $("#forgot-password-modal").modal("hide");
          $("#successResetPasswordLinkModal")
            .modal("show")
            .on("hidden.bs.modal", function () {
              resetForgotPasswordForm();
            });
        } else {
          $("#forgot-password-modal").modal("show");
          emailError.text(response.message);
          $("#email").removeClass(
            "is-valid was-validated border border-success"
          );
          $("#email").addClass("is-invalid border border-danger");
          sendButton.prop("disabled", true);
          cancelButton.prop("disabled", false);
          sendButton.html(origSendBtnState);
        }
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
        emailError.text("Something went wrong. Please try again.");
        $("#email").removeClass("is-valid was-validated border border-success");
        $("#email").addClass("is-invalid border border-danger");
        sendButton.prop("disabled", true);
        cancelButton.prop("disabled", false);
        sendButton.html(origSendBtnState);
      },
    });
  });
});

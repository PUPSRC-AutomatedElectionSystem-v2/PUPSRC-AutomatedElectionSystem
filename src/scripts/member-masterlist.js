$(document).ready(function () {
  let currentPage = 1;
  const pageLimit = 5;
  const pagination = $("#pagination");
  const spinner = $("#spinner");
  const masterList = $("#masterList");
  const searchBarContainer = $("#searchBarContainer");
  const searchBar = $("#searchBar");
  const tableHeader = $(".tl-header");
  const voterIdField = $("#voterId");
  const fullNameField = $("#fullName");
  const emailField = $("#email");
  const sendAccSetupLinkModal = $("#sendAccSetupLinkModal");
  const sendAccSetupLinkForm = $("#sendAccSetupLinkForm");
  const sendAccSetupLink = $("#sendAccSetupLink");
  const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
  const emailErrorMessageContainer = $("#emailErrorMessage");
  // const tokenField = $("#token");
  // const verifyTokenModal = $("#verifyTokenModal");
  // const verifyTokenForm = $("#verifyTokenForm");
  // const tokenErrorMessageContainer = $("#tokenErrorMessage");
  // const verifyTokenBtn = $("#verifyTokenBtn");
  const cancelSendAccSetupLinkBtn = $("#cancelSendAccSetupLinkBtn");
  const cancelVerifyTokenBtn = $("#cancelVerifyTokenBtn");
  const successResetPasswordLinkModal = $("#successResetPasswordLinkModal");

  // search input
  searchBar.on("input", function () {
    const searchQuery = $(this).val();
    if (searchQuery.length > 0) {
      loadSearchResults(searchQuery, currentPage);
    } else {
      loadmasterListData(currentPage);
    }
  });

  // for pagination clicks
  $(document).on("click", ".page-link", function (e) {
    e.preventDefault();
    const page = $(this).data("page");
    if (page) {
      currentPage = page;
      const searchQuery = searchBar.val().trim();
      if (searchQuery.length > 0) {
        loadPagination(page, true);
        loadSearchResults(searchQuery, page);
      } else {
        loadPagination(page, true);
        loadmasterListData(page);
      }
    }
  });

  sendAccSetupLink.prop("disabled", true);

  // prevent leading and trailing whitespace to inputs
  emailField.on("input", function (event) {
    preventSpaces(event);
  });

  // resets the form state of modals
  cancelSendAccSetupLinkBtn.on("click", function () {
    resetFormState(emailErrorMessageContainer, sendAccSetupLink, emailField);
  });

  // cancelVerifyTokenBtn.on("click", function () {
  //   resetFormState(tokenErrorMessageContainer, verifyTokenBtn, tokenField);
  // });

  // validate email format
  emailField.on("input", function () {
    if (!emailRegex.test(emailField.val())) {
      emailField.addClass("is-invalid border border-danger");
      emailErrorMessageContainer.text("Please provide a valid email address.");
      sendAccSetupLink.prop("disabled", true);
    } else {
      emailField.removeClass("is-invalid border border-danger");
      emailErrorMessageContainer.text("");
      sendAccSetupLink.prop("disabled", false);
    }

    if (emailField.val().length > 255) {
      let emailFieldVal = emailField.val().slice(0, 255);
      emailField.val(emailFieldVal);
    }
  });

  /* ----------------------------------------------------
                START: CHECK EXISTING TOKEN
  ------------------------------------------------------- */
  $(document).on("click", ".setup-acc-link", function () {
    const row = $(this).closest("tr");
    const voterId = row.data("voter-id");
    const fullName = row.data("full-name");

    // Set the modal input values
    voterIdField.val(voterId);
    fullNameField.text(fullName);

    sendAccSetupLinkModal.modal("show");

    // AJAX request to check existing token
    // $.ajax({
    //   url: "includes/check-existing-token.php",
    //   type: "POST",
    //   data: {
    //     voterIdVal: voterIdField.val(),
    //   },
    //   dataType: "json",
    //   success: function (response) {
    //     if (response.tokenExist) {
    //       verifyTokenModal.modal("show");
    //     } else {
    //       sendAccSetupLinkModal.modal("show");
    //     }
    //   },
    //   error: function (error) {
    //     console.error(error); //d debug
    //   },
    // });

    // debug
    // console.log(voterIdField.val());
  });
  /* ----------------------------------------------------
                END: CHECK EXISTING TOKEN
  ------------------------------------------------------- */

  /* ----------------------------------------------------
                START: VERIFY MATCHED EMAIL 
  ------------------------------------------------------- */
  sendAccSetupLinkForm.on("submit", function (event) {
    event.preventDefault();
    const emailVal = emailField.val().trim();
    if (emailVal === "") {
      event.preventDefault();
      emailField.addClass("is-invalid border border-danger");
      emailErrorMessageContainer.text("Email input field cannot be empty.");
    } else {
      cancelSendAccSetupLinkBtn.add(sendAccSetupLink).prop("disabled", true);
      $.ajax({
        url: "includes/verify-email.php",
        type: "POST",
        data: {
          emailVal: emailVal,
          voterIdVal: voterIdField.val(),
        },
        dataType: "json",
        success: function (response) {
          if (response.maxLimit) {
            // code for max limit modal here. below is jsut sample copde
            alert("Max reached");
            window.location.href = "landing-page.php";
            resetFormState(
              emailErrorMessageContainer,
              sendAccSetupLink,
              emailField
            );
          } else if (response.success) {
            sendAccSetupLinkModal.modal("hide");
            successResetPasswordLinkModal.modal("show");
            // success modal
            resetFormState(
              emailErrorMessageContainer,
              sendAccSetupLink,
              emailField
            );
          } else {
            // code for incorrect email
            emailErrorMessageContainer.text(response.message);
            emailField.addClass("is-invalid border border-danger");
            cancelSendAccSetupLinkBtn.prop("disabled", false);
          }
        },
        error: function (xhr, status, error) {
          // console.error(xhr, status, error);
          resetFormState(
            emailErrorMessageContainer,
            sendAccSetupLink,
            emailField
          );
        },
      });
    }
  });
  /* ----------------------------------------------------
                END: VERIFY MATCHED EMAIL 
  ------------------------------------------------------- */

  /* ----------------------------------------------------
                START: VERIFY MATCHED TOKEN 
  ------------------------------------------------------- */

  // verifyTokenForm.on("submit", function (event) {
  //   event.preventDefault();
  //   const emailVal = emailField.val().trim();
  //   const tokenVal = tokenField.val();

  //   if (tokenVal === "") {
  //     tokenField.addClass("is-invalid border border-danger");
  //     tokenErrorMessageContainer.text("Token input field cannot be empty.");
  //   } else {
  //     verifyTokenBtn.add(cancelVerifyTokenBtn).prop("disabled", true);
  //     $.ajax({
  //       url: "includes/verify-token.php",
  //       type: "POST",
  //       data: {
  //         emailVal: emailVal,
  //         voterIdVal: voterIdField.val(),
  //         verificationTokenVal: tokenVal,
  //       },
  //       dataType: "json",
  //       success: function (response) {
  //         if (response.maxLimit) {
  //           // code for max limit modal here. below is jsut sample copde
  //           alert("Max reached");
  //           window.location.href = "landing-page.php";
  //           resetFormState(
  //             tokenErrorMessageContainer,
  //             verifyTokenBtn,
  //             tokenField
  //           );
  //         } else if (response.success) {
  //           verifyTokenModal.modal("hide");
  //           window.location.href = "create-password.php";
  //           resetFormState(
  //             tokenErrorMessageContainer,
  //             verifyTokenBtn,
  //             tokenField
  //           );
  //         } else {
  //           // code for incorrect token
  //           tokenErrorMessageContainer.text(response.message);
  //           tokenField.addClass("is-invalid border border-danger");
  //           verifyTokenBtn.prop("disabled", false);
  //         }
  //       },
  //       error: function (xhr, status, error) {
  //         console.error(xhr, status, error);
  //         resetFormState(
  //           tokenErrorMessageContainer,
  //           verifyTokenBtn,
  //           tokenField
  //         );
  //       },
  //     });
  //   }
  // });

  /* ----------------------------------------------------
                END: VERIFY MATCHED TOKEN 
  ------------------------------------------------------- */

  // load initial row
  loadPagination(currentPage, true);
  loadmasterListData(currentPage);

  masterList.on("click", ".send-token", function (e) {
    e.preventDefault();
    const voterId = $(this).data("id");
    const fullName = $(this).data("name");

    $("#voterId").val(voterId);
    $("#sendAccSetupLinkModal").text(fullName);

    sendAccSetupLinkModal.modal("show");
  });

  function loadmasterListData(page) {
    // spinner.show();
    // masterList.hide()

    $.ajax({
      url: "includes/load-masterlist.php",
      method: "POST",
      data: {
        actionVal: "loadTableRows",
        pageLimitVal: pageLimit,
        pageStartVal: (page - 1) * pageLimit,
      },
      success: function (response) {
        const data = JSON.parse(response);
        displayTableData(data);
        loadPagination(page, true);
        // spinner.hide();
      },
      error: function (error) {
        // console.error(error);
        // spinner.hide();
      },
    });
  }

  function loadSearchResults(query, page) {
    // spinner.show();
    // masterList.hide();
    $.ajax({
      url: "includes/load-masterlist.php",
      method: "POST",
      data: {
        actionVal: "searchName",
        searchQuery: query,
        // pageLimitVal: pageLimit,
        // pageStartVal: (page - 1) * pageLimit,
      },
      success: function (response) {
        const data = JSON.parse(response);
        displayTableData(data);
        loadPagination(page, true);
        // spinner.hide();
      },
      error: function (error) {
        // console.error(error);
        // spinner.hide();
      },
    });
  }

  function displayTableData(data) {
    let tableHtml = "";

    if (data.length === 0) {
      tableHeader.hide();
      pagination.hide();
      tableHtml = `
          <tr>
            <td colspan="2" class="no-registration text-center">
              <img src="images/resc/folder-empty.png" class="illus">
              <p class="fw-bold spacing-6 black">No records found.</p>
              <p class="spacing-3 pt-1 black"></p>
            </td>
          </tr>
        `;
    } else {
      data.forEach((row) => {
        searchBarContainer.show();
        tableHeader.show();
        pagination.show();

        tableHtml += `
                <tr data-voter-id="${row.voter_id}" data-full-name="${row.full_name}" class="clickable-row">
                <td class="text-center">
                    <div class="full-name">${row.full_name}</div>
                </td>
                <td class="text-center text-body-secondary">
                    <div class="text-reset setup-acc-link" role="button"><u>Set Up Account</u></div>
                </td>
                </tr>
            `;
      });
    }

    masterList.html(tableHtml).fadeIn();
  }

  function loadPagination(currentPage, isSearch) {
    // spinner.show();
    $.ajax({
      url: "includes/load-masterlist.php",
      method: "POST",
      data: {
        actionVal: isSearch ? "searchPagination" : "loadPagination",
        searchQuery: isSearch ? searchBar.val() : null,
      },
      success: function (response) {
        const totalItems = JSON.parse(response).total;
        const totalPages = Math.ceil(totalItems / pageLimit);

        let paginationHtml = "";

        // previous button <
        if (currentPage > 1) {
          paginationHtml += `<li class="pagination-button"><a class="page-link" href="#" data-page="${
            currentPage - 1
          }"><i class="fas fa-chevron-left"></i></a></li>`;
        } else {
          paginationHtml += `<li class="pagination-button disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>`;
        }

        // page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (endPage - startPage < 4) {
          if (startPage === 1) {
            endPage = Math.min(totalPages, startPage + 4);
          } else if (endPage === totalPages) {
            startPage = Math.max(1, endPage - 4);
          }
        }

        // shows ellipsis if pagination is more than 5
        if (startPage > 1) {
          paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
          if (startPage > 2) {
            paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
          }
        }

        for (let i = startPage; i <= endPage; i++) {
          if (i === currentPage) {
            paginationHtml += `<li class="page-item active"><a class="page-link" href="#">${i}</a></li>`;
          } else {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
          }
        }

        if (endPage < totalPages) {
          if (endPage < totalPages - 1) {
            paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
          }
          paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // next button >
        if (currentPage < totalPages) {
          paginationHtml += `<li class="pagination-button"><a class="page-link" href="#" data-page="${
            currentPage + 1
          }"><i class="fas fa-chevron-right"></i></a></li>`;
        } else {
          paginationHtml += `<li class="pagination-button disabled"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>`;
        }

        $("#pagination").html(paginationHtml);
        // spinner.hide();
      },
      error: function (error) {
        // console.error(error);
        // spinner.hide();
      },
    });
  }

  function preventSpaces(event) {
    let input = event.target;
    let value = $(input).val();
    value = value.replace(/\s/g, "");
    $(input).val(value);
  }

  function resetFormState(errorMessageContainers, btnId, inputId) {
    errorMessageContainers.text("");
    btnId.prop("disabled", true);
    cancelSendAccSetupLinkBtn.prop("disabled", false);
    cancelVerifyTokenBtn.prop("disabled", false);
    inputId.removeClass("is-invalid border border-danger");
    inputId.val("");
  }
});

$(document).ready(function () {
  // Bootstrap Popovers
  const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
  );
  const popoverList = [...popoverTriggerList].map(
    (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
  );

  /* Scroll to top event listener  */

  const scrollTopPageBtn = $("#scrollTopBtn");
  const popover = new bootstrap.Popover(scrollTopBtn, {
    trigger: "custom",
  });
  let canShowScrollTop = true;

  // Shows popover on hover
  // $("#scrollTopBtn").hover(
  //   function () {
  //     popover.show();
  //   },
  //   function () {
  //     setTimeout(() => {
  //       if (!$(this).is(":hover")) {
  //         popover.hide();
  //       }
  //     }, 100);
  //   }
  // );

  // Hides popover on click
  $("#scrollTopBtn").click(function () {
    canShowScrollTop = false;
    popover.hide();
    scrollTopPageBtn.prop("disabled", true).removeClass("show");

    // Scroll to top functionality
    $("html, body").animate({ scrollTop: 0 }, "slow", function () {
      canShowScrollTop = true;
      scrollTopPageBtn.prop("disabled", false);
    });

    return false;
  });

  $(window).scroll(function () {
    let scrollTop = $(window).scrollTop();

    if (scrollTop > 0 && canShowScrollTop) {
      scrollTopPageBtn.addClass("show");
    } else {
      scrollTopPageBtn.removeClass("show");
    }

    // if (
    //   $(window).scrollTop() + $(window).height() >=
    //   $(document).height() - 600
    // ) {
    //   loadMoreLogs();
    // }
  });

  // if (window.innerWidth < 768) {
  //   popoverList.forEach((popover) => {
  //     popover.dispose();
  //     const popoverTriggerEl = popover._element;
  //     new bootstrap.Popover(popoverTriggerEl, {
  //       trigger: "hover",
  //     });
  //   });
  // }

  /* Load data on the page */
  // let page = 1;
  // let loading = false;

  // function loadMoreLogs() {
  //   if (loading) return;
  //   loading = true;
  //   $.ajax({
  //     url: "includes/load-more-logs.php",
  //     method: "GET",
  //     data: { page: page },
  //     success: function (response) {
  //       var $newLogs = $(response).hide();
  //       $(".timeline").append($newLogs);
  //       $newLogs.fadeIn("slow");
  //       page++;
  //       loading = false;
  //     },
  //     error: function (xhr, status, error) {
  //       console.error("Error loading more logs:", error);
  //       loading = false;
  //     },
  //   });
  // }
  function loadActivities(filter) {
    $.ajax({
      url: "includes/filter-logs.php",
      method: "POST",
      data: { filter: filter },
      success: function (response) {
        $(".activity-log-content").html(response);
      },
      error: function (xhr, status, error) {
        console.error(error);
      },
    });
  }

  // Event listener for dropdown item clicks
  $(".custom-dropdown-item").on("click", function (e) {
    e.preventDefault();

    if ($(this).hasClass("disabled")) {
      return;
    }

    $(".custom-dropdown-item").removeClass("disabled");
    $(this).addClass("disabled");

    var filter = $(this).attr("id");
    var filterText = $(this).text();

    loadActivities(filter);

    function sanitizeHTML(text) {
      return $("<div/>").text(text).html();
    }

    $(".activity-dropdown").html(
      sanitizeHTML(filterText) +
        ' <i data-feather="chevron-down" class="chevron"></i>'
    );
    feather.replace();
  });

  $(".custom-dropdown-item").first().addClass("disabled");
});

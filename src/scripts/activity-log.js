$(document).ready(function () {
  const emptyStateMessage = `
  <div class="col-12 mt-4">
      <div class="card border pb-5 pt-3 border-0 rounded-3">
          <div class="card-body empty-state text-center">
              <img src="images/resc/folder-empty.png" class="illus">
              <p class="fw-bold spacing-6 black">No activities found.</p>
          </div>
      </div>
  </div>
`;
  const viewMoreBtn = $("#viewMoreBtn");
  const scrollTopPageBtn = $("#scrollTopBtn");
  let canShowScrollTop = true;

  // hides the button by default
  viewMoreBtn.hide();

  /* ----------------------------------------------------
          START: Scroll to top event listener
  ------------------------------------------------------- */
  scrollTopPageBtn.click(function () {
    canShowScrollTop = false;
    scrollTopPageBtn.prop("disabled", true).removeClass("show");

    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
    scrollTopPageBtn.prop("disabled", false);
    canShowScrollTop = true;

    return false;
  });

  $(window).scroll(function () {
    if (canShowScrollTop) {
      if ($(this).scrollTop() > 0) {
        scrollTopPageBtn.addClass("show");
      } else {
        scrollTopPageBtn.removeClass("show");
      }
    }
  });
  /* ----------------------------------------------------
          END: Scroll to top event listener
  ------------------------------------------------------- */

  let currentPage = 1;
  let filter = "";

  function renderLogs(logs, append = false) {
    let logGroups = {};
    let content = "";

    logs.forEach((log) => {
      let formattedDate = log.date;
      let formattedTime = log.time;

      if (!logGroups[formattedDate]) {
        logGroups[formattedDate] = [];
      }

      logGroups[formattedDate].push({
        time: formattedTime,
        action: log.action,
        role: log.role,
        email: log.email,
        ip_address: log.ip_address,
        browser: log.browser,
      });
    });

    // empty logs
    if (Object.keys(logGroups).length === 0) {
      if (append) {
        $(".activity-log-content").append(emptyStateMessage);
      } else {
        $(".activity-log-content").html(emptyStateMessage);
      }
      return;
    }

    for (const date in logGroups) {
      if (append) {
        // appends only if card of the date already exists
        let existingCard = $(
          `.activity-log-content [data-date="${date}"] .timeline`
        );
        if (existingCard.length > 0) {
          // appends logs to existing card
          logGroups[date].forEach((log) => {
            let item = `
              <li>
                <div class="row pb-4">
                  <div class="col-2">
                    <div class="time text-secondary">${log.time}</div>
                  </div>
                  <div class="col-10 activity-content">
                    <div class="activity-title">${log.action}</div>
                    <ul class="list-inline text-secondary">
                      <li class="list-inline-item py-1 activity-info">Email: ${log.email}</li>
                      <li class="list-inline-item py-1 activity-info">IP Address: ${log.ip_address}</li>
                      <li class="list-inline-item py-1 activity-info">Browser: ${log.browser}</li>
                    </ul>
                  </div>
                </div>
              </li>
            `;
            existingCard.append(item);
          });
        } else {
          // creates new card if new date is found
          let cardContent = `
            <div class="col-12 mt-4" data-date="${date}">
              <div class="card border border-0 rounded-3">
                <div class="card-body">
                  <div class="card-title py-3">${date}</div>
                  <ul class="timeline">
          `;
          logGroups[date].forEach((log) => {
            cardContent += `
              <li>
                <div class="row pb-4">
                  <div class="col-2">
                    <div class="time text-secondary">${log.time}</div>
                  </div>
                  <div class="col-10 activity-content">
                    <div class="activity-title">${log.action}</div>
                    <ul class="list-inline text-secondary">
                      <li class="list-inline-item py-1 activity-info">Email: ${log.email}</li>
                      <li class="list-inline-item py-1 activity-info">IP Address: ${log.ip_address}</li>
                      <li class="list-inline-item py-1 activity-info">Browser: ${log.browser}</li>
                    </ul>
                  </div>
                </div>
              </li>
            `;
          });
          cardContent += `
                  </ul>
                </div>
              </div>
            </div>
          `;
          $(".activity-log-content").append(cardContent);
        }
      } else {
        // always generate new cards if append is false
        content += `
          <div class="col-12 mt-4" data-date="${date}">
            <div class="card border border-0 rounded-3">
              <div class="card-body">
                <div class="card-title py-3">${date}</div>
                <ul class="timeline">
        `;
        logGroups[date].forEach((log) => {
          content += `
            <li>
              <div class="row pb-4">
                <div class="col-2">
                  <div class="time text-secondary">${log.time}</div>
                </div>
                <div class="col-10 activity-content">
                  <div class="activity-title">${log.action}</div>
                  <ul class="list-inline text-secondary">
                    <li class="list-inline-item py-1 activity-info">Email: ${log.email}</li>
                    <li class="list-inline-item py-1 activity-info">IP Address: ${log.ip_address}</li>
                    <li class="list-inline-item py-1 activity-info">Browser: ${log.browser}</li>
                  </ul>
                </div>
              </div>
            </li>
          `;
        });
        content += `
                </ul>
              </div>
            </div>
          </div>
        `;
        $(".activity-log-content").html(content);
      }
    }
  }

  function loadActivities(filter, page = 1, append = false) {
    $.ajax({
      url: "includes/load-activity-logs.php",
      method: "POST",
      data: { filter: filter, page: page },
      dataType: "json",
      success: function (response) {
        if (response && Array.isArray(response.logs)) {
          renderLogs(response.logs, append);

          if (response.hasMore) {
            viewMoreBtn.show();
          } else {
            viewMoreBtn.hide();
          }
        } else {
          // console.error(response);
        }
      },
      error: function (xhr, status, error) {
        // console.error(error);
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

    filter = $(this).attr("id");
    var filterText = $(this).text();

    currentPage = 1;
    loadActivities(filter, currentPage);

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

  filter = "allActLogs";
  loadActivities(filter, currentPage);

  viewMoreBtn.on("click", function () {
    // console.log("View More button clicked. Current Page:", currentPage);

    currentPage++;
    loadActivities(filter, currentPage, true);
  });
});

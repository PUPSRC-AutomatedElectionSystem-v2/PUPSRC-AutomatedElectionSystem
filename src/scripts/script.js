import {
  reqNotificationPermission,
  setNotificationPermCookie,
  checkNotificationCookie
} from './configuration.js';

// Sidebar Functionality
// ----------------------

// Submenu dropdown toggle
var sidebar = document.querySelector(".sidebar");
var sidebarClose = document.getElementById("sidebar-close");

if (sidebar && sidebarClose) {
  // Function to toggle sidebar visibility
  function toggleSidebar() {
    if (sidebar.classList.contains("open")) {
      sidebar.classList.remove("open");
      sidebar.classList.add("close");
    } else {
      sidebar.classList.remove("close");
      sidebar.classList.add("open");
    }
  }

  // Check window width and adjust sidebar class
  function checkWindowSize() {
    if (window.innerWidth <= 1024) {
      if (sidebar.classList.contains("open")) {
        sidebar.classList.remove("open");
        sidebar.classList.add("close");
      }
    }
  }

  checkWindowSize();
  window.addEventListener("resize", checkWindowSize);
  sidebarClose.addEventListener("click", toggleSidebar);

} else {
  console.error("Sidebar or sidebar close button not found.");
}



try {
  menuItems.forEach(function (item) {
    item.addEventListener("click", function () {
      toggleSidebar();

      var submenu = item.querySelector(".submenu");
      $(submenu).collapse("toggle");

      menuItems.forEach(function (otherItem) {
        if (otherItem !== item) {
          var otherSubmenu = otherItem.querySelector(".submenu");
          $(otherSubmenu).collapse("hide");
        }
      });
    });
  });
} catch (error) {
  console.error(error);
}

try {
  subMenuTitles.forEach((title) => {
    title.addEventListener("click", () => {
      menu.classList.remove("submenu-active");
    });
  });
} catch (error) {
  console.error(error);
}





// Checkbox Table Functionality
// ----------------------

$(document).ready(function () {
  $('[data-toggle="tooltip"]').tooltip();

  var checkbox = $('table tbody input[type="checkbox"]');
  $("#selectAll").click(function () {
    if (this.checked) {
      checkbox.each(function () {
        this.checked = true;
      });
    } else {
      checkbox.each(function () {
        this.checked = false;
      });
    }
  });
  checkbox.click(function () {
    if (!this.checked) {
      $("#selectAll").prop("checked", false);
    }
  });
});



try {
  let configLinks = document.querySelectorAll('a[href*="configuration"]');

  configLinks.forEach(link => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const clickedConfigLink = event.currentTarget;

      reqNotificationPermission()
        .then(perm => {
          if (perm === "granted") {
            if (!checkNotificationCookie("notification_granted")) {
              new Notification("You allowed iVote to send notifications.");
              setNotificationPermCookie();
            }
          }
        })
        .catch(error => {
          console.error('Error requesting notification permission:', error);
        })
        .finally(() => {
          if (clickedConfigLink) {
            window.location.href = clickedConfigLink.href;
          }
        });

    });
  });
} catch (error) {
  console.error(error);
}


document.addEventListener("DOMContentLoaded", function () {
  var initialYear = "2024-2025";
  var initialPage = 1;

  document.getElementById("selectedYear").innerHTML =
    "Current Academic Year: <strong>" + initialYear + "</strong>";
  document.getElementById("dropdownButtonText").innerHTML =
    "A.Y. " +
    initialYear +
    ' <i data-feather="chevron-down" class="white im-cust feather-1xs"></i>';

  window.selectedYear = initialYear;
  window.currentPage = initialPage;

  selectYearAndPage(initialYear, initialPage);
});

function showLoading() {
  document.getElementById("election-results").innerHTML =
    '<div class="loading">Loading...</div>';
}

function selectYear(year) {
  selectYearAndPage(year, 1);
}

function selectYearAndPage(year, page) {
  showLoading();
  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      document.getElementById("election-results").innerHTML = this.responseText;
      document.getElementById("selectedYear").innerHTML =
        "Current Academic Year: <strong>" + year + "</strong>";
      window.selectedYear = year;
      window.currentPage = page;
      document.getElementById("dropdownButtonText").innerHTML =
        "A.Y. " +
        year +
        ' <i data-feather="chevron-down" class="white im-cust feather-1xs"></i>';
      // Trigger position select change to update the chart with the new year
      document
        .getElementById("positionSelect")
        .dispatchEvent(new Event("change"));
      feather.replace();
    }
  };
  xhttp.open(
    "GET",
    "includes/fetch-election-data.php?election_year=" + year + "&page=" + page,
    true
  );
  xhttp.send();
}

document.getElementById("btn-previous").addEventListener("click", function () {
  if (window.currentPage > 1) {
    selectYearAndPage(window.selectedYear, window.currentPage - 1);
  }
});

document.getElementById("btn-next").addEventListener("click", function () {
  selectYearAndPage(window.selectedYear, window.currentPage + 1);
});

// Initialize global selected year
window.selectedYear = "<?php echo $selected_year; ?>";
var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [], // Initially empty
        datasets: [{
            label: '# of Votes',
            data: [], // Initially empty
            backgroundColor: [], // Initially empty
            borderColor: 'rgba(0, 0, 0, 0)', // Transparent border color
            borderWidth: 1,
            barThickness: 40 // Initial thickness
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    display: true // Hide grid lines on y-axis (vertical grid lines)
                }
            },
            x: {
                grid: {
                    display: false // Show grid lines on x-axis (horizontal grid lines)
                }
            }
        },
        responsive: true,
        maintainAspectRatio: true, // Maintain aspect ratio to adjust height dynamically
        aspectRatio: 2, // Initial aspect ratio (arbitrary number for starting point)
        layout: {
            padding: {
                top: 20,
                bottom: 20
            }
        }
    }
});

// Function to generate lighter shades of a base color
function generateLighterShades(baseColor, steps) {
    const RGB_COLOR = baseColor.match(/\d+/g); // Extract RGB values from the base color

    const R_STEP = (255 - RGB_COLOR[0]) / steps;
    const G_STEP = (255 - RGB_COLOR[1]) / steps;
    const B_STEP = (255 - RGB_COLOR[2]) / steps;

    const LIGHTER_SHADES = [];
    for (let i = 0; i < steps; i++) {
        const R = Math.round(parseInt(RGB_COLOR[0]) + i * R_STEP);
        const G = Math.round(parseInt(RGB_COLOR[1]) + i * G_STEP);
        const B = Math.round(parseInt(RGB_COLOR[2]) + i * B_STEP);
        LIGHTER_SHADES.push(`rgba(${R},${G},${B},0.7)`); // Adjust alpha value as needed
    }

    return LIGHTER_SHADES;
}

// Update chart function using computed color for shades
function updateChart(labels, votes) {
    // Get the computed background color of .main-bg-color
    const COMPUTED_COLOR = getComputedStyle(document.querySelector('.main-bg-color')).backgroundColor;

    // Generate lighter shades based on the computed color
    const LIGHTER_SHADES = generateLighterShades(COMPUTED_COLOR, 3); // Change 3 to the number of shades you want

    // Set different colors for the candidates and abstained bar
    var backgroundColors = labels.map((label, index) => label === 'Abstained' ? LIGHTER_SHADES[0] : LIGHTER_SHADES[index % LIGHTER_SHADES.length]);

    myChart.data.labels = labels;
    myChart.data.datasets[0].data = votes;
    myChart.data.datasets[0].backgroundColor = backgroundColors;
    myChart.update();
}

// Function to update chart aspect ratio based on window size
function updateChartSize() {
    if (window.innerWidth < 480) {
        myChart.options.aspectRatio = 0.8; // Adjust aspect ratio for smaller screens
    } else {
        myChart.options.aspectRatio = 2; // Default aspect ratio for larger screens
    }
    myChart.update();
}

// Event listener to handle window resize
window.addEventListener('resize', updateChartSize);

// Initial call to set the correct aspect ratio
updateChartSize();

document
  .getElementById("positionSelect")
  .addEventListener("change", function () {
    var selectedPosition = this.value;
    var electionYear = window.selectedYear;

    fetch(
      `includes/result-candidates.php?position_id=${selectedPosition}&election_year=${electionYear}`
    )
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        var labels = data.candidates.map((candidate) => candidate.name);
        var votes = data.candidates.map((candidate) => candidate.vote_count);
        updateChart(labels, votes);
      })
      .catch((error) => console.error("Error fetching data:", error));
  });
//dropdown
window.onclick = function (event) {
  if (!event.target.matches(".dropdown-button2")) {
    var dropdowns = document.getElementsByClassName("dropdown-content2");
    for (var i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.style.display === "block") {
        openDropdown.style.display = "none";
      }
    }
  }
};

//download pdf
function downloadPDF() {
  const electionYear = window.selectedYear; // Get the global selected year

  fetch(`includes/result-pdf.php?election_year=${electionYear}`)
    .then((response) => response.text())
    .then((phpString) => {
      const options = {
        margin: 10,
        filename: "generated_pdf.pdf",
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: "mm", format: "a4", orientation: "landscape" },
      };

      html2pdf().from(phpString).set(options).save();
    })
    .catch((error) => {
      console.error("Error fetching PHP content:", error);
    });
}

//download pdf
function downloadAllPDF() {
  const electionYear = window.selectedYear; // Get the global selected year

  fetch(`includes/results-all-pdf.php?election_year=${electionYear}`)
    .then((response) => response.text())
    .then((phpString) => {
      const options = {
        margin: 10,
        filename: "generated_pdf.pdf",
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: "mm", format: "a4", orientation: "landscape" },
      };

      html2pdf().from(phpString).set(options).save();
    })
    .catch((error) => {
      console.error("Error fetching PHP content:", error);
    });
}

//excel download
function downloadExcel() {
  const electionYear = window.selectedYear; // Get the global selected year
  fetch(`includes/result-xls.php?election_year=${electionYear}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        console.error("Error:", data.error);
        return;
      }

      // Create a new workbook and a worksheet
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.json_to_sheet(data);

      // Append the worksheet to the workbook
      XLSX.utils.book_append_sheet(wb, ws, "Election Winners");

      // Generate Excel file and trigger download
      XLSX.writeFile(wb, `election_winners_${electionYear}.xlsx`);
    })
    .catch((error) => console.error("Error fetching data:", error));
}


  // Feedback and pagination
document.addEventListener("DOMContentLoaded", function () {

  const sortButtons = document.querySelectorAll(".dropdown-item");
  sortButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
          event.preventDefault();
          const sort = this.getAttribute("data-sort");
          const order = this.getAttribute("data-order");
          fetchFeedbackData(sort, order);
      });
  });

  function fetchFeedbackData(sort, order, page = 1) {
      const data = new FormData();
      data.append("sort", sort);
      data.append("order", order);
      data.append("page", page);

      fetch("includes/fetch-feedback.php", {
          method: "POST",
          body: data,
      })
          .then((response) => response.json())
          .then((data) => {
              updateFeedbackTable(data.feedback_data);
              updateFeedbackPagination(data.total_pages, data.current_page, sort, order);
          })
          .catch((error) => console.error("Error:", error));
  }

  function updateFeedbackTable(feedback_data) {
    const tableBody = document.querySelector(".feedback-table tbody");
    if (!tableBody) {
        console.error("Feedback table body not found.");
        return;
    }
    tableBody.innerHTML = "";

    feedback_data.forEach((row) => {
        const truncatedFeedback = truncateText(row.feedback, 20); // Adjust the number to your desired truncation length

        // Format the date with specific options
        const dateOptions = {
            year: "numeric",
            month: "long",
            day: "numeric"
        };

        const formattedDate = new Date(row.timestamp).toLocaleString("en-US", dateOptions);

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="col-md-7 text-center">${truncatedFeedback}
                <p class="view-more" data-bs-toggle="modal" data-bs-target="#successEmailModal" data-feedback='${JSON.stringify(row)}'>View More</p>
            </td>
            <td class="col-md-2 text-center">${formattedDate}</td>
        `;
        tableBody.appendChild(tr);
    });

    function truncateText(text, maxLength) {
        if (text.length > maxLength) {
            return text.substring(0, maxLength) + '...';
        }
        return text;
    }

       // Add event listeners for view-more elements
       document.querySelectorAll(".view-more").forEach((element) => {
        element.addEventListener("click", function () {
            const feedbackData = JSON.parse(this.getAttribute("data-feedback"));
            const dateOptions = {
                year: "numeric",
                month: "long",
                day: "numeric"
            };
            const timeOptions = {
                hour: "numeric",
                minute: "numeric",
                hour12: true
            };
            
            const formattedTime = new Date(feedbackData.timestamp).toLocaleString("en-US", timeOptions);
            const formattedDate = new Date(feedbackData.timestamp).toLocaleString("en-US", dateOptions);
            document.getElementById("modal-feedback").textContent = feedbackData.feedback;
            document.getElementById("modal-date").textContent = `${formattedDate} | ${formattedTime}`;
        });
    });
}

  function updateFeedbackPagination(total_pages, current_page, sort, order) {
      const pagination = document.querySelector(".feedback-pagination");
      if (!pagination) {
          console.error("Feedback pagination not found.");
          return;
      }
      pagination.innerHTML = "";

      if (current_page > 1) {
          const prev = document.createElement("li");
          prev.className = "page-item";
          prev.innerHTML = `<a href="#" class="page-link" data-page="${current_page - 1}"> <i class="fas fa-chevron-left" id="btn-previous"></i> </a>`;
          pagination.appendChild(prev);
      }

      for (let i = 1; i <= total_pages; i++) {
          const page = document.createElement("li");
          page.className = `page-item ${i === current_page ? "active" : ""}`;
          page.innerHTML = `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
          pagination.appendChild(page);
      }

      if (current_page < total_pages) {
          const next = document.createElement("li");
          next.className = "page-item";
          next.innerHTML = `<a href="#" class="page-link" data-page="${current_page + 1}"> <i class="fas fa-chevron-right" id="btn-next"></i> </a>`;
          pagination.appendChild(next);
      }

      const pageLinks = document.querySelectorAll(".feedback-pagination .page-link");
      pageLinks.forEach((link) => {
          link.addEventListener("click", function (event) {
              event.preventDefault();
              const page = this.getAttribute("data-page");
              fetchFeedbackData(sort, order, page);
          });
      });
  }

  // Initial load
  fetchFeedbackData("timestamp", "DESC");

// Positions and pagination
const searchInput = document.getElementById("searchPosition");
if (!searchInput) {
    console.error("Search input not found.");
} else {
    searchInput.addEventListener("input", function () {
        const searchQuery = this.value;
        fetchPositionData(searchQuery);
    });
}

// Function to fetch position data and update the table
function fetchPositionData(searchQuery, page = 1) {
    const data = new FormData();
    data.append("searchQuery", searchQuery);
    data.append("page", page);

    fetch("includes/fetch-positions.php", {
        method: "POST",
        body: data,
    })
    .then((response) => response.json())
    .then((data) => {
        if (data.error) {
            console.error(data.error);
        } else {
            updatePositionTable(data.position_data);
            updatePositionPagination(data.total_pages, data.current_page, searchQuery);
        }
    })
    .catch((error) => console.error("Error:", error));
}

// Function to update the positions table
function updatePositionTable(position_data) {
    const tableBody = document.querySelector(".positions-table tbody");
    if (!tableBody) {
        console.error("Positions table body not found.");
        return;
    }
    tableBody.innerHTML = "";

    position_data.forEach((row) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="col-md-3 text-center">${row.title}
                <p class="view-more" data-bs-toggle="modal" data-bs-target="#positionModal" data-position-id="${row.id}">View Details</p>
            </td>
            <td class="col-md-3 text-center">${row.total_count}</td>
        `;
        tableBody.appendChild(tr);

        // Add event listener for view-more element in this specific row
        const viewMoreElement = tr.querySelector(".view-more");
        if (viewMoreElement) {
            viewMoreElement.addEventListener("click", function () {
                const positionId = this.getAttribute("data-position-id");
                fetchPositionDetails(positionId);
            });
        }
    });
}

// Function to fetch position details and candidates for the modal
async function fetchPositionDetails(positionId) {
    try {
        const response = await fetch(`includes/fetch-positions.php?positionId=${positionId}`);
        const data = await response.json();

        if (data.error) {
            console.error(data.error);
            return;
        }

        // Update modal content with fetched data
        document.getElementById("modal-position-title").textContent = data.title + ' Candidates';
        document.getElementById("modal-position-description").textContent = data.description;

        const candidatesContainer = document.getElementById("modal-candidates");
        candidatesContainer.innerHTML = "";

        data.candidates.forEach(candidate => {
            const candidateDiv = document.createElement("div");
            candidateDiv.className = "candidate";
            candidateDiv.innerHTML = `
                <div class="candidate-container">
                  <img src="user_data/${data.org_name}/candidate_imgs/${candidate.photo_url}" alt="${candidate.last_name}" class="candidate-photo main-color">
                  <p class="candidate-name">
                    ${candidate.first_name} <br>
                    <span class="last-name">${candidate.last_name}</span>
                  </p>
                </div>
            `;
            candidatesContainer.appendChild(candidateDiv);
        });

    } catch (error) {
        console.error("Error fetching position details:", error);
    }
}
document.getElementById('searchPosition').addEventListener('input', function() {
  if (this.value.length > 0) {
      this.style.backgroundImage = 'none';
  } else {
      this.style.background = 'url("data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' fill=\'currentColor\' class=\'bi bi-search\' viewBox=\'0 0 16 16\'> <path d=\'M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001a1.007 1.007 0 0 0-.198.195l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.85-3.85a1.007 1.007 0 0 0-.195-.198zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z\'/> </svg>") no-repeat';
      this.style.backgroundSize = '16px 16px';
      this.style.backgroundPosition = '10px center';
  }
});
// Function to update the pagination
function updatePositionPagination(total_pages, current_page, searchQuery) {
    const pagination = document.querySelector(".positions-pagination");
    if (!pagination) {
        console.error("Positions pagination not found.");
        return;
    }
    pagination.innerHTML = "";

    if (current_page > 1) {
        const prev = document.createElement("li");
        prev.className = "page-item";
        prev.innerHTML = `<a href="#" class="page-link" data-page="${current_page - 1}"> <i class="fas fa-chevron-left" id="btn-previous"></i> </a>`;
        pagination.appendChild(prev);
    }

    for (let i = 1; i <= total_pages; i++) {
        const page = document.createElement("li");
        page.className = `page-item ${i === current_page ? "active" : ""}`;
        page.innerHTML = `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
        pagination.appendChild(page);
    }

    if (current_page < total_pages) {
        const next = document.createElement("li");
        next.className = "page-item";
        next.innerHTML = `<a href="#" class="page-link" data-page="${current_page + 1}"> <i class="fas fa-chevron-right" id="btn-next"></i> </a>`;
        pagination.appendChild(next);
    }

    const pageLinks = document.querySelectorAll(".positions-pagination .page-link");
    pageLinks.forEach((link) => {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const page = this.getAttribute("data-page");
            fetchPositionData(searchQuery, page);
        });
    });
}

// Initial load of positions data
fetchPositionData("");
});

<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, 'includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('includes/session-handler.php');
require_once FileUtils::normalizeFilePath('includes/classes/session-manager.php');

// Check if voter_id and role is set in session
SessionManager::checkUserRoleAndRedirect();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Preloader Stylesheet and Image -->
  <link rel="preload" href="images/resc/ivote-icon.webp" as="image">
  <link rel="preload" href="styles/loader.css" as="style" />
  <link rel="stylesheet" href="styles/loader.css" />

  <!-- Fontawesome Link for Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles/dist/landing.css">
  <link rel="stylesheet" href="styles/dist/all-footer.css">
  <link rel="stylesheet" href="styles/our-story.css">
  <link rel="stylesheet" href="styles/dist/landing-animation.css">
  <link rel="icon" href="images/resc/ivote-favicon.png" type="image/x-icon">
  <title>iVote</title>

  <!-- Montserrat Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body id="index-body">

  <?php
  include_once FileUtils::normalizeFilePath('includes/components/loader.html');
  include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/outside-header.php');
  ?>

  <!-- Parallax section -->
  <section class="parallax">
    <div class="container">
      <div class="row fade-in">
        <div class="col text-center text-white justify-content-center slide-in">
          <img src="images/resc/iVOTE4.webp" class="img-fluid ivote-main-logo" alt="iVote Logo">
          <h5 id="index-PUPSRC" class="text-truncate mt-3">Polytechnic University of the Philippines - Santa Rosa Campus
          </h5>
          <h1 id="index-AES">AUTOMATED ELECTION SYSTEM</h1>
          <a href="landing-page#organizations" type="button" class="btn btn-primary fw-bold index-button">Select Organization</a>
        </div>
      </div>
      <div class="index-wave-footer" id="organizations">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
          <path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z" class="shape-fill"></path>
        </svg>
      </div>
    </div>
  </section>

  <!-- Normal section -->
  <section class="about-us-section mt-4 pt-3" id="normal-section">
    <div class="container-fluid fade-in">
      <div class="row">
        <div class="col col-md-6 our-story-left slide-in">
          <h2 class="landing-organization-title"><span class="hello-text">Our</span> Story</h2>
          <p class="our-story-subtitle">How it all came to be.</p>
          <div id="carouselMain" class="carousel slide carousel-shadow" data-bs-ride="carousel" data-bs-wrap="true">
            <div class="carousel-indicators">
              <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
              <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="1" aria-label="Slide 2"></button>
              <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="2" aria-label="Slide 3"></button>
              <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="3" aria-label="Slide 4"></button>
            </div>
            <div class="carousel-inner">
              <div class="carousel-item active" id="pm">
                <img src="images/our-story/PM Department/PM main pic.jpg" class="d-block w-100 carousel-image" alt="PM Department">
              </div>
              <div class="carousel-item" id="dev">
                <img src="images/our-story/DEV Department/DEV main pic.jpg" class="d-block w-100 carousel-image" alt="DEV Department">
              </div>
              <div class="carousel-item" id="qa">
                <img src="images/our-story/QA Department/QA main pic.jpg" class="d-block w-100 carousel-image" alt="QA Department">
              </div>
              <div class="carousel-item" id="ba">
                <img src="images/our-story/BA Department/BA main pic.jpg" class="d-block w-100 carousel-image" alt="BA Department">
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselMain" data-bs-slide="prev">
              <i class="bi bi-arrow-left" aria-hidden="true"></i>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselMain" data-bs-slide="next">
              <i class="bi bi-arrow-right" aria-hidden="true"></i>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>
        <div class="col-md-6 our-story-right  slide-in">
          <!-- <div id="carouselIndicatorsCopy">
            <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="3" aria-label="Slide 4"></button>
          </div> -->
          <div class="container-fluid">
            <div class="row">
              <div class="col-4 col-sm-4 col-md-4 col-lg-4 ">
                <div id="carouselSmallPic" class="image-container-small slide" data-bs-ride="carousel" data-bs-wrap="true">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="images/our-story/PM Department/PM small pic.jpg" class="d-block w-100 carousel-image" alt="PM Department">
                    </div>
                    <div class="carousel-item">
                      <img src="images/our-story/DEV Department/DEV small pic.jpg" class="d-block w-100 carousel-image" alt="DEV Department">
                    </div>
                    <div class="carousel-item">
                      <img src="images/our-story/QA Department/QA small pic.jpg" class="d-block w-100 carousel-image" alt="QA Department">
                    </div>
                    <div class="carousel-item">
                      <img src="images/our-story/BA Department/BA small pic.jpg" class="d-block w-100 carousel-image" alt="BA Department">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-8 col-sm-8 col-md-8 col-lg-8 ">
                <div id="carouselBigPic" class="image-container-big slide" data-bs-ride="carousel" data-bs-wrap="true">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="images/our-story/PM Department/PM big pic.jpg" class="d-block w-100 carousel-image" alt="PM Department">
                    </div>
                    <div class="carousel-item">
                      <img src="images/our-story/DEV Department/DEV big pic.jpg" class="d-block w-100 carousel-image" alt="DEV Department">
                    </div>
                    <div class="carousel-item">
                      <img src="images/our-story/QA Department/QA big pic.jpg" class="d-block w-100 carousel-image" alt="QA Department">
                    </div>
                    <div class="carousel-item">
                      <img src="images/our-story/BA Department/BA big pic.jpg" class="d-block w-100 carousel-image" alt="BA Department">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="department-box mt-3" id="departmentBox">
              <div id="carouselDepartment" class="slide" data-bs-ride="carousel" data-bs-wrap="true">
                <div class="carousel-inner">
                  <div class="carousel-item active" id="pm">
                    <h4 class="department-name">Project Manager Team</h4>
                    <p class="department-definition">Project teams unite diverse specialists to tackle specific goals. These collaborative groups plan, develop, and implement projects, aiming to launch products, services, or client features.</p>
                  </div>
                  <div class="carousel-item" id="dev">
                    <h4 class="department-name">Development Team</h4>
                    <p class="department-definition">Development teams bring ideas to life. From software to buildings, they combine skills to design, build, and deliver projects, ensuring successful completion.</p>
                  </div>
                  <div class="carousel-item" id="qa">
                    <h4 class="department-name">Quality Assurance Team</h4>
                    <p class="department-definition">Ensuring quality from start to finish, QA teams safeguard your brand reputation. They meticulously evaluate products and services to meet or exceed customer expectations, fostering loyalty. </p>
                  </div>
                  <div class="carousel-item" id="ba">
                    <h4 class="department-name">Business Analyst Team</h4>
                    <p class="department-definition">Business Analysts bridge the business-IT gap. They translate needs into solutions, analyze requirements, and ensure project success by collaborating across all levels.</p>
                  </div>
                </div>
              </div>
              <div class="department-icon mt-0">
                <img src="images/resc/ivote-favicon.png" class="img-fluid ivote-our-story" alt="iVote Logo">
              </div>
            </div>

            <div class="container-fluid mt-3">
              <div class="row">
                <div class="col-3 col-sm-3 col-md-3 col-lg-3">
                  <div id="carouselAuthor" class="image-container-author slide" data-bs-ride="carousel" data-bs-wrap="true">
                    <div class="carousel-inner">
                      <div class="carousel-item active" id="pm">
                        <img src="images/our-story/PM Department/PM.jpg" class="d-block w-100 carousel-image-author" alt="PM Department">
                      </div>
                      <div class="carousel-item" id="dev">
                        <img src="images/our-story/DEV Department/DEV.jpg" class="d-block w-100 carousel-image-author" alt="DEV Department">
                      </div>
                      <div class="carousel-item" id="qs">
                        <img src="images/our-story/QA Department/QA.jpg" class="d-block w-100 carousel-image-author" alt="QA Department">
                      </div>
                      <div class="carousel-item" id="ba">
                        <img src="images/our-story/BA Department/BA.jpg" class="d-block w-100 carousel-image-author" alt="BA Department">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-9 col-sm-9 col-md-9 col-lg-9 authorQuoteBox">
                  <div class="row">
                    <div class="col-2 quotationMark">
                      “
                    </div>
                    <div class="col-10">
                      <div id="carouselAuthorQuote" class="image-container-author-quote slide" data-bs-ride="carousel" data-bs-wrap="true">
                        <div class="carousel-inner">
                          <div class="carousel-item active" id="pm">
                            <p class="authorQuote">Overcoming hardship unlocks potential. Embrace challenges, achieve greatness.</p>
                          </div>
                          <div class="carousel-item" id="dev">
                            <p class="authorQuote">Inspiring collaboration fosters innovative solutions for campus elections </p>
                          </div>
                          <div class="carousel-item" id="qa">
                            <p class="authorQuote">Overcoming challenges, exceeding standards, driving impactful results.</p>
                          </div>
                          <div class="carousel-item" id="ba">
                            <p class="authorQuote">Deep appreciation for team's collaboration. Data-driven insights empower achievement.</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div id="carouselAuthorName" class="image-container-author-name slide" data-bs-ride="carousel" data-bs-wrap="true">
                    <div class="carousel-inner">
                      <div class="carousel-item active">
                        <p class="authorName">Apolo Trasmonte, Project Manager</p>
                      </div>
                      <div class="carousel-item">
                        <p class="authorName">Marie Jeremie Legrama, Developer</p>
                      </div>
                      <div class="carousel-item">
                        <p class="authorName">Mathew Cervantes, Quality Assurance</p>
                      </div>
                      <div class="carousel-item">
                        <p class="authorName">Abegail Vicuña, Business Analyst</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>



      </div>
    </div>
  </section>

  <?php include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/all-footer.php'); ?>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="scripts/loader.js"></script>

  <!-- JavaScript for dynamic text change PUPSRC -->
  <script>
    const indexPUPSRC = document.getElementById('index-PUPSRC');

    function updateText() {
      if (window.innerWidth <= 768) {
        indexPUPSRC.textContent = 'PUP SANTA ROSA CAMPUS';
      } else {
        indexPUPSRC.textContent = 'Polytechnic University of the Philippines - Santa Rosa Campus';
      }
    }

    window.addEventListener('load', updateText);
    window.addEventListener('resize', updateText);
  </script>


  <!-- for the department -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get the carousel element
      let carousel = document.getElementById('carouselDepartment');

      // Listen for the slide.bs.carousel event
      carousel.addEventListener('slide.bs.carousel', function(event) {
        // Get the active item
        let activeItem = event.relatedTarget;

        // Get the departmentBox element
        let departmentBox = document.getElementById('departmentBox');

        // Remove any existing transition effect
        departmentBox.style.transition = 'background-color 0.5s ease-in-out';
        departmentBox.style.backgroundColor = ''; // Clear any previous color

        // Set background color based on active item's ID after a short delay
        setTimeout(() => {
          switch (activeItem.id) {
            case 'pm':
              departmentBox.style.backgroundColor = '#D9E0F0';
              break;
            case 'dev':
              departmentBox.style.backgroundColor = '#ffc2d1';
              break;
            case 'qa':
              departmentBox.style.backgroundColor = '#fff185';
              break;
            case 'ba':
              departmentBox.style.backgroundColor = '#eec7fc';
              break;
            default:
              departmentBox.style.backgroundColor = ''; // Default or clear previous color
              break;
          }
        }, 100); // Adjust delay as needed
      });
    });
  </script>

  <!-- for the border color of the author -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get the carousel element
      let carousel = document.getElementById('carouselAuthor');

      // Listen for the slide.bs.carousel event
      carousel.addEventListener('slide.bs.carousel', function(event) {
        // Get the active item
        let activeItem = event.relatedTarget;

        // Remove any existing transition effect
        let carouselImages = document.querySelectorAll('.carousel-image-author');
        carouselImages.forEach(img => {
          img.style.transition = 'border-color 0.5s ease-in-out';
          img.style.borderColor = ''; // Clear any previous border color
        });

        // Set border color based on active item's ID after a short delay
        setTimeout(() => {
          switch (activeItem.id) {
            case 'pm':
              carousel.querySelector('#pm .carousel-image-author').style.borderColor = '#9bb2e2';
              break;
            case 'dev':
              carousel.querySelector('#dev .carousel-image-author').style.borderColor = '#ff8fab';
              break;
            case 'qs':
              carousel.querySelector('#qs .carousel-image-author').style.borderColor = '#ffdd1f';
              break;
            case 'ba':
              carousel.querySelector('#ba .carousel-image-author').style.borderColor = '#c670ff';
              break;
            default:
              break;
          }
        }, 100); // Adjust delay as needed
      });
    });
  </script>

  <!-- for thee arrow colors -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get the carousel element
      let carousel = document.getElementById('carouselMain');

      // Listen for the slide.bs.carousel event
      carousel.addEventListener('slide.bs.carousel', function(event) {
        // Get the active item
        let activeItem = event.relatedTarget;

        // Remove any existing transition effect
        let prevControl = document.querySelector('.carousel-control-prev');
        let nextControl = document.querySelector('.carousel-control-next');
        prevControl.style.transition = 'color 0.5s ease-in-out';
        nextControl.style.transition = 'color 0.5s ease-in-out';
        prevControl.style.color = ''; // Clear any previous color
        nextControl.style.color = ''; // Clear any previous color

        // Set color based on active item's ID after a short delay
        setTimeout(() => {
          switch (activeItem.id) {
            case 'pm':
              prevControl.style.color = '#3355cc'; // Blue color for previous button
              nextControl.style.color = '#3355cc'; // Blue color for next button
              break;
            case 'dev':
              prevControl.style.color = '#ff0033'; // Red color for previous button
              nextControl.style.color = '#ff0033'; // Red color for next button
              break;
            case 'qa':
              prevControl.style.color = '#ff99cc'; // Pink color for previous button
              nextControl.style.color = '#ff99cc'; // Pink color for next button
              break;
            case 'ba':
              prevControl.style.color = '#66cc00'; // Green color for previous button
              nextControl.style.color = '#66cc00'; // Green color for next button
              break;
            default:
              break;
          }
        }, 100); // Adjust delay as needed for smoother transition
      });
    });
  </script>

  <!-- for the quote -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get the carousel element
      let carousel = document.getElementById('carouselAuthorQuote');

      // Listen for the slide.bs.carousel event
      carousel.addEventListener('slide.bs.carousel', function(event) {
        // Get the active item
        let activeItem = event.relatedTarget;

        // Remove any existing transition effect
        let authorQuoteBox = document.querySelector('.authorQuoteBox');
        authorQuoteBox.style.transition = 'background-color 0.5s ease-in-out';
        authorQuoteBox.style.backgroundColor = ''; // Clear any previous background color

        // Set background color based on active item's ID after a short delay
        setTimeout(() => {
          switch (activeItem.id) {
            case 'pm':
              authorQuoteBox.style.backgroundColor = '#9bb2e2';
              break;
            case 'dev':
              authorQuoteBox.style.backgroundColor = '#ff8fab';
              break;
            case 'qa':
              authorQuoteBox.style.backgroundColor = '#ffdd1f';
              break;
            case 'ba':
              authorQuoteBox.style.backgroundColor = '#c670ff';
              break;
            default:
              break;
          }
        }, 100); // Adjust delay as needed for smoother transition
      });
    });
  </script>

  <!-- for the sycnronization when clicked the buttons -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get all carousel elements
      let carouselMain = new bootstrap.Carousel(document.getElementById('carouselMain'));
      let carouselSmallPic = new bootstrap.Carousel(document.getElementById('carouselSmallPic'));
      let carouselBigPic = new bootstrap.Carousel(document.getElementById('carouselBigPic'));
      let carouselDepartment = new bootstrap.Carousel(document.getElementById('carouselDepartment'));
      let carouselAuthor = new bootstrap.Carousel(document.getElementById('carouselAuthor'));
      let carouselAuthorQuote = new bootstrap.Carousel(document.getElementById('carouselAuthorQuote'));
      let carouselAuthorName = new bootstrap.Carousel(document.getElementById('carouselAuthorName'));

      // Event listener for carouselMain slide event
      carouselMain._element.addEventListener('slide.bs.carousel', function(event) {
        // Get the slide index of the active item in carouselMain
        let slideIndex = event.to;

        // Move other carousels to the corresponding slide index
        carouselSmallPic.to(slideIndex);
        carouselBigPic.to(slideIndex);
        carouselDepartment.to(slideIndex);
        carouselAuthor.to(slideIndex);
        carouselAuthorQuote.to(slideIndex);
        carouselAuthorName.to(slideIndex);
      });

      // Event listeners for carouselMain prev and next buttons
      document.querySelector('#carouselMain .carousel-control-prev').addEventListener('click', function() {
        let currentIndex = carouselMain._activeIndex;
        let newIndex = currentIndex > 0 ? currentIndex - 1 : carouselMain._items.length - 1;

        carouselSmallPic.to(newIndex);
        carouselBigPic.to(newIndex);
        carouselDepartment.to(newIndex);
        carouselAuthor.to(newIndex);
        carouselAuthorQuote.to(newIndex);
        carouselAuthorName.to(newIndex);
      });

      document.querySelector('#carouselMain .carousel-control-next').addEventListener('click', function() {
        let currentIndex = carouselMain._activeIndex;
        let newIndex = currentIndex < carouselMain._items.length - 1 ? currentIndex + 1 : 0;

        carouselSmallPic.to(newIndex);
        carouselBigPic.to(newIndex);
        carouselDepartment.to(newIndex);
        carouselAuthor.to(newIndex);
        carouselAuthorQuote.to(newIndex);
        carouselAuthorName.to(newIndex);
      });
    });
  </script>

  <script>
    // Wait for the document to finish loading
    document.addEventListener("DOMContentLoaded", function() {
      // Get the element to scroll to
      var normalSection = document.getElementById("normal-section");
      // Scroll to the element
      normalSection.scrollIntoView();
    });
  </script>

</body>

</html>
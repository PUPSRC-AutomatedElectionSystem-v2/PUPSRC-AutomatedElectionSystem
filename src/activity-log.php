<?php 
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('includes/session-handler.php');
require_once FileUtils::normalizeFilePath('includes/error-reporting.php');
include_once FileUtils::normalizeFilePath('includes/default-time-zone.php');

if(isset($_SESSION['voter_id']) && isset($_SESSION['role'])) {
    include_once FileUtils::normalizeFilePath('includes/session-exchange.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Favicon -->
    <link rel="icon" href="images/resc/ivote-favicon.png" type="image/x-icon">
    <title>Activity Log</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="../vendor/node_modules/bootstrap/dist/css/bootstrap.min.css" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

    <!-- Preloader Stylesheet and Image -->
    <link rel="preload" href="images/resc/ivote-icon.webp" as="image">
    <link rel="stylesheet" href="styles/loader.css" />

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="<?php echo 'styles/orgs/' . $org_name . '.css'; ?>" id="org-style">
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/core.css" />
    <link rel="stylesheet" href="styles/activity-log.css" />

    <!-- Bootstrap JavaScript -->
    <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- CDN jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Custom JavaScript -->
    <script type="text/javascript" src="scripts/script.js" defer></script>
    <script type="text/javascript" src="scripts/feather.js" defer></script>
    <script type="text/javascript" src="scripts/activity-log.js" defer></script>
    <script type="text/javascript" src="scripts/loader.js" defer></script>
</head>

<body>

    <?php
    include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/loader.html');
    include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/sidebar.php'); 
    ?>

    <div class="main">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xxl-9 card-title-container">
                <div class="card border border-0 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="row w-100">
                                <div class="col-md-7 col-xs-12 d-flex flex-column justify-content-center">
                                    <div class="card-title">
                                        <span class="pe-1">
                                            <i data-feather="clock" class="clock feather-xs im-cust-2 main-color"></i>
                                        </span>
                                        Activity Log
                                    </div>
                                    <div class="card-subtitle">History of recent and past activities are recorded here.</div>
                                </div>

                                <?php if($_SESSION['role'] === 'head_admin') : ?>
                                    <div class="col-md-5 col-xs-12 d-flex justify-content-lg-end justify-content-xs-start align-items-center activity-dropdown-parent">
                                        <div class="dropdown dropdown-center">
                                            <button class="btn btn-org-color rounded-5 ps-4 py-2 activity-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                All Activities
                                                <i data-feather="chevron-down" class="chevron"></i>
                                            </button>
                                            <ul class="dropdown-menu custom-dropdown-menu border border-0">
                                                <li><a class="custom-dropdown-item dropdown-item rounded-0" href="#" id="allActLogs" disabled>All Activities</a></li>
                                                <li><a class="custom-dropdown-item dropdown-item rounded-0" href="#" id="adminActLogs">Admin Activities</a></li>
                                                <li><a class="custom-dropdown-item dropdown-item rounded-0" href="#" id="voterActLogs">Voter Activities</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>                   
            </div>
            
            <div class="col-lg-10 col-xxl-9 activity-log-content">
                <!-- Activity Logs will be loaded here -->
            </div>

        </div>

        <div class="row justify-content-center align-items-center mt-4">
            <div class="col-lg-10 d-flex justify-content-center align-items-center">
                <button class="btn btn-org-color rounded-3 fw-semibold fs-7 spacing-5 px-4 py-2 border border-0" id="viewMoreBtn" style="display:none;">View 5 More</button>
            </div>
        </div>

    </div>

    <!-- Scroll to top button -->
    <button class="d-inline-block btn border border-0" type="button" id="scrollTopBtn"><i data-feather="arrow-up" class="arrow-up-icon"></i></button>
                                
    <!-- Page Footer -->
    <?php include_once FileUtils::normalizeFilePath('includes/components/footer.php');?>

</body>
</html>

<?php 
} else {
    header("Location: landing-page.php");
}
?>
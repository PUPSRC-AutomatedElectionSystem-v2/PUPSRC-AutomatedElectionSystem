<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, 'includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('includes/session-handler.php');
require_once FileUtils::normalizeFilePath('includes/classes/session-manager.php');
include_once FileUtils::normalizeFilePath('includes/error-reporting.php');
include_once FileUtils::normalizeFilePath('includes/session-exchange.php');

// Check if voter_id and role is set in session
SessionManager::checkUserRoleAndRedirect();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Preloader Stylesheet and Image -->
    <link rel="preload" href="images/resc/ivote-icon.webp" as="image">
    <link rel="preload" href="styles/loader.css" as="style" />
    <link rel="stylesheet" href="styles/loader.css" />

    <!-- Fontawesome Link for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="../vendor/node_modules/bootstrap/dist/css/bootstrap.min.css" />

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="styles/orgs/<?php echo $org_name; ?>.css">
    <link rel="stylesheet" href="styles/core.css">
    <link rel="stylesheet" href="styles/tables.css" />
    <link rel="stylesheet" href="styles/member-masterlist.css">
    <link rel="stylesheet" href="styles/dist/all-footer.css">

    <!-- Montserrat Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="images/resc/ivote-favicon.png" type="image/x-icon">
    <title>Account List</title>

    <!-- Bootstrap JavaScript -->
    <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="scripts/loader.js" defer></script>
    <script src="scripts/feather.js" defer></script>
</head>

<body>

    <?php 
    include_once FileUtils::normalizeFilePath('includes/components/loader.html'); 
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top" id="login-navbar">
        <div class="container-fluid d-flex justify-content-center align-items-center">
            <a href="landing-page.php"><img src="images/resc/iVOTE-Landing2.webp" id="ivote-logo-landing-header" alt="ivote-logo"></a>
        </div>
    </nav>

    <section class="account-section">
        <div class="container account-container" id="memMasterList">
            <div class="row justify-content-center">
                
                <div class="col-12">

                    <div class="mt-3">
                        <a href="voter-login.php" class="fs-7 spacing-5 link-offset-1"><i data-feather="arrow-left" class="feather-sm im-cust"></i><u>Go back to login page</u></a>                        
                    </div>

                    <div class="table-wrapper rounded-3 px-5 shadow-sm">
                        <div class="table-title">
                            <div class="row">
                                <div class="col-md-8 col-xs-12 d-flex flex-column justify-content-center align-items-start">
                                    <div class="main-color fw-bold ls-10 spacing-6 header-title">Members' Master List</div>
                                    <div class="header-subtitle">Search your name to start setting up your account.</div>
                                </div>

                                <div class="col-md-4 col-xs-12 mt-2 d-flex header-title align-items-center justify-content-md-end justify-content-xs-start">
                                    <div class="search-input w-100">
                                        <span class="search-icon" >
                                            <i data-feather="search" class="feather-sm"></i>
                                        </span>
                                        <input type="text" class="search-input-bar fs-7 spacing-6 fw-semibold w-75" placeholder="Search your name here..."> 
                                    </div>
                                </div>

                            </div>
                        </div>

                        <table class="table account-table">

                            <thead class="tl-header">
                                <tr>
                                    <th class="col-md-6 tl-left text-center fs-7 fw-bold spacing-5"><i data-feather="user" class="feather-sm im-cust"></i>Full Name</th>
                                    <th class="col-md-6 tl-right text-center fs-7 fw-bold spacing-5"><i data-feather="check-circle" class="feather-sm im-cust"></i>Verification Token</th>
                                </tr>                            
                            </thead>

                            <tbody>
                                <tr class="">
                                    <td class="text-center">Legrama, Marie Jeremie R.</td>
                                    <td class="text-center text-body-secondary"><a href="#" class="text-reset">Send Verification Token</a></td>
                                </tr>
                                <tr>
                                    <td class="text-center">Escueta, Peter Wilrexe M.</td>
                                    <td class="text-center text-body-secondary"><a href="#" class="text-reset">Send Verification Token</a></td>
                                </tr>  
                                
                                <!-- Pagination -->
                                <ul class="pagination mt-4" id="pagination">
                                <!-- Load pagination here -->
                                </ul>

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

            <div class="row justify-content-center mt-3">
                <div class="col-12">
                    <div class="card border border-0 shadow-sm">
                        <div class="card-body px-5 mb-5">
                            <div class="main-color fw-bold ls-10 spacing-6 step-title">How To Setup Your Account</div>
                            <div class="step-subtitle pb-2">To setup your account, please follow these steps:</div>
                                <ul class="list-group ps-2 list-group-flush step">
                                    <li class="list-group-item lh-base border border-0"><strong>1. Find your Name:</strong> Go to the <a href="#memMasterList" class="fw-bold main-color">Members' Master List</a> table and search your full name.</li>
                                    <li class="list-group-item lh-base border border-0"><strong>2. Send Verification Token:</strong> Once found, click the “Send Verification Token”.</li>
                                    <li class="list-group-item lh-base border border-0"><strong>3. Input Email:</strong> Input the email you provided to the organization.</li>
                                    <li class="list-group-item lh-base border border-0"><strong>4. Check Your Inbox:</strong> Look for the email with the verification token in your inbox.</li>
                                    <li class="list-group-item lh-base border border-0"><strong>5. Enter Token:</strong> Type the token into the verification token field.</li>
                                    <li class="list-group-item lh-base border border-0"><strong>6. Create a Password:</strong> Complete your account setup by providing a strong password.</li>
                                </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>

</body>
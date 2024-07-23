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
    <title>Master List</title>

    <!-- Bootstrap JavaScript -->
    <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="scripts/loader.js" defer></script>
    <script src="scripts/feather.js" defer></script>
    <script src="scripts/member-masterlist.js" defer></script>
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
                        <a href="voter-login.php" class="spacing-5 link-offset-1 nav-back"><i data-feather="arrow-left" class="feather-sm im-cust"></i><u>Go back to login page</u></a>                        
                    </div>

                    <div class="table-wrapper rounded-3 tbl-masterlist shadow-sm">
                        <div class="table-title">
                            <div class="row">
                                <div class="col-md-8 col-xs-12 d-flex flex-column justify-content-center align-items-start">
                                    <div class="main-color fw-bold ls-10 spacing-6 header-title">Members' Master List</div>
                                    <div class="header-subtitle">Search your name to start setting up your account.</div>
                                </div>

                                <div class="col-md-4 col-xs-12 mt-2 d-flex header-title align-items-center justify-content-md-end justify-content-xs-start">
                                    <div class="search-input w-100" id="searchBarContainer" style="display: none;">
                                        <span class="search-icon" >
                                            <i data-feather="search" class="feather-sm"></i>
                                        </span>
                                        <input type="text" class="search-input-bar spacing-6 fw-semibold w-75" id="searchBar" placeholder="Search your name here..."> 
                                    </div>
                                </div>

                            </div>
                        </div>

                        <table class="table account-table">

                            <thead class="tl-header" style="display: none;">
                                <tr>
                                    <th class="col-md-6 tl-left text-center fw-bold spacing-5 full-name-header"><i data-feather="user" class="feather-sm im-cust"></i>Full Name</th>
                                    <th class="col-md-6 tl-right text-center fw-bold spacing-5 acc-activation-header"><i data-feather="check-circle" class="feather-sm im-cust"></i>Account Activation</th>
                                </tr>                            
                            </thead>

                            <!-- simple spinner when loading table -->
                            <tr id="spinner" style="display: none;">
                                <td colspan="2" class="text-center">
                                    <div class="spinner-border text-secondary" role="status">
                                    </div>
                                    <div class="mt-2 fs-7">
                                        Getting things ready...
                                    </div>
                                </td>
                            </tr>

                            <tbody id="masterList">
                                <!-- Full names and ver tokens will be loaded here -->
                            </tbody>

                        </table>

                        <div class="clearfix col-xs-12">
                            <ul class="pagination mt-4" id="pagination" style="display: none;">
                            <!-- Pagination will be loaded here -->
                            </ul>
                        </div>

                    </div>

                </div>
            </div>

            <div class="row justify-content-center mt-3">
                <div class="col-12">
                    <div class="card border border-0 guidelines shadow-sm">
                        <div class="card-body mb-5">
                            <div class="main-color fw-bold ls-10 spacing-6 step-title">How To Setup Your Account</div>
                            <div class="step-subtitle pb-2">To setup your account, please follow these steps:</div>
                                <ul class="list-group list-group-flush step">
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

            <!-- Send Verification Token Modal -->
            <div class="modal fade" id="sendVerificationTokenModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h1 class="fw-bold main-color text-center spacing-4 verify-email">Email Address</h1>
                                    <form id="sendVerificationTokenForm" method="POST">
                                        <input type="hidden" id="voterId" name="voter_id">
                                        <p><strong id="fullName"></strong></p>
                                        <div class="mb-1">
                                            <!-- <label for="email" class="form-label">Email address</label> -->
                                            <input type="email" class="form-control bg-primary shadow-sm" id="email" name="email" placeholder="Enter your email address">
                                        </div>
                                        <div id="emailErrorMessage" class="fs-7 text-danger mb-4 fw-medium me-5">
                                            <!-- Display error messages here -->
                                        </div>
                                        <div class="row d-flex justify-content-center">
                                            <div class="col-5">
                                                <button type="button" class="btn btn-secondary w-100" id="cancelSendVerificationTokenBtn" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                            <div class="col-7">
                                                <button type="submit" id="sendTokenBtn" class="btn btn-org-color w-100">Send Token</button>
                                            </div>                                            
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div class="modal" id="successResetPasswordLinkModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" id="success-modal">
                        <div class="modal-body">
                            <div class="d-flex justify-content-end">
                                <i class="fa fa-solid fa-circle-xmark fa-xl close-mark light-gray" role="button" data-bs-dismiss="modal"></i>
                            </div>
                            <div class="text-center">
                                <div class="col-md-12">
                                    <img src="images/resc/check-animation.gif" class="check-perc" alt="iVote Logo">
                                </div>
                                <div class="row">
                                    <div class="col-md-12 pb-3">
                                        <p class="fw-bold text-success spacing-4 success-title">Success!</p>
                                        <p class="fw-medium spacing-5 success-subtitle">An email containing the password reset link has been sent. Kindly check your email.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Verify Token Modal -->
            <!-- <div class="modal fade" id="verifyTokenModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row px-4 pt-4">
                                <div class="col-md-12 pb-3">
                                    <p class="fw-bold main-color text-center spacing-4 pt-3">Verify Token</p>
                                    <form id="verifyTokenForm" method="POST">
                                        <div class="mb-1">
                                            <label for="token" class="form-label">Enter Token</label>
                                            <input type="text" class="form-control bg-primary shadow-sm" id="token" name="token" placeholder="Put token here">
                                        </div>
                                        <div id="tokenErrorMessage" class="fs-7 text-danger mb-4 fw-medium me-5">

                                        </div>
                                        <div class="row d-flex justify-content-center">
                                            <div class="col-md-5">
                                                <button type="button" class="btn btn-secondary w-100" id="cancelVerifyTokenBtn" data-bs-dismiss="modal">Cancel</button>                                               
                                            </div>
                                            <div class="col-md-7">
                                                <button type="submit" id="verifyTokenBtn" class="btn btn-org-color w-100">Send Token</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div> -->

        </div>        
    </section>

</body>
</html>
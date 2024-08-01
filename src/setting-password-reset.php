<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, 'includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('includes/classes/db-connector.php');
require_once FileUtils::normalizeFilePath('includes/session-handler.php');
include_once FileUtils::normalizeFilePath('includes/error-reporting.php');

if (isset($_SESSION['voter_id']) && (isset($_SESSION['role'])) && ($_SESSION['role'] == 'student_voter')) {
    // ------ SESSION EXCHANGE
    include FileUtils::normalizeFilePath('includes/session-exchange.php');
    // ------ END OF SESSION EXCHANGE

    $connection = DatabaseConnection::connect();
    $voter_id = $_SESSION['voter_id']; // Get voter id to update the vote status
    $vote_status = $_SESSION['vote_status']; // Get voter id to update the vote status
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Change Password</title>
        <link rel="icon" type="image/x-icon" href="images/resc/ivote-favicon.png">

        <!-- Montserrat Font -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <!-- Fontawesome CDN Link -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
        <!-- Bootstrap 5 code -->
        <link type="text/css" href="../vendor/node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="styles/loader.css" />
        <link rel="stylesheet" href="styles/feedback-suggestions.css" />
        <link rel="stylesheet" href="styles/setting-email.css" />
        <link rel="stylesheet" href="<?php echo '../src/styles/orgs/' . $org_acronym . '.css'; ?>">
        <!-- Jquery-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <!-- Icons -->
        <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

        <style>
            .nav-link:hover,
            .nav-link:focus {
                color: var(--main-color);
            }

            .navbar-nav .nav-item.dropdown.show .nav-link.main-color {
                color: var(--main-color);
            }

            .navbar-nav .nav-item.dropdown .nav-link.main-color,
            .navbar-nav .nav-item.dropdown .nav-link.main-color:hover,
            .navbar-nav .nav-item.dropdown .nav-link.main-color:focus {
                color: var(--main-color);
            }
        </style>
    </head>

    <body>

        <?php
        include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/loader.html');
        include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/topnavbar.php');
        ?>

        <main>
            <div class="container-fluid">
                <div class="row justify-content-center align-items-center">
                    <div class="col-12 col-md-6 reset-password-form" style="margin-top: 170px; margin-bottom: 140px;">
                        <form class="needs-validation" id="reset-password-form" novalidate enctype="multipart/form-data">
                            <input type="hidden" id="voter_id" name="voter_id" value="<?php echo $voter_id ?>">
                            <div class="img-container">
                                <img src="images/resc/Change-Pass/<?php echo strtolower($org_name); ?>-change-pass.png" alt="Forgot Password Icon" class="forgot-password-icon">
                            </div>

                            <div class="form-group" style="text-align: center;">
                                <h4 class="reset-password-title main-color">Set your new password</h4>
                                <p class="reset-password-subtitle">Let's keep your account safe! Please choose a strong password for added security.</p>

                                <div class="row mt-5 mb-3 reset-pass">
                                    <div class="col-md-8 mb-2 position-relative">
                                        <div class="input-group mb-3" id="reset-password">
                                            <input type="password" class="form-control reset-password-password shadow-sm" name="password" placeholder="Enter a strong password" id="password" required>
                                            <label for="password" class="new-password translate-middle-y" id="scoSignUP">NEW PASSWORD</label>
                                            <button class="btn btn-secondary reset-password-password" type="button" id="reset-password-toggle-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <div class="password-requirements">
                                            <ul id="password-requirements-list">
                                                <h6 class="reset-pass-title">YOUR PASSWORD MUST CONTAIN:</h6>
                                                <li id="length" class="requirement unmet"><span class="requirement-circle"></span> Between 8 and 20 characters</li>
                                                <li id="uppercase" class="requirement unmet"><span class="requirement-circle"></span> At least 1 uppercase letter</li>
                                                <li id="lowercase" class="requirement unmet"><span class="requirement-circle"></span> At least 1 lowercase letter</li>
                                                <li id="number" class="requirement unmet"><span class="requirement-circle"></span> At least 1 number</li>
                                                <li id="special" class="requirement unmet"><span class="requirement-circle"></span> At least 1 special character</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-md-8 mb-0 mt-0 position-relative">
                                        <div class="input-group" id="reset-password">
                                            <input type="password" class="form-control reset-password-password shadow-sm" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" required>
                                            <label for="password_confirmation" class="new-password translate-middle-y" id="scoSignUP">CONFIRM PASSWORD</label>
                                            <button class="btn btn-secondary reset-password-password" type="button" id="reset-password-toggle-2">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <div id="password-mismatch-error" class="ps-1 text-danger fw-bold mt-2" style="display: none; font-size: 12px;">PASSWORDS DO NOT MATCH.</div>
                                    </div>
                                </div>
                            <div class="pb-4">
                                <center><button class="btn main-bg-color text-white mt-3 mb-3" id="submit" type="submit" disabled>Set Password</button></center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Success Modal -->
            <div class="modal" id="successPasswordResetModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" id="success-modal">
                        <div class="modal-body">
                            <div class="text-center">
                                <div class="col-md-12">
                                    <img src="images/resc/check-animation.gif" class="check-perc" alt="Checked Logo">
                                </div>
                                <div class="row">
                                    <div class="col-md-12 pb-3">
                                        <p class="fw-bold fs-3 text-success spacing-4">Password Updated</p>
                                        <p class="fw-medium spacing-3">Your password has been successfully updated! Please log in again.</p>
                                        <button class="button-check main-bg-color text-white py-2 px-4 custom-link" onclick="window.location.href='includes/voter-logout.php'"><b>Back to Landing Page</b></button>
                                    </div>
                                </div>
                                <p class="timer">Redirecting to landing page in <strong>10</strong> seconds...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="footer">
            <?php include_once __DIR__ . '/includes/components/footer.php'; ?>
        </div>

        <script src="scripts/feather.js"></script>
        <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="scripts/loader.js"></script>
        <script src="scripts/user-reset-password.js"></script>
    </body>

    </html>

<?php
} else {
    header("Location: landing-page");
}
?>
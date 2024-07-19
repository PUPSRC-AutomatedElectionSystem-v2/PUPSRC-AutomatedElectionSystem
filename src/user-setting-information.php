<?php 
include_once str_replace('/', DIRECTORY_SEPARATOR, 'includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('includes/classes/db-connector.php');
require_once FileUtils::normalizeFilePath('includes/classes/csrf-token.php');
require_once FileUtils::normalizeFilePath('includes/session-handler.php');
include_once FileUtils::normalizeFilePath('includes/error-reporting.php');
require_once FileUtils::normalizeFilePath('includes/classes/manage-ip-address.php');

if (isset($_SESSION['voter_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'student_voter') {


    // ------ SESSION EXCHANGE
    include FileUtils::normalizeFilePath('includes/session-exchange.php');
    // ------ END OF SESSION EXCHANGE

    $connection = DatabaseConnection::connect();
    $voter_id = $_SESSION['voter_id'];

    $csrf_token = CsrfToken::generateCSRFToken();

    $stmt = $connection->prepare("SELECT * FROM voter WHERE voter_id = ?");
    $stmt->bind_param('s', $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($_SESSION['organization'] != 'sco') {
        $firstColumnClass = 'col-lg-3 mb-4 pe-lg-3';
        $secondColumnClass = 'col-lg-9 ps-lg-4';
    } else {
        // 'sco' organization layout
        $firstColumnClass = 'd-none';
        $secondColumnClass = 'col-lg-12 col-12 col-md-12 col-sm-12'; 
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Information</title>
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
    <link rel="stylesheet" href="styles/user-setting-information.css" />
    <link rel="stylesheet" href="styles/profile.css" />
    <link rel="stylesheet" href="<?php echo '../src/styles/orgs/' . $org_acronym . '.css'; ?>">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

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
        <div class="container" style="margin-top: 5%; margin-bottom:5%;">
            <div class="row">
                <!-- Left side (Settings & Privacy) -->
                <div class="<?php echo $firstColumnClass; ?>">
                    <div class="row pb-3">
                        <div class="px-4 pt-4 pb-3 title main-color text-center spacing">
                            <h5><b>Settings & Privacy</b></h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="p-4 title" style="font-size: 12.8px;">
                            <div class="px-2">
                                <div class="main-color d-flex align-items-center pt-2 pb-4">
                                    <div class="pe-4">
                                        <i data-feather="user" class="white" style="width: 20px; height: 20px;"></i>
                                    </div>
                                    <div>
                                        <div class="side-nav mb-0">
                                            <a href="user-setting-information.php" class="custom-link"> Information </a>
                                        </div>
                                        <div class="mb-0 des">See your account information like your email address and certificate of registration.</div>
                                    </div>
                                </div>
                                <?php if ($_SESSION['organization'] != 'sco') { ?>
                                    <div class="d-flex align-items-center pb-4">
                                        <div class="pe-4">
                                            <i data-feather="lock" class="white" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div>
                                            <div class="side-nav mb-0">
                                                <a href="user-setting-password.php" class="custom-link"> Change Password </a>
                                            </div>
                                            <div class="mb-0 des">Ensure your account's security by updating your password whenever you need.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center pb-4">
                                        <i class="fas fa-exchange-alt me-4" style="font-size: 1.1rem;"></i>
                                        <div>
                                            <div class="side-nav mb-0">
                                                <a href="user-setting-transfer.php" class="custom-link">Transfer Org</a>
                                            </div>
                                            <div class="mb-0 des">Move your account to a different organization upon transfer.</div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side (Information) -->
                <div class="<?php echo $secondColumnClass; ?>">
                    <div class="row">
                        <div class="p-4 title" style="font-size: 15px;">
                            <div class="py-3 px-2 px-lg-4 px-sm-1 row">
                                <h5 class="main-color pb-3">
                                    <b>
                                        <i data-feather="user" class="fas fa-exchange-alt me-4" style="font-size: 1rem;"></i> Information
                                    </b>
                                </h5>
                                <div class="col-lg-6 col-12 col-sm-12 col-md-12 mb-4 mb-lg-0 text-center">
                                    <div class="d-flex flex-column align-items-center pt-4">
                                        <div class="rounded-icon main-bg-color">
                                            <i data-feather="mail" class="white im-cust feather-4l"></i>
                                        </div>
                                        <div class="text-center w-100">
                                            <h4 class="email-add">Email Address</h4>
                                            <p class="user-email-1"><?php echo $row['email']; ?></p>
                                            <hr style="display: block;" class="mx-5">
                                            <?php if ($_SESSION['organization'] != 'sco') { ?>
                                                <a href="#" class="pt-2 main-color custom-link" id="changePasswordBtn" data-bs-toggle="modal" data-bs-target="#confirmPassModal">Change Email Address</a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-12 col-sm-12 col-md-12">
                                    <div class="pt-4"></div>
                                    <iframe id="pdfViewer" src="<?php echo "user_data/$org_acronym/cor/" . $row['cor']; ?>" frameborder="0" style="width: 100%; height:100%"></iframe>
                                    <div class="row">
                                        <div class="filename">
                                            <span>
                                                <i data-feather="paperclip" class="white im-cust feather-2xs"></i> <?php echo $row['cor']; ?>
                                            </span>
                                            <span class="right-icons">
                                                <a href="<?php echo "user_data/$org_acronym/cor/" . $row['cor']; ?>" download class="custom-link">
                                                    <i class="fas fa-download fa-sm"></i> Download
                                                </a>
                                                <i class="fa-solid fa-expand fullscreen-icon" onclick="toggleFullScreen('pdfViewer')"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="py-5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Password Modal -->
        <div class="modal fade adjust-modal" id="confirmPassModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content d-flex align-items-center justify-content-center" id="success-modal">
                    <div class="modal-body text-center w-100 mt-4 mb-2">
                        <div class="col-md-12">
                            <img src="images/resc/icons/shield.png" style="width: 25%; height:25%" alt="Shield Logo">
                        </div>
                        <p class="fw-bold fs-4 information-title spacing-4 mt-3">Enter Password</p>
                        <p class="info-sub">To change your email address, please provide your current password.</p>
                        <form id="confirmPasswordForm" method="post">
                            <!-- CSRF Token hidden field -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="password-input-container">
                                <input type="password" maxlength="50" class="password-input" name="password" id="password" autocomplete="off" onkeypress="return preventSpaces(event)" placeholder="Type password here..." oninput="handleInput()">
                                <span class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                            <div class="pt-2" id="errorMessage" style="color: red; display: none; font-size:12px"></div>
                            <div class="pt-4">
                                <input type="hidden" name="voter_id" value="<?php echo $voter_id ?>">
                                <input type="hidden" name="email" value="<?php echo $row['email'] ?>">
                                <button type="button" class="btn btn-gray" id="cancelModalButton" data-bs-dismiss="modal" aria-label="Close"><b>Cancel</b></button>
                                <button type="button" class="btn button-proceed" id="realSubmitBtn" disabled>Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal" id="approvalModal" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="d-flex justify-content-end">
                            <i class="fa fa-solid fa-circle-xmark fa-xl close-mark light-gray custom-margin" data-bs-dismiss="modal" aria-label="Close"></i>
                        </div>
                        <div class="text-center">
                            <div class="col-md-12">
                                <img src="images/resc/check-animation.gif" class="check-perc" alt="iVote Logo">
                            </div>
                            <div class="row">
                                <div class="col-md-12 pb-3">
                                    <p class="fw-bold fs-3 success-color spacing-4">Email Verification Sent</p>
                                    <p class="fw-medium spacing-5">We sent a message to your current email.<br> Please follow the instructions to change <br> your email address.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maximum Attempt Modal -->
        <div class="modal fade adjust-modal" id="maximumAttemptsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <div class="d-flex justify-content-end w-100 border-0 me-4 mt-4">
                            <button type="button" class="btn-close custom-close-btn" id="closeMaximumAttemptsModal" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="col-md-12">
                            <img src="images/resc/warning.png" class="py-2" style="width: 30%; height:30%" alt="Warning Logo">
                        </div>
                        <p class="fw-bold fs-4 information-title spacing-4 text-danger">Max Attempts Reached</p>
                        <p class="info-sub">Sorry you've reached the maximum number<br> of attempts to confirm your password. For <br>
                            security reasons, please wait for 30 minutes <br> before trying again.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Sending Modal -->
        <div class="modal" id="emailSending" tabindex="-1" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body pb-5">
                        <div class="text-center">
                            <div class="col-md-12 pt-5">
                                <img src="images/resc/loader.gif" class="loading-gif" alt="iVote Logo">
                            </div>
                            <div class="row">
                                <div class="col-md-12 pt-4">
                                    <p class="fw-bold fs-4 spacing-4">Sending email...</p>
                                    <p class="fw-medium spacing-5 fs-7">Please wait for a moment.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <div class="footer">
        <?php include_once __DIR__ . '/includes/components/footer.php'; ?>
    </div>

    <script src="../src/scripts/feather.js"></script>
    <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="scripts/loader.js"></script>
    <script src="scripts/change-email.js"></script>
</body>

</html>

<?php
} else {
    header("Location: landing-page.php");
}
?>

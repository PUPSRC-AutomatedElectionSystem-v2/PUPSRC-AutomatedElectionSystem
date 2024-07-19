<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('includes/classes/db-connector.php');
require_once FileUtils::normalizeFilePath('includes/session-handler.php');
require_once FileUtils::normalizeFilePath('includes/classes/session-manager.php');
require_once FileUtils::normalizeFilePath('includes/classes/query-handler.php');

if (isset($_SESSION['voter_id']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'head_admin')) {


    include FileUtils::normalizeFilePath('includes/session-exchange.php');
    include FileUtils::normalizeFilePath('submission_handlers/manage-acc.php');


    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/x-icon" href="images/resc/ivote-favicon.png">
        <title>Manage Account</title>

        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

        <!-- Styles -->
        <link rel="stylesheet" href="<?php echo 'styles/orgs/' . $org_name . '.css'; ?>" id="org-style">
        <link rel="stylesheet" href="styles/style.css" />
        <link rel="stylesheet" href="styles/core.css" />
        <link rel="stylesheet" href="styles/manage-voters.css" />
        <link rel="stylesheet" href="styles/loader.css" />
        <link rel="stylesheet" href="../vendor/node_modules/bootstrap/dist/css/bootstrap.min.css" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="scripts/loader.js"></script>

    </head>

    <body>

        <?php
        include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/loader.html');
        include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/sidebar.php');
        ?>

        <div class="main">
            <div class="container mb-5 breadcrumbs-cont">
                <div class="row justify-content-center breadcrumbs-main">
                    <div class="col-md-11">
                        <div class="breadcrumbs d-flex">
                            <button type="button" class="btn btn-lvl-white d-flex align-items-center spacing-8 fs-8">
                                <i data-feather="users" class="white im-cust feather-2xl"></i> <span
                                    class="hide-text">MANAGE USERS</span>
                            </button>
                            <button type="button" class="btn btn-lvl-current rounded-pill spacing-8 fs-8">ADD
                                VOTERS</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10 card-box">
                        <div class="container-fluid">
                            <div class="card-box">
                                <div class="row">
                                    <div class="content p-5">
                                        <!-- Header -->
                                        <section>
                                            <div>
                                                <p class="fs-4 fw-bold spacing-6">Import Voters' Accounts</p>
                                            </div>

                                            <div>
                                                <p class="fw-medium spacing-6">Import the list of students to create their
                                                    accounts in your organization.</p>
                                            </div>
                                        </section>

                                        <!-- File Import -->
                                        <section>
                                            <div class="row pt-3">
                                                <div class="col-12 d-flex">
                                                    <div class="col-xl-11 col-md-11">
                                                        <input class="form-control pl-2" style="background-color:#fff"
                                                            type="file" name="voters-list" id="voters-list"
                                                            accept=".xls, .xlsx, .csv" max="25MB" required>
                                                        <div class="form-text mt-2" style="font-size: 12px;"><span
                                                                class="fw-semibold">Note:</span> Only CSV, Excel files are
                                                            allowed.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <!-- Additional Information/Guide -->
                                        <section class="pt-4 stdnt-file-guide">
                                            <p><i data-feather="info" class="link-blue mb-xl-1 im-cust feather-sm pe-none"
                                                    style="margin-right: 0.5rem;"></i><span class="fs-7 fw-medium">Uncertain
                                                    about what to upload? See the
                                                    <a class="link-blue text-underline fw-semibold" href="#"
                                                        target="_blank">required format</a>
                                                    and ensure your file includes these columns and data.</span></p>
                                        </section>

                                        <!-- Submit Button -->
                                        <section>
                                            <div class="row pt-4">
                                                <div class="col-12 d-flex justify-content-end">
                                                    <div class="col-xl-4 col-md-4">
                                                        <div class="d-flex justify-content-end me-5">
                                                            <button
                                                                class="btn btn-main-primary px-sm-5 py-sm-1-5 btn-sm fw-bold fs-6 spacing-6 text-white"
                                                                type="button" id="import-voters" name="import-voters">Import
                                                                Voters</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once __DIR__ . '/includes/components/footer.php'; ?>

        <!-- Duplicates Modal -->
        <div class="modal" id="duplicatesModal" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="d-sm-flex text-end justify-content-end">
                            <i class="fa fa-solid fa-circle-xmark fa-xl close-mark light-gray" id="duplicatesClose">
                            </i>
                        </div>
                        <div class="text-center">
                            <div class="col-md-12">
                                <img src="images/resc/warning.png" class="warning-icon" alt="Warning Icon">
                            </div>

                            <div class="row">
                                <div class="col-md-12 pb-3 pt-4">
                                    <p class="fw-bold danger spacing-4" id="duplicatesTitle">Duplicate Students Found!</p>
                                    <p class="fw-medium spacing-5 pt-2"></p>
                                    <div id="duplicatesList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Only Excel, CSV Files Are Allowed Modal -->
        <div class="modal" id="onlyPDFAllowedModal" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="d-sm-flex text-end justify-content-end">
                            <i class="fa fa-solid fa-circle-xmark fa-xl close-mark light-gray" id="onlyPDFClose"
                                data-bs-dismiss="modal">
                            </i>
                        </div>
                        <div class="text-center">
                            <div class="col-md-12">
                                <img src="images/resc/warning.png" class="warning-icon" alt="Warning Icon">
                            </div>

                            <div class="row">
                                <div class="col-md-12 pb-3 pt-4">
                                    <p class="fw-bold danger spacing-4" id="dangerTitle">Invalid file format!</p>
                                    <p class="fw-medium spacing-5 pt-2" id="dangerSubtitle">Excel and CSV files are only
                                        allowed. Please also ensure the file is no larger than 25 mb and the file headers are correct. Let's try that
                                        again!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invalid Content of CSV/Excel Modal -->
        <div class="modal" id="invalidContentModal" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="d-sm-flex text-end justify-content-end">
                            <i class="fa fa-solid fa-circle-xmark fa-xl close-mark light-gray" id="invalidContentClose"
                                data-bs-dismiss="modal">
                            </i>
                        </div>
                        <div class="text-center">
                            <div class="col-md-12">
                                <img src="images/resc/warning.png" class="warning-icon" alt="Warning Icon">
                            </div>

                            <div class="row">
                                <div class="col-md-12 pb-3 pt-4">
                                    <p class="fw-bold danger spacing-4" id="invalidTitle">Invalid Content!</p>
                                    <p class="fw-medium spacing-5 pt-2" id="invalidSubtitle">The file content is invalid. Please ensure that: 
                                    <ul class="text-start">
                                    <li class="fw-medium spacing-5 pt-2">The file headers are correct and in the right order</li>
                                    <li class="fw-medium spacing-5 pt-2">All required fields are filled</li>
                                    <li class="fw-medium spacing-5 pt-2">Data formats are correct (e.g., Valid Email Addresses, Correct Student ID, Complete Name)</li>
                                    <li class="fw-medium spacing-5 pt-2">No Duplicates (You may check the Recycle Bin for such duplicates)</li>
                                </ul>
                                        <p class="fw-medium spacing-5 pt-2">
                                        Please check your file and try again.
                                        </p>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Imported Successfully Modal -->
        <div class="modal" id="importDoneModal" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body pb-5">
                        <div class="d-flex justify-content-end">
                            <i class="fa fa-solid fa-circle-xmark fa-xl close-mark light-gray" data-bs-dismiss="modal">
                            </i>
                        </div>
                        <div class="text-center">
                            <div class="col-md-12">
                                <img src="images/resc/check-animation.gif" class="check-perc" alt="iVote Logo">
                            </div>

                            <div class="row">
                                <div class="col-md-12 pb-3">
                                    <p class="fw-bold fs-3 success-color spacing-4">Successfully imported!</p>
                                    <p class="fw-medium spacing-5 fs-7">Accounts have been successfully created. You can
                                        view the newly added accounts in <span class="fw-bold">Voters’ Accounts.</span>.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-12 pt-1 d-flex justify-content-center">
                                <button class="btn btn-success px-sm-5 py-sm-1-5 btn-sm fw-bold fs-6 spacing-6"
                                    aria-label="Close"> <a href="manage-voters.php" style="color: white"> Go To Voters'
                                        Accounts</a></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="scripts/script.js"></script>
        <script src="scripts/feather.js"></script>
        <script src="scripts/import-voter.js"></script>

    </body>

    </html>

    <?php
} else {
    header("Location: landing-page.php");
}
?>
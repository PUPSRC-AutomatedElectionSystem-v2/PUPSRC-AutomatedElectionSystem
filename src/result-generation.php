<?php
// Include necessary files
include_once str_replace('/', DIRECTORY_SEPARATOR, 'includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('includes/classes/db-connector.php');
require_once FileUtils::normalizeFilePath('includes/session-handler.php');
require_once FileUtils::normalizeFilePath('includes/classes/session-manager.php');
require_once FileUtils::normalizeFilePath('includes/classes/query-handler.php');
require_once FileUtils::normalizeFilePath('includes/classes/feedback-manager.php');

// Check session and role
if (isset($_SESSION['voter_id']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'head_admin')) {
    // Include session exchange
    $connection = DatabaseConnection::connect();


    // ------ SESSION EXCHANGE
    include FileUtils::normalizeFilePath('includes/session-exchange.php');
    // ------ END OF SESSION EXCHANGE

    // Specify the path to the file you want to check
    $file_to_check = 'includes/data/' . $org_name . '/voters-turnout.json'; // Adjust the path accordingly

    function displayElectionReports()
    {
        $org_name = $_SESSION['organization'] ?? '';
        // Path to the JSON file
        $jsonFilePath = 'includes/data/' . $org_name . '/voters-turnout.json';
        $jsonData = json_decode(file_get_contents($jsonFilePath), true);
        // Fetch organization name

?>

        <div class="container">
            <div class="row">
                <div class="col-11 col-md-10 col-lg-11 mx-auto" style="margin-top: -5px;">
                    <div class="col-11 col-md-10 col-lg-11 mx-auto">
                        <div class="card-report main-bg-color mb-5">
                            <div class="card-body main-bg-color d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title"><i data-feather="bar-chart-2" class="white mb-xl-1"></i> Election Reports</h5>
                                    <p class="card-text" id="selectedYear">
                                        <?php
                                        $jsonFilePath = __DIR__ . '/includes/data/' . $org_name . '/voters-turnout.json';
                                        $jsonData = json_decode(file_get_contents($jsonFilePath), true);
                                        if ($jsonData && isset($jsonData['candidate'])) {
                                            $election_years = array_unique(array_column($jsonData['candidate'], 'election_year'));
                                            rsort($election_years);
                                            $selected_year = isset($_GET['election_year']) ? $_GET['election_year'] : null;
                                            if ($selected_year && in_array($selected_year, $election_years)) {
                                                echo "Current Academic Year: <strong>" . htmlspecialchars($selected_year) . "</strong>";
                                            } else {
                                                echo "Select <strong> ELECTION YEAR</strong> to show";
                                            }
                                        } else {
                                            echo "<strong> ELECTION YEAR</strong>";
                                        }
                                        ?>
                                    </p>
                                </div>
                                <?php
                                if ($jsonData && isset($jsonData['candidate'])) {
                                    $selected_year = isset($_GET['election_year']) ? $_GET['election_year'] : null;
                                    $selected_year_title = "Election Year";
                                    if ($selected_year && in_array($selected_year, $election_years)) {
                                        $selected_year_title = htmlspecialchars($selected_year);
                                    }
                                ?>
                                    <button class="report-generator-btn main-bg-color" onclick="downloadAllPDF()" type="button" aria-expanded="false">
                                        <i data-feather="download" class="white im-cust feather-1xs"></i> Download
                                    </button>
                                    <div class="dropdown main-color">
                                        <button class="btn-election hover-color dropdown-button btn-with-margin" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="ms-auto">
                                                <div id="dropdownButtonText" class="main-color text-truncate"><?php echo "A.Y. " . $selected_year_title; ?> <i data-feather="chevron-down" class="white im-cust feather-1xs"></i></div>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <?php
                                            foreach ($election_years as $year) {
                                            ?>
                                                <li><a class="dropdown-item" href="#" onclick="selectYear('<?php echo $year; ?>')"><?php echo $year; ?></a></li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                <?php
                                } else {
                                    echo '<span class="empty-text">No election year available</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card card-header main-color">
                            <p class="main-color"><strong>ELECTION RESULTS</strong></p>
                        </div>
                    </div>

                    <div id="election-results"></div>

                    <div class="container-fluid">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-auto">
                                <div class="dropdown2">
                                    <button class="btn btn-convert dropdown-button">
                                        <i data-feather="download" class="main-color im-cust small-icon"></i> Download results as...
                                    </button>
                                    <div class="dropdown-content2">
                                        <a href="#" onclick="downloadPDF()">PDF (.pdf)</a>
                                        <a href="#" onclick="downloadExcel()">Excel (.xsl)</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-previous"  id="btn-previous">
                                    <i class="fas fa-chevron-left"></i>
                                    <span class="d-none d-md-inline">Previous</span>
                                </button>
                                <button class="btn btn-next" id="btn-next">
                                    <span class="d-none d-md-inline">Next</span>
                                    <i class="fas fa-chevron-right" ></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <br>
                    <br>

                    <div class="col-11 col-md-10 col-lg-11 mx-auto">
                        <div class="card-graph mb-5 position-relative">
                            <canvas id="myChart"></canvas>
                            <div class="form-group custom-form-group">
                                <select id="positionSelect" class="form-control2 main-bg-color text-truncate">
                                    <!-- Dynamically populate this with PHP -->
                                    <?php
                                    if (!empty($jsonData['position'])) {
                                        foreach ($jsonData['position'] as $position) {
                                            echo "<option value='" . htmlspecialchars($position['position_id']) . "'>" . htmlspecialchars($position['title']) . "</option>";
                                        }
                                    } else {
                                        echo '<option value="" disabled>No positions available</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <script>
                        const jsonData = <?php echo json_encode($jsonData); ?>;
                    </script>

                    <div class="col-11 col-md-10 col-lg-11 mx-auto">
                        <div class="card card-header">
                            <p class="main-color"><strong>VOTERS TURNOUT</strong></p>
                        </div>
                    </div>

                    <?php
                    // Specify the path to your JSON file
                    $json_file = __DIR__ . '/includes/data/' . $org_name . '/voters-turnout.json';

                    // Check if the JSON file exists
                    if (file_exists($json_file)) {
                        // Read the JSON file
                        $json_data = file_get_contents($json_file);

                        // Decode JSON data into an associative array
                        $data = json_decode($json_data, true);

                        // Extract relevant data
                        $candidateCount = $data['candidate_count'];
                        $totalVotersCount = $data['voter_counts']['totalVotersCount'];
                        $votedVotersCount = $data['voter_counts']['votedVotersCount'];
                        $abstainedVotersCount = $data['voter_counts']['abstainedVotersCount'];
                        $totalPercentage = $data['voter_counts']['totalPercentage'];
                        $votedPercentage = $data['voter_counts']['votedPercentage'];
                    } else {
                        // Handle the case where the JSON file doesn't exist or can't be read
                        echo "Error: JSON file not found or readable.";
                        exit;
                    }
                    ?>
                    <div class="col-11 col-md-10 col-lg-11 mx-auto">
                        <div class="row m-0 p-0 justify-content-between">
                            <div class="col-md-7 m-0 ps-0 pe-md-4 pe-md-0 pe-sm-0 pe-0 ">
                                <div class="card2 p-4 " style="border-radius: 20px; height: 270px;">
                                    <div>
                                        <div class="row justify-content-center">
                                            <div class="col-md-12 col-lg-6 pe-lg-0 pe-xl-5">
                                                <canvas id="chartProgress" width="200" height="200"></canvas>
                                            </div>
                                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                            <script>
                                                // Create a doughnut chart
                                                var myChartCircle = new Chart('chartProgress', {
                                                    type: 'doughnut',
                                                    data: {
                                                        datasets: [{
                                                            data: [<?php echo $totalPercentage; ?>, <?php echo $votedPercentage; ?>], // Static dummy data
                                                            backgroundColor: ['#4CAF50', '#E5E5E5'], // Static colors
                                                            borderWidth: 0
                                                        }]
                                                    },
                                                    options: {
                                                        maintainAspectRatio: false,
                                                        cutout: 75,
                                                        cutoutPercentage: 75, // Adjust the size of the circle
                                                        rotation: Math.PI / 2,
                                                        legend: {
                                                            display: false
                                                        },
                                                        tooltips: {
                                                            enabled: true,
                                                            callbacks: {
                                                                label: function(tooltipItem, data) {
                                                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                                                    var total = dataset.data.reduce(function(previousValue, currentValue) {
                                                                        return previousValue + currentValue;
                                                                    });
                                                                    var currentValue = dataset.data[tooltipItem.index];
                                                                    var percentage = Math.floor(((currentValue / total) * 100) + 0.5);
                                                                    return percentage + "%";
                                                                }
                                                            }
                                                        }
                                                    },
                                                    plugins: [{
                                                            beforeInit: (chart) => {
                                                                const dataset = chart.data.datasets[0];
                                                                dataset.data = [dataset.data[0], 100 - dataset.data[0]]; // Calculate remaining percentage
                                                            }
                                                        },
                                                        {
                                                            beforeDraw: (chart) => {
                                                                var width = chart.width,
                                                                    height = chart.height,
                                                                    ctx = chart.ctx;
                                                                ctx.restore();
                                                                var fontSize = (height / 150).toFixed(2);
                                                                ctx.font = "bold " + fontSize + "em Montserrat, sans-serif"; // Bold and Montserrat
                                                                ctx.fillStyle = "black";
                                                                ctx.textBaseline = "middle";
                                                                var text = parseFloat(chart.data.datasets[0].data[0]).toFixed(0) + "%", // Rounds off to 3 decimal places
                                                                    textX = Math.round((width - ctx.measureText(text).width) / 2),
                                                                    textY = height / 1.75;
                                                                ctx.fillText(text, textX, textY);

                                                                // Adding 'Completed' text
                                                                var completedFontSize = (height / 300).toFixed(2); // Smaller font size
                                                                ctx.font = "bold " + completedFontSize + "em Montserrat";
                                                                var completedText = "Completed",
                                                                    completedTextX = Math.round((width - ctx.measureText(completedText).width) / 2),
                                                                    completedTextY = textY - 30; // Position above the percentage text
                                                                ctx.fillText(completedText, completedTextX, completedTextY);
                                                                ctx.save();
                                                            }
                                                        }
                                                    ]
                                                });
                                            </script>

                                            <div class="col-md-12 col-lg-6 justify-content-center align-self-center border-left pb-4">
                                                <div class="col-md-12 metrics-header justify-content-center align-items-center d-flex d-sm-flex d-md-block mt-3">
                                                    <small class="text-center ps-4 fw-700">Total Votes</small>
                                                </div>
                                                <div class="col-md-12 metrics-content justify-content-center align-items-center main-color d-flex d-sm-flex d-md-block mb-3">
                                                    <span class="text-center ps-4 fw-700 fs-20"><?php echo  $votedVotersCount; ?> out of <?php echo $totalVotersCount; ?></span>
                                                </div>

                                                <div class="col-md-12 metrics-header justify-content-center align-items-center d-flex d-sm-flex d-md-block">
                                                    <small class="text-center ps-4 fw-700">Total Abstained</small>
                                                </div>
                                                <div class="col-md-12 metrics-content justify-content-center align-items-center main-color d-flex d-sm-flex d-md-block">
                                                    <span class="text-center ps-4 fw-700"><?php echo $abstainedVotersCount; ?> students</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5 col-lg-5 px-0 d-flex flex-column">
                                <!-- Voters Account Card -->
                                <div class="card p-1 mt-1 mt-md-0 " style="border-radius: 20px; border: none;">
                                    <div class="card-body3 d-flex align-items-center justify-content-between p-3" style="padding-left: 30px;">
                                        <div class="row w-100">
                                            <div class="col-9">
                                                <div class="col-12">
                                                    <span class="secondary-metrics-header main-color">Total count of</span>
                                                </div>
                                                <div class="col-12">
                                                    <span class="secondary-metrics-content">VOTER ACCOUNTS</span>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="col-12">
                                                    <div class="circle main-bg-color">
                                                        <span class="secondary-metrics-number"><?php echo $totalVotersCount; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <!--Candidate Card-->
                                <div class="card p-1 mt-1 mt-md-0" style="border-radius: 20px; border: none;">
                                    <div class="card-body3 d-flex align-items-center justify-content-between p-3" style="padding-left: 30px;">
                                        <div class="row w-100">
                                            <div class="col-9">
                                                <div class="col-12">
                                                    <span class="secondary-metrics-header main-color">Total count of</span>
                                                </div>
                                                <div class="col-12">
                                                    <span class="secondary-metrics-content">CANDIDATES</span>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="col-12">
                                                    <div class="circle main-bg-color">
                                                        <span class="secondary-metrics-number"><?php echo $candidateCount; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <!-- Positions Table -->
                        <div class="row justify-content-center">
                            <div class="container-fluid">
                                <div class="card-box">
                                    <div class="row">
                                        <div class="content">
                                            <div class="table-wrapper">
                                                <div class="table-title">
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <p class="fs-3 main-color fw-bold ls-10 spacing-6 text-comment2">Candidate Counts by Position</p>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="row">
                                                                <div class="col-md-12 text-end flex-end">
                                                                    <div class="d-inline-block ps-3">
                                                                        <form class="d-inline-block">
                                                                            <div class="search-container">
                                                                                <input class="search-input fs-7 spacing-6 fw-medium" type="text" placeholder="  Search" id="searchPosition">
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table class="table table-hover positions-table">
                                                    <thead class="tl-header">
                                                        <tr>
                                                            <th class="col-md-7 text-center fs-7 fw-bold spacing-5"><i data-feather="user" class="feather-xs im-cust"></i>Position Name</th>
                                                            <th class="col-md-2 text-center fs-7 fw-bold spacing-5"><i data-feather="bar-chart-2" class="feather-xs im-cust"></i>Total Count</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Table data will be injected here by JavaScript -->
                                                    </tbody>
                                                </table>
                                                <div class="clearfix col-xs-12">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <ul class="pagination positions-pagination">
                                                            <!-- Pagination will be injected here by JavaScript -->
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <div class="col-11 col-md-10 col-lg-11 mx-auto">
                        <div class="card card-header2">
                            <p class="main-color"><strong>FEEDBACK AND SUGGESTIONS</strong></p>
                        </div>
                        <div class="card-feedback mb-5">
                            <div class="col-sm-6">
                                <p class="feedback-text fs-3 main-color fw-bold ls-10 spacing-6">Feedback Ratings</p>
                            </div>
                            <div class="d-flex flex-column justify-content-between">
                                <div class="row">
                                    <div class="emoji">
                                        <ul class="feedback pb-4">
                                            <li class="angry" data-value="Very Unsatisfied">
                                                <span class="emoji-class"></span>
                                            </li>
                                            <li class="sad" data-value="Unsatisfied">
                                                <span class="emoji-class"></span>
                                            </li>
                                            <li class="ok" data-value="Neutral">
                                                <span class="emoji-class"></span>
                                            </li>
                                            <li class="good" data-value="Satisfied">
                                                <span class="emoji-class"></span>
                                            </li>
                                            <li class="happy" data-value="Very Satisfied">
                                                <span class="emoji-class"></span>
                                            </li>
                                        </ul>
                                        <div class="pb-2"></div>
                                        <div class="horizontal-align">
                                            <?php
                                            // Read the JSON file
                                            $jsonData = file_get_contents('includes/data/' . $org_name . '/voters-turnout.json');

                                            // Decode the JSON data into an associative array
                                            $data = json_decode($jsonData, true);

                                            // Assign the rating percentages to a variable
                                            $ratings_percentage = $data['rating_percentage'];

                                            // Determine the highest percentage rating
                                            $highest_percentage = max($ratings_percentage);
                                            $highest_rating = array_search($highest_percentage, $ratings_percentage);

                                            // Display ratings percentages and add conditional classes
                                            $emojis = [
                                                'Very Unsatisfied' => 'angry',
                                                'Unsatisfied' => 'sad',
                                                'Neutral' => 'ok',
                                                'Satisfied' => 'good',
                                                'Very Satisfied' => 'happy'
                                            ];
                                            ?>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    // Fetch the rating percentages from PHP to JavaScript
                                                    const ratingsPercentage = <?php echo json_encode($ratings_percentage); ?>;
                                                    const emojis = document.querySelectorAll('.emoji .emoji-class');

                                                    // Find the highest percentage rating
                                                    let highestPercentage = 0;
                                                    let highestRating = '';
                                                    Object.keys(ratingsPercentage).forEach(function(rating) {
                                                        if (ratingsPercentage[rating] > highestPercentage) {
                                                            highestPercentage = ratingsPercentage[rating];
                                                            highestRating = rating;
                                                        }
                                                    });

                                                    // Apply classes based on ratings
                                                    emojis.forEach(function(emoji) {
                                                        const emojiRating = emoji.parentNode.dataset.value;
                                                        if (emojiRating === highestRating) {
                                                            emoji.classList.add('highlighted');
                                                        } else {
                                                            emoji.classList.add('lowlight');
                                                        }
                                                    });
                                                });
                                            </script>

                                            <?php foreach ($emojis as $rating => $emoji_class) {
                                                $percentage = $ratings_percentage[$rating] ?? 0; // Default to 0% if rating percentage is not set
                                            ?>
                                                <div class="rating-item <?php if ($rating == $highest_rating) echo 'highest-rating'; ?> d-flex align-items-center justify-content-center">
                                                    <div>
                                                        <h1 class="<?php if ($rating == $highest_rating) echo 'main-color'; ?>"><?php echo $percentage; ?>%</h1>
                                                        <h2 class="<?php if ($rating == $highest_rating) echo 'main-color'; ?>"><?php echo $rating; ?></h2>
                                                    </div>

                                                </div>



                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Table -->
                        <div class="row justify-content-center">
                            <div class="container-fluid">
                                <div class="card-box">
                                    <div class="row">
                                        <div class="content">
                                            <div class="table-wrapper">
                                                <div class="table-title">
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <p class="fs-3 main-color fw-bold ls-10 spacing-6 text-comment">Feedback Comments</p>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="row">
                                                                <div class="col-md-12 text-end flex-end">
                                                                    <div class="d-inline-block ps-3">
                                                                        <form class="d-inline-block">
                                                                            <div class="dropdown sort-by">
                                                                                <button class="sortby-tbn fs-7 spacing-6 fw-medium" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <i class="fa-solid fa-arrow-down-wide-short fa-sm"></i>
                                                                                    Sort by
                                                                                </button>
                                                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton" style="padding: 0.5rem">
                                                                                    <li class="dropdown-item ps-3 fs-7 fw-medium" data-sort="timestamp" data-order="DESC">
                                                                                        <a href="#">Newest to Oldest</a>
                                                                                    </li>
                                                                                    <li class="dropdown-item ps-3 fs-7 fw-medium" data-sort="timestamp" data-order="ASC">
                                                                                        <a href="#">Oldest to Newest</a>
                                                                                    </li>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <table class="table table-hover feedback-table">
                                                    <thead class="tl-header">
                                                        <tr>
                                                            <th class="col-md-7 text-center fs-7 fw-bold spacing-5"><i data-feather="mail" class="feather-xs im-cust"></i>Feedback</th>
                                                            <th class="col-md-2 text-center fs-7 fw-bold spacing-5"><i data-feather="calendar" class="feather-xs im-cust"></i>Date Submitted</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Table data will be injected here by JavaScript -->
                                                    </tbody>
                                                </table>
                                                <div class="clearfix col-xs-12">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <ul class="pagination feedback-pagination">

                                                            <!-- Pagination will be injected here by JavaScript -->
                                                        </ul>
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
            </div>
        </div>



    <?php
    }


    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/x-icon" href="images/resc/ivote-favicon.png">
        <title>Election Reports</title>

        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

        <!-- Styles -->
        <link rel="stylesheet" href="<?php echo 'styles/orgs/' . $org_name . '.css'; ?>" id="org-style">
        <link rel="stylesheet" href="styles/style.css" />
        <link rel="stylesheet" href="styles/core.css" />
        <link rel="stylesheet" href="styles/result.css" />
        <link rel="stylesheet" href="styles/tables.css" />
        <link rel="stylesheet" href="styles/loader.css" />
        <link href="../vendor/node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../vendor/node_modules/bootstrap/dist/css/bootstrap.min.css" />
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

        <!--JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

        <style>
            <?php

            // Output the CSS with the organization color variable for background-color



            ?>.btn-with-margin {
                margin-top: 38px;
                width: 180px;
                height: 33px;
                padding-left: 10px;
                padding-right: -20px;
                margin-right: 50px;
                border-radius: 25px;
            }
        </style>

    </head>

    <body>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

        <?php
        include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/loader.html');
        include_once FileUtils::normalizeFilePath(__DIR__ . '/includes/components/sidebar.php');
        ?>
        <div id="content" class="main">
    <?php
    // Helper function to display empty state messages
    function displayEmptyState($message) {
          // ------ SESSION EXCHANGE
    include FileUtils::normalizeFilePath('includes/session-exchange.php');
    // ------ END OF SESSION EXCHANGE
        ?>
        <div class="row justify-content-center">
            <div class="col-11 col-md-10 col-lg-11" style="margin-top: -15px;">
                <div class="card-report main-bg-color mb-5">
                    <div class="card-body main-bg-color d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><i data-feather="bar-chart-2" class="white mb-xl-1"></i> Election Reports</h5>
                            <p class="card-text" id="selectedYear">
                                <?php
                                $jsonFilePath = __DIR__ . '/includes/data/' . $org_name . '/voters-turnout.json';
                                $jsonData = json_decode(file_get_contents($jsonFilePath), true);
                                if ($jsonData && isset($jsonData['candidate'])) {
                                    $election_years = array_unique(array_column($jsonData['candidate'], 'election_year'));
                                    rsort($election_years);
                                    $selected_year = isset($_GET['election_year']) ? $_GET['election_year'] : null;
                                    if ($selected_year && in_array($selected_year, $election_years)) {
                                        echo "Academic Year: <strong>" . htmlspecialchars($selected_year) . "</strong>";
                                    }
                                } else {
                                    echo "<strong> ELECTION YEAR</strong>";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card p-1 mt-1 mt-md-0" style="border-radius: 5px;">
                    <div class="card-body4">
                        <div class="row w-100">
                            <div class="center-image">
                                <img class="empty-state custom-image-size" src="images/resc/Dashboard/candidate-empty-state.jpg">
                            </div>
                            <h5 class="fs-6 main-color pb-3 pt-0 text-center election-text"><?php echo $message; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // Establish database connection
    $conn = DatabaseConnection::connect();

    // Fetch election close time from database
    $sql = "SELECT `close` FROM `election_schedule` WHERE `schedule_id` = 0"; // Assuming schedule_id is known
    $result = $conn->query($sql);

    // Check for election schedule
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $closeTimeStr = $row['close'];

        // Validate current time against close time
        $closeDateTime = new DateTime($closeTimeStr);
        $currentDateTime = new DateTime();

        if ($currentDateTime > $closeDateTime) {
            // Current time is past the election close time, check for feedback data
            $feedbackSql = "SELECT COUNT(*) AS count FROM feedback"; // Count the rows in feedback table
            $feedbackResult = $conn->query($feedbackSql);

            // Debug: Check if the feedback query is successful
            if (!$feedbackResult) {
                die("Error executing feedback query: " . $conn->error);
            }

            $feedbackRow = $feedbackResult->fetch_assoc();
            $feedbackCount = $feedbackRow['count'];

         

            if ($feedbackCount > 0) {
                // If there is feedback data, display election reports
                
                $jsonFilePath = 'includes/data/' . $org_name . '/voters-turnout.json';
                if (file_exists( $jsonFilePath)) {
                    displayElectionReports();
                } else {
                    // If no JSON script, show results not found
                    displayEmptyState("Results not found");
                }
            } else {
                // No feedback data found, show empty state
                displayEmptyState("It looks like there is insufficient data to be displayed at the moment");
            }
        } else {
            // Election period has not yet ended, show the empty state
            displayEmptyState("The election period is still ongoing. Election reports will be generated once voting has ended, please check back later for the latest results!");
        }
    } else {
        // No election schedule found
        displayEmptyState("No election schedule found. Please configure the election schedule.");
    }

    // Close database connection
    $conn->close();
    ?>
</div>


        <!-- Feedback Modal -->
        <div class="modal fade" id="successEmailModal" tabindex="-1" role="dialog" aria-labelledby="successEmailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 25px;">
                    <div class="modal-body">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="row">
                            <div class="col-md-12 pb-3">
                                <p class="fw-bold fs-3 main-color modal-text spacing-4">Comment</p>
                                <p id="modal-feedback" class="fw-medium spacing-5 comment"></p>
                                <p id="modal-date" class="fw-medium spacing-5 main-color text-end time-date"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for Position Details -->
        <div class="modal fade" id="positionModal" tabindex="-1" role="dialog" aria-labelledby="positionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content" style="border-radius: 25px;">
                    <div class="modal-body">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="row">
                            <div class="col-md-12 pb-3">
                                <p class="fw-bold fs-3 main-color modal-text spacing-4" id="modal-position-title"></p>
                                <div id="modal-position-details" class="comment-descrip">
                                    <p id="modal-position-description"></p>
                                    <p id="description"></p>
                                </div>

                                <div id="modal-candidates">
                                    <!-- Candidates will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script type="module" src="scripts/script.js"></script>
        <script src="scripts/result-generation.js"></script>
        <script src="scripts/feather.js"></script>
        <script src="scripts/loader.js"></script>
    </body>
    <?php include_once __DIR__ . '/includes/components/footer.php'; ?>

    </html>

<?php
} else {
    header("Location: landing-page.php");
}
?>

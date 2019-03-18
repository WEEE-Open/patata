<?php
include 'db.php';
include 'functions.php';
session_start();

if (isset($_GET['quote'])) {
    $quote = get_random_quote();
    header('Content-Type', 'application/json');
    echo json_encode($quote);
    exit(0);
} else if (isset($_GET['tasks'])) {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Patatasks™</title>
</head>

<body onload="display_ct(), auto_update_qt()">
    <?php print_tasktable() ?>
</body>
<?php
exit(0);
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="icon" type="image/svg+xml" href="patata.svg">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" />
    <script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</head>

<body onload="display_ct(), auto_update_qt()">
    <div class="container">
        <h1>Patata</h1>

        <div class="row">
            <div class="col-md-6">
                <div id='ct'></div>
                <div id='ct2'></div>
            </div>
            <div class="col-md-6">
                <div id='quotesbox' class="text-right"></div>
                <div id='authorbox' class="text-right"></div>
            </div>
            <script>
                /**
                 * Update quotes every N seconds
                 */
                function auto_update_qt() {
                    let refresh = 1000 * 5;

                    fetch('?quote')
                        .then(response => response.json())
                        .then(json => display_qt(json))
                        .then(() => setTimeout(auto_update_qt, refresh));
                }

                /**
                 * Display a quote
                 */
                function display_qt(quoteJson) {
                    document.getElementById('quotesbox').textContent = quoteJson['quote'];
                    if ("context" in quoteJson) {
                        document.getElementById('authorbox').textContent = "Cit. " + quoteJson['author'] + " " + quoteJson['context'];
                    } else {
                        document.getElementById('authorbox').textContent = "Cit. " + quoteJson['author'];
                    }
                }
            </script>
        </div>

        <hr>

        <?php print_tasktable() ?>

        <script type="text/javascript">
            // Reload task table every N seconds
            let $tasktable = $("#tasktable");
            setInterval(function() {
                $tasktable.load("index.php?tasks #tasktable");
            }, 10 * 1000);
        </script>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6 class="text-center">Recently added items</h6>
                <table class="table table-striped table-sm text-center">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><a href="/item/R374">R374</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/R373">R373</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/C165">C165</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/B152">B152</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/R372">R372</a></td>
                            <td>2018-10-18, 13:20</td>
                        </tr>
                        <tr>
                            <td><a href="/item/R371">R371</a></td>
                            <td>2018-10-18, 13:20</td>
                        </tr>
                        <tr>
                            <td><a href="/item/127">127</a></td>
                            <td>2018-10-16, 19:37</td>
                        </tr>
                        <tr>
                            <td><a href="/item/126">126</a></td>
                            <td>2018-10-16, 19:16</td>
                        </tr>
                        <tr>
                            <td><a href="/item/HDD228">HDD228</a></td>
                            <td>2018-10-16, 19:07</td>
                        </tr>
                        <tr>
                            <td><a href="/item/HDD227">HDD227</a></td>
                            <td>2018-10-16, 19:04</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h6 class="text-center">Recently modified items</h6>
                <table class="table table-striped table-sm text-center">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><a href="/item/R374">R374</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/R373">R373</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/C165">C165</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/B152">B152</a></td>
                            <td>2018-10-18, 20:01</td>
                        </tr>
                        <tr>
                            <td><a href="/item/R372">R372</a></td>
                            <td>2018-10-18, 13:20</td>
                        </tr>
                        <tr>
                            <td><a href="/item/R371">R371</a></td>
                            <td>2018-10-18, 13:20</td>
                        </tr>
                        <tr>
                            <td><a href="/item/127">127</a></td>
                            <td>2018-10-16, 19:37</td>
                        </tr>
                        <tr>
                            <td><a href="/item/126">126</a></td>
                            <td>2018-10-16, 19:16</td>
                        </tr>
                        <tr>
                            <td><a href="/item/HDD228">HDD228</a></td>
                            <td>2018-10-16, 19:07</td>
                        </tr>
                        <tr>
                            <td><a href="/item/HDD227">HDD227</a></td>
                            <td>2018-10-16, 19:04</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function display_c() { //Refresh time for the date function
            var refresh = 1000;
            mytime = setTimeout('display_ct()', refresh)
        }

        function addZero(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }

        function display_ct() { //Date generator function
            var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            var x = new Date()
            var d = addZero(x.getDate())
            var mo = months[x.getMonth()]
            var y = addZero(x.getFullYear())
            var h = addZero(x.getHours())
            var mi = addZero(x.getMinutes())
            var s = addZero(x.getSeconds())
            var wd = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
            var td = wd[x.getDay()]
            var x1 = td + " - " + d + " " + mo + " " + y
            var x2 = h + ":" + mi + ":" + s
            document.getElementById('ct').innerHTML = x1
            document.getElementById('ct2').innerHTML = x2

            display_c();
        }
    </script>
</body>

</html> 
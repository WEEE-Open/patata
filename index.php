<?php
include 'functions.php';
session_start();

if (isset($_GET['quote'])) {
    $quote = get_random_quote();
    header('Content-Type', 'application/json');
    echo json_encode($quote);
    exit(0);
} else if (isset($_GET['stats'])) {
    ?>
<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Stat iStiche™ (<?= htmlspecialchars($_GET['stats']) ?>)</title>
    </head>

    <body>
        <?php print_stats($_GET['stats']) ?>
    </body>
</html>

<?php
    exit(0);
} else if (isset($_GET['tasks'])) {
    ?>
<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Patatasks™</title>
    </head>

    <body>
        <?php print_tasktable() ?>
    </body>
</html>
<?php
exit(0);
}
?>
<!DOCTYPE html>
<html lang='it'>

<head>
	<title>Patata</title>
    <link rel="icon" type="image/svg+xml" href="patata.svg">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="bootstrap.min.css" />
	<script src="jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<script src="bootstrap.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" type="text/css" rel="stylesheet">
	<link rel="stylesheet" id="darktheme" type="text/css" href="bootstrap-dark.min.css" />
    <style>
        * {
            font-family: 'Noto Sans', sans-serif;
        }
        .labels-container {
            padding-top: 5px;
        }
        .label {
            padding: 2px 10px 2px 10px;
            margin-right: 10px;
            border-radius: 20px;

        }
        .assignee {
            border-left: solid lightgrey 1px;
        }
        .duedate {
            color: darkred;
        }
        .tableHeader {
            padding: 2px 0px 2px 10px !important;
        }

        .darkTheme .text-muted {
            color: #c4c4c4 !important;
        }
        .darkTheme .duedate {
            color: red !important;
        }
    </style>
</head>

<body onload="display_ct();  auto_update_qt();  auto_switch_theme()">
    <div class="container d-flex flex-column" style="height: 100vh;">
        <div id="datequoterow" class='row' style="flex-shrink: 1;">
            <div class='col-md-6'>
                <div id='ct' style='padding-left: 30px;margin-left: 0;'></div>
                <div id='ct2' style='padding-left: 30px;'></div>
            </div>
            <div class='col-md-6'>
                <div id='quotesbox' class='text-right' style='margin-right: 0;padding-right: 30px;'></div>
                <div id='authorbox' class='text-right' style='margin-right: 0;padding-right: 30px;'></div>
            </div>

            <script type='text/javascript'>
                // Refresh time for the date function
                function display_c() {
                    const refresh = 1000;
                    setTimeout(display_ct, refresh);
                }

                function addZero(i) {
                    if (i < 10) {
                        i = '0' + i;
                    }
                    return i;
                }

                function display_ct() { //Date generator function
                    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    let x = new Date();
                    let d = addZero(x.getDate());
                    let mo = months[x.getMonth()];
                    let y = addZero(x.getFullYear());
                    let h = addZero(x.getHours());
                    let mi = addZero(x.getMinutes());
                    let s = addZero(x.getSeconds());
                    const wd = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    let td = wd[x.getDay()];
                    let x1 = td + ' - ' + d + ' ' + mo + ' ' + y;
                    let x2 = h + ':' + mi + ':' + s;
                    document.getElementById('ct').textContent = x1;
                    document.getElementById('ct2').textContent = x2;

                    display_c();
                }
            </script>
            <script>
                /**
                 * Update quotes and dark theme every N seconds
                 */
                const refresh_timer = 60 * 60;

                function auto_update_qt() {
                    fetch('?quote')
                        .then(response => response.json())
                        .then(json => display_qt(json))
                        .then(() => setTimeout(auto_update_qt, refresh_timer * 1000));
                }

				let theme = document.getElementById('darktheme');
				function auto_switch_theme() {
					theme.disabled = !theme.disabled;
                    if (theme.disabled){
                        document.body.classList.remove("darkTheme");
                    } else {
                        document.body.classList.add("darkTheme");
                    }
					setTimeout(auto_switch_theme, refresh_timer * 1000);
				}

                /**
                 * Display a quote
                 */
                function display_qt(quoteJson) {
                    document.getElementById('quotesbox').textContent = quoteJson['quote'];
                    if ('context' in quoteJson) {
                        document.getElementById('authorbox').textContent = 'Cit. ' + quoteJson['author'] + ' ' + quoteJson['context'];
                    } else {
                        document.getElementById('authorbox').textContent = 'Cit. ' + quoteJson['author'];
                    }
                }
            </script>
        </div>

        <div id="tasktableheader" class="mt-2">
            <table class="table table-striped my-0 mx-auto">
                <thead class="thead-dark">
                <tr>
                    <th class="tableHeader" id="taskHead">Task</th>
                    <th class="tableHeader" id="assigneeHead">Assignee</th>
                </tr>
                </thead>
            </table>
        </div>

        <div id="tasktablediv" style="height: 60%; overflow: hidden;">
            <?php print_tasktable() ?>
        </div>

        <script type='text/javascript'>
            let interval = 60 * 60;
            let $tasktablediv = $('#tasktablediv');
            let $tasktable =  document.getElementById('tasktable');
            let $taskHeader = document.getElementById('taskHead');
            let $assigneeHeader = document.getElementById('assigneeHead');
            let $tasktable_table = document.getElementById('tasktable_table');

            // Set correct table header width
            $taskHeader.style.width = $tasktable_table.rows[0].cells[0].offsetWidth + "px";
            $assigneeHeader.style.width = $tasktable_table.rows[0].cells[1].offsetWidth + "px";

            // Define tasktable task update function
            setInterval(function() {
                $tasktablediv.load('/index.php?tasks #tasktable');
            }, interval * 1000);

            // Define tasktable autoscroll function
            (async function autoscroll() {
                while(true) {
                    let velocity = 30
                    let duration = $tasktable.clientHeight * velocity;
                    await $tasktablediv.animate({scrollTop: 0}, 800).promise();
                    await $tasktablediv.animate({scrollTop: 0}, 2000).promise();
                    await $tasktablediv.animate({scrollTop: $tasktable.clientHeight}, duration, "linear").promise();
                }
            })();
        </script>

        <hr>

        <div id="statsdiv" style="height: 40%; overflow: hidden;">
            <?php print_stats('0') ?>
        </div>

        <script type='text/javascript'>
            (function() {
                // Reload stats every N seconds
                let interval = 22 * 60;
                let page = 1;
                let max = 2;
                let $stats = $('#statsdiv');
                setInterval(function () {
                    let url = 'index.php? #stats';
                    let param = 'stats=' + (page++ % max);
                    //console.log(param);
                    $stats.load(url, param);
                }, interval * 1000);
            }());
        </script>

        <hr>

        <div id="socialstatsdiv">
            <h6 class='text-center'>Social stats</h6>
            <div class="text-center">
            <?php print_social_stats() ?>
            </div>
        </div>
    </div>
</body>

</html> 
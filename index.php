<?php
include 'functions.php';
session_start();

if (isset($_GET['stats'])) {
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
<html lang='en'>

<head>
	<title>Patata</title>
    <link rel="icon" type="image/svg+xml" href="patata.svg">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="bootstrap.min.css" />
	<script src="jquery-3.3.1.min.js"></script>
	<script src="bootstrap.min.js"></script>
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
        /*.darkTheme .label {*/
        /*}*/

    </style>
</head>

<body onload="displayDate();  auto_update_qt();  auto_switch_theme()">
    <div class="container d-flex flex-column" style="height: 100vh;">
        <div class='row' style="flex-shrink: 1;">
            <div class='col-md-6'>
                <div id='currentDate' style='padding-left: 30px;margin-left: 0;'></div>
                <div id='currentTime' style='padding-left: 30px;'></div>
            </div>
            <div class='col-md-6'>
                <div id='quotesbox' class='text-right' style='margin-right: 0;padding-right: 30px;'></div>
                <div id='authorbox' class='text-right' style='margin-right: 0;padding-right: 30px;'></div>
            </div>

            <script type='text/javascript'>
                function displayDate() {

                    const WEEK_DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const MONTHS    = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

                    let date = new Date();
                    let day  = date.getDay();

                    /** Display current date **/
                    document.getElementById('currentDate').textContent = `${WEEK_DAYS[day]} - ${day} ${MONTHS[date.getMonth()]} ${date.getFullYear()}`;

                    /** Display current time **/
                    document.getElementById('currentTime').textContent = (date).toLocaleTimeString();

                    setInterval(() => {
                        /** Display current time **/
                        document.getElementById('currentTime').textContent = (new Date()).toLocaleTimeString();
                    }, 1000);
                }
            </script>
            <script>
                /**
                 * Update quotes and dark theme every N seconds
                 */
                const refresh_timer = 60 * 60;

                function auto_update_qt() {
                    fetch('get_quotes.php')
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

<!--        <hr style='margin-left: 30px;margin-right: 30px;'>-->

        <div id="tasktableheader" class="mt-2">
            <table class="table table-striped my-0 mx-auto">
                <thead class="thead-dark">
                <tr>
                    <th id="taskHead">Task</th>
                    <th id="assigneeHead">Assignee</th>
                </tr>
                </thead>
            </table>
        </div>

        <div id="tasktablediv" style="flex-shrink: 1; overflow: hidden;">
            <?php print_tasktable() ?>
        </div>

        <script type='text/javascript'>
            let interval = 60 * 60;
            let $tasktablediv = $('#tasktablediv');
            let $tasktable =  document.getElementById('tasktable');
            let $taskHeader = document.getElementById('taskHead');
            let $assigneeHeader = document.getElementById('assigneeHead');
            let $tasktable_table = document.getElementById('tasktable_table');

            // Define tasktable task update function
            setInterval(function() {
                $tasktablediv.load('/index.php?tasks #tasktable');
            }, interval * 1000);

            // Define tasktable autoscroll function
            (async function autoscroll() {
                while(true) {
                    let duration = $tasktable.clientHeight * 15;
                    await $tasktablediv.animate({scrollTop: 0}, 800).promise();
                    await $tasktablediv.animate({scrollTop: 0}, 2000).promise();
                    await $tasktablediv.animate({scrollTop: $tasktable.clientHeight}, duration, "linear").promise();
                }
            })();
        </script>

        <hr>

        <div id="statsdiv" style="flex-grow: 1;">
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
    </div>
</body>

</html> 
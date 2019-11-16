<?php
include 'db.php';
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
	<script src="jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<script src="bootstrap.min.js"></script>
	<link rel="stylesheet" id="darktheme" type="text/css" href="bootstrap-dark.min.css" />
    <style>
        * {
            font-family: 'Noto Sans', sans-serif;
        }
    </style>
</head>

<body onload="display_ct(); auto_update_qt(); auto_switch_theme()">
    <div class="container">

        <div class='row'>
            <div class='col-md-6'>
                <div id='ct' style='padding-left: 30px;margin-left: 0;'></div>
                <div id='ct2' style='padding-left: 30px;'></div>
            </div>
            <div class='col-md-6'>
                <div id='quotesbox' class='text-right' style='margin-right: 0;padding-right: 30px;'></div>
                <div id='authorbox' class='text-right' style='margin-right: 0;padding-right: 30px;'></div>
            </div>
            <script>
                /**
                 * Update quotes every N seconds
                 */
                const refresh_timer = 1000 * 60 * 30;

                function auto_update_qt() {
                    fetch('?quote')
                        .then(response => response.json())
                        .then(json => display_qt(json))
                        .then(() => setTimeout(auto_update_qt, refresh_timer));
                }

				let theme = document.getElementById('darktheme');
				function auto_switch_theme() {
					theme.disabled = !theme.disabled;
					setTimeout(auto_switch_theme, refresh_timer);
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

        <hr style='margin-left: 30px;margin-right: 30px;'>

        <?php print_tasktable() ?>

        <script type='text/javascript'>
            // Reload task table every N seconds
            let $tasktable = $('#tasktable');
            setInterval(function() {
                $tasktable.load('index.php?tasks #tasktable');
            }, 10 * 1000);
        </script>

        <hr>

        <div class='row'>
            <?php print_stats('1') ?>
        </div>
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
</body>

</html> 
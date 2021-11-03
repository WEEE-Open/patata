<?php

require_once 'conf.php';

const TYPE_EMOJI = [
    'E' => '💡',
    'I' => '💻',
    'M' => '🔨',
    'D' => '📘',
    'C' => '🧽',
    'V' => '🍀',
    'R' => '💾',
    'S' => '🎮',
];
const TYPE_DESCRIPTION = [
    'E' => 'Elettronica',
    'I' => 'Informatica',
    'M' => 'Meccanica',
    'D' => 'Documentazione',
    'C' => 'Riordino',
    'V' => 'Eventi',
    'R' => 'Retrocomputing',
    'S' => 'Svago',
];

const CACHE_FILE = 'stacks_cache.json';

function deck_request(string $url): string {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERPWD, DECK_USER . ":" . DECK_PASS);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'OCS-APIRequest: true',
        //'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
        //If an error occured, throw an Exception.
        throw new RuntimeException(curl_error($ch));
    }

    return $response;
}

function get_random_quote()
{
    $quotes_file = file_get_contents('quotes.json');
    $quotes = json_decode($quotes_file, true);
    $quote_id = rand(0, count($quotes) - 1);
    return $quotes[$quote_id];
}

function print_stats(string $stats)
{
    require_once 'conf.php';

    $curl = get_curl();

    echo '<div class="row" id="stats">';
    if ($stats === '0') {
        print_stat($curl, 'add');
        print_stat($curl, 'update');
    } else {
        print_stat($curl, 'update');
        print_stat($curl, 'move');
    }
    echo '</div>';

    curl_close($curl);
}

function relative_date($time)
{
    $day = date('Y-m-d', $time);
    if($day === date('Y-m-d', strtotime('today'))) {
        return 'Today';
    } elseif($day === date('Y-m-d', strtotime('yesterday'))) {
        return 'Yesterday';
    } else {
        return $day;
    }
}

function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
}

function print_stat($curl, string $stat)
{
    switch ($stat) {
        case 'add':
        default:
            $url = '/v2/stats/getRecentAuditByType/C/5';
            $title = 'Recently added items';
            break;
        case 'update':
            $url = '/v2/stats/getRecentAuditByType/U/5';
            $title = 'Recently modified items';
            break;
        case 'move':
            $url = '/v2/stats/getRecentAuditByType/M/5';
            $title = 'Recently moved items';
            break;
    }

    $data = get_data_from_tarallo($curl, $url);
    ?>
    <div class='col-md-6'>
        <h6 class='text-center'><?= e($title) ?></h6>
        <table class='table table-striped table-sm text-center'>
            <thead style="position: sticky; top: 0;">
            <tr>
                <th>Item</th>
                <th>Time</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $item => $timestamp): $timestamp = (int)floatval($timestamp); ?>
                <tr>
                    <td><?= e($item) ?></td>
                    <td><?= relative_date($timestamp) . date(' H:i') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * @param resource $curl CURL
 * @param string $path /v2/whatever
 *
 * @return array
 */
function get_data_from_tarallo($curl, string $path): array
{
    $url = TARALLO_URL . $path;
    curl_setopt($curl, CURLOPT_URL, $url);
    $result = curl_exec($curl);
    $result = json_decode($result, true);

    return $result;
}

/**
 * @return false|resource
 */
function get_curl()
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Token ' . TARALLO_TOKEN, 'Accept: application/json']);
    //curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Token ' . TARALLO_TOKEN, 'Accept: application/json', 'Cookie: XDEBUG_SESSION=PHPSTORM']);

    return $curl;
}

function download_tasks(): array
{
    // TODO: check cache before downloading
    if(file_exists(CACHE_FILE)) {
        return json_decode(file_get_contents(CACHE_FILE), true);
    }
    
    $request = deck_request(DECK_URL . "/apps/deck/api/v1.0/boards/" . DECK_BOARD . "/stacks"); //. "/stacks/" . $stack);
    $stacks_json = json_decode($request, true);
    $stacks_to_display = [];
    foreach(explode(',', DECK_STACKS) as $stack_to_display) {
        $stacks_to_display[] = (int) $stack_to_display;
    }
    $stacks_json2 = [];
    foreach($stacks_json as $stack) {
        if(in_array($stack['id'], $stacks_to_display)) {
            $stacks_json2[] = $stack;
        }
    }
    unset($stacks_json);

    file_put_contents(CACHE_FILE, json_encode($stacks_json2));

    return $stacks_json2;
}

function get_brightness($hex) {
    // returns brightness value from 0 to 255
    // strip off any leading #
    $c_r = hexdec(substr($hex, 0, 2));
    $c_g = hexdec(substr($hex, 2, 2));
    $c_b = hexdec(substr($hex, 4, 2));

    return (($c_r * 587) + ($c_g * 299) + ($c_b * 114)) / 1000;
}


function print_tasktable()
{
    $stacks = download_tasks();

//    echo "<pre style='color: white;'>";
//    echo json_encode($stacks, JSON_PRETTY_PRINT);
//    echo "</pre>";
//    exit(0);

    // ["title" => "Fare cose", "assignee" => null, "tags" => [["Alta priorita'", "#f0f0f0"], ["Lollogne", "#00cccc"], "stack" => "Da fare"]
    // ["title" => "Riparare roba", "assignee" => "Mario Rossi", "tags" => [["Riparazioni ardite", "#aaaaaa"]], "stack" => "In corso"]
    $tasks = [];

    foreach($stacks as $stack) {
        // TODO: parse it all...
        $cards = $stack['cards'];
        $timezone = new DateTimeZone('Europe/Rome');
        foreach ($cards as $card){
            $task = [];
            $labels = $card['labels'];
            $assigned_users = $card['assignedUsers'];
            $task['title'] = $card['title'];
            $task['description'] = $card['description'];
            $task['duedate'] = $card["duedate"] === null?null:new DateTime($card["duedate"], $timezone);
            $task['createdate'] = $card["createdAt"];
            $task['labels'] = [];
            $task['assignee'] = [];
            foreach ($labels as $label){
                $task['labels'][] = [
                        "title" => $label['title'],
                        "color" => $label['color']
                ];
            }
            foreach ($assigned_users as $assignee){
                $displayname = $assignee['participant']['displayname'];
                $displayname = explode(' (', $displayname);
                array_pop($displayname);
                $displayname = implode(" (", $displayname);
                $task['assignee'][] = $displayname;
            }
            $tasks[] = $task;
        }
    }

//    echo "<pre style='color: white;'>";
//    echo json_encode($tasks, JSON_PRETTY_PRINT);
//    echo "</pre>";
//    exit(0);



    // TODO: update everything
    $_SESSION['max_row'] = count($tasks);
    $per_page = 10;
    if (!isset($_SESSION['offset'])) {
        $_SESSION['offset'] = 0;
    }

    $page = 1 + floor(($_SESSION['offset']) / $per_page);
    $pages = ceil($_SESSION['max_row'] / $per_page);
    ?>
    <div id='tasktable'>
<!--        <h5 class='text-center'>Tasklist --><?//= "page $page of $pages" ?><!--</h5>-->
        <table id="tasktable_table" class='table table-striped' style='margin: 0 auto;'>
            <tbody>
            <?php
            foreach ($tasks as $task) {
                // Add title with description and tags
                echo '<tr>';
                echo '<td style="vertical-align: middle;">';
                echo htmlspecialchars($task['title']) . '<br>';
                if ($task['duedate'] != null) {
                    echo '<span class="duedate">Due by ' . htmlspecialchars($task['duedate']->format('d-m-Y')) . '</span> - ';
                }
                if ($task['description'] != null) {
                    echo '<small class="text-muted">' . htmlspecialchars($task['description']) . '</small><br>';
                }
                echo '<div class="labels-container">';
                foreach ($task['labels'] as $label){
                    echo '<span class="label" style="';
                    echo "background: #{$label['color']};";
                    if (get_brightness($label['color']) < 149){
                        echo "color: white;";
                    } else {
                        echo "color: black;";
                    }
                    echo '">';
                    echo htmlspecialchars($label['title']);
                    echo "</span>";
                }
                echo '</td>';
                echo '<td class="text-center assignee">';
                foreach ($task['assignee'] as $assignee) {
                    echo '<span>' . $assignee;
                    echo '</span><br>';
                }
                echo '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

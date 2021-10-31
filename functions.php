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
            $url = '/v2/stats/getRecentAuditByType/C/10';
            $title = 'Recently added items';
            break;
        case 'update':
            $url = '/v2/stats/getRecentAuditByType/U/10';
            $title = 'Recently modified items';
            break;
        case 'move':
            $url = '/v2/stats/getRecentAuditByType/M/10';
            $title = 'Recently moved items';
            break;
    }

    $data = get_data_from_tarallo($curl, $url);
    ?>
    <div class='col-md-6'>
        <h6 class='text-center'><?= e($title) ?></h6>
        <table class='table table-striped table-sm text-center'>
            <thead>
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

function print_tasktable()
{
    $stacks = download_tasks();

    echo "<pre>";
    echo json_encode($stacks, JSON_PRETTY_PRINT);
    echo "</pre>";
    exit(0);

    // ["title" => "Fare cose", "assignee" => null, "tags" => [["Alta priorita'", "#f0f0f0"], ["Lollogne", "#00cccc"], "stack" => "Da fare"]
    // ["title" => "Riparare roba", "assignee" => "Mario Rossi", "tags" => [["Riparazioni ardite", "#aaaaaa"]], "stack" => "In corso"]
    $tasks = [];

    foreach($stacks as $stack) {
        // TODO: parse it all...
    }

    // TODO: update everything
    $_SESSION['max_row'] = get_task_number();
    $db = new MyDB();
    $per_page = 10;
    if (!isset($_SESSION['offset'])) {
        $_SESSION['offset'] = 0;
    }
    list($result, $maintainer) = get_tasks_and_maintainers($db, false, 0, 0, $per_page, $_SESSION['offset']);

    $page = 1 + floor(($_SESSION['offset']) / $per_page);
    $pages = ceil($_SESSION['max_row'] / $per_page);
    ?>
    <div id='tasktable'>
        <h5 class='text-center'>Tasklist <?= "page $page of $pages" ?></h5>
        <table class='table table-striped' style='margin: 0 auto;'>
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Durate (Minutes)</th>
                <th>Maintainer</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($tasks as $task) {
                echo '<tr>';
                echo '<td>' . $task['title'] . '</td>';
                echo '<td>';
                echo isset($tasklist['Description']) ? $tasklist['Description'] : '';
                echo '</td>';
                echo '<td>' . $tasklist['Durate'] . '</td>';
                echo '<td>';
                echo isset($maintainer[$tasklist['ID']]) ? implode(', ', $maintainer[$tasklist['ID']]) : '';
                echo '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

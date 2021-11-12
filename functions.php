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

function print_stats(string $stats)
{
    require_once 'conf.php';

    $curl = get_curl();

    echo '<div class="row" id="stats">';
    print_stat($curl,'cpu');
    print_stat($curl,'ram');
    echo '</div>';

    curl_close($curl);
}


function print_social_stats(){
    require_once 'conf.php';

    $socials = ['youtube'];

    foreach ($socials as $social){
        echo print_social_stat($social);
    }

}

function print_social_stat($case)
{
    switch ($case) {
        case 'youtube':
            $urls = ['subs' => 'https://www.googleapis.com/youtube/v3/channels?part=statistics&id=' . YOUTUBE_CHANNEL_ID .
                '&fields=items/statistics/subscriberCount&key=' . YOUTUBE_API_KEY ,
                'views' => 'https://www.googleapis.com/youtube/v3/channels?part=statistics&id=' . YOUTUBE_CHANNEL_ID .
                '&fields=items/statistics/viewCount&key=' . YOUTUBE_API_KEY];
            $result = '<div class="col-12 align-middle">'.
                '<i class="fa fa-youtube-play" style="vertical-align: middle; font-size: 1.5rem; color: red;"></i>';
            foreach ($urls as $key => $url){
                $query = file_get_contents($url);
                $query = json_decode($query, true);
                if ($key == 'subs'){
                    $query = $query['items'][0]['statistics']['subscriberCount'];
                    $result .= '<i class="pl-1 fa fa-user" style="vertical-align: middle; font-size: 1rem; color: grey;"></i>'.
                        '<span class="pl-1" style="font-size: 1rem; vertical-align: middle;">' .
                        $query . '</span>';
                } else {
                    $query = $query['items'][0]['statistics']['viewCount'];
                    $result .= '<i class="pl-1 fa fa-eye" style="vertical-align: middle; font-size: 1rem; color: grey;"></i>'.
                        '<span class="pl-1" style="font-size: 1rem; vertical-align: middle;">' .
                        $query . '</span>';
                }

            }
            $result .= '</div>';
            break;
        case 'facebook':
            echo 'facebook_stats';
            break;
        case 'instagram':
            echo 'instagram_stats';
            break;
        default:
            echo 'lol';
    }
    return $result;
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


/**
 * @param resource $curl CURL
 * @param string $path /v2/whatever
 *
 * @return array
 */
function get_data_from_tarallo($curl, string $path)
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


function get_social_curl(){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    return $curl;
}


function download_tasks(): array
{
    // TODO: use etag/If-Modified-Since instead
    if(file_exists(CACHE_FILE)) {
        if(time() - filemtime(CACHE_FILE) >= 60 * 60 * 1) { // 1 hour
            return json_decode(file_get_contents(CACHE_FILE), true);
        }
    }

    $request = deck_request(DECK_URL . "/apps/deck/api/v1.0/boards/" . DECK_BOARD . "/stacks"); //. "/stacks/" . $stack);
    $stacks_json = json_decode($request, true);
    $stacks_to_display = [];

    // Only show first and second stacks
    foreach ($stacks_json as $key => $value)
        if ($key < 2) $stacks_to_display[] = $value;
        else break;

    unset($stacks_json);

    file_put_contents(CACHE_FILE, json_encode($stacks_to_display));

    return $stacks_to_display;
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
                echo ucfirst(htmlspecialchars($task['title']) . '<br>');
                if ($task['duedate'] != null) {
                    echo '<span class="duedate">Due by ' . htmlspecialchars($task['duedate']->format('d-m-Y')) . '</span> - ';
                }
                if ($task['description'] != null) {
                    echo '<small class="text-muted">' . ucfirst(htmlspecialchars($task['description'])) . '</small><br>';
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


function print_stat($curl, string $stat)
{
    switch ($stat) {
        case 'cpu':
            $urls = ['Intel' => '/v2/stats/getCountByFeature/cpu-socket/brand=Intel/box12',
                'AMD' => '/v2/stats/getCountByFeature/cpu-socket/brand=AMD/box12'];
            $title = 'CPUs available';
            break;
        case 'ram':
            $url = '/v2/stats/getCountByFeature/ram-type/working=yes/rambox';
            $title = 'RAMs available';
            break;
        case 'latest_mod':
            $url = '/v2/stats/getRecentAuditByType/U/5';
            $title = 'Recently modified items';
            break;
        case 'latest_creation':
            $url = '/v2/stats/getRecentAuditByType/C/5';
            $title = 'Recently modified items';
            break;
        default:
            echo 'print_stat error: no such case.';
    }

    if ($title == 'CPUs available'){
        $data = [];
        $data['Intel'] = get_data_from_tarallo($curl, $urls['Intel']);
        $data['AMD'] = get_data_from_tarallo($curl, $urls['AMD']);
    } else {
        $datas = get_data_from_tarallo($curl, $url);
        $data = [];
        foreach ($datas as $key => $entry) {
            $data[strtoupper($key)] = $entry;
        }
    }
    ?>
    <div class='col-md-6'>
        <h6 class='text-center'><?= e($title) ?></h6>
        <table class='table table-striped table-sm text-center'>
            <thead style="position: sticky; top: 0;">
            <tr>

                <?php if ($title == 'CPUs available'): ?>
                    <th>Brand</th>
                    <th>Socket</th>
                <?php else: ?>
                    <th>Type</th>
                <?php endif; ?>
                <th>Qty</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $key => $array): ?>
            <?php if ($title == 'CPUs available'): ?>
            <?php foreach ($array as $entry_key => $entry): ?>
                <tr>
                    <td><?= e($key) ?></td>
                    <td><?= e($entry_key) ?></td>
                    <td><?= e($entry) ?></td>
                </tr>
            <?php endforeach;?>
            <?php else: ?>
                    <tr>
                        <td><?= e($key) ?></td>
                        <td><?= e($array) ?></td>
                    </tr>
            <?php endif; ?>
            <?php endforeach;?>
            </tbody>
        </table>
    </div>
    <?php
}
<?php
const TYPE_EMOJI = ["C"=>"🍀", "E"=>"⚡️", "I"=>"💻", "S"=>"🎮"];
const TYPE_DESCRIPTION = ["C"=>"Cose", "E"=>"Elettronica", "I"=>"Informatica", "S"=>"Svago"];

function get_random_quote() {
    $quotes_file = file_get_contents("quotes.json");
    $quotes = json_decode($quotes_file, TRUE);
    $quote_id = (int) floor(rand(0, count($quotes) - 1));
    return $quotes[$quote_id];
}

function get_max_id(){
    $db = new MyDB();
    $temp = $db->query("SELECT MAX(ID) ID FROM TASK");
    $temp = $temp->fetchArray(SQLITE3_ASSOC);
    return $temp['ID'];
}

function get_number_to_do(){
    $db = new MyDB();
    $temp = $db->query("SELECT COUNT (ID) ID FROM TASK WHERE DONE = 0");
    $temp = $temp->fetchArray(SQLITE3_ASSOC);
    return $temp['ID'];
}

function print_tasktable() {
    $_SESSION['max_row'] = get_number_to_do();
    ?>
    <div id='tasktable'>
        <h5 class="text-center">Tasklist</h5>
        <table class="table table-striped" style="margin: 0 auto;">
            <thead>
                <tr>
                    <th class="col-1">Type</th>
                    <th class="col-5">Title</th>
                    <th class="col-3">Description</th>
                    <th class="col-1">Durate (Minutes)</th>
                    <th class="col-2">Maintainer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $db = new MyDB();
                list($result, $maintainer) = get_tasks_and_maintainers($db, false);

                while ($tasklist = $result->fetchArray(SQLITE3_ASSOC)){

                    $emoji = TYPE_EMOJI[$tasklist['TaskType']];
                    $taskName = TYPE_DESCRIPTION[$tasklist['TaskType']];

                    echo "<tr>";
                    echo "<td title=\"$taskName\">".$emoji."</td>";
                    echo "<td>".$tasklist['Title']."</td>";
                    echo "<td>";
                    echo isset($tasklist['Description']) ? $tasklist['Description']: "";
                    echo "</td>";
                    echo "<td>".$tasklist['Durate']."</td>";
                    echo "<td>";
                    echo isset($maintainer[$tasklist['ID']]) ? implode(', ',$maintainer[$tasklist['ID']]) : "";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * @param MyDB $db The patabase
 * @param $done bool True if you only want completed tasks, false if you only want tasks that are still to do (default)
 * @return array $result, $maintainer
 */
function get_tasks_and_maintainers(MyDB $db, bool $done): array {
    
    $done = (int) $done;
    isset($_SESSION['count']) ? $row_count = 5 : $row_count = get_number_to_do();
    $offset = 0 + (int) $_SESSION['count'];
    if($offset <= $_SESSION['max_row']){
        $_SESSION['count']+=5;
    }else{
        $offset = 0;
        $_SESSION['count'] = 0;
    }

    $result = $db->query("SELECT ID, Tasktype, Title, Description, Durate, Done
                                            FROM TASK 
                                            WHERE Done = $done
                                            ORDER BY ID
                                            LIMIT $row_count OFFSET $offset");
    $result2 = $db->query("SELECT T_ID, Maintainer
                                            FROM T_MAINTAINER
                                            WHERE T_ID IN (SELECT ID
                                                            FROM TASK 
                                                            WHERE Done = $done
                                                            LIMIT $row_count OFFSET $offset)
                                            ORDER BY T_ID");
    $maintainer = array();
    while($temp = $result2->fetchArray(SQLITE3_ASSOC)) {
        $maintainer[$temp['T_ID']] = array();
    }
    while($temp = $result2->fetchArray(SQLITE3_ASSOC)) {
        //$maintainer[$temp['T_ID']]=array();
        // Why do we need two cycle?
        array_push($maintainer[$temp['T_ID']], $temp['Maintainer']);
    }
    return array($result, $maintainer);
}

function handle_post() {
    $db = new MyDB();

    if (isset($_POST['title'])) {
        if(empty($_POST['title'])) {
            $idn = (int) $_POST['idn'];
            delete_task($db, $idn);
            $_SESSION['max_row'] = get_number_to_do();
            return;
        }
        $title = test_input($_POST['title']);
        if(empty($_POST['idn'])) {
            $idn = null;
        } else {
            $idn = (int) $_POST['idn'];
        }
        foreach(TYPE_EMOJI as $tempType => $tempEmoji){
            if($tempType == $_POST['tasktype']){         
                $type = $_POST['tasktype'];
            }
        }
        if(!isset($type)){
            $_POST['typeErr'] = "Select a valid task type";
        }
        $description = test_input($_POST['description']);
        $durate = (int) $_POST['durate'];
        $maintainer = explode(',', $_POST['maintainer']);
        foreach($maintainer as &$temp_maintainer) {
            $temp_maintainer = test_input($temp_maintainer);
        }
        unset($temp_maintainer);
        $edit = $_POST['submit'];
        if($edit === "Save") {
            update_task($db, $title, $description, $durate, $type, $idn, $maintainer, false, NULL);
        } elseif($edit === "Add") {
            add_new_task($db, $title, $description, $durate, $type, $maintainer);
        } elseif($edit === "Done") {
            $date = date("Y-m-d H:i:s");
            update_task($db, $title, $description, $durate, $type, $idn, $maintainer, true, $date);
        } elseif($edit === "Undo") {
            update_task($db, $title, $description, $durate, $type, $idn, $maintainer, false, NULL);
        }
    }
}

function update_task(MyDB $db, $title, $description, int $durate, $type, int $idn, array $maintainer, bool $done, $date) {
    $stmt = $db->prepare('UPDATE "Task" 
                                    SET "Title" = :title, "Description" = :descr, "Durate" = :durate,
                                         "TaskType" = :tasktype, "Done" = :done, "Date" = :compDate
                                    WHERE "ID" = :id');
    if($stmt === false) {
        throw new RuntimeException('Cannot prepare statement');
    }

    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':descr', $description);
    $stmt->bindValue(':durate', $durate);
    $stmt->bindValue(':tasktype', $type);
    $stmt->bindValue(':done', $done);
    $stmt->bindValue(':compDate', $date);
    $stmt->bindValue(':id', $idn);
    $stmt->execute();

    $db->query("DELETE FROM T_Maintainer WHERE T_ID = $idn");

    add_maintainers($db, $maintainer, $idn);
}

function add_new_task(MyDB $db, $title, $description, int $durate, $type, array $maintainer) {
    $stmt = $db->prepare("INSERT INTO Task (Title,Description,Durate,TaskType)
                        VALUES (:title, :descr, :durate, :type)");
    if($stmt === false) {
        throw new RuntimeException('Cannot prepare statement');
    }

    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':descr', $description);
    $stmt->bindValue(':durate', $durate);
    $stmt->bindValue(':type', $type);

    $stmt->execute();

    $idn = get_max_id();

    add_maintainers($db, $maintainer, $idn);
}

function add_maintainers(MyDB $db, array $maintainer, int $idn) {
    foreach($maintainer as $temp_maintainer) {
        $db->query("INSERT INTO T_Maintainer (T_ID,Maintainer)
                        VALUES ('$idn', '$temp_maintainer')");
    }
}

function delete_task(MyDB $db, int $idn) {
    $db->query("DELETE FROM Task 
                WHERE ID = $idn");
}

function test_input($input){
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}
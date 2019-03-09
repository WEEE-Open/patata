<?php

function get_random_quote() {
    $quotes_file = file_get_contents("quotes.json");
    $quotes = json_decode($quotes_file, TRUE);
    $quote_id = (int) floor(rand(0, count($quotes) - 1));
    return $quotes[$quote_id];
}

function print_tasktable() {
    ?>
    <div id='tasktable'>
        <div class="task">
            <h5 class="text-center">Tasklist</h5>
            <table class="table table-striped " style="width: 70%; margin: 0 auto;">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Durate (Minutes)</th>
                        <th>Maintainer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $db = new MyDB();
                    list($result, $maintainer) = get_tasks_and_maintainers($db);

                    $emoText = array("C"=>"🍀", "E"=>"⚡", "I"=>"💻", "S"=>"🎮");
                    $emoDescription = array("C"=>"Cose", "E"=>"Elettronica", "I"=>"Informatica", "S"=>"Svago");

                    while ($tasklist = $result->fetchArray(SQLITE3_ASSOC)){
                        echo "<tr>";
                        echo "<td title=\"${emoDescription[$tasklist['TaskType']]}\">".$emoText[$tasklist['TaskType']]."</td>";
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
    </div>
    <?php
}

/**
 * @param MyDB $db
 * @return array
 */
function get_tasks_and_maintainers(MyDB $db): array
{
    $result = $db->query('SELECT ID, Tasktype, Title, Description, Durate, Done
                                            FROM TASK 
                                            WHERE Done = 0
                                            ORDER BY ID');
    $result2 = $db->query('SELECT T_ID, Maintainer
                                            FROM T_MAINTAINER
                                            ORDER BY T_ID');
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
        $title = $_POST['title'];
        if(empty($_POST['idn'])) {
            $idn = null;
        } else {
            $idn = (int) $_POST['idn'];
        }
        $type = $_POST['tasktype'];
        $description = $_POST['description'];
        $durate = (int) $_POST['durate'];
        $maintainer = explode(',', $_POST['maintainer']);
        foreach($maintainer as &$temp_maintainer) {
            $temp_maintainer = trim($temp_maintainer);
        }
        unset($temp_maintainer);
        $edit = $_POST['submit'];
        if($edit === "Save") {
            update_task($db, $title, $description, $durate, $type, $idn, $maintainer);
        } elseif($edit === "Add") {
            add_new_task($db, $title, $description, $durate, $type, $maintainer);
        }
    }
}

function update_task(MyDB $db, $title, $description, int $durate, $type, int $idn, array $maintainer) {
    $stmt = $db->prepare('UPDATE "Task" 
                                    SET "Title" = :title, "Description" = :descr, "Durate" = :durate, "TaskType" = :tasktype
                                    WHERE "ID" = :id');
    if($stmt === false) {
        throw new RuntimeException('Cannot prepare statement');
    }

    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':descr', $description);
    $stmt->bindValue(':durate', $durate);
    $stmt->bindValue(':tasktype', $type);
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

    $temp = $db->query("SELECT MAX(ID) ID FROM TASK");
    $temp = $temp->fetchArray(SQLITE3_ASSOC);
    $idn = $temp['ID'];

    add_maintainers($db, $maintainer, $idn);
}


function add_maintainers(MyDB $db, array $maintainer, $idn) {
    foreach($maintainer as $temp_maintainer) {
        $db->query("INSERT INTO T_Maintainer (T_ID,Maintainer)
                        VALUES ('$idn', '$temp_maintainer')");
    }
}

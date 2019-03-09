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
                        $result = $db->query('SELECT ID, Tasktype, Title, Description, Durate, Done
                                            FROM TASK 
                                            WHERE Done = 0
                                            ORDER BY ID');
                        $result2 = $db->query('SELECT T_ID, Maintainer
                                            FROM T_MAINTAINER
                                            ORDER BY T_ID');
                        $maintainer = array();
                        while ($temp = $result2->fetchArray(SQLITE3_ASSOC)){
                            $maintainer[$temp['T_ID']] = array();
                        }
                        while ($temp = $result2->fetchArray(SQLITE3_ASSOC)){
                            //$maintainer[$temp['T_ID']]=array();
                            //// Why do we need two cycle?
                            array_push($maintainer[$temp['T_ID']],$temp['Maintainer']);
                        }

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

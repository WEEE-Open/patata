<?php
$quotes_file = file_get_contents('quotes.json');
$quotes = json_decode($quotes_file, true);
$quote_id = rand(0, count($quotes) - 1);
header('Content-Type', 'application/json');
exit(json_encode($quotes[$quote_id]));
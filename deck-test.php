<?php

require_once 'conf.php';
require 'functions.php';

header('Content-type: text/plain; charset=utf-8');

//$url = DECK_URL . "/apps/api/V2/deck/$board/$stack/cards";

$response = deck_request(DECK_URL . "/apps/deck/api/v1.0/boards");
$boards = json_decode($response, JSON_OBJECT_AS_ARRAY);

foreach($boards as $board) {
	echo "Board ${board['id']}: ${board['title']}\n";
	$stacks = deck_request(DECK_URL . "/apps/deck/api/v1.0/boards/${board['id']}/stacks");
	$stacks = json_decode($stacks, JSON_OBJECT_AS_ARRAY);
	foreach($stacks as $stack) {
		$count = count($stack['cards']);
		echo "-> Stack ${stack['id']}: ${stack['title']} ($count cards)\n";
	}
	echo "\n";
}

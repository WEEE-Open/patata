<?php

require 'conf.php';

header('Content-type: text/plain; charset=utf-8');

function sendRequest(string $url) {
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

//$url = DECK_URL . "/apps/api/V2/deck/$board/$stack/cards";

$response = sendRequest(DECK_URL . "/apps/deck/api/v1.0/boards");
$boards = json_decode($response, JSON_OBJECT_AS_ARRAY);

foreach($boards as $board) {
	echo "Board ${board['id']}: ${board['title']}\n";
	$stacks = sendRequest(DECK_URL . "/apps/deck/api/v1.0/boards/${board['id']}/stacks");
	$stacks = json_decode($stacks, JSON_OBJECT_AS_ARRAY);
	foreach($stacks as $stack) {
		$count = count($stack['cards']);
		echo "-> Stack ${stack['id']}: ${stack['title']} ($count cards)\n";
	}
	echo "\n";
}

echo "Type the correct URL in configuration: " . DECK_URL . "/apps/deck/api/v1.0/boards/.../stacks/...";

<?php

	if(isset($_POST["AuthenticationResponse"])) {
		$AuthenticationResponse = $_POST["AuthenticationResponse"];
		$RequestContent = urldecode($AuthenticationResponse);
		$response = simplexml_load_string($RequestContent);
		print_r($response);
	}
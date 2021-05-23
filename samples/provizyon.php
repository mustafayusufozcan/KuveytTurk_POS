<?php

	if(isset($_POST)) {
		
		require "class.kuveytturk.php";
		
		$kuveytturk = new KuveytTurk_POS("400235", "496", "apitest", "api123", 0);
		$response = $kuveytturk->provisionGate($_POST);
		
		if($response->ResponseCode == "00") {
			//OTORİZASYON VERİLDİ
		}
	}
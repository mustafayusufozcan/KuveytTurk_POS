<?php

	require "../class.kuveytturk.php";
	
	$kuveytturk = new KuveytTurk_POS("400235", "496", "apitest", "api123", 0);
	
	$orderID = "1";
	$amount = 100; //1 lira
	$cardHolderName = "Ali Veli";
	$cardNumber = "4033602562020327";
	$cardExpireDateMonth = "01";
	$cardExpireDateYear = "30";
	$cardCVV = "861";
	$okURL = "http://localhost/provizyon.php"; //Provizyon adresi
	$failURL = "http://localhost/hata.php"; //Hata adresi
	echo $kuveytturk->createPayment($orderID, $amount, $cardHolderName, $cardNumber, $cardExpireDateMonth, $cardExpireDateYear, $cardCVV, $okURL, $failURL);

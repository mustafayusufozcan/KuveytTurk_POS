<?php

	require "../class.kuveytturk.php";
	
	$kuveytturk = new KuveytTurk_POS("400235", "496", "apitest", "api123", 0);
	
	echo $kuveytturk->createPayment(1, 100, "Ali Veli", "4033602562020327", "01", "30", "861", "http://localhost/provizyon.php", "http://localhost/hata.php");

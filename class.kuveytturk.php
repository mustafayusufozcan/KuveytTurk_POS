<?php

	/**
	  * Kuveyt Türk Sanal POS
	  @author Mustafa Yusuf Özcan
	
	*/

	class KuveytTurk_POS {
		
		private $customerID;
		private $merchantID;
		private $username;
		private $password;
		private $production;
		private $urls = [
			0 => [
				"payGate" => "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate",
				"provisionGate" => "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelProvisionGate"
			],
			1 => [
				"payGate" => "https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelPayGate",
				"provisionGate" => "https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelProvisionGate"
			]
		];
		
		
		/**
			@param int $customerID Müşteri numarası
			@param int $merchantID Mağaza kodu
			@param string $username API kullanıcı adı
			@param string $password API Parolası
			@param int $production Test ortamı için 0, production için 1
		*/
		function __construct($customerID, $merchantID, $username, $password, $production = 1) {
			$this->customerID = $customerID;
			$this->merchantID = $merchantID;
			$this->username = $username;
			$this->password = $password;
			$this->production = $production;
		}
		
		
		/**
		  * Kart doğrulamasında kullanılacak XML şablonunu hazırlamak için kullanılır.
		  @param string $orderID Sipariş numarası
		  @param long $amount Tutar
		  @param string $cardHolderName Kartın üzerindeki ad
		  @param string $cardNumber Kart numarası
		  @param string $cardExpireDateMonth Kart son kullanma tarihi(ay)
		  @param string $cardExpireDateYear Kart son kullanma tarihi(yıl)
		  @param string $cardCVV Kart güvenlik kodu
		  @param string $okURL Provizyon adresi
		  @param string $failURL Hata adresi
		  
		  @return $this->payGate
		*/
		function createPayment($orderID, $amount, $cardHolderName, $cardNumber, $cardExpireDateMonth, $cardExpireDateYear, $cardCVV, $okURL, $failURL) {
			$hashData = $this->createHash($orderID, $amount, $okURL, $failURL);
			$cardType = $this->getCardType($cardNumber);
			
			$xml= '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
				.'<APIVersion>1.0.0</APIVersion>'
				.'<OkUrl>'.$okURL.'</OkUrl>'
				.'<FailUrl>'.$failURL.'</FailUrl>'
				.'<HashData>'.$hashData.'</HashData>'
				.'<MerchantId>'.$this->merchantID.'</MerchantId>'
				.'<CustomerId>'.$this->customerID.'</CustomerId>'
				.'<UserName>'.$this->username.'</UserName>'
				.'<CardNumber>'.$cardNumber.'</CardNumber>'
				.'<CardExpireDateYear>'.$cardExpireDateYear.'</CardExpireDateYear>'
				.'<CardExpireDateMonth>'.$cardExpireDateMonth.'</CardExpireDateMonth>'
				.'<CardCVV2>'.$cardCVV.'</CardCVV2>'
				.'<CardHolderName>'.$cardHolderName.'</CardHolderName>'
				.'<CardType>'.$cardType.'</CardType>'
				.'<BatchID>0</BatchID>'
				.'<TransactionType>Sale</TransactionType>'
				.'<InstallmentCount>0</InstallmentCount>'
				.'<Amount>'.$amount.'</Amount>'
				.'<DisplayAmount>'.$amount.'</DisplayAmount>'
				.'<CurrencyCode>0949</CurrencyCode>'
				.'<MerchantOrderId>'.$orderID.'</MerchantOrderId>'
				.'<TransactionSecurity>3</TransactionSecurity>'
				.'</KuveytTurkVPosMessage>';
			return $this->payGate($xml);
		}
		
		/**
		  * Hash oluşturmak için kullanılır,
		  @param string $orderID Sipariş numarası
		  @param long $amount Tutar
		  @param string $okURL Provizyon adresi
		  @param string $failURL Hata adresi
		  
		  @return string $hash
		
		*/
		private function createHash($orderID, $amount, $okURL, $failURL) {
			$hashedPassword = base64_encode(sha1($this->password, "ISO-8859-9"));
			return base64_encode(sha1($this->merchantID.$orderID.$amount.$okURL.$failURL.$this->username.$hashedPassword , "ISO-8859-9"));
		}
		
		/**
		  * Kartın türünü belirlemek için kullanılır.
		  
		  @param string $cardNumber Kart numarası
		  
		  @return string $cardType
		*/
		
		private function getCardType($cardNumber) {
			if(substr($cardNumber, 0, 1) == 4) {
				return "Visa";
			} elseif(substr($cardNumber, 0, 2) >= 51 && substr($cardNumber, 0, 2) <= 55) {
				return "MasterCard";
			} elseif(in_array(substr($cardNumber, 0, 4), [9792, 6500, 6501, 6504, 6509, 6573, 6579, 6549])) {
				return "Troy";
			}
		}
		
		/**
		  * createPayment metodunda oluşturuan XML şablonunu bankaya POST etmek için kullanılır.
		  
		  @param string $xml XML şablonunu
		  
		  @return string $data
		*/
		
		private function payGate($xml) {
			try {
				$ch = curl_init();  
				curl_setopt($ch, CURLOPT_SSLVERSION, 6);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '. strlen($xml)) );
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_URL, $this->urls[$this->production]["payGate"]); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$data = curl_exec($ch);  
				curl_close($ch);
				return $data;
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
		
		/**
		  * Provizyon almak için kullanılır.
		  
		  @param string $data Provizyon sayfasına banka tarafından gönderilen veri
		  
		  @return object $response
		
		*/
		
		function provisionGate($data) {
			if(isset($data["AuthenticationResponse"])) {
				$responseContent = urldecode($data["AuthenticationResponse"]);
				if($response = simplexml_load_string($responseContent)) {
					if($response->ResponseCode == "00") {
						$orderID = $response->VPosMessage->MerchantOrderId;
						$amount = $response->VPosMessage->Amount;
						$MD = $response->MD;
						$type = "Sale";
						$hashedPassword = base64_encode(sha1($this->password,"ISO-8859-9"));
						$hashData = base64_encode(sha1($this->merchantID.$orderID.$amount.$this->username.$hashedPassword , "ISO-8859-9"));
						$xml = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
							<APIVersion>1.0.0</APIVersion>
							<HashData>'.$hashData.'</HashData>
							<MerchantId>'.$this->merchantID.'</MerchantId>
							<CustomerId>'.$this->customerID.'</CustomerId>
							<UserName>'.$this->username.'</UserName>
							<TransactionType>Sale</TransactionType>
							<InstallmentCount>0</InstallmentCount>
							<CurrencyCode>0949</CurrencyCode>
							<Amount>'.$amount.'</Amount>
							<MerchantOrderId>'.$orderID.'</MerchantOrderId>
							<TransactionSecurity>3</TransactionSecurity>
							<KuveytTurkVPosAdditionalData>
							<AdditionalData>
								<Key>MD</Key>
								<Data>'.$MD.'</Data>
							</AdditionalData>
						</KuveytTurkVPosAdditionalData>
						</KuveytTurkVPosMessage>';
						try {
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_SSLVERSION, 6);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
							curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '. strlen($xml)));
							curl_setopt($ch, CURLOPT_POST, true);
							curl_setopt($ch, CURLOPT_HEADER, false); 
							curl_setopt($ch, CURLOPT_URL,$this->urls[$this->production]["provisionGate"]); 
							curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							$data = curl_exec($ch);  
							curl_close($ch);
							if($response = simplexml_load_string($data)) {
								return $response;
							}
						} catch (Exception $e) {
							throw new Exception($e->getMessage());
						}
					}
				}
			}
			throw new Exception("Authentication error!");
		}
		
	}
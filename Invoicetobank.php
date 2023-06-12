<?php
//https://dev.intacctservices.com/se/xxxx/in/Don/invoice_to_bank.php?ARcustid=C-4848&sessionid=teMkxQeOQPE8jRyFOWTNwo5f8T0.&ARDOCid=INV-2230
//https://dev.intacctservices.com/se/xxxx/in/Don/invoice_to_bank.php?ARcustid={!SODOCUMENT.CUSTVENDID!}&sessionid={!USERPROFILE.SESSIONID!}&ARDOCid={!SODOCUMENT.DOCID!}
   
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting (E_ALL); 
      
require_once "PostXml.php";
require_once "PostXml2.php";

//Actual live data freed from Intacct Application
$sessionid =$_GET["sessionid"];
$ARcustomerid = $_GET["ARcustid"];

$DocID = $_GET["ARDOCid"];
//echo $ARcustomerid." ".$sessionid;

//instantiate post object
$myPostXml = new ts_PostXml("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);
$myPostXml2 = new ts_PostXml2("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);

//Get the Invoice 2.1
$xmlAR ='<content>
 <function controlid="testControlId">
  <get_list object="invoice" maxitems="10">
  <filter>
  <expression>
   <field>invoiceno</field>
   <operator>=</operator>
   <value>'.$DocID.'</value>
  </expression>
  </filter>
  </get_list>
 </function>
</content>';

 try {
	  $responseAR = $myPostXml->postXml($xmlAR);
	//$responseAR = $myPostXml2->postXml($xmlAR);
				
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}

$xmlRsltAR = new SimpleXMLElement($responseAR);

 $arCustid = $xmlRsltAR->operation->result->data->invoice->customerid;
echo "\n";
 $arKey = $xmlRsltAR->operation->result->data->invoice->key;
echo "\n";
 $arAmt = $xmlRsltAR->operation->result->data->invoice->totaldue;
echo "\n";

//Get the customer ach/card indicator 3.0 - double quoting the full xml and removing period and placing single quotes 
$xmlCust ="<content>
 <function controlid=\"testControlId\">
  <readByQuery>
    <object>CUSTOMER</object>
    <fields>CUSTOMERID,ENABLEONLINECARDPAYMENT,ENABLEONLINEACHPAYMENT</fields>
    <query>CUSTOMERID = '$arCustid'</query>
    <docparid></docparid>
  </readByQuery>
 </function>
</content>";

 try {
//	$responseCust = $myPostXml->postXml($xmlcust);
	$responseCust = $myPostXml2->postXml($xmlCust);
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}

    $xmlRsltCust = new SimpleXMLElement($responseCust);
  
 echo "\n"; 
  echo " This is the record for arpayment ";
echo "\n";
echo $arCustid = $xmlRsltAR->operation->result->data->invoice->customerid;
echo "\n";
echo $arKey = $xmlRsltAR->operation->result->data->invoice->key;
echo "\n";
echo $arAmt = $xmlRsltAR->operation->result->data->invoice->totaldue;
echo "\n";
echo $custCard = $xmlRsltCust->operation->result->data->customer->ENABLEONLINECARDPAYMENT;
echo "\n";
echo $custACH = $xmlRsltCust->operation->result->data->customer->ENABLEONLINEACHPAYMENT;
echo "\n";
     $chgType = '';
     //$arKey = intval($arKey);

   //Get the charge reference
           $custCard = $xmlRsltCust->operation->result->data->customer->ENABLEONLINECARDPAYMENT;
		   $custACH = $xmlRsltCust->operation->result->data->customer->ENABLEONLINEACHPAYMENT;
	     
				if ($custCard == 'true'){
				   $chgType = 'Online Charge Card';	
					  
		  			//Create payment for invoices Card 2.1
					$xmlChgUpd ='<content>
                         <function controlid="testControlId">
					    <create_arpayment>
					      <customerid>'.$arCustid.'</customerid>
					      <paymentamount>'.$arAmt.'</paymentamount>
					      <bankaccountid>WF01</bankaccountid>
					      <refid>tmPay101</refid>
					      <datereceived>
					        <year>2014</year>
					        <month>08</month>
					        <day>8</day>
					      </datereceived>
					      <paymentmethod>'.$chgType.'</paymentmethod>
					      <arpaymentitem>
					        <invoicekey>'.$arKey.'</invoicekey>
					        <amount>'.$arAmt.'</amount>
					      </arpaymentitem>
					     <onlinecardpayment>
					          <cardnum/>
					           	<expirydate><exp_month/><exp_year/></expirydate>
					          <cardtype/>
					          <usedefaultcard>true</usedefaultcard>
					     </onlinecardpayment>
					    </create_arpayment>
					      </function>
                            </content>';
					
				 }else{
				         $chgType = "Online ACH Debit";
				     		
						//Create payment for invoices ACH 2.1
						$xmlChgUpd ='<content>
                         <function controlid="testControlId">
						<create_arpayment>
						      <customerid>'.$arCustid.'</customerid>
						      <paymentamount>'.$arAmt.'</paymentamount>
						      <bankaccountid>WF01</bankaccountid>
						      <refid>tmPay101</refid>
						      <datereceived>
						        <year>2014</year>
						        <month>08</month>
						        <day>8</day>
						      </datereceived>
						      <paymentmethod>'.$chgType.'</paymentmethod>
						      <arpaymentitem>
						        <invoicekey>'.$arKey.'</invoicekey>
						        <amount>'.$arAmt.'</amount>
						      </arpaymentitem>
						      <onlineachpayment>
						          <bankname>Bank of America</bankname>
						          <accounttype>Business Checking</accounttype>
						          <accountnumber>7658493094587</accountnumber>
						          <routingnumber>121000358</routingnumber>
						          <accountholder>Sample Merchant 2</accountholder>      
						     </onlineachpayment>
						    </create_arpayment>
						     </function>
                            </content>';
						
	                  }

						 try {
							$responseChgUpd = $myPostXml->postXml($xmlChgUpd);
						//	$responseChgUpd = $myPostXml2->postXml($xmlChgUpd);
						var_dump($responseChgUpd);
						} catch (Exception $e) {
							echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
						}
 
/* to get rid of empty spaces on variables
		   if(rtrim($arCustid) == rtrim($custID) ) {  
		   	echo "cars";
			 echo $custCard = $xmlRsltCust->operation->result->data->customer->ENABLEONLINECARDPAYMENT;
		     echo $custACH = $xmlRsltCust->operation->result->data->customer->ENABLEONLINEACHPAYMENT;
	        }  
*/   
   
?>

<?php
//https:www.metricmill.com/intacct/venki/Don/advanceToOE.php
//https:www.metricmill.com/intacct/venki/Don/advanceToOE.php?SOcustid=C-0003&SOrefno=13&sessionid=y5MF-Z70aKRvT0_8_yoZw-R3pG4.


ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting (E_ALL); 

//include
//include_once('api/api_session.php');
//include_once('api/api_util.php');

//require
require_once "PostXml.php";
require_once "PostXml2.php";

//Actual live data freed from Intacct Application
$sessionid =$_GET["sessionid"];
$SOcustomerid = $_GET["SOcustid"];
$SOreferenceno= $_GET["SOrefno"];

//Testing variables 
//$sessionid = "y5MF-Z70aKRvT0_8_yoZw-R3pG4.";
//$SOcustomerid ='C-0002';
//$SOreferenceno = '12';

//instantiate post object
$myPostXml = new ts_PostXml("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);
$myPostXml2 = new ts_PostXml2("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);


//Capture memo line remarks to compare 2.1
$xmlAdv ='<content>
 <function controlid="testControlId">
  <get_list object="arpayment" maxitems="10">
<filter>
  <expression>
   <field>customerid</field>
   <operator>=</operator>
   <value>'.$SOcustomerid.'</value>
  </expression>
 </filter>
  </get_list>
 </function>
</content>';

 try {
	$responseAdv = $myPostXml->postXml($xmlAdv);
//	$responseAdv = $myPostXml2->postXml($xmlAdv);
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}

//Calling a standard php function
	$xmlRsltAdv = new SimpleXMLElement($responseAdv);
	
//create variable
//var_dump($xmlRsltAdv);

//	$AdvanceCust = $xmlRsltAdv->operation->result->data->arpayment->customerid;
//	$AdvanceAmt = $xmlRsltAdv->operation->result->data->arpayment->lineitems->lineitem->amount;
//	$AdvanceMemo  = $xmlRsltAdv->operation->result->data->arpayment->lineitems->lineitem->memo;
//	$Advancekey  = $xmlRsltAdv->operation->result->data->arpayment->key;
//	$Advancebatchkey = $xmlRsltAdv->operation->result->data->arpayment->batchkey;
	  
//echo $AdvanceCust."  ".$AdvanceMemo."  ".$AdvanceAmt." ".$Advancekey." ".$Advancebatchkey;
	
//Get the current invoice that reference ar advance 2.1

$xmlInv ='<content>
 <function controlid="testControlId">
  <get_list object="sotransaction" maxitems="10">
  <filter>
  <expression>
   <field>customerid</field>
   <operator>=</operator>
   <value>'.$SOcustomerid.'</value>
  </expression>
  <expression>
   <field>referenceno</field>
   <operator>=</operator>
   <value>'.$SOreferenceno.'</value>
  </expression>
</filter>
  </get_list>
 </function>
</content>';


//referenceno
 try {
	$responseInv = $myPostXml->postXml($xmlInv);
//	$responseInv = $myPostXml2->postXml($xmlInv);
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}

//Calling a standard php function 3.0
	$xmlRsltInv = new SimpleXMLElement($responseInv);

//	$SOcustomerid2 =  $xmlRsltInv->operation->result->data->sotransaction->customerid;
	$SOprrecordkey = $xmlRsltInv->operation->result->data->sotransaction->prrecordkey;

    foreach($xmlRsltAdv->operation->result->data->arpayment as $Advancekey){
						
				  $AdvanceCust = $Advancekey->customerid;
	              $AdvanceAmt = $Advancekey->lineitems->lineitem->amount;
	              $AdvanceMemo  = $Advancekey->lineitems->lineitem->memo;
	              $Advkey  = $Advancekey->key;
	              $Advbatchkey = $Advancekey->batchkey;
						     
						echo "who is mitchell";	 
						echo " 1 ".$Advkey." 2 ".$Advbatchkey." 3 ".$SOprrecordkey." 4 ".$AdvanceAmt;
							 
            if ($AdvanceMemo == $SOreferenceno){	
		             	echo "Who is donald";
						echo " 1 ".$Advkey." 2 ".$Advbatchkey." 3 ".$SOprrecordkey." 4 ".$AdvanceAmt;
						
				 $xmlupdate ='<content>
			     	 <function controlid="testControlId">
				      <apply_arpayment>
					    <arpaymentkey>'.$Advkey.'</arpaymentkey>
					      <paymentdate>
								<year>2014</year>
								<month>06</month>
								<day>03</day>
						</paymentdate>
						<batchkey>'.$Advbatchkey.'</batchkey>
						<memo></memo>
						<overpaylocid></overpaylocid>
						<overpaydeptid></overpaydeptid>
						<arpaymentitems>
							<arpaymentitem>
								<invoicekey>'.$SOprrecordkey.'</invoicekey>
								<amount>'.$AdvanceAmt.'</amount>
							</arpaymentitem>
						</arpaymentitems>
					</apply_arpayment>
					 </function>
					</content> ';
			 
				
				try {
					$responseInv2 = $myPostXml->postXml($xmlupdate);
					$responseInv2 = $myPostXml2->postXml($xmlupdate);
				} catch (Exception $e) {
					echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
				}
			  }
			}
?>

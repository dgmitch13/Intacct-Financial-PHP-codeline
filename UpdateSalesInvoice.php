<?php

ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting (E_ALL); 


      
require_once "PostXml.php";
require_once "PostXml2.php";

//Actual live data freed from Intacct Application
$sessionid =$_GET["sessionid"];
$SICustID = $_GET["SICustID"];
$SIDocID = $_GET["SIDocID"];

//instantiate post object
$myPostXml = new ts_PostXml("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);
$myPostXml2 = new ts_PostXml2("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);

//Read the Project object 3.0
$xmlProj = "<content>
 <function controlid=\"testControlId\">
  <readByQuery>
    <object>PROJECTRESOURCES</object>
    <query>PROJECTID = 'Proj-00251'</query>
  </readByQuery>
 </function>
</content>";

try {
	 //$responseProj = $myPostXml->postXml($xmlProj );
	$responseProj = $myPostXml2->postXml($xmlProj);
				
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}
     $xmlRsltProj = new SimpleXMLElement($responseProj);
		
        $Twenty = '';
		$Fifteen = '';
		$Flatfee = '';

		foreach($xmlRsltProj->operation->result->data->projectresources as $proRec2){
	           	
	           $project = array($proRec2->POAPRATE);
			   $project2 = array($proRec2->ITEMID);
			   $project3 = array($proRec2->BILLINGRATE)	;	 	
               
               if ($project2[0]=='Benefit Load'){
               	    $Twenty = ($project[0]/100);
			  }elseif ($project2[0]=='Management Fee') {
					$Fifteen = ($project[0]/100);
			  }elseif($project2[0]=='PHP'){
			  		$FlatFee = ($project3[0]);
			  	
			  }
			  	 
		}
 
	
//Read the Statistical account 3.0

$xmlStat = "<content>
 <function controlid=\"testControlId\">
  <readByQuery>
    <object>GLENTRY</object>
<query>STATISTICAL = 'T' and ACCOUNTNO = 'SMEC'</query>    
  </readByQuery>
 </function>
</content>";

try {
	//$responseStat = $myPostXml->postXml($xmlStat);
	$responseStat = $myPostXml2->postXml($xmlStat);
					
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}
  $xmlRsltStat = new SimpleXMLElement($responseStat);	
    
//Read the number of employees and find the latest update
    
    $maxrec= array($xmlRsltStat->operation->result->data->glentry->BATCH_NUMBER);
	$max = max($maxrec);
	    
	foreach($xmlRsltStat->operation->result->data->glentry as $Track){
		   if ($Track->BATCH_NUMBER == $max){
	   	  	$EmpCt= $Track->TRX_AMOUNT;
	   }	
	}

//Update a current Sales Invoice 3.0
$xmlSI ="<content>
 <function controlid=\"testControlId\">
  <readByQuery>
    <object>SODOCUMENTENTRY</object>
<query> CUSTOMERID = '$SICustID'</query>
  </readByQuery>
 </function>
</content>";

 try {
	 // $responseInv = $myPostXml->postXml($xmlInv);
	$responseSI = $myPostXml2->postXml($xmlSI);
				
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}

$xmlRsltSI = new SimpleXMLElement($responseSI);

          $InvGrdTot = '';
		  $chproload ='';
		  $chstaffload = '';
		  $mgmtfee = '';
		  $emplyfees = '';
          
          foreach($xmlRsltSI->operation->result->data->sodocumententry as $invoice){
          
				echo "\n";   	
                  $InvLine = $invoice->LINE_NO;
			    echo "\n";
                 $InvItem= $invoice->ITEMNAME;
				echo "\n";
		          $InvTotal = $invoice->TOTAL;
          	    echo "\n";
		        $InvGrdTot = $InvGrdTot + $InvTotal;
				echo "\n";		
												
			//calculations	   
			if ('CH Provider Payroll'== rtrim($InvItem) ){	        
				$chproload = ($InvTotal*$Twenty);	
			}elseif('CH Staff Payroll' == rtrim($InvItem)){
			    $chstaffload = ($InvTotal*$Twenty);
			}		
								
		   
	  }     	  
		   //calculations
		   $emplyfees = ($EmpCt*($FlatFee));
		   echo $chproload.'   '.$chstaffload; 
		    $mgmtfee = (($InvGrdTot + $chproload + $chstaffload)*$Fifteen);
		   echo "\n";
		   echo $mgmtfee;
		   echo "\n";
		   $subtotal = $InvGrdTot + $chproload + $chstaffload+$mgmtfee+$emplyfees;
		   echo $subtotal; 
 
 //Capture invoice number 
 
 $xmlIn =  '<content>
 <function controlid="testControlId">
  <get_list object="invoice" maxitems="10">
<filter>
  <expression>
   <field>customerid</field>
   <operator>=</operator>
   <value>'.$SICustID.'</value>
  </expression>
</filter>
  </get_list>
 </function>
</content>';
 
 try {
	  $responseIn = $myPostXml->postXml($xmlIn);
	//$responseSI2 = $myPostXml2->postXml($xmlSI2);
				
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
} 
  $xmlRsltIn = new SimpleXMLElement($responseIn);
  $InvoiceKey =  $xmlRsltIn->operation->result->data->invoice->invoiceno;
 
 
//Update a current Sales Invoice 2.1

$xmlSI2 =' <content>
 <function controlid="testControlId">
<update_sotransaction  key="Sales Invoice-'.$InvoiceKey.'" disablevalidation="true">
	<updatesotransitems>	
       <sotransitem>
			<itemid>CH Provider Load</itemid>
			<itemdesc>CH Provider Benefit Load</itemdesc>
			<warehouseid></warehouseid>
			<quantity>1</quantity>
			<unit>Each</unit>
			<price>'.$chproload.'</price>
			<locationid></locationid>
			<departmentid></departmentid>
			<projectid>Proj-00251</projectid>
			</sotransitem>    
      <sotransitem>
			<itemid>CH Staff Load</itemid>
			<itemdesc>CH Staff Benefit Load</itemdesc>
			<warehouseid></warehouseid>
			<quantity>1</quantity>
			<unit>Each</unit>
			<price>'.$chstaffload.'</price>
			<locationid></locationid>
			<departmentid></departmentid>
			<projectid>Proj-00251</projectid>
		  </sotransitem>	  
	  <sotransitem>
			<itemid>Management Fee</itemid>
			<itemdesc>Management Fee</itemdesc>
			<warehouseid></warehouseid>
			<quantity>1</quantity>
			<unit>Each</unit>
			<price>'.$mgmtfee.'</price>
			<locationid></locationid>
			<departmentid></departmentid>
			<projectid>Proj-00251</projectid>
			</sotransitem>	
	<sotransitem>
				<itemid>PHP</itemid>
				<itemdesc>Patient Health Platform</itemdesc>
				<warehouseid></warehouseid>
				<quantity>1</quantity>
				<unit>Each</unit>
				<price>'.$emplyfees.'</price>
				<locationid></locationid>
				<departmentid></departmentid>
				<projectid>Proj-00251</projectid>
			 </sotransitem>	
    </updatesotransitems>
   </update_sotransaction>
   </function>
</content>';

 try {
	  $responseSI2 = $myPostXml->postXml($xmlSI2);
	//$responseSI2 = $myPostXml2->postXml($xmlSI2);
	var_dump($responseSI2);
				
} catch (Exception $e) {
	echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
}

$xmlRsltSI2 = new SimpleXMLElement($responseSI2);		 

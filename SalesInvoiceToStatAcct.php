<?php
//https://www.windyhighway.com/intacct/xxxxx/Don/SI_to_StatAcct_3.php
//https://www.windyhighway.com/intacct/xxxxx/Don/SI_to_StatAcct_3.php?SOcustid={!SODOCUMENT.CUSTVENDID!}&SOrefno={!SODOCUMENT.PONUMBER!}&sessionid={!USERPROFILE.SESSIONID!}
//https://www.windyhighway.com/intacct/xxxxx/Don/SI_to_StatAcct_3.php?SOcustid=C-0003&SOrefno=24&sessionid=Wu7kRcfCnftsFDWegYuDKcKC_20.

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
//$sessionid = "GanihmrotxFMnx2bXg33KOioEU0.";
//$SOcustomerid ='C-0008';
//$SOreferenceno = '28';

//instantiate post object
$myPostXml = new ts_PostXml("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);
$myPostXml2 = new ts_PostXml2("https://www.intacct.com/ia/xml/xmlgw.phtml","dgmitchell","Welcome1!","","","","","",$sessionid);

	  
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

	$xmlRsltInv = new SimpleXMLElement($responseInv);
    //echo var_dump($xmlRsltInv);
    
	$itemid  = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->itemid;
	$custid =$xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->customerid;
	$stDateYr = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->revrecstartdate->year;
	$stDateMth = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->revrecstartdate->month;
	$stDateDay = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->revrecstartdate->day;
	$endDateYr = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->revrecenddate->year;
	$endDateMth = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->revrecenddate->month;
	$endDateDay = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->revrecenddate->day;
	$location = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->locationid;
    $department = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->departmentid;
    $class = $xmlRsltInv->operation->result->data->sotransaction->sotransitems->sotransitem->classid; 

//Time capture========	
//Current date
	 $Today = date("m/d/Y");		
	 
	   echo $month1 = strval($stDateMth);
	   echo "\n";
	   echo $month2 = strval($endDateMth); 
	   echo "\n";
     
//calculate for month
     if ($month1 == $month2){
     	
	$stNum = cal_days_in_month(CAL_GREGORIAN,strval($stDateMth),strval($stDateYr));//30
	$endNum = cal_days_in_month(CAL_GREGORIAN,strval($endDateMth),strval($endDateYr));//30
	
		echo $mthSt= strval($stNum)-strval($stDateDay);//19	  
		echo "\n";  
	    echo $mthEnd= strval($endNum) -strval($endDateDay);//3 
	    echo "\n";
	    echo  $mthBal = $mthSt - $mthEnd;
			
     }Else{
//calculate over months
  
         $StartDay = $stDateMth."-".$stDateDay."-".$stDateYr;
		 $EndDay = $endDateMth."-".$endDateDay."-".$endDateYr; 
		 $calCount = (365/12);       
	     $numMonth =  (($EndDay- $StartDay)*$calCount);
	    // echo "\n";
		 echo "number of months ".$a = ($EndDay- $StartDay);//2
         $b = $a;
         
         $totDay = 0;
		 $totDay1 = 0;
		 $totDay2  = 0;
		 $totDay3 =0;
		 
		  for ($i=0; $i<$a ;$i++){
		  	
		    $stNum = cal_days_in_month(CAL_GREGORIAN,strval($stDateMth),strval($stDateYr));
		    $endNum = cal_days_in_month(CAL_GREGORIAN,strval($endDateMth),strval($endDateYr)); 
				
		        if ($i == 0){       
			      $result = strval($stNum) - strval($stDateDay); 
				  $totDay += round($result + $numMonth);
		  		  
				        }
				
				 if($i == 0){
				  echo " the first number ".$totDay = $totDay- strval($stNum); 			
				  }
				 
				  if($b-1 == $i){
                   //   echo " what is b ".$b;
				    //  echo " what is i ".$i;	   
                      $totDay =$totDay + strval($endNum); 
					//  echo " total to minus ".strval($endNum);
					 // echo " the ending total ".strval($endDateDay);
					  $totDay2 =strval($endNum) - (strval($endDateDay)+1);
					  echo " Total number of days ".$totDay3 =$totDay - $totDay2;
					  

				  } 
				}
		 
			}

//Time capture========	

//Create Stat account 2.1
    $xmlupdate ='<content>
    <function controlid="testControlId">
    <create_statgltransaction>
	<journalid>Stats</journalid>
	<datecreated>
		<year>2014</year>
        <month>06</month>
		<day>06</day>	
	</datecreated>
	<description>Test Transaction</description>
	<statgltransactionentries>
		<glentry>
			<trtype>debit</trtype>
			<amount>'.$totDay3.'</amount>
			<glaccountno>STUD</glaccountno>
			<document></document>
			<datecreated>
				 <year>2014</year>
                    <month>06</month>
				 <day>06</day>
			</datecreated>
			<memo></memo>
			<locationid>'.$location.'</locationid>
			<departmentid>'.$department.'</departmentid>
                        <customerid>'.$custid.'</customerid>
                        <itemid>'.$itemid.'</itemid>
                        <classid>'.$class.'</classid>
			<customfields>
				<customfield>
					<customfieldname>INCREASE</customfieldname>
					<customfieldvalue>10</customfieldvalue>
				</customfield>
			</customfields>
			<recon_date>
				<year></year>
				<month></month>
				<day></day>
			</recon_date>
			<currency></currency>
			<exchratetype>Intacct Daily Rate</exchratetype>
		</glentry>
	</statgltransactionentries>
  </create_statgltransaction>
    </function>
  </content>';
		
		 try {
			$responseStat = $myPostXml->postXml($xmlupdate);
	//		$responseStat = $myPostXml2->postXml($xmlupdate);
			var_dump($responseStat);
				
		} catch (Exception $e) {
			echo "<br/><p><strong>Error Processing Results: </strong> " . $e->getMessage() . "</p>";
		}		
			
//<!ELEMENT glentry (trtype, amount, glaccountno,locationid?, departmentid?, customerid?, vendorid?, itemid?, classid?, customfields?, recon_date?, (currency, ((exchratedate?, exchratetype) | exchrate))?)>


 
?>

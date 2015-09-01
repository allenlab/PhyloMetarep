<!----------------------------------------------------------

  File: result_panel.ctp
  Description: Compare Result Panel
 
  The Compare Result Panel displays the comparison results 
  within the Compare Tab Panel. It provides options to change
  the category level at which datasets are being compared, 
  flip the axis of the result matrix, change the font size, 
  change the color of the HTML heatmap, download data and 
  layouts PDF graphics within an iFrame.
  
  PHP versions 4 and 5

  METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
  Copyright(c)  J. Craig Venter Institute (http://www.jcvi.org)

  Licensed under The MIT License
  Redistributions of files must retain the above copyright notice.

  @link http://www.jcvi.org/metarep METAREP Project
  @package metarep
  @version METAREP v 1.3.2
  @author Johannes Goll
  @lastmodified 2010-07-09
  @license http://www.opensource.org/licenses/mit-license.php The MIT License
  
<!---------------------------------------------------------->


<?php

$selectedDatasets 	= $session->read('selectedDatasets'); 
$option				= $session->read('option'); 
$minCount			= $session->read('wordCount'); 
$tabs				= $session->read('tabs');
$mode				= $session->read('mode');

## if empty results
if(isset($message)) {
	echo("<div id=\"flashMessage\" class=\"message\" style=\"position:absolute;font-size:1.4em;top:90px;text-align:center;left:200px\">$message</div>");
	exit();
}

$currentTabPosition =0;
$tabPosition = 0;
if(isset($tabs)) {
	foreach($tabs as $tab) {
		if($tab['function'] === $mode) {			
			$currentTabPosition = $tabPosition;
		}
		$tabPosition ++;
	}
}

$lastPos = sizeof($annotationFields)-1;
 
?>

<script>
jQuery(document).ready(function(){
  jQuery.noConflict() ;
  
  jQuery("#myTable").tablesorter({widgets:['zebra'],sortList: [[<?php echo($lastPos)?>,1]]}); 
	
  // Reset Font Size
  var originalFontSize = jQuery('.comparison-results-table').css('font-size');
  jQuery(".resetFont").click(function(){
    	jQuery('.comparison-results-table').css('font-size', originalFontSize);
  });
  // Increase Font Size
  jQuery(".increaseFont").click(function(){
	
    var currentFontSize = jQuery('.comparison-results-table').css('font-size');
    var currentFontSizeNum = parseFloat(currentFontSize, 10);
    var newFontSize = currentFontSizeNum*1.2;
    jQuery('.comparison-results-table').css('font-size', newFontSize);
    return false;
  });
  // Decrease Font Size
  jQuery(".decreaseFont").click(function(){
	 
    var currentFontSize = jQuery('.comparison-results-table').css('font-size');
    var currentFontSizeNum = parseFloat(currentFontSize, 10);
    var newFontSize = currentFontSizeNum*0.8;
    jQuery('.comparison-results-table').css('font-size', newFontSize);
    return false;
  });

jQuery.ajax({ success: function(){
  jQuery("#myTable").trigger("appendCache");
  jQuery("#tabs").tabs( "option", "selected",<?php echo($currentTabPosition);?>);
}}); 

jQuery.('#Download').click(function(){
	jQuery('#AnnotationSelectForm').submit();
	});

});
</script>
    
    
   
<?php #echo $form->create('Annotation',array('options'=>false,'action'=>'select'));?>

<?php  

	#$options = array(1 => 'ONE', 'TWO', 'THREE');
	#$selected = array(1, 3);
	
	#echo $form->input('Model.name', array('multiple' => 'checkbox', 'options' => $options, 'selected' => $selected));
	#echo $form->end('Save');
	echo $html->div('comparator-download', $html->link($html->image("download-medium.png",array("title" => 'Download Blast Results','ID'=>'Download')), array('controller'=> 'blast','action'=>'download'),array('escape' => false)));	

	echo("<div id=\"resize-box\"><a href=\"#\" class=\"increaseFont\">zoom in</a> <a href=\"#\" class=\"decreaseFont\">zoom out</a>
				</div>");
	$content = '<BR><BR>';
	$content .= "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead>"; 	
	$content .= "<tr>";
	#$content .= "<th>Select</th>";
	
	## generate header
	foreach($annotationFields as $fieldId => $fieldName) {
		$content.= "<th>$fieldName</th>";
	}

		
	$content.= "</tr>";	
	$content .= "</thead><tbody>";
	
	foreach ($annotations as $hit ) {	
			$class = null;
			
			$peptideId = $hit->{'peptide_id'};
			
			#$content.=  "<td><input type=\"checkbox\" name=\"data[Annotation][Annotation][]\" value=\"$peptideId\" id=\"AnnotationAnnotation$peptideId\"/></td>";
			
			
			//print field values
			foreach($annotationFields as $fieldId => $fieldName) {
				
				if(!is_null(ClassRegistry::init("Phylodb.Protein")) && $fieldId === 'peptide_id' && !empty($hit->{'com_name_src'})) {	
					
					$content .=  "<td>{$hit->{$fieldId}}<BR>".$html->link('[phylodb homolog]', array('plugin' => 'phylodb','controller'=> 'phylodb', 'action'=>'protein',$hit->{'com_name_src'}),array('target'=>'_blank')); 
					$content .=  $html->link('[feature]', array('plugin' => null,'controller'=> 'features', 'action'=>'index',42,'',$hit->{$fieldId},'Search'),array('target'=>'_blank'));
					
					if(file_exists(SEQUENCE_STORE_PATH."/42/$dataset/tree/{$hit->{$fieldId}}.pdf")) {
						$content .=  $html->link('[tree]', array('plugin' => null,'controller'=> 'features', 'action'=>'tree',42,'',$hit->{$fieldId}),array('target'=>'_blank'))."</td>"; 
					}
				} 
				else {	
					$value = $hit->{$fieldId};
					if(is_array($value)) {
						$value =  implode('<BR>',$value);
					}									
							
					$content .= "<td>".$value."</td>";
				}				
			}
			$content .= '</tr>';
		}
	$content .= '</tbody></table>';
	
	echo($content);	
?>
<?php #echo $form->end('Save');?>
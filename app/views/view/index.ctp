<!----------------------------------------------------------
  File: index.ctp
  Description: View Index Page.

  Author: jgoll
  Date:   Mar 18, 2010
<!---------------------------------------------------------->

<?php echo $html->css('view.css');

$viewResults 		= $session->read($sessionId);
$projectId			= $viewResults['projectId'];
$projectName		= $viewResults['projectName'];
$optionalDatatypes	= $viewResults['optionalDatatypes'];
$numHits			= $viewResults['numHits']; 
$numDocs			= $viewResults['numDocs']; 
$documents			= $viewResults['documents']; 

if(isset($viewResults['filters'])) {
$filters		= $viewResults['filters']; 
}

$tabs   = $session->read($sessionId.'tabs');
$resultFields = $session->read($sessionId.'resultFields');

?>
<div class="view-panel">

<ul id="breadcrumb">
 	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
    <li><?php echo $html->link('Investigators', "/projects/index");?></li>
    <li><?php echo $html->link('View Investigator Project', "/projects/view/$projectId");?></li>
    <li><?php echo $html->link('View Dataset', "/view/index/$dataset");?></li>
</ul>

<h2><?php __("View"); ?><span class="selected_library"><?php echo "$dataset ($projectName)"; ?></span>
	<span id="spinner" style="display: none;"><?php echo $html->image('ajax-loader.gif', array('width'=>'25px')); ?></span>
</h2>
<div id="view-tabs">

<?php

echo("
	<ul>
		<li><a href=\"#view-data-tab\">Data</a></li>");

		$inactiveTabs = array();
		$tabPosition = 1;
		$facetPrefix ='';
		//generate tabs
		foreach($tabs as $tabId => $tab) {
			echo("<li >");
				//print ajax link
				echo $ajax->link("<span>{$tab['name']}</span>",array('action'=>$tab['action'],$dataset,$sessionId,$tabId),
				 array('update' => 'view-facet-panel', 'indicator' => 'spinner','title' => 'view-facet-panel','loading' => 
				 'Element.show(\'spinner\')', 'complete' => 'Element.hide(\'spinner\'); Effect.Appear(\'view-facet-panel\',{ duration: 1.2 })',
				  'before' => 'Element.hide(\'view-facet-panel\')'), null, null, false); 
			echo("</li>");	
			
			if(!$tab['isActive']) {
				array_push($inactiveTabs,$tabPosition);
			}
			$tabPosition ++;
		}

		echo("</ul><div id=\"view-data-tab\">");?>
		
		<?php 
		echo($luceneResultPaginator->addPageInformation($page,$numDocs,NUM_VIEW_RESULTS));
		
		$table= "<table cellpadding=\"0\" cellspacing=\"0\" ><tr>";
		
		//add field header
		foreach($resultFields as $fieldId => $fieldName) {
			$table.= "<th>$fieldName</th>";
		}
		$table.= "</tr>";
					
		$i = 0;
			
		foreach ($documents as $document ) {	
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			$table .= "<tr  $class>";
			
			//print field values
			foreach($resultFields as $fieldId => $fieldName) {
				if(!is_null(ClassRegistry::init("Phylodb.Protein")) && $fieldId === 'peptide_id' && !empty($document->{'com_name_src'})) {	
						
					$table .=  "<td>{$document->{$fieldId}}<BR>".$html->link('[phylodb homolog]', array('plugin' => 'phylodb','controller'=> 'phylodb', 'action'=>'protein',$document->{'com_name_src'}),array('target'=>'_blank')); 
					$table .=  $html->link('[feature]', array('plugin' => null,'controller'=> 'peptides', 'action'=>'drawFeatures',$dataset,42,$document->{$fieldId},'view'),array('target'=>'_blank'));
					
					if(file_exists("".SEQUENCE_STORE_PATH."/42/$dataset/tree/{$document->{$fieldId}}.pdf")) {
						$table .=  $html->link('[tree]', array('plugin' => null,'controller'=> 'peptides', 'action'=>'downloadApisTree',$dataset,42,$document->{$fieldId}),array('target'=>'_blank'))."</td>"; 
					}
				} 
				else {					
					$value = $document->{$fieldId};
					if(is_array($value)) {
						$value = implode('<BR>',$value);
				}
				
				$table .= "<td>".$value."</td>";
				}
			}
			$table .= '</tr>';
		}
		$table .= '</table>';
			
		echo $table;
		echo $luceneResultPaginator->addPagination($page,$numDocs,$dataset,"view",NUM_VIEW_RESULTS,null);	
		echo '</div>';	
	echo '</div>';	
echo '</div>';	
?>

<script type="text/javascript">
jQuery(function() {
	jQuery("#view-tabs").tabs({ spinner: '<img src="/phylo-metarep/img/ajax.gif"/>' });
	jQuery("#view-tabs").tabs( "option", "disabled", <?php echo('['.implode(',',$inactiveTabs).']');?>);
});
</script>	
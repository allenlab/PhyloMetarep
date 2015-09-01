<!----------------------------------------------------------
  
  File: all.ctp
  Description: Search All Page
  
  The Search All Page allows to search dataset as well as annotation information
  accross all datasets. 

  METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
  Copyright(c)  J. Craig Venter Institute (http://www.jcvi.org)

  Licensed under The MIT License
  Redistributions of files must retain the above copyright notice.

  @link http://www.jcvi.org/metarep METAREP Project
  @package metarep
  @version METAREP v 1.3.1
  @author Johannes Goll
  @lastmodified 2010-07-09
  @license http://www.opensource.org/licenses/mit-license.php The MIT License
  
<!---------------------------------------------------------->


<?php echo $html->css('search_all.css'); ?>

<div id="search-all">
<?php 	
	//read session variables
	$query = $session->read('query');
	$searchFields = $session->read('searchFields');
	$searchResults = $session->read('searchResults');
	$field = $session->read('searchField');
	$facets = $session->read('facets');
	$numHits = $session->read('numHits');
	$suggestions = $session->read('suggestions');
		
?>
<ul id="breadcrumb">
 	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
    <li><?php echo $html->link('Search', "/search/all/$query");?></li>
</ul>

<h2><?php __("Search");?><span class="selected_library"></span><span id="spinner" style="display: none;"><?php echo $html->image('ajax-loader.gif', array('width'=>'25px')); ?></span></h2>


	<div class="search-panel">
		<a href="#" id="dialog_link" class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-newwin"></span>Help</a>
		<fieldset>
		<legend> </legend>
		
		<?php echo $form->create('Search', array('url' => array('controller' => 'search', 'action' => 'all'))); ?>
		
		
		<?php 
			if(isset($exception)) {
				$label = "<b><FONT COLOR=\"#990000\">$exception</FONT><b>";
			}
			else if(!is_null($numHits)) {
				$label = "Found <B>".number_format($numHits)." hits</b> in <b>".count($searchResults)." datasets</b> for";
			}
			else {
				$label = "Search in <b>".count($searchResults)."</b> datasets";			
			}
			
			echo('<div class="search-box">');
			echo $form->input("query", array('type'=>'text', 'value'=>$query,'label' =>$label));
			echo('</div>');	
			
			echo $form->input('field',array('options' => $searchFields,'label' => "Select Search Field",'selected' =>$field,'div'=>'search-field-select-option'));
			echo $ajax->submit('Search', array('url'=> array('controller'=>'search', 'action'=>'all'),'update' => 'search-all', 'loading' => 'Element.show(\'spinner\')', 'complete' => 'Element.hide(\'spinner\'); Element.hide(\'search-all-result-panel\');Effect.Appear(\'search-all-result-panel\',{ duration: 1.5})','before' => 'Element.hide(\'search-all-result-panel\')'));
			echo $form->end();
		?>
			
		</fieldset>
	</div>
	<?php if(!empty($suggestions)) { ?>
	<div id="search-suggestions">
			<fieldset>
		<legend>Search Terms (<?php echo count($suggestions)?>)</legend>
		<div id="search-suggestions-panel">
		<?php
			echo('<ul>');
			foreach($suggestions as $suggestion) {
					echo("<li>$suggestion</li>");
			}
			echo('</ul>');
		?>
		</div>
		</fieldset>
	</div>
	<?php }?>	
	<div id="search-all-result-panel">
		<?php  if($numHits != 0):?>	
			<?php echo $html->div('download', $html->link($html->image("download-medium.png",array("title" => "Download Top Ten List")), array('controller'=> 'search','action'=>'downloadMetaInformationFacets'),array('escape' => false)));?>	
				<?php echo $facet->topTenMetaInformationList($facets,$numHits);?>	
				
		 <div class="facet-pie-panel">
			<?php echo $html->div('download', $html->link($html->image("download-medium.png",array("title" => "Download Top Ten List")), array('controller'=>  'search','action'=>'downloadMetaInformationFacets'),array('escape' => false)));?>	
			<?php  echo $facet->topTenMetaInformationPieCharts($facets,$numHits,"700x200");?>
		</div>	
		
		<?php			
		$searchResultPanel = (
		"<div id=\"search-all-result-panel\">
				<fieldset>
					<legend>Search Results</legend>
						<table cellpadding=\"0\" cellspacing=\"0\">	
						<tr>	
							<th>#Hits</th>
							<th>%Hits</th>
							<th>Dataset</th>
							<th>Description</th>					
							<th>Investigator</th>
							<th>Analyse</th>
						</tr>");
			 	
		$i=0;
		foreach ( $searchResults as $hit ) {	
			
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			
			$searchResultPanel .= "<tr  $class>";
			
			$hits = number_format($hit['hits']);
			
			
			$searchResultPanel .= "<td style=\"width:4%;text-align:right\">$hits</td>";
			$searchResultPanel .= "<td style=\"width:4%;text-align:right\">{$hit['perc']}</td>";
			$searchResultPanel .= "<td style=\"width:10%;text-align:left\">{$hit['name']}</td>";
			$searchResultPanel .= "<td style=\"text-align:left\">{$hit['description']}</td>";
			$searchResultPanel .= "<td style=\"width:10%;text-align:center\">{$hit['project']}</td>";	
			
			
			$searchResultPanel .= "<td class=\"actions\" style=\"width:4%;text-align:right\">";
			$searchResultPanel .= "<select onChange=\"goThere(this.options[this.selectedIndex].value)\" name=\"s1\">";				
			$searchResultPanel .= "<option value=\"\" SELECTED>--Select Action--</option>";
			$searchResultPanel .= "<option value=\"/phylo-metarep/view/index/{$hit['name']}\">View</option>
								   <option value=\"/phylo-metarep/search/index/{$hit['name']}\">Search</option>
								   <option value=\"/phylo-metarep/compare/index/{$hit['name']}\">Compare</option>
								   <option value=\"/phylo-metarep/browse/blastTaxonomy/{$hit['name']}\">Browse Taxonomy (Best Hit)</option>
									<option value=\"/phylo-metarep/browse/apisTaxonomy/{$hit['name']}\">Browse Taxonomy (Apis)</option>";
			#TODO add apis option 			
			//			if($hit['apis_database']) {
			//				$searchResultPanel .="<option value=\"/phylo-metarep/browse/apisTaxonomy/{$hit['name']}\">Browse Taxonomy (Apis)</option>";
			//			}							
			$searchResultPanel .=  "<option value=\"/phylo-metarep/browse/keggPathwaysKo/{$hit['name']}\">Browse Kegg Pathways (KO)</option>
									<option value=\"/phylo-metarep/browse/keggPathwaysEc/{$hit['name']}\">Browse Kegg Pathways (EC)</option>
									<option value=\"/phylo-metarep/browse/metacycPathways/{$hit['name']}\">Browse Metacyc Pathways (EC)</option>
									<option value=\"/phylo-metarep/browse/enzymes/{$hit['name']}\">Browse Enzymes</option>
									<option value=\"/phylo-metarep/browse/geneOntology/{$hit['name']}\">Browse Gene Ontology</option>";

						
			$searchResultPanel .="</select></td>";			
			$searchResultPanel .= '</tr>';	
			
		}
		$searchResultPanel .= '</table>';
		echo($searchResultPanel);
		?>
		<?php  endif;?>	
	</div>	
</div>

<?php
echo $ajax->observeField( 'SearchField', 
    array(
        'url' => array('controller'=>'search', 'action'=>'all',$query),
        'frequency' => 0.1,
    	'update' => 'search-all', 'loading' => 'Element.show(\'spinner\')', 'complete' => 'Element.hide(\'spinner\'); Element.hide(\'search-all-result-panel\');Effect.Appear(\'search-all-result-panel\',{ duration: 1.5})','before' => 'Element.hide(\'search-all-result-panel\')',
		'with' => 'Form.serialize(\'SearchAddForm\')'
    ) 
);
?>

<script type="text/javascript">
function goThere(loc) {
	window.location.href=loc;
}
</script>
<script type="text/javascript">
 jQuery.noConflict();
	
	jQuery(function(){			

		// Dialog			
		jQuery('#dialog').dialog({
			autoOpen: false,
			width: 400,
			modal: true,
			buttons: {
				"Ok": function() { 
					jQuery(this).dialog("close"); 
				},
			}
		});
		
		// Dialog Link
		jQuery('#dialog_link').click(function(){
			jQuery('#dialog').dialog('open');
			return false;
		});
});
</script>
<?php echo $dialog->printSearch("dialog","",'all') ?>	
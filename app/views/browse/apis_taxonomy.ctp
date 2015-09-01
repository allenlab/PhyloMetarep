<!----------------------------------------------------------
 
  File: apis_taxonomy.ctp
  Description: View page to browse NCBI taxonomy based on
  taxonomic APIS assignments stored in the apis_tree field.
  (JCVI-only feature)

  PHP versions 4 and 5

  METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
  Copyright(c) J. Craig Venter Institute (http://www.jcvi.org)

  Licensed under The MIT License
  Redistributions of files must retain the above copyright notice.

  @link http://www.jcvi.org/metarep METAREP Project
  @package metarep
  @version METAREP v 1.3.1
  @author Johannes Goll
  @lastmodified 2010-07-09
  @license http://www.opensource.org/licenses/mit-license.php The MIT License
    
<!---------------------------------------------------------->

<?php echo $html->css('browse.css'); 

if($session->check('apisTaxonomy.browse.query')) {
	$filter = $session->read('apisTaxonomy.browse.query');
}
else {
	$filter = '*:*';
}

$facetFields = $session->read("$mode.browse.facetFields");

$selectedNode = $session->read($mode.'.browse.selectedNode');
$filterQuery = ($filter === '*:*' ? "apis_tree:$selectedNode" : "(apis_tree:$selectedNode AND $filter)");
$facetFields = $session->read("$mode.browse.facetFields");

?>
<div id="Browse">
	<ul id="breadcrumb">
	 	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
	    <li><?php echo $html->link('Investigators', "/projects/index");?></li>
	    <li><?php echo $html->link('View Investigator Project', "/projects/view/$projectId");?></li>
	    <li><?php echo $html->link('Browse Dataset', "/browse/apisTaxonomy/$dataset");?></li>
	</ul>

	<h2><?php __("Browse Taxonomy (Apis)");?><span class="selected_library"><?php echo "$dataset ($projectName)"; ?></span>	
	<span id="spinner" style="display: none;">
	 	<?php echo $html->image('ajax-loader.gif', array('width'=>'25px')); ?>
	 </span></h2><BR>
	
	<div id="browse-main-panel">	
		<div id="browse-left-panel">		
			<div id="browse-search-panel">			
				<fieldset>
					<legend>Filter</legend>
					<?php echo $form->create('Filter');?>	
					<a href="#" id="dialog_link" class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-newwin"></span>Help</a>
					<?php echo $form->input("filter", array('type'=>'text', 'value'=>$filter,'label' => false,'div' => 'filter-input-form')); ?>
					<?php echo $ajax->submit('Filter', array('url'=> array('controller'=>'browse', 'action'=>'filter',$dataset,'apisTaxonomy'),'update' => 'Browse', 'loading' => 'Element.show(\'spinner\')', 'complete' => 'Element.hide(\'spinner\'); Effect.Appear(\'browse-main-panel\',{ duration: 1.5 })', 'before' => 'Element.hide(\'browse-main-panel\');'));?>
					<?php echo $form->end();?>
				</fieldset>
			</div>		
	
			<div id="browse-tree-panel">			
				<fieldset>
				<legend>Browse Taxonomy</legend>		
					<?php 			
					if($numHits == 0) {
						echo("<div id=\"flashMessage\" class=\"message\" style=\"text-align:center\">No hits found. Please try again with a different filter query.</div>");
						
					} 
					else {
						$treeData = $session->read($mode.'.browse.tree');
						echo $tree->taxonomy($dataset,$treeData,$taxon,'apisTaxonomy');
					}
					?>
				</fieldset>
			</div>
		</div>
			<?php if($numHits > 0) :?>
			<div id="browse-right-panel">
				<div id="browse-classification-panel">	
					<fieldset>
					<legend>Taxonomic Distribution</legend>
					<?php echo $html->div('browse-download-classification', $html->link($html->image("download-medium.png"), array('controller'=> 'browse','action'=>'downloadChildCounts',$dataset,$taxon,$mode,array_sum($childCounts),urlencode($filter)),array('escape' => false)));?>						
					<h2><span class="selected_library"><?php echo($taxon)?></h2>
					<?php 					
					echo $facet->pieChart('',$childCounts,$numHits,"700x300");
					?>
					</fieldset>
				</div>
				<div id="browse-facet-list-panel">
					<?php echo $html->div('browse-download-facets', $html->link($html->image("download-medium.png"), array('controller'=> 'browse','action'=>'dowloadFacets',$dataset,$taxon,$mode,$numHits,urlencode($filter)),array('escape' => false)));?>	
					<?php echo $facet->topTenList($facets,$facetFields,$numHits,$dataset,$filterQuery);?>	
				</div>
				<div id="browse-facet-pie-panel">
					<?php echo $html->div('browse-download-facets', $html->link($html->image("download-medium.png"), array('controller'=> 'browse','action'=>'dowloadFacets',$dataset,$taxon,$mode,$numHits,urlencode($filter)),array('escape' => false)));?>	
					<?php  echo $facet->topTenPieCharts($facets,$facetFields,$numHits,"700x200","300x150");?>
				</div>
			</div>
			<?php endif;?>
</div>

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

<?php $dialog->browseTaxonomy('dialog')?>	

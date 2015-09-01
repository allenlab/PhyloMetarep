<!----------------------------------------------------------
  
  File: index.ctp
  Description: Project Index Page
  
  The Project Index Page lists all projects.
  
  PHP versions 4 and 5

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
<?php echo $html->css('search_dataset.css');?>
<style type="text/css">
	td a:link {text-decoration:underline;} 
	td a:visited {text-decoration:underline;}
	p {word-wrap:break-word;} 
	.inner-panel {padding-left:0.0%;width:100%;} 
	.key-td {width:10%;text-align:left;border-right:none !important;font-weight:bold;}
	.value-td{width:50%;text-align:left;border-right:none !important;}
	.main-header{border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px; width:70%;}
	.sub-header{padding:0px; width:100%; border:0px; !important;font-size:0.9em !important;}
	.seq-header{border:1px; padding-bottom:5px; border-bottom-style:dashed;border-width:0.9px; width:70%;}
	.pagination{padding-left:0.5%;width:100%;} 
}
</style>

<div id="search-dataset">
	<ul id="breadcrumb">
		<php $webRootParts = explode('/',METAREP_WEB_ROOT); $webRootDir = $webRootParts[sizeof($webRootParts)-1];?>
  		<li><a href="/metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
  		<li><?php echo $html->link('PhyloDB Search Page', "/phylodb/search/$field/$query");?></li>
	</ul>

	<h2><?php __("PhyloDB");?><span class="selected_library">Search</span><span id="spinner" style="display: none;"><?php echo $html->image('ajax-loader.gif', array('width'=>'25px')); ?></span></h2>
	
	<div class="search-panel" style="width:900px;">
		<a href="#" id="dialog_link" class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-newwin"></span>Help</a>
		<fieldset style="width:885px;">
		<legend> </legend>
		
		<?php echo $form->create('Search', array('url' => array('plugin'=>'phylodb','controller' => 'phylodb', 'action' => 'search',$field,$query))); ?>
		
		
		<?php 
			echo('<div class="search-box">');
			
			if(!isset($exception)) {
				$label = "Found <B>$numHits hits </b> in <b>PhyloDB 1.04</b> for";
			}
			else {
				$label = "<b><FONT COLOR=\"#990000\">$exception</FONT><b>";
			}
			
			echo $form->input("query", array('type'=>'text', 'value'=>$query,'label' => $label));
			echo('</div>');	
		
			echo $form->input('field',array('options' => $searchFields,'label' => "Select Search Field",'selected' =>$field,'div'=>'search-field-select-option'));
			echo $ajax->submit('Search', array('url'=> array('plugin'=>'phylodb','controller' => 'phylodb', 'action' => 'search',$field,$query),'update' => 'search-dataset', 'loading' => 'Element.show(\'spinner\')', 'complete' => 'Element.hide(\'spinner\'); Element.hide(\'search-results\');Effect.Appear(\'search-results\',{ duration: 1.5})','before' => 'Element.hide(\'search-results\')'));
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
	
	
 	<div id="search-results" style="padding-top:20px;">
	
 	
		<?php if($numHits != 0) { ?>
	<fieldset style="width:885px;">
			
			<div class="inner-panel">
			 	<div class="pagination">
 		<?php echo($phylodbPaginator->addPageInformation($page,$numHits,$limit));?>		
	</div>
			<table class="sub-header">
			
				<tr>
					<th width=16%>Protein ID</th><th width=20%>Description</th><th width=20%>Seguid</th><th width=20%>Organism</th><th width=24%>Lineage</th>				
				</tr>		
				
				<?php $i =1; foreach ($proteins as $protein):		
					$class = null;
		
					if ($i++ % 2 == 0) {
						$class = ' class="altrow"';
					}
				?>
				<tr<?php echo $class;?>>
					<td><?php echo($html->link($protein['Protein']['name'],
								array('plugin' =>'phylodb','controller' =>'phylodb','action'=>'protein',$protein['Protein']['proteinIdUrlString'],$field,$query,$page)));
						?>
					</td>
					<td><?php echo($protein['Protein']['annotation']);?></td>
					<td style ="text-align:center;"><?php echo($html->link($protein['Protein']['seguid'],
								array('plugin' =>'phylodb','controller' =>'phylodb','action'=>'seguid',$protein['Protein']['seguidUrlString'],$field,$query,$page)));
								$seguidCount = $protein['Protein']['seguidCount'];
								if($seguidCount ==1 ) {
									echo("<BR>(Contains {$protein['Protein']['seguidCount']} protein)");
								}
								else{
									echo("<BR>(Contains {$protein['Protein']['seguidCount']} proteins at 100% PID)");
								}
						?>
					</td>
					<td><?php echo("{$protein['Contig']['species']} (Taxon ID: ". 
						$html->link($protein['Contig']['taxon_id'],
							"http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id={$protein['Contig']['taxon_id']}",
						array('class'=>'ext', 'target'=>'_blank')).",Contig: {$protein['Contig']['name']})");
						?>
					</td>
					<td><?php echo("{$protein['Contig']['taxonomy']}");?>
					</td>
				</tr>
				<?php endforeach; ?>	
			</table>
			</div>
				<div class="pagination">
				<?php echo($phylodbPaginator->addPagination($field,$queryUrlString,$page,$numHits,$limit));?>			
			</div>
			</fieldset>
		<?php }?>
	</div>
	
</div>
<?php
echo $ajax->observeField( 'SearchField', 
    array(
        'url' => array('plugin' =>'phylodb','controller' =>'phylodb','action'=>'search',$field,$query),
        'frequency' => 0.1,
    	'update' => 'search-dataset', 'loading' => 'Element.show(\'spinner\')', 'complete' => 'Element.hide(\'spinner\'); Element.hide(\'search-results\');Effect.Appear(\'search-results\',{ duration: 1.5})','before' => 'Element.hide(\'search-results\')',
		'with' => 'Form.serialize(\'SearchAddForm\')'
    ) 
);
?>

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
<?php echo $phylodbDialog->printSearch("dialog") ?>	




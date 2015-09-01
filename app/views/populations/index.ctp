<!----------------------------------------------------------
  
  File: index.ctp
  Description: Index Population Page. Lists all populations.
  
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

<ul id="breadcrumb">
  	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
    <li><?php echo $html->link('List Populations', "/populations/index");?></li>
</ul>

<style type="text/css">
	select {
		height: 20px;
		width: 150px;
		font-size:0.9em;
	}
</style>


<div class="libraries index">
<h2><?php __('Populations'); ?></h2>
<p>
<?php

echo $paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0" style="width:82em;">
<tr>
	<th><?php echo $paginator->sort('updated');?></th>
	<th class="actions"><?php __('#Entries');?></th>
	<th><?php echo $paginator->sort('name');?></th>
	<th><?php echo $paginator->sort('description');?></th>
	<th><?php echo $paginator->sort('project_id');?></th>
	<th class="actions"><?php __('Action');?></th>
	<th class="actions"><?php __('Analyze');?></th>
</tr>
<?php
$i = 0;
foreach ($population as $population):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td style="width:5%;text-align:center">
			<?php echo $population['Population']['updated']; ?>		
		</td>
		<td style="width:4%;text-align:right">
			<?php  echo(number_format($this->requestAction("/search/count/".$population['Population']['name']))); ?>
		</td>		
		<td style="width:10%;text-align:left">
			<?php echo $population['Population']['name']; ?>
		</td>
		<td style="width:20%;text-align:left">
			<?php echo $population['Population']['description']; ?>
		</td>		
		<td style="width:15%;text-align:left">
			<?php echo $population['Project']['name']; ?>
		</td>
		<td class="actions" style="width:4%;text-align:left">
			<?php echo $html->link(__('View', true), array('action'=>'view', $population['Population']['id'])); ?>
		</td>		
		<td class="actions" style="width:4%;text-align:center">
	
			<?php 	echo("<select onChange=\"goThere(this.options[this.selectedIndex].value)\" name=\"s1\">
					<option value=\"\" SELECTED>--Select Action--</option>
					<option value=\"/phylo-metarep/view/index/{$population['Population']['name']}\">View</option>
					<option value=\"/phylo-metarep/search/index/{$population['Population']['name']}\">Search</option>
					<option value=\"/phylo-metarep/compare/index/{$population['Population']['name']}\">Compare</option>
					<option value=\"/phylo-metarep/browse/blastTaxonomy/{$population['Population']['name']}\">Browse Taxonomy (PhyloDB)</option>");
					if($population['Population']['has_apis']) {
						echo("<option value=\"/phylo-metarep/browse/apisTaxonomy/{$population['Population']['name']}\">Browse Taxonomy (Apis)</option>");
					}
					echo("	
					<option value=\"/phylo-metarep/browse/keggPathways/{$population['Population']['name']}\">Browse Kegg Pathways</option>
					<option value=\"/phylo-metarep/browse/metacycPathways/{$population['Population']['name']}\">Browse Metacyc Pathways</option>
					<option value=\"/phylo-metarep/browse/enzymes/{$population['Population']['name']}\">Browse Enzymes</option>
					<option value=\"/phylo-metarep/browse/geneOntology/{$population['Population']['name']}\">Browse Gene Ontology</option>
					</select>");?>	
			</td>				
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__('next', true).' >>', array(), null, array('class'=>'disabled'));?>
</div>

<script type="text/javascript">
function goThere(loc) {
	window.location.href=loc;
}
</script>
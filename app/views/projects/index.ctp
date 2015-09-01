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

<ul id="breadcrumb">
  	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
    <li><?php echo $html->link('Investigators', "/projects/index");?></li>
</ul>

<div class="projects index">
<h2><?php __('Investigators');?></h2>
<p>
<?php
echo $paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0" style="width:82em;">
<tr>
	<th><?php echo $paginator->sort('id');?></th>
	<th><?php echo $paginator->sort('updated');?></th>
	<th><?php echo $paginator->sort('name');?></th>
	<th class="actions"><?php __('Action');?></th>	
	<th>#Transcriptomes</th>
	<th>#Populations</th>
	
	<?php 
	
	
	$currentUser 	= Authsome::get();
	$currentUserId 	= $currentUser['User']['id'];	    	        	
	$userGroup  	= $currentUser['UserGroup']['name'];	
	?>	
</tr>

<?php

	
$i = 0;
foreach ($projects as $project):
	$class = null;
	
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td style="width:3%;text-align:right">
			<?php echo $project['Project']['id'];?>
		</td>
		<td style="width:6%;text-align:center">
			<?php echo $project['Project']['updated']; ?>
		</td>		
		<td>
			<?php echo  $project['Project']['name']; ?>
		</td>
		<td class="actions">
			<?php echo $html->link(__('View', true), array('action'=>'view', $project['Project']['id'])); ?>
		</td>
		<td style="width:8%;text-align:right">
			<?php echo  count($project['Library']); ?>
		</td>		
		<td style="width:8%;text-align:right">
			<?php echo  count($project['Population']); ?>
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


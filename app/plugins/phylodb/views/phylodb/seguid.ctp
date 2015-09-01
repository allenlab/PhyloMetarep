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

<style type="text/css">
	td a:link {text-decoration:underline;} 
	td a:visited {text-decoration:underline;}
	p {word-wrap:break-word;} 
	.inner-panel {padding-left:1%;} 
	.key-td {width:10%;text-align:left;border-right:none !important;font-weight:bold;}
	.value-td{width:50%;text-align:left;border-right:none !important;}
	.main-header{border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px; width:60%;}
	.sub-header{padding:0px; width:60%; border:0px; !important;font-size:0.9em !important;}
	.seq-header{border:1px; padding-bottom:5px; border-bottom-style:dashed;border-width:0.9px; width:60%;}
}
</style>

<ul id="breadcrumb">
 	<php $webRootParts = explode('/',METAREP_WEB_ROOT); $webRootDir = $webRootParts[sizeof($webRootParts)-1];?>
  	<li><a href="/metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
  	<?php if($query) { echo "<li>".$html->link('PhyloDB Search Results', "/phylodb/search/$field/$query/$page");}?></li>
    <li><?php echo $html->link('PhyloDB Seguid Page', "/phylodb/seguid/$seguid/$field/$query/$page");?></li>
</ul>

<div class="phylodb">
	<h2><?php __("PhyloDB");?><span class="selected_library"><?php echo($seguid);?></span></h2>
	<table class="main-header">
		<tr>
			<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:1.1em;background-color:#FFFFFF;">
			<?php 
				$seguidCount = $proteins[0]['Protein']['seguidCount'];
				if($seguidCount ==1 ) {
					echo("100% Sequence Identity Cluster Contains $seguidCount Protein");
				}
				else{
					echo("100% Sequence Identity Cluster Contains $seguidCount Proteins");
				}			
			?>
			</th>
		</tr>	
		</table>	
		<div class="inner-panel">
		<table class="sub-header">
				<tr>
					<th width=16%>Protein ID</th><th width=20%>Description</th><th width=30%>Organism</th><th width=34%>Lineage</th>				
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
</div>


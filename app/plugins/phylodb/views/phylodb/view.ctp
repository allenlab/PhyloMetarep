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
  	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
    <li><?php echo $html->link('PhyloDB Protein Page', "/phylodb/view/{$phylodb['Protein']['name']}");?></li>
</ul>

<div class="phylodb">
	<h2><?php __("PhyloDB");?><span class="selected_library"><?php echo($phylodb['Protein']['name']);?></span></h2>
	<BR>	
	<table class="main-header">
		<tr>
			<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:1.1em;background-color:#FFFFFF;">
			Protein Information
			</th>
		</tr>
		
		</table>	
		<div class="inner-panel">
		<table class="sub-header">
			<tr>
				<td class="key-td">Description</td>
				<td class="value-td"><?php echo($phylodb['Protein']['annotation']);?></td>
			</tr>
			<tr class="altrow">
				<td class="key-td">Protein ID</td>
				<td class="value-td"><?php echo($phylodb['Protein']['name']);?></td>
			</tr>
			<tr>
				<td class="key-td">Seguid ID</td>
				<td class="value-td"><?php echo($phylodb['Protein']['seguid']);?></td>
			</tr >
			<tr class="altrow">
				<td class="key-td">Species</td>
				<td class="value-td">
					<?php echo("{$phylodb['Contig']['species']} (NCBI Taxon ID: ". 
					$html->link($phylodb['Contig']['taxon_id'],
						"http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id={$phylodb['Contig']['taxon_id']}",array('class'=>'ext', 'target'=>'_blank')).")");
					?>
				</td>
			</tr>					
			<tr>
				<td class="key-td">Taxonomy</td>
				<td class="value-td"><?php echo($phylodb['Contig']['taxonomy']);?></td>
			</tr >							
			</table>	
		<table class="seq-header">
			<tr>
				<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:0.9em;background-color:#FFFFFF;">
				Protein Sequence
				</th>
			</tr>
		</table>				
		<p style="width:60%;font-size:0.8em"><?php echo($phylodb['Protein']['seq']);?></p>
	</div>
<BR>	
	<table class="main-header">
		<tr>
			<th style="padding-right:5px; width:29%; text-align:left;border-width:0px;font-size:1.1em;background-color:#FFFFFF;">
			Contig Information
			</th>
		</tr>
	</table>	
	<div class="inner-panel">
		<table class="sub-header">
			<tr>
				<td class="key-td">Contig Name</td>
				<td class="value-td"><?php echo($phylodb['Contig']['name']);?></td>
			</tr>
			<tr class="altrow">
				<td class="key-td">Contig Description</td>
				<td class="value-td"><?php echo($phylodb['Contig']['description']);?></td>
			</tr>				
			<tr>
				<td class="key-td">Form</td>
				<td class="value-td"><?php echo($phylodb['Contig']['form']);?></td>
			</tr>				
			<tr class="altrow">
				<td class="key-td">Location</td>
				<td class="value-td"><?php echo("{$phylodb['Protein']['contig_name']}:{$phylodb['GeneOrder']['start']}-{$phylodb['GeneOrder']['stop']}");?></td>
			</tr>			
			<tr>
				<td class="key-td">Strand</td>
				<td class="value-td"><?php echo($phylodb['GeneOrder']['strand']);?></td>
			</tr>					
		</table>
		<table class="seq-header">
			<tr>
				<th style="padding-right:5px; width:29%; text-align:left;border-width:0px;font-size:0.9em;background-color:#FFFFFF;">
				Transcript Sequence
				</th>
			</tr>
		</table>		
		<p style="width:60%;font-size:0.8em"><?php echo(strtoupper($phylodb['Transcript']['seq']));?></p>
</div>
<?php if(array_key_exists('KeggAnnotation',$phylodb)) :?>
	<BR>	
	<table class="main-header">
		<tr>
			<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:1.1em;background-color:#FFFFFF;">
				KEGG Annotation
			</th>
		</tr>
	</table>
	<div class="inner-panel">					
		<table class="sub-header">
			<tr>
				<td class="key-td">Ortholog</td>
				<td class="value-td">
				<?php 
					foreach($phylodb['KeggAnnotation']['KO'] as $koId=>$koName) {
						echo($html->link($koId,"http://www.genome.jp/dbget-bin/www_bget?$koId",array('class'=>'ext', 'target'=>'_blank'))." ($koName); ");
					}
				?>
				</td>		
			</tr>
			<tr class="altrow">
				<td class="key-td">Pathway</td>
				<td class="value-td">
					<?php 
					foreach($phylodb['KeggAnnotation']['Pathway'] as $pathway) {
						echo($html->link($pathway,"http://www.kegg.jp/dbget-bin/www_bget?$pathway",array('class'=>'ext', 'target'=>'_blank'))." ");
					}
					?>
				</td>			
			</tr>				
			<tr>
				<td class="key-td">Enzyme Commission ID</td>
				<td class="value-td">
					<?php 
					foreach($phylodb['KeggAnnotation']['EC'] as $ecId => $ecName ) {
						echo($html->link($ecId,"http://www.kegg.jp/dbget-bin/www_bget?$ecId",array('class'=>'ext', 'target'=>'_blank'))." ($ecName); ");
					}
					?>
				</td>	
			</tr>					
			<tr class="altrow">
				<td class="key-td">UniProtKB ID</td>
				<td class="value-td">
					<?php 
					echo($html->link($phylodb['KeggAnnotation']['UniprotID'],
						"http://www.uniprot.org/uniprot/{$phylodb['KeggAnnotation']['UniprotID']}",array('class'=>'ext', 'target'=>'_blank')));
					?>
				</td>
			</tr>
		</tr>
		</table>
<?php endif ;?>
	</div>
	<BR>	
	<table class="main-header">
		<tr>
			<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:1.1em;background-color:#FFFFFF;">
				PhyloDB Annotation
			</th>
		</tr>
	</table>
	<div class="inner-panel">										
		<table class="sub-header">		
			<tr>
				<td class="key-td">NCBI ID</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['ncbi_id']);?></td>
			</tr>
			<tr class="altrow">
				<td class="key-td">JGI ID</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['jgi_id']);?></td>
			</tr>				
			<tr>
				<td class="key-td">UniProtKB ID (Full)</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['uniprot_id']);?></td>
			</tr>					
			<tr class="altrow">
				<td class="key-td">EggNog ID</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['eggnog_id']);?></td>
			</tr>
			<tr >
				<td class="key-td">GOS Extended Cluster ID</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['gos_cluster_id']);?></td>
			</tr>
			<tr class="altrow">
				<td class="key-td">GOS Core Cluster ID</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['gos_core_cluster_id']);?></td>
			</tr>
			<tr >
				<td class="key-td">Pfam</td>
				<td class="value-td">
					<?php 
					foreach($phylodb['PhylodbAnnotation']['pfam'] as $pfamAcc=>$pfamName) {
						echo($html->link($pfamAcc,"http://pfam.sanger.ac.uk/family/$pfamAcc",array('class'=>'ext', 'target'=>'_blank'))." ($pfamName); ");
					}
					?>
				</td>
			</tr>	
			<tr class="altrow">
				<td class="key-td">Tigrfam</td>
				<td class="value-td">
					<?php 
					foreach($phylodb['PhylodbAnnotation']['tigrfam'] as $tigrfamAcc =>$tigrfamName) {
						echo($html->link($tigrfamAcc,"http://cmr.jcvi.org/tigr-scripts/CMR/HmmReport.cgi?hmm_acc=$tigrfamAcc",array('class'=>'ext', 'target'=>'_blank'))." ($tigrfamName); ");
					}
					?>
				</td>
			</tr>	
			<tr >
				<td class="key-td">Enzyme Commission ID</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['ec']);?></td>
			</tr>		
			<tr class="altrow">
				<td class="key-td">Gene Ontology ID</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['go']);?></td>
			</tr>	
			<tr >
				<td class="key-td">TIGR Role</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['tigrrole']);?></td>
			</tr>		
			<tr class="altrow">
				<td class="key-td">Pathway</td>
				<td class="value-td"><?php echo($phylodb['PhylodbAnnotation']['pathway']);?></td>
			</tr>	
		</tr>
		</table>
	</div>
</div>


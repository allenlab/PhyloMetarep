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

<?php echo $html->css('user-dashboard.css'); ?>
<?php echo $html->css('jquery-ui-1.7.2.custom.css');?>


<ul id="breadcrumb">
  	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
    <li><?php echo $html->link('Investigators', "/projects/index");?></li>
</ul>

<div class="projects index">
<h2><?php __("PhyloDB");?><span class="selected_library"><?php echo($phylodb['Protein']['name']);?></span>	
<BR>
<BR>


<div class="demo">

<div id="accordion" style="width:60% ;font-size:0.8em !important;";>
	<h3><a href="#">Entry</a></h3>
	<div>
		<p>
			
		</p>
	</div>
	<h3><a href="#">Transcript</a></h3>
	<div>
		<p>
			<table cellpadding="10" cellspacing="10" style="width:60%; border:0px; !important;font-size:0.7em !important; ">
				<tr>
					<td style="width:5%;text-align:left;border-right:none !important;">Contig Name</td>
					<td style="width:40%;text-align:left;border-right:none !important;"><?php echo($phylodb['Transcript']['name']);?></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:left;border-right:none !important;">Sequence</td>
					<td style="width:40%;text-align:left;border-right:none !important;word-wrap: break-word"><?php echo($phylodb['Transcript']['seq']);?></td>
				</tr>
			</table>
		</p>
	</div>
	<h3><a href="#">KEGG Annotation</a></h3>
	<div>
		<p>
			<table cellpadding="10" cellspacing="10" style="width:60%; border:0px; !important;font-size:0.7em !important; ">
				<tr>
					<td style="width:5%;text-align:left;border-right:none !important;">Ortholog</td>
					<td style="width:40%;text-align:left;border-right:none !important;"><?php echo($phylodb['KeggAnnotation']['KO']);?></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:left;border-right:none !important;">Pathway</td>
					<td style="width:40%;text-align:left;border-right:none !important;"><?php echo($phylodb['KeggAnnotation']['Pathway']);?></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:left;border-right:none !important;">Enzyme Accession</td>
					<td style="width:40%;text-align:left;border-right:none !important;"><?php echo($phylodb['KeggAnnotation']['EC']);?></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:left;border-right:none !important;">UniProtKB ID</td>
					<td style="width:40%;text-align:left;border-right:none !important;"><?php echo($phylodb['KeggAnnotation']['UniprotID']);?></td>
				</tr>		
			</table>
		</p>
	</div>
</div>

</div><!-- End demo -->


<table style="border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px; width:60%;">
	<tr>
		<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:0.9em;background-color:#FFFFFF;">
		Protein Information
		</th>
	</tr>
<table cellpadding="70" cellspacing="0" style="width:80%; border:0px; !important;font-size:0.9em !important;">
				<tr>
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Description</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Protein']['annotation']);?></td>
				</tr>
				<tr class="altrow">
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Protein ID</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Protein']['name']);?></td>
				</tr>
				<tr>
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Seguid ID</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Protein']['seguid']);?></td>
				</tr >
				<tr >
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Species</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Contig']['species']);?></td>
				</tr>
				<tr class="altrow" >
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Sequence</td>
					<td style="text-align:left;border-right:none !important;white-space:nowrap;max-width:70%;overflow: hidden;"><?php echo($phylodb['Contig']['seq']);?></td>
				</tr>		
				<tr>
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Contig Name</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Contig']['name']);?></td>
				</tr>
				<tr class="altrow">
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Contig Description</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Contig']['description']);?></td>
				</tr>				
				<tr>
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Form</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Contig']['form']);?></td>
				</tr>				
				<tr class="altrow">
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Location</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo("{$phylodb['Protein']['contig_name']}:{$phylodb['GeneOrder']['start']}-{$phylodb['GeneOrder']['stop']}");?></td>
				</tr>			
				<tr>
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Strand</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['GeneOrder']['strand']);?></td>
				</tr>					
				<tr class="altrow">
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Species</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Contig']['species']);?></td>
				</tr>
				<tr>
					<td style="width:10%;text-align:left;border-right:none !important;font-weight:bold">Taxonomy</td>
					<td style="width:70%;text-align:left;border-right:none !important;"><?php echo($phylodb['Contig']['taxonomy']);?></td>
				</tr >
		
		</table>		

<table style="border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px; width:60%;">
	<tr>
		<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:0.9em;background-color:#FFFFFF;">
		Contig Information
		</th>
	</tr>
</table>				
<table cellpadding="10" cellspacing="10" style="width:60%; border:0px; !important;font-size:0.7em;">
<tr>
<th style="width:5%;text-align:left"></th>
<th style="width:40%;text-align:right"></th>
<tr>
	<td style="width:5%;text-align:left">Name</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['Protein']['annotation']);?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Protein ID</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['Protein']['name']);?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Seguid ID</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['Protein']['seguid']);?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Location</td>
	<td style="width:40%;text-align:left"><?php echo("{$phylodb['Protein']['contig_name']}:{$phylodb['GeneOrder']['start']}-{$phylodb['GeneOrder']['stop']}");?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Strain</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['GeneOrder']['strand']);?></td>
</tr>
</table>

<table style="border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px; width:60%;">
	<tr>
		<th style="padding-right:5px; width:30%; text-align:left;border-width:0px;font-size:0.9em;background-color:#FFFFFF;">
			KEGG Annotation
		</th>
	</tr>
</table>
					
<table cellpadding="10" cellspacing="10" style="width:60%; border:0px; !important;font-size:0.7em;">
<tr>
<th style="width:5%;text-align:left"></th>
<th style="width:40%;text-align:right"></th>
<tr>
	<td style="width:5%;text-align:left">Name</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['Protein']['annotation']);?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Protein ID</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['Protein']['name']);?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Seguid ID</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['Protein']['seguid']);?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Location</td>
	<td style="width:40%;text-align:left"><?php echo("{$phylodb['Protein']['contig_name']}:{$phylodb['GeneOrder']['start']}-{$phylodb['GeneOrder']['stop']}");?></td>
</tr>
<tr>
	<td style="width:5%;text-align:left">Strain</td>
	<td style="width:40%;text-align:left"><?php echo($phylodb['GeneOrder']['strand']);?></td>
</tr>
</table>
</div>

<script type="text/javascript">
	jQuery(function() {
		jQuery("#accordion").accordion({fillSpace: true,collapsible: true, fillSpace:true
			
			});
		});
</script>
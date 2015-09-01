<?php
/***********************************************************
* File: dialog.php
* Description: The Dialog Helper class defines methods
* to print help dialog messages.
*
* PHP versions 4 and 5
*
* METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
* Copyright(c)  J. Craig Venter Institute (http://www.jcvi.org)
*
* Licensed under The MIT License
* Redistributions of files must retain the above copyright notice.
*
* @link http://www.jcvi.org/metarep METAREP Project
* @package metarep
* @version METAREP v 1.3.1
* @author Johannes Goll
* @lastmodified 2010-07-09
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
**/

class PhylodbDialogHelper extends AppHelper {

	var $helpers = array('Html');	

	function printSearch($divId) {
		echo("<div id=\"$divId\" title=\"PhyloDB Search Help Dialog\" style=\"font-size:1em; width=%40;\">	
		<p><p>Enter a search term into the text box and select the field to search in. There are two basic flavors: (1) you can search for proteins that have exact-matching IDs or (2) search for proteins that have annotation categories that partially-match your input using name-based searches. <BR>The following fields are supported:
	<BR><BR>
	</p>
		<p>
		<table>
		<tr><td valign=\"top\">
		<ul>
		<h5>ID-based Search Fields</h5>
		<BR>
			<li><strong>Protein ID</strong> The unique identifier for every PhyloDB protein.</li>
			<li><strong>Seguid ID</strong> Proteins with 100% identical sequences have the same seguids. Enter seguid to retrieve a family of identical proteins.</li>	 
			<li><strong>Contig ID</strong> The unique name of the contig that the protein belongs to.</li>
			<li><strong>NCBI Taxonomy (PhyloDB) ID</strong> PhyloDB specific taxonomy IDs with positive and negative integers (postive integers encode NCBI taxa e.g. 83333 for E. coli k12 while negative integers encode PhyloDB specific taxa).</li>			
			<li><strong>UniProt KB ID</strong> KEGG based UniProt Knowledge Base IDs, e.g. A8GNT8</li>
			<li><strong>JGI ID</strong>	Joint Genome Institute IDs, e.g. 644476301</li>
			<li><strong>NCBI ID</strong> NCBI ID, e.g. gi|229181821|ref|ZP_04309129.1</li>
			<li><strong>Eznyme Commission (EC) ID</strong> 	Unique enzyme commissions e.g. 4.2.1.24</li>
			<li><strong>KEGG Ortholog (KO) ID</strong> KEGG based orthology, e.g. K09796v
			<li><strong>KEGG Pathway ID</strong> Organism specific pathways, e.g. rak00550 (Peptidoglycan biosynthesis - Rickettsia akari)
			<li><strong>Pfam ID</strong> based on PhyloDB denovo Pfam searches, e.g. PF02687	</li>
			<li><strong>Tigrfam ID</strong> based on PhyloDB denovo Tigrfam searches, e.g. TIGR01364</li>
			<li><strong>Eggnog ID</strong> Eggnog Cluster ID</li>			
			<li><strong>GOS Extended Cluster ID</strong> e.g. CAM_CL_13247374</li>			
			<li><strong>GOS Core Cluster ID</strong> e.g. CAM_CRCL_6247374</li>			
		</ul>
		</td><td valign=\"top\">
		<h5>Name-based Search Fields</h5>
		<BR>
			<li><strong>NCBI Taxonomy (PhyloDB) Name</strong> Search by species name.</li>
			<li><strong>GOS Extended Cluster Name</strong> Search by cluster name.</i></li>
			<li><strong>Pfam Name</strong> Search by Pfam name.</li>
			<li><strong>Tigrfam Name</strong> Search by Tigrfam name.</li>
		</ul>
		</td><td valign=\"top\">
		</div>");
	}	
}
?>




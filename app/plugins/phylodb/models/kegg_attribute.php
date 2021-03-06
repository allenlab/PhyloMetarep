<?php
/***********************************************************
* File: protein.php
* Description: Model to store contig information fetched from
* the PhyloDB database.
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

class KeggAttribute extends PhylodbAppModel {	
	var $useDbConfig = 'phylodb';
	var $primaryKey = 'id';
	var $useTable = 'kegg_attributes';
	
 	var $belongsTo = array(
	 	'Protein' => array(
	 		'className' => 'Phylodb.Protein',
	 		'foreignKey' => 'protein_id',
	 		'dependent' => false,
 		),					
 	); 
}
?>
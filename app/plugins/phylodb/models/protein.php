<?php
/***********************************************************
* File: protein.php
* Description: Model to store protein information fetched from
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

class Protein extends PhylodbAppModel {	
	var $useDbConfig = 'phylodb';
	var $primaryKey = 'name';
	var $useTable = 'proteins';
	
	
 	var $hasOne = array(
	 	'Transcript' => array(
	 		'className' => 'Phylodb.Transcript',
	 		'foreignKey' => 'name',
	 		'dependent' => false,
 		),
	 	'KeggAnnotation' => array(
	 		'className' => 'Phylodb.KeggAnnotation',
	 		'foreignKey' => 'seq_id',
	 		'dependent' => false,
 		), 	
 		'GeneOrder' => array(
	 		'className' => 'Phylodb.GeneOrder',
	 		'foreignKey' => 'protein_name',
	 		'dependent' => false,
 		),
		'KeggAttribute' => array(
	 		'className' => 'Phylodb.KeggAttribute',
	 		'foreignKey' => 'protein_id',
	 		'dependent' => false,
  		), 		
		'PhylodbAttribute' => array(
	 		'className' => 'Phylodb.PhylodbAttribute',
	 		'foreignKey' => 'protein_id',
	 		'dependent' => false,
  		), 		
 	);
 	
 	var $belongsTo = array(
	 	'Contig' => array(
	 		'className' => 'Phylodb.Contig',
	 		'foreignKey' => 'contig_name',
	 		'dependent' => false,
 			'type' => 'INNER',
 		),
 		'Taxonomy' => array(
	 		'className' => 'Phylodb.Taxonomy',
	 		'foreignKey' => 'taxon_id',
	 		'dependent' => false,
 			'type' => 'INNER',
 		) , 
 		'PhylodbAnnotation' => array(
	 		'className' => 'Phylodb.PhylodbAnnotation',
	 		'foreignKey' => 'seguid',
	 		'dependent' => false,
 			'type' => 'INNER',
 		), 	 	 						
 	); 

	public function findByAttribute($table,$field,$query,$proteinFields,$start,$stop) {
		$result = array();
		
		$this->contain($table,'Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form');
		
		$result['numHits'] = $this->find('count',array('fields' => 'COUNT(DISTINCT protein_id) as count','conditions' => array("$table.key"=>$field,"$table.value"=>$query)));
		
		$result['proteins'] = $this->find('all',array('contain'=>array($table,'Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),
										 'conditions' =>array("$table.key"=>$field,"$table.value"=>$query),
										 'fields'=>$proteinFields,
										 'order' => array('Protein.name'),
										 'limit' => $start.','.$stop));	
		return $result;			
	}
	
	public function findByAttributes($table,$field,$queries,$proteinFields,$start,$stop) {
		$result = array();
	
		$this->contain($table,'Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form');
		
		$result['numHits']  = $this->find('count',array('fields' => 'COUNT(DISTINCT protein_id) as count','conditions' =>array("$table.key"=>$field,"$table.value"=>$queries)));
		
		$result['proteins'] = $this->find('all',array('contain'=>array($table,'Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),
										 'conditions' =>array("$table.key"=>$field,"$table.value"=>$queries),
										 'fields'=>$proteinFields,
										 'order' => array('Protein.name'),
										 'limit' => $start.','.$stop));	
		return $result;			
	} 	
}
?>
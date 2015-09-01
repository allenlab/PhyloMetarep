<?php
/***********************************************************
* File: user.php
* Description: Application Model - parent class of all phylodb model classes
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
* @lastmodified 2010-08-26
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
**/

class PhylodbAppModel extends AppModel {
	
	var $phylodb = array(
		'driver' => 'mysqli',
		'persistent' => true,
		'host' => 'localhost',
		'database' => 'phylodb',	
		'login' => '<your-login>',
		'password' => '<your-password>',
	);		

	var $phylodbAnnotation = array(
		'driver' => 'mysqli',
		'persistent' => true,
		'host' => 'localhost',
		'database' => 'phylodb_annotation',	
		'login' => '<your-login>',
		'password' => '<your-password>',
	);		

	//avoid using database config 
	function __construct($id = false, $table = null, $ds = null) { 
		ConnectionManager::create('phylodb', $this->phylodb);
		ConnectionManager::create('phylodbAnnotation', $this->phylodbAnnotation);		
		parent::__construct($id, $table, $ds);					
	}
}
?>
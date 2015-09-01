<?php
/***********************************************************
* File: user.php
* Description: Application Model - parent class of all model classes
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



class PhylodbAppModel extends Model {	
	public $recursive 	= -1;	
	public $actsAs 		= array('Containable');
	

}
?>
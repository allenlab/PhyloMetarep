<?php
/***********************************************************
* File: feature_controller.php
* Description: Display protein features.  
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

class FeatureController extends AppController {	
	
	var $uses = array();
	
	/**
	 * Display features
	 * 
	 * @param int $projectId 
	 * @param String $link absolute link the Apis page
	 * @return void
	 * @access public
	 */	
	function view($proteinId,$projectId) {
		debug("grep ^$proteinId ".SEQUENCE_STORE_PATH."/$projectId/$dataset/  > ".METAREP_TMP_DIR."/$proteinId.$projectId.tab");
		exec("grep ^$proteinId ".SEQUENCE_STORE_PATH."/$projectId/$dataset/  > ".METAREP_TMP_DIR."/$proteinId.$projectId.tab");
	}
}
?>

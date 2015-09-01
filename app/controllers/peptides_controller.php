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

class PeptidesController extends AppController {	
	
	var $uses = array();
	var $components = array('Download');
	
	/**
	 * Draw features
	 * 
	 * @param int $projectId 
	 * @param String $link absolute link the Apis page
	 * @return void
	 * @access public
	 */	
	function drawFeatures($dataset,$projectId,$peptideId,$parentPage) {		
		$featureTabOutfile = METAREP_TMP_DIR."/".uniqid('tmp_').'feature.tab';
		$featurePngOutFile = uniqid('tmp_').'feature.png';
		
		#debug("grep '^$peptideId' ".SEQUENCE_STORE_PATH."/$projectId/$dataset/feature.tab | sort | uniq | cut -f 2-6 > $featureTabOutfile");
		exec("grep '^$peptideId' ".SEQUENCE_STORE_PATH."/$projectId/$dataset/feature.tab  | sort | uniq | cut -f 2-7 > $featureTabOutfile");		
		
		$featureSeqCntFile = METAREP_TMP_DIR."/".uniqid('tmp_').'feature.cnt';
		$peptideId = str_replace('|','@',$peptideId);
		$peptideSeqLength = exec(FASTACMD_PATH." -d ".SEQUENCE_STORE_PATH."/$projectId/$dataset/$dataset -s $peptideId  | grep -v '>' | wc -m");
		
		#debug(FASTACMD_PATH." -d ".SEQUENCE_STORE_PATH."/$projectId/$dataset/$dataset '$peptideId' >> $fastaFilePath");

		#debug(PERL_PATH." ".METAREP_WEB_ROOT."/app/plugins/phylodb/scripts/perl/draw_feature_map.pl $featureTabOutfile $peptideSeqLength > ".METAREP_TMP_DIR."/".$featurePngOutFile);
		exec(PERL_PATH." ".METAREP_WEB_ROOT."/app/plugins/phylodb/scripts/perl/draw_feature_map.pl $featureTabOutfile $peptideSeqLength denovo > ".METAREP_TMP_DIR."/".$featurePngOutFile);
		
		$peptideId = str_replace('@','|',$peptideId);
		$webRootParts = explode('/',METAREP_WEB_ROOT);
		$webRootDir = $webRootParts[sizeof($webRootParts)-1];		
		$this->set('featureMapPng', DS.$webRootDir.DS.'tmp'.DS.$featurePngOutFile);
		$this->set('peptideId', $peptideId);		
		$this->set('dataset', $dataset);
		$this->set('projectId', $projectId);
		$this->set('parentPage', $parentPage);		
	}
	
	function downloadApisTree($dataset,$projectId,$peptideId) {
		$treeOutFile = uniqid('phylo-metarep-apis-tree-').".pdf";
		exec("cp '".SEQUENCE_STORE_PATH."/$projectId/$dataset/tree/$peptideId.pdf' ".METAREP_TMP_DIR."/$treeOutFile");
		$webRootParts = explode('/',METAREP_WEB_ROOT);
		$webRootDir = $webRootParts[sizeof($webRootParts)-1];
		$this->Download->pdfFile("{$peptideId}.pdf",METAREP_TMP_DIR."/$treeOutFile");
	}
	
	function downloadSequence($dataset,$projectId,$peptideId) {
		
	}
}
?>
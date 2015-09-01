<?php
/***********************************************************
* File: lucene_result_paginator.php
* Description: The Lucene Result Paginator Helper class, 
* provides result pagination support for Solr search results.
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

class PhylodbPaginatorHelper extends AppHelper {

	var $helpers = array('Html');
	
	function addPagination($field,$query,$page,$numHits,$limit) {
	
		//calculate the number of pages	
		$pageLimit = ceil($numHits/($limit));
		
		//maximum of displayed page links is 10
		$paginationPageLimit = $pageLimit >= $limit ?  $limit : $pageLimit;
		
		//disable pagination elements if needed (first page, and last page)
		$paginationString='<div class="paging">';
			
		//first page
		if($page==1) {
			$paginationString .= "<div class=\"disabled\">&lt;&lt; previous</div>";
		}
		else {
			$paginationString .= $this->printLink("<< previous",($page-1),$field,$query);
		}
		//pages between first and last			
		for($i=1; $i< $paginationPageLimit ; $i++) {
			if($i==$page) {
				$paginationString .="| <span class=\"current\">$i</span>";
			}
			else {
				//$paginationString .="| <span><a href=\"/mg-reports-dev/searches/search/$i/".$query."\">$i</a></span>";
				$paginationString .="| <span>".$this->printLink($i,$i,$field,$query)."</span>";
			}
		}
			
		//last page
		if($page==$pageLimit) {
			$paginationString .= "<div class=\"disabled\"> next &gt;&gt;</div>";
		}
		else {
			$paginationString .= $this->printLink(" next >>",($page+1),$field,$query)."";
		}
		$paginationString .='</div>';

		return $paginationString;
	}
	
	function addPageInformation($page,$numHits,$limit) {
		$pageLimit = ceil($numHits/$limit) ;
		$pageStart = (($page-1)*$limit)+1;
		$pageStop  = ($page==$pageLimit) ? $numHits : ($page)*$limit;
		return "<p>Page $page of $pageLimit, showing $limit records out of $numHits total, starting on record $pageStart, ending on $pageStop</p>";
	}
	private function printLink($text,$page,$field,$query){
		//use clean names and seguids for URL safe links		
		$query = str_replace(':','<>',$query);	
		$query = str_replace('/','[]',$query);	
		$query = rawurlencode($query);		
		
		return $this->Html->link($text, array('plugin'=>'phylodb','controller'=>'phylodb','action'=>'search',$field,$query,$page));
	}
	//<legend>Search Results</legend>".$this->addPageInformation($page,$numHits,$limit)."
	//$html .= $this->addPagination($page,$numHits,$dataset,"search",$limit,$sessionQueryId);
}

?>
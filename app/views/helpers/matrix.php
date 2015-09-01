<?php
/***********************************************************
* File: matrix.php
* Description: The Matrix Helper class helps to layout compare
* results and provides a HTML-based heatmap.
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
* @version METAREP v 1.3.2
* @author Johannes Goll
* @lastmodified 2010-07-09
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
**/

class MatrixHelper extends AppHelper {
 	var $helpers = array('Session','Html');
	var $uses = array('Library');
		
	function printTable($datasets,$counts,$option,$mode,$maxPvalue,$level) {
		
		$html='';
				
		if($option == METASTATS || $option == WILCOXON || $option == CHISQUARE || $option == FISHER || $option == PROPORTION_TEST || $option == EDGE_R) {	
			
				$libraryCountPopulationA = $this->Session->read('libraryCountPopulationA');
				$libraryCountPopulationB = $this->Session->read('libraryCountPopulationB');
						
				if($option == METASTATS) {		
					$html .= "<table style=\"border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px;\">
						<tr>"	;
					$html .= "<th style=\"padding-right:5px; width:30%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">METASTATS Test</th>";			
					$html .= "<th style=\"padding-center:5px; width:21%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[0]} (n=$libraryCountPopulationA)</th>";
					$html .= "<th style=\"padding-center:5px; width:21%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[1]} (n=$libraryCountPopulationB)</th>";
					$html .= "<th style=\"padding-center:5px; width:27%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">Significance (permutations=".NUM_METASTATS_BOOTSTRAP_PERMUTATIONS.")</th>";
					$html .= "</tr></table>";
					
					$html .= "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead> 	
						<tr><th style=\"padding-right:5px;width:30%;\">Category</th>";
					
					foreach($datasets as $dataset) {	
						$html .= "<th style=\"padding-right:5px;width:7%;\">#Total</th>";			
						$html .= "<th style=\"padding-right:5px;width:7%;\">%Mean</th>";
						#$html .= "<th style=\"padding-right:5px;width:6%;\">Variance</th>";
						$html .= "<th style=\"padding-right:5px;width:7%;\">%SE</th>";					
					}
					
					$html .= "<th style=\"padding-right:5px;width:7%;\">Mean Ratio</th>";
					$html .= "<th style=\"padding-right:5px;width:7%;\">p-value</th>";
					$html .= "<th style=\"padding-right:5px;width:7%;\">p-value (bonferroni)</th>";	
					$html .= "<th style=\"padding-right:5px;width:7%;\">q-value (fdr)</th>";				
					$html .= "<th style=\"padding-right:5px;width:14%;\">CI (%Mean +/- %SE)</th>";	
				}
				elseif($option == WILCOXON) {
					$html .= "<table style=\"border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px;\">
						<tr>"	;
					$html .= "<th style=\"padding-right:5px; width:35%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">Wilcoxon Rank Sum Test</th>";			
					$html .= "<th style=\"padding-center:5px; width:20%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[0]} (n=$libraryCountPopulationA)</th>";
					$html .= "<th style=\"padding-center:5px; width:20%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[1]} (n=$libraryCountPopulationB)</th>";
					$html .= "<th style=\"padding-center:5px; width:25%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">Significance</th>";
					$html .= "</tr></table>";
					
					$html .= "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead> 	
						<tr><th style=\"padding-right:5px;width:35%;\">Category</th>";
					
					foreach($datasets as $dataset) {	
						$html .= "<th style=\"padding-right:8px;width:10%;\">%Median</th>";
						$html .= "<th style=\"padding-right:8px;width:10%;\">%MAD</th>";		
					}
					
					$html .= "<th style=\"padding-right:5px;width:9%;\">Median Ratio</th>";
					$html .= "<th style=\"padding-right:5px;width:8%;\">p-value</th>";
					$html .= "<th style=\"padding-right:5px;width:8%;\">p-value (bonf. corr.)</th>";		
								
					#$html .= "<th style=\"padding-right:5px;width:14%;\">CI (Mean +/- SE)</th>";
				}
				elseif($option == CHISQUARE || $option == FISHER || $option == PROPORTION_TEST){
					if($option == CHISQUARE) {
						$test = 'Chi-Square Test of Independence';
					}
					elseif($option == FISHER) {
						$test = 'Fishers Exact Test';
					}
					elseif($option == PROPORTION_TEST) {
						$test = 'Equality of Proportions Test';
					}
					
					$html .= "<table style=\"border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px;\">
						<tr>"	;
					$html .= "<th style=\"padding-right:5px; width:30%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">$test</th>";			
					$html .= "<th style=\"padding-center:5px; width:16%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[0]} (n=1)</th>";
					$html .= "<th style=\"padding-center:5px; width:16%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[1]} (n=1)</th>";
					$html .= "<th style=\"padding-center:5px; width:38%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">Significance</th>";
					$html .= "</tr></table>";
					$html .= "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead> 	
						<tr>	
							<th style=\"padding-right:5px;width:30%;\">Category</th>";
					$html .= '<th style=\"padding-right:5px;width:8%;\">Count</th>';	
					$html .= '<th style=\"padding-right:5px;width:8%;\">Prop.</th>';	
					$html .= '<th style=\"padding-right:5px;width:8%;\">Count</th>';	
					$html .= '<th style=\"padding-right:5px;width:8%;\">Prop.</th>';	
					$html .= '<th style=\"padding-right:5px;width:7%;\">Log Odds Ratio</th>';							
					$html .= '<th style=\"padding-right:5px;width:7%;\">Rel. Risk</th>';					
					$html .= '<th style=\"padding-right:5px;width:7%;\">p-value</th>';
					$html .= '<th style=\"padding-right:5px;width:7%;\">p-value (bonferroni)</th>';
					$html .= '<th style=\"padding-right:5px;width:7%;\">q-value (fdr)</th>';
					
					if(($mode === 'keggPathwaysEc' || $mode === 'keggPathwaysKo') && $level === 'pathway') {
						$html .= '<th style=\"padding-right:2px; \">Action</th>';
					}
				}
				elseif($option == EDGE_R){
					$test = 'edgeR Test';
										
					$html .= "<table style=\"border:1px; padding-bottom:5px; border-bottom-style:solid;border-width:1px;\">
						<tr>"	;
					$html .= "<th style=\"padding-right:5px; width:30%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">$test</th>";			
									$html .= "<th style=\"padding-center:5px; width:21%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[0]} (n=$libraryCountPopulationA)</th>";
					$html .= "<th style=\"padding-center:5px; width:21%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">{$datasets[1]} (n=$libraryCountPopulationB)</th>";
					$html .= "<th style=\"padding-center:5px; width:38%; border-width:0px;font-size:1.2em;background-color:#FFFFFF;\">Significance</th>";
					$html .= "</tr></table>";
					
					$html .= "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead> 	
						<tr>	
							<th style=\"padding-right:5px;width:30%;\">Category</th>";
					$html .= '<th style=\"padding-right:5px;width:8%;\">Count A</th>';	
					$html .= '<th style=\"padding-right:5px;width:8%;\">Count B</th>';					
					$html .= '<th style=\"padding-right:5px;width:8%;\">Log Conc.</th>';	
					$html .= '<th style=\"padding-right:5px;width:8%;\">Log FC.</th>';	
					$html .= '<th style=\"padding-right:5px;width:8%;\">Disp.</th>';						
					$html .= '<th style=\"padding-right:5px;width:7%;\">p-value</th>';
					$html .= '<th style=\"padding-right:5px;width:7%;\">p-value (bonferroni)</th>';
					$html .= '<th style=\"padding-right:5px;width:7%;\">q-value (fdr)</th>';
					
//					if(($mode === 'keggPathwaysEc' || $mode === 'keggPathwaysKo') && $level === 'pathway') {
//						$html .= '<th style=\"padding-right:2px; \">Action</th>';
//					}
				}				
		}
		else {
		
			$html .= "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead> 	
						<tr>	
							<th>Category</th>";			

			foreach($datasets as $dataset) {
					$html .= "<th style=\"padding-right:0px; \">$dataset</th>";		
			}	
			if($option == ABSOLUTE_COUNTS) {			
				$html .= '<th style=\"padding-right:10px; \">Total</th>';
			}		
		}
		
		$html .= '</tr></thead><tbody>';
		
		## end of header; start data rows
		$i = 0;	
		
		
		foreach($counts as $category => $row) {	
				
				if($maxPvalue != PVALUE_ALL  && ($option == METASTATS || $option == WILCOXON || $option == FISHER || $option == CHISQUARE || $option == PROPORTION_TEST || $option == EDGE_R)) {
						
					$fieldName = null;
					$cutoff = null;
						
					// handle p-value filtering
					switch ($maxPvalue) {
						case PVALUE_HIGH_SIGNIFICANCE;
						$cutoff = 0.01;
						$fieldName = 'pvalue';
						break;
						case PVALUE_MEDIUM_SIGNIFICANCE;
						$cutoff = 0.05;
						$fieldName = 'pvalue';
						break;
						case PVALUE_LOW_SIGNIFICANCE;
						$cutoff = 0.1;
						$fieldName = 'pvalue';
						break;
						case PVALUE_BONFERONI_HIGH_SIGNIFICANCE;
						$cutoff = 0.01;
						$fieldName = 'bvalue';
						break;
						case PVALUE_BONFERONI_MEDIUM_SIGNIFICANCE;
						$cutoff = 0.05;
						$fieldName = 'bvalue';
						break;
						case PVALUE_BONFERONI_LOW_SIGNIFICANCE;
						$cutoff = 0.1;
						$fieldName = 'bvalue';
						break;
						case PVALUE_FDR_HIGH_SIGNIFICANCE;
						$cutoff = 0.01;
						$fieldName = 'qvalue';
						break;
						case PVALUE_FDR_MEDIUM_SIGNIFICANCE;
						$cutoff = 0.05;
						$fieldName = 'qvalue';
						break;
						case PVALUE_FDR_LOW_SIGNIFICANCE;
						$cutoff = 0.1;
						$fieldName = 'qvalue';
						break;						
					}
					
					if(! is_null($fieldName) && $row[$fieldName] >= $cutoff)	{
							
						continue;
					}
				}
					
				if($row['sum'] > 0 || $option == METASTATS || $option == WILCOXON) {					
									
					if ($i++ % 2 == 0) {
						$color = '#FFFFFF';
					}	
					else {
						$color = '#FFFFFF';
					}	
					if($category === 'unclassified') {
						$html .="<tr style=\"text-align:left;font-weight:bold; \">";
						$html .= "<td style=\"text-align:left; \">{$row['name']}</td>";
					}
					else {			
						$html .= "<tr>";
						$rowValue = '';

						
						switch ($mode) {
							case 'taxonomy':
								$rowValue = "{$row['name']} (taxid:$category)";
								break;
							case 'commonNames':
								$rowValue = $row['name'];						
								break;
							case 'clusters':
								$rowValue = $row['name'];						
								break;	
							case 'pathways':
								$rowValue = "{$row['name']} (map$category)";
								break;							
							case 'environmentalLibraries':
								$rowValue = $row['name'];
								break;																	
							default:
								$rowValue = "{$row['name']} ($category)";
								break;
						}
						$html .= "<td style=\"text-align:left; \">$rowValue</td>";
					}
					
					if($option == METASTATS) {
						foreach($datasets as $dataset) {	
							$html .= "<td style=\"text-align:right;\">{$row[$dataset]['total']}</td>";							
							$html .= "<td style=\"text-align:right;\">".($row[$dataset]['mean'])."</td>";
							#$html .= "<td style=\"text-align:right;\">{$row[$dataset]['variance']}</td>";
							$html .= "<td style=\"text-align:right;\">".($row[$dataset]['se'])."</td>";
						}	
											
						$meanA 			= $row[$datasets[0]]['mean'];
						$lowBoundA 		= ($row[$datasets[0]]['mean']-$row[$datasets[0]]['se']);
						$upperBoundA 	= ($row[$datasets[0]]['mean']+$row[$datasets[0]]['se']);
						
						$meanB 			= $row[$datasets[1]]['mean'];
						$lowBoundB 		= ($row[$datasets[1]]['mean']-$row[$datasets[1]]['se']);
						$upperBoundB 	= ($row[$datasets[1]]['mean']+$row[$datasets[1]]['se']);
											
						$html .= "<td style=\"text-align:right;\">{$row['mratio']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['pvalue']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['bvalue']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['qvalue']}</td>";	
					
						
						$chartUrl 	= "http://chart.apis.google.com/chart?chs=140x18&cht=bhs&chd=t0:-1,";
						$chartUrl  .= "{$lowBoundA},{$lowBoundB},-1|-1,{$meanA},{$meanB},-1|-1,{$meanA},{$meanB},";
						$chartUrl  .= "-1|-1,{$upperBoundA},{$upperBoundB},-1|-1,{$meanA},{$meanB},-1&chm=F,C00000,0,1:4,5&chxr=0,0,1,100&chbh=1,5,1";
						
						#$largeChart 	= "http://chart.apis.google.com/chart?chs=325x48&cht=bhs&chd=t0:-1,{$lowBoundA},{$lowBoundB},-1|-1,{$meanA},{$meanB},-1|-1,{$meanA},{$meanB},-1|-1,{$upperBoundA},{$upperBoundB},-1|-1,{$meanA},{$meanB},-1&chm=F,808080,0,1:4,5&chxr=0,0,1,100&chbh=1,5,1";
						
						$html .="<td style=\"text-align:center;\"><img src=\"$chartUrl\" name=\"ci_chart\">";
						$html .="</td>";
					}
					elseif($option == WILCOXON) {
						
						foreach($datasets as $dataset) {			
							$html .= "<td style=\"text-align:right;\">".($row[$dataset]['median'])."</td>";
							#$html .= "<td style=\"text-align:right;\">{$row[$dataset]['variance']}</td>";
							$html .= "<td style=\"text-align:right;\">".($row[$dataset]['mad'])."</td>";
						}	
											
						$medianA 		= $row[$datasets[0]]['median'];				
						$medianB 		= $row[$datasets[1]]['median'];																
						$html .= "<td style=\"text-align:right;\">{$row['mratio']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['pvalue']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['bvalue']}</td>";							
						$html .="</td>";
					}
					elseif($option == CHISQUARE || $option == FISHER || $option == PROPORTION_TEST) {
						
						$countA 		= $row[$datasets[0]];	
						$countB 		= $row[$datasets[1]];
						$proportionA 	= $row['propa'];
						$proportionB 	= $row['propb'];
						
						$html .= "<td style=\"text-align:right;\">$countA</td>";
						$html .= "<td style=\"text-align:right;\">$proportionA</td>";
						$html .= "<td style=\"text-align:right;\">$countB</td>";
						$html .= "<td style=\"text-align:right;\">$proportionB</td>";							
						$html .= "<td style=\"text-align:right;\">{$row['oratio']}</td>";		
						$html .= "<td style=\"text-align:right;\">{$row['rrisk']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['pvalue']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['bvalue']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['qvalue']}</td>";	
						if(($mode === 'keggPathwaysEc' || $mode === 'keggPathwaysKo') && $level === 'pathway') {
							$html .= "<td style=\"text-align:center;\">{$this->Html->link($this->Html->image("pathway.jpg",array("title" => "Compare {$row['name']} Pathway Map",'width'=>'25px')), array('controller'=> 'compare','action'=>'pathwayMap',$mode,'enzyme',$category),array('escape' => false,'target' => '_blank'))}</td>";	
						}
					}
					elseif($option == EDGE_R) {
						
						$html .= "<td style=\"text-align:right;\">{$row['counta']}</td>";
						$html .= "<td style=\"text-align:right;\">{$row['countb']}</td>";
						$html .= "<td style=\"text-align:right;\">{$row['logconc']}</td>";				
						$html .= "<td style=\"text-align:right;\">{$row['logfc']}</td>";		
						$html .= "<td style=\"text-align:right;\">{$row['disp']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['pvalue']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['bvalue']}</td>";	
						$html .= "<td style=\"text-align:right;\">{$row['qvalue']}</td>";	
						if(($mode === 'keggPathwaysEc' || $mode === 'keggPathwaysKo') && $level === 'pathway') {
							$html .= "<td style=\"text-align:center;\">{$this->Html->link($this->Html->image("pathway.jpg",array("title" => "Compare {$row['name']} Pathway Map",'width'=>'25px')), array('controller'=> 'compare','action'=>'pathwayMap',$mode,'enzyme',$category),array('escape' => false,'target' => '_blank'))}</td>";	
						}
					}					
					else {
						
						#set the individual counts
						foreach($datasets as $dataset) {	
								$count = $row[$dataset];					
								$html .= "<td style=\"text-align:right;\">$count</td>";
						}
		
						if($option != RELATIVE_COUNTS) {	
							#set the sum
							$sum 	= trim($counts[$category]['sum']);
							$html .= "<td style=\"text-align:right; \">$sum</td>";
						}
					}
					
					$html .= '</tr>';
				}		
		}
			
		if(preg_match('/.*<tbody>$/',$html)) {
			return 'No hits found for the selected pvalue cut off. Adjust filter settings and try again.';
		}

		$html .= '</tbody></table>';
		
		return $html;
	}
	
	

	function printFlippedTable($datasets,$counts,$option,$mode) {
	
		## generate table heading
		$html = "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead> 	
					<tr>	
						<th>Dataset</th>";
		
		foreach($counts as $category => $row) {	
			
			
			if(!empty($row['name'])  && $row['sum'] > 0 ) {
				$html .= "<th style=\"padding-right:5px; \">{$row['name']}</th>";	
			}				
		}
		
		$html .= '</tr></thead><tbody> ';

		## add total column of absolute counts
		if($option == ABSOLUTE_COUNTS) {	
			array_push($datasets,'Total')	;
		}

		## add p-value and adj. p-value if user has selected a test
		if($option == CHISQUARE || $option == FISHER) {	
				array_push($datasets,'Total')	;
				array_push($datasets,'P-Value')	;
				array_push($datasets,'P-Value (Bonf. Corr.)')	;
		}
		
		## loop through each dataset [dimension 1]	
		foreach($datasets as $dataset) {		
				
			$rowValue = '';
								
			switch ($mode) {
				case 'taxonomy':
					$rowValue = "{$row['name']} (taxid:$category)";
					break;
				case 'commonNames':
					$rowValue = $row['name'];
					break;
				case 'clusters':
					$rowValue = $row['name'];
					break;		
				case 'pathways':
					$rowValue = "{$row['name']} (map$category)";
					break;							
				case 'environmentalLibraries':
					$rowValue = $row['name'];
					break;										
				default:
					$rowValue = "{$row['name']} ($category)";
					break;
			}
			
			## set font weight to bold for total 
			if($dataset === 'Total') {
				$html .= "<tr style=\"text-align:left;font-weight:bold;\"><td >$dataset</td>";
			}
			else {
				$html .= "<tr style=\"text-align:left;\"><td>$dataset</td>";				
			}
			
			## set the individual counts
			foreach($counts as $category => $row) {	
				## exclude unclassified
				if($row['sum'] > 0) { 
					if($dataset==='P-Value') {
						$count = $row['pvalue'];	
					}
					elseif($dataset==='P-Value (Bonf. Corr.)') {
						$count = $row['bvalue'];
					}
					elseif($dataset==='Total') {
						$count  = $row['sum'];
						
					}
					else {
						$count = $row[$dataset];		
					}	
					$html .= "<td style=\"text-align:right;\">$count</td>";
				}													
			}
					
			$html .= '</tr>';		
		}
				
		
		$html .= '<tbody></table>';
		
		return $html;
	}	
	
	
	function printHeatMap($datasets,$counts,$option,$mode,$colorGradient) {
		
		$html = $this->printHeatmapColorLegend($colorGradient);	
		
		## print table header
		$html .= "<table cellpadding=\"0\" cellspacing=\"0\" id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead>	
					<tr>	
						<th>Category</th>";
		
		foreach($datasets as $dataset) {
				$html .= "<th style=\"padding-right:5px; \">$dataset</th>";		
		}	
		
		$html .= '</tr></thead><tbody>';
		
		
		#print table body
		foreach($counts as $category => $row) {	
			
			#filter rows for those with entries
			if($row['sum']>0 && !empty($row['name'])) {					
					$html .= "<tr class=\"comparator-heatmap\" \" >";
									
					switch ($mode) {
						case 'taxonomy':
							$rowValue = "{$row['name']} (taxid:$category)";
							break;
						case 'commonNames':
							$rowValue = $row['name'];
							break;
						case 'clusters':
							$rowValue = $row['name'];
							break;
						case 'pathways':
							$rowValue = "{$row['name']} (map$category)";
							break;				
						case 'environmentalLibraries':
							$rowValue = $row['name'];
							break;												
						default:
							$rowValue = "{$row['name']} ($category)";
							break;
					}					
					

					$html .= "<td style=\"text-align:left; \">$rowValue</td>";
					
					foreach($datasets as $dataset) {	
						
						$color = $colorGradient[floor($row[$dataset]*19)];
						$html .= "<td style=\"text-align:left; background-color:#$color;\">{$row[$dataset]}</td>";
					}				
					$html .= '</tr>';
			}
		}
		#$html .= "<tr style=\"text-align:left;font-weight:bold; \">";
		#$html .= "<td>Unclassified</td>";	
		
//		foreach($datasets as $dataset) {		
//			$color = $colorGradient[floor($counts['unclassified'][$dataset]*19)];				
//			$html .= "<td style=\"text-align:left; background-color:#$color;\">{$counts['unclassified'][$dataset]}</td>";			
//		}
		
		$html .= '</tr>';			
		
		
		$html .= '<tbody></table>';
		
		return $html;	
	}

	function printFlippedHeat2Map($datasets,$counts,$mode,$colorGradient) {
			
		#print heatmap color legend
		$html = "<table cellpadding=\"0\" cellspacing=\"0\"><tr>";
		
		$offset= 0;
		$step  = 0.05;
		foreach($colorGradient as $color) {
			$start = $offset;
			$end   =  $offset + $step;
			$html.="<td class=\"comparator-heatmap-legend\" style=\"background-color:#$color; \">{$start} - {$end}</td>";
			$offset +=$step;
		}
		$html .="</table>";
		
		#print values
		$html .= "<table cellpadding=\"0\" cellspacing=\"0\" id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead>	
					<tr>	
						<th>Category</th>";
		
		foreach($counts as $category => $row) {	
			if(!empty($row['name'])) {
				switch ($mode) {
					case 'taxonomy':
						$rowValue = "{$row['name']} (taxid:$category)";
						break;
					case 'commonNames':
						$rowValue = $row['name'];
						break;
					case 'clusters':
						$rowValue = $row['name'];
						break;	
					case 'pathways':
						$rowValue = "{$row['name']} (map$category)";
						break;							
					case 'environmetnalLibraries':
						$rowValue = $row['name'];
						break;													
					default:
						$rowValue = "{$row['name']} ($category)";
						break;
				}	
			}
			
			$html .= "<th style=\"padding-right:5px; \">$rowValue</th>";					
		}
		
		$html .= '</tr></thead><tbody>';
		
		foreach($datasets as $dataset) {
			
			#filter rows for those with entries
			if($row['sum']>0 && !empty($row['name'])){					
					$html .= "<tr class=\"comparator-heatmap\" \" >";
					

					$html .= "<td style=\"text-align:left; \">$dataset</td>";
					
					foreach($counts as $category => $row) {		
						
						$color = $colorGradient[floor($row[$dataset]*19)];
						$html .= "<td style=\"text-align:left; background-color:#$color;\">{$row[$dataset]}</td>";
					}				
					$html .= '</tr>';
			}
		}
		$html .= '<tbody></table>';
		
		return $html;

	}	
	
	function printFlippedHeatmap($datasets,$counts,$option,$mode,$colorGradient) {

		$html = $this->printHeatmapColorLegend($colorGradient);
		
		#print table header
		$html .= "<table cellpadding=\"0px\" cellspacing=\"0\", id=\"myTable\" class=\"tablesorter comparison-results-table\"><thead> 	
					<tr>	
						<th>Category</th>";
		
		foreach($counts as $category => $row) {	
			if(!empty($row['name']) && $row['sum'] > 0) {
				$html .= "<th style=\"padding-right:5px; \">{$row['name']}</th>";	
			}				
		}
		
		#$html .= "<th style=\"padding-right:5px; \">Unclassified</th>";	
		
		$html .= '</tr></thead><tbody> ';
			
		#llop through datasets
		foreach($datasets as $dataset) {
				
				$rowValue = '';
								
				switch ($mode) {
					case 'taxonomy':
						$rowValue = "{$row['name']} (taxid:$category)";
						break;
					case 'commonName':
						$rowValue = $row['name'];
						break;
					case 'clusters':
						$rowValue = $row['name'];
						break;	
					case 'pathways':
						$rowValue = "{$row['name']} (map$category)";
						break;							
					case 'environmetnalLibraries':
						$rowValue = $row['name'];
						break;													
					default:
						$rowValue = "{$row['name']} ($category)";
						break;
				}

				$html .= "<tr style=\"text-align:left;\"><td>$dataset</td>";				

				
				#set the individual counts
				foreach($counts as $category => $row) {						
					if(!empty($row['name']) && $row['sum']>0) { 
						$count = $row[$dataset];		
						$color = $colorGradient[floor($count *19)];
						$html .= "<td style=\"text-align:right;background-color:#$color;\">$count</td>";
					}													
				}
				#$color = $colorGradient[floor($counts['unclassified'][$dataset] *19)];
				#$html .= "<td style=\"text-align:right;background-color:#$color;\">{$counts['unclassified'][$dataset]}</td>";
				
				
				
				$html .= '</tr>';
			}			
		
				
		
		$html .= '<tbody></table>';
		
		return $html;
	}		
			
	private function printHeatmapColorLegend(&$colorGradient) {
		#print heatmap color legend
		$html = "<table cellpadding=\"0\" cellspacing=\"0\"><tr>";
		
		$offset= 0;
		$step  = 0.05;
		
		foreach($colorGradient as $color) {
			$start = $offset;
			$end   =  $offset + $step;
			$html.="<td class=\"comparator-heatmap-legend\" style=\"background-color:#$color; \">{$start} - {$end}</td>";
			$offset +=$step;
		}
		$html .="</table>";
		return $html;		
	}
}
?>
<?php
/***********************************************************
 * File: r.php
 * Description: The R component handles all interactions between
 * METAREP and the R statistical software.
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

class RComponent extends Object {

	var $components = array('Session');

	/**
	 * Executes R PDF plots
	 *
	 * @param array $datasets list of selected datasets
	 * @param reference $counts matrix containing absolute counts
	 * @return void
	 * @access public
	 */
	public function writeRPlotMatrix($datasets,&$counts,$option,$plotLabel,$clusterMethod,$distanceMatrix) {
		$this->Project =& ClassRegistry::init('Project');

		$tabName = '';

		$mode 	= str_replace('ajax','',$this->Session->read('mode'));

		## read session variables
		$level 		  = $this->Session->read("$mode.level");
		$tabs 		  = $this->Session->read("tabs");
		$heatmapColor = $this->Session->read("heatmapColor");

		foreach($tabs as $tab) {
			if($tab['function'] === $mode) {
				$tabName = $tab['tabName'];
			}
		}

		$subtitle = "data type:$tabName | level:$level";

		switch($option) {
			case HIERARCHICAL_CLUSTER_PLOT:
				$title 	 = "Hierarchical Clustering Plot";
				$subtitle.=" | distance matrix:$distanceMatrix | cluster method:$clusterMethod";
				break;
			case HEATMAP_PLOT:
				$title = "Heatmap Plot";
				$subtitle.=" | distance matrix:$distanceMatrix | cluster method:$clusterMethod";
				break;				
			case MDS_PLOT:
				$title = "MDS Plot";
				$subtitle.=" | distance matrix:$distanceMatrix";
				break;
			case MOSAIC_PLOT:				
				$title = "Mosaic Plot";
				break;				
		}

		$categroyDatasetMatrix = $this->categoryDatasetMatrixToString($datasets,$counts,$plotLabel,$mode);
		
		$id 	  = uniqid();
		$inFile   = "jcvi_phylogenomics_report_".$id;
		$distFile = "jcvi_phylogenomics_report_".$id."_dist";


		//write to file
		$fh = fopen(METAREP_TMP_DIR."/$inFile", 'w');		#write session variables

		fwrite($fh,$categroyDatasetMatrix);
		fclose($fh);
		exec(RSCRIPT_PATH." ".METAREP_WEB_ROOT."/scripts/r/plots.r ".METAREP_TMP_DIR."/$inFile $option \"$title\" \"$subtitle\" $distanceMatrix $clusterMethod $heatmapColor"." \"".METAREP_TMP_DIR."/".$distFile."\"");

		if($option == HEATMAP_PLOT || $option == MDS_PLOT || $option == HIERARCHICAL_CLUSTER_PLOT) {
			$clusterMatrices = file_get_contents(METAREP_TMP_DIR."/$distFile", 'w');
			$this->Session->write('distantMatrices',$clusterMatrices);
		}
		
		$this->Session->write('plotFile',$inFile);
	}
	
	private function categoryDatasetMatrixToString($datasets,&$counts,$plotLabel,$mode){
		$result = ' ';


		foreach($counts as $category => $row) {
				
			if($mode === 'Hmms') {
				$result .= preg_replace('/\s++/','_',trim($category))." ";
			}
			else {
				//replace white spaces and quotes
				$result .= preg_replace('/\'/','',preg_replace('/\s++/','_',trim($row['name'])))." ";
			}
		}
		$result .="\n";
			
		$categories = array_keys($counts);

		//loop through each dataset [dimension 1 / column]
		foreach($datasets as $dataset) {
				
			$label = $this->Project->getDatasetLabel($dataset,$plotLabel);
			$result .= "$label ";

			//loop through each category [dimension 2 / row]
			foreach($categories as $category) {
				$result .= $counts[$category][$dataset]." ";
			}
			$result .="\n";
		}
		return $result;
	}

	/**
	 * Executes METASTATS - a modified non -parameteric t-test (White et al 2009)
	 *
	 * @param array $datasets list of selected datasets
	 * @param reference $counts matrix containing absolute counts
	 * @return Array of counts with test results
	 * @access public
	 */
	public function writeMetastatsMatrix($datasets,&$counts) {

		$startIndexPopulationB	= $this->Session->read('startIndexPopulationB');
		$populations 			= $this->Session->read('populations');
		$mode				 	= $this->Session->read('mode');

		$matrixContent = "";
		$id = time();

		#generate file names
		$metastatsFrequencyMatrixFile = "metarep_metagenomics_report_metastats_in_".$id;
		$metastatsResultsFile 		  = "metarep_metagenomics_report_metastats_out_".$id;
		$metastatsRscriptFile		  = "metarep_metastats_rscript_".$id;

		#write headings
		foreach($datasets as $dataset) {
			$matrixContent .="\t".$dataset;
		}
		$matrixContent .="\n";

		#loop through each category [dimension 1]
		foreach($counts as $category => $row) {

			$matrixContent .= $category;

			#loop through each dataset [dimension 1 / column]
			foreach($datasets as $dataset) {
				$matrixContent .= "\t".$row[$dataset];
			}
			$matrixContent .="\n";
		}

		#write frequency matrix to file
		$fh = fopen(METAREP_TMP_DIR."/$metastatsFrequencyMatrixFile", 'w');
		fwrite($fh,$matrixContent);
		fclose($fh);

		#prepare metastats rscript
		$metastatsRScriptContent  = "#!".RSCRIPT_PATH. " --vanilla\n";
		$metastatsRScriptContent .= "source(\"".METAREP_WEB_ROOT."/scripts/r/metastats/detect_DA_features.r\")\n";
		$metastatsRScriptContent .= "jobj <- load_frequency_matrix(\"".METAREP_TMP_DIR."/$metastatsFrequencyMatrixFile"."\")\n";
		$metastatsRScriptContent .= "detect_differentially_abundant_features(jobj,$startIndexPopulationB,\"".METAREP_TMP_DIR."/$metastatsResultsFile"."\",B=".NUM_METASTATS_BOOTSTRAP_PERMUTATIONS.")\n";

		#write metastats r script to file
		$fh = fopen(METAREP_TMP_DIR."/$metastatsRscriptFile", 'w');
		fwrite($fh,$metastatsRScriptContent);
		fclose($fh);

		#create results file
		$fh = fopen(METAREP_TMP_DIR."/$metastatsResultsFile", 'w');

		fclose($fh);
		chmod(METAREP_TMP_DIR."/$metastatsResultsFile", 0755);

		#execute script
		exec(RSCRIPT_PATH." ".METAREP_TMP_DIR."/$metastatsRscriptFile");

		#read R results
		$fh = fopen(METAREP_TMP_DIR."/$metastatsResultsFile", 'r');
			
		$newCounts   = array();
		$populationA = $populations[0];
		$populationB = $populations[1];

		$categoryCount = count($counts);

		#loop through metastats output results
		while (!feof($fh)) {
				
			$line 	= fgets($fh);
			if($line) {
				$column 	= split("\t",$line);

				//round means with precison defined by RELATIVE_COUNT_PRECISION
				$meanA = round($column[1],RELATIVE_COUNT_PRECISION);
				$meanB = round($column[4],RELATIVE_COUNT_PRECISION);
					
				//if either mean is zero after rounding skip line
				if($meanA == 0 ||  $meanB == 0) {
					$categoryCount--;
					continue;
				}

				//get the category from the metastats output
				$category = trim($column[0]);

				//for KEGG pathways we need to left-pad the categories with zeros
				if($mode === 'keggPathwaysEc' || $mode === 'keggPathwaysKo') {
					$category = str_pad($category,5,0,STR_PAD_LEFT);
				}
					
				## init newCounts fields
				$newCounts[$category]['sum']  = 0;
				$newCounts[$category]['name'] = $counts[$category]['name'];
				$newCounts[$category][$populationA]['total']=0;
				$newCounts[$category][$populationB]['total']=0;
					
				## sum up library counts for each category to set population totals
				for($i=0;$i<count($datasets);$i++) {
					if($i < ($startIndexPopulationB - 1)) {
						$newCounts[$category][$populationA]['total'] += $counts[$category][$datasets[$i]];
					}
					else {
						$newCounts[$category][$populationB]['total'] += $counts[$category][$datasets[$i]];
					}
				}

				$newCounts[$category][$populationA]['mean'] 	= $meanA*100; //%mean;
				
				$newCounts[$category][$populationA]['variance']	= round($column[2],RELATIVE_COUNT_PRECISION);
				$newCounts[$category][$populationA]['se']		= round($column[3],RELATIVE_COUNT_PRECISION)*100; //%se
				$newCounts[$category][$populationB]['mean'] 	= $meanB*100; //%mean;
				$newCounts[$category][$populationB]['variance']	= round($column[5],RELATIVE_COUNT_PRECISION); 
				$newCounts[$category][$populationB]['se']		= round($column[6],RELATIVE_COUNT_PRECISION)*100; //%se
					
				if($column[4] !=0) {
					$newCounts[$category]['mratio']	= round($column[1]/$column[4],RELATIVE_COUNT_PRECISION/2);
				}
				else {
					$newCounts[$category]['mratio']	 =0;
				}
					
				$newCounts[$category]['pvalue'] = round($column[7],(strlen(NUM_METASTATS_BOOTSTRAP_PERMUTATIONS)-1));
				
				
				$newCounts[$category]['qvalue'] = round($column[8],(strlen(NUM_METASTATS_BOOTSTRAP_PERMUTATIONS)-1));

				$newCounts[$category]['bvalue']	= round($column[7]*$categoryCount,(strlen(NUM_METASTATS_BOOTSTRAP_PERMUTATIONS)-1));
				
				## handle negative q-values
				if($newCounts[$category]['qvalue'] < 0 ) {
					$newCounts[$category]['qvalue'] =0 ;
				}

				if($newCounts[$category]['bvalue']>1) {
					$newCounts[$category]['bvalue']	= 1;
				}

				if($newCounts[$category]['bvalue'] < 0) {
					$newCounts[$category]['bvalue'] = 0 ;
				}
			}
		}
		fclose($fh);

		$this->Session->write('selectedDatasets',$populations);



		$counts= $newCounts;
	}


	/**
	 * Transforms counts to R compatible contingency table format string
	 *
	 * @param array $selectedDatasets list of selected datasets
	 * @param reference $counts count matrix
	 * @param reference $totalCounts matrix that contains the total count for each dataset
	 * @return String R compatible contingency table format string
	 * @access public
	 */
	private function countsToContingencyTables($datasets,&$counts,&$totalCounts) {

		$tables = array();
			
		$counter=0;

		foreach($counts as $category => $row) {
			$rString="";

			#loop through datatsets
			foreach($datasets as $dataset) {

				#create contigency table based on postive and negative counts for
				#a certain category
				$countCategory 		= $row[$dataset];
				$countNonCategory 	= $totalCounts[$dataset] - $countCategory;

				#uses only first two datasets
				if(empty($rString)) {
					$rString ="ct_$counter=matrix(c($countCategory,$countNonCategory";
				}
				else {
					$rString.=",$countCategory,$countNonCategory";
				}
			}
			$rString.="),2,".count($datasets).")";
			array_push($tables,array($category,$rString));
			$counter++;
		}
		
		return $tables;
	}

	/**
	 * Writes contigency matrix and executes either Fisher's exact test or
	 * a ChiSquare test of independence
	 *
	 * @param array $selectedDatasets list of selected datasets
	 * @param reference $counts count matrix
	 * @param reference $totalCounts matrix that contains the total count for each dataset
	 * @param constant $option defines test to use CHISQUARE/FISHER
	 * @return Array of counts with test results
	 * @access public
	 */
	public function writeContingencyMatrix($selectedDatasets,&$counts,&$totalCounts,$option) {
		## format data as R matrix (2x2 contingency table)
		$tables = $this->countsToContingencyTables($selectedDatasets,$counts,$totalCounts) ;
		$this->countsToTwoWayTables($selectedDatasets,$counts,$totalCounts) ;

		if($option == CHISQUARE) {
			$test = 'chisq.test';
		}
		elseif($option == FISHER) {
			$test = 'fisher.test';
		}
		elseif($option == PROPORTION_TEST) {
			$test = 'prop.test';
		}		

		$id = uniqid();
		$inFile = "metarep_$test.in_".$id.'.txt';
		$outFile = "metarep_$test.out_".$id.'.txt';
		$fh = fopen(METAREP_TMP_DIR."/$inFile", 'w');

		$counter=1;

		## write R code
		for ($i = 0; $i < count($tables); $i++) {
			$matrix 		= "{$tables[$i][1]}";
			$testPvalue 	= "$test(ct_$i)\$p.value";
			$oddsRatio 		= "(ct_{$i}[1,1]*ct_{$i}[2,2])/(ct_{$i}[2,1]*ct_{$i}[1,2])";
			$relativeRisk 	= "(ct_{$i}[1,1]/sum(ct_0[,1]))/((ct_{$i}[1,2]/sum(ct_0[,2])))";
			$rCommand 		= "$matrix\nround(c($testPvalue,$oddsRatio,$relativeRisk),".RELATIVE_COUNT_PRECISION.")\n";
			## write test
			fwrite($fh, $rCommand);
			$counter++;
		}
		fclose($fh);


		#execute R code
		exec(R_PATH." --quiet --vanilla < ".METAREP_TMP_DIR."/$inFile > ".METAREP_TMP_DIR."/$outFile");

		#read R results
		$fh = fopen(METAREP_TMP_DIR."/$outFile", 'r');
		while (!feof($fh)) {

			$line = fgets($fh);

			if (substr($line,0,5) == '> ct_') {
				$line =	str_replace('> ct_','',$line);
				$tmp= split("=",$line);
				$id = $tmp[0];

				#get the id to category mapping
				$category = $tables[$id][0];

			}
			else if(substr($line,0,3) === '[1]') {
				$line = trim(str_replace('[1] ','',$line));
				$results = explode(" ",$line);
				
				$counts[$category]['pvalue'] 	= $results[0];
				$counts[$category]['oddsratio'] = $results[1];
				$counts[$category]['relrisk'] 	= $results[2];
				
				## set bonferroni corrected p-value
				$counts[$category]['bvalue'] = ($pValue*count($counts)>1) ? 1 : $pValue*count($counts); 
			}
		}
		fclose($fh);
	}
	
	/**
	 * Writes two way sample test flat file results with the following format:
	 * id count1 count2 prop1 prop2 odds_ratio relative_risk p_value b_value q_value
	 *
	 * @param reference $filePath absolute path to file
	 * @param reference $datasetA name of dataset A
	 * @param reference $datasetB name of dataset B
	 * @param reference $counts original count matrix
	 * @param reference $totalCounts total count matrix
	 * @return void
	 * @access private
	 */
	private function writeTwoWaySampleTestCounts(&$filePath,&$datasetA,&$datasetB,&$counts,&$totalCounts) {
		## open file
		$fh = fopen($filePath, 'wb');
		
		## loop through results and write counts
		foreach($counts as $category => $row) {
			$rowString  = "\"$category\"\t";
			$rowString .= "{$row[$datasetA]}\t".($totalCounts[$datasetA] - $row[$datasetA]."\t");
			$rowString .= "{$row[$datasetB]}\t".($totalCounts[$datasetB] - $row[$datasetB]."\n");
			fwrite($fh,$rowString);
		}	
		fclose($fh);
	}
	
	/**
	 * Reads two way sample test flat file results with the following format:
	 * id count1 count2 prop1 prop2 odds_ratio relative_risk p_value b_value q_value
	 *
	 * @param reference absolute path to file
	 * @param reference name of dataset A
	 * @param reference name of dataset B
	 * @param reference original count matrix
	 * @param reference total count matrix
	 * @return void (updates counts directly using &$counts reference
	 * @access private
	 */
	private function readTwoWaySampleTestResults(&$filePath,&$datasetA,&$datasetB,&$counts,&$totalCounts) {
		
		## open file
		$fh = fopen($filePath, 'r');
		
		## update counts using test results
		$isHeader=true;
		while (!feof($fh)) {
			$line = fgets($fh);
			
			## skip first line which contains header
			if($isHeader || empty($line)) {
				$isHeader=false;
				continue;
			}
			
			## skip last line
			if(empty($line)) {
				continue;
			}
			
			## parse file			
			$fields = explode("\t",$line);
			$id 				   = trim($fields[0]); 
			$counts[$id]['propa']  = trim($fields[3]); 
			$counts[$id]['propb']  = trim($fields[4]); 
			$counts[$id]['oratio'] = trim($fields[5]); 
			$counts[$id]['rrisk']  = trim($fields[6]); 
			$counts[$id]['pvalue'] = trim($fields[7]); 
			$counts[$id]['bvalue'] = trim($fields[8]); 
			$counts[$id]['qvalue'] = trim($fields[9]); 		
		}
		
		fclose($fh);
	}
	
	/**
	 * Reads edgeR test flat file results with the following format:
	 * id,count1,count2,logConc,logFC,disperson,p_value,b_value,q_value
	 *
	 * @param reference absolute path to file
	 * @param reference name of dataset A
	 * @param reference name of dataset B
	 * @param reference original count matrix
	 * @param reference total count matrix
	 * @return void (updates counts directly using &$counts reference
	 * @access private
	 */
	private function readEdgeRTestResults(&$filePath,&$datasetA,&$datasetB,&$counts,&$totalCounts) {
		
		## open file
		$fh = fopen($filePath, 'r');
		
		## update counts using test results
		$isHeader=true;
		while (!feof($fh)) {
			$line = fgets($fh);
			
			## skip first line which contains header
			if($isHeader || empty($line)) {
				$isHeader=false;
				continue;
			}
			
			## skip last line
			if(empty($line)) {
				continue;
			}
			
			## parse file			
			$fields = explode("\t",$line);
			
			$id 				   = trim($fields[0]); 
			$counts[$id]['counta'] = trim($fields[1]); 
			$counts[$id]['countb'] = trim($fields[2]); 
			$counts[$id]['logconc']= trim($fields[3]); 
			$counts[$id]['logfc']  = trim($fields[4]); 	
			$counts[$id]['disp']   = trim($fields[5]); 
			$counts[$id]['pvalue'] = trim($fields[6]); 
			$counts[$id]['bvalue'] = trim($fields[7]); 
			$counts[$id]['qvalue'] = trim($fields[8]); 			
		}
		
		fclose($fh);
	}	
	
	public function executeTwoWaySampleTest(&$selectedDatasets,&$counts,&$totalCounts,&$option) {
		
		$id 		= uniqid();
		$datasetA = $selectedDatasets[0];
		$datasetB = $selectedDatasets[1];

		if($option == CHISQUARE) {
			$test = 'chisq.test';
		}
		elseif($option == FISHER) {
			$test = 'fisher.test';
		}
		elseif($option == PROPORTION_TEST) {
			$test = 'prop.test';
		}		

		$inFilePath 	= METAREP_TMP_DIR."/metarep_".$test."_in.$id.tab";
		$outFilePath 	= METAREP_TMP_DIR."/metarep_".$test."_out.$id.tab";
		
		## write counts to file
		$this->writeTwoWaySampleTestCounts($inFilePath,$datasetA,$datasetB,$counts,$totalCounts);
		chmod($inFilePath, 0777);
		##debug(RSCRIPT_PATH." --vanilla ".METAREP_WEB_ROOT."/scripts/r/two_way_sample_test.r $inFilePath $outFilePath $test ".RELATIVE_COUNT_PRECISION." ".PVALUE_PRECISION);
		exec(RSCRIPT_PATH." --vanilla ".METAREP_WEB_ROOT."/scripts/r/two_way_sample_test.r $inFilePath $outFilePath $test ".RELATIVE_COUNT_PRECISION." ".PVALUE_PRECISION);
		chmod($outFilePath, 0777);		
		## read test results from file
		$this->readTwoWaySampleTestResults($outFilePath,$datasetA,$datasetB,$counts,$totalCounts);
	}
		
	public function executeEdgeRTest($datasets,&$counts) {
		$id = uniqid();

		$startIndexPopulationB	= $this->Session->read('startIndexPopulationB');
		$populations 			= $this->Session->read('populations');
		$mode				 	= $this->Session->read('mode');
		$populationA = $populations[0];
		$populationB = $populations[1];
		
		$inFilePath 	= METAREP_TMP_DIR."/metarep_edge_r_targets_in.$id.tab";
		$outFilePath 	= METAREP_TMP_DIR."/metarep_edge_r_detags_out.$id.tab";
		$test ='';
		$inFile = fopen($inFilePath, 'w');
		fwrite($inFile,"files\tgroup\tdescription\n");	
		
		## generate file for each dataset
		for($i =0; $i< count($datasets);$i++) {
			$targetId = uniqid();
			$dataset = $datasets[$i];
			
			$targetGroup = ($i >= ($startIndexPopulationB-1)) ? $populations[1] : $populations[0];			
			$targetFilePath = METAREP_TMP_DIR."/metarep_".$dataset."_in.$targetId.tab";			
			$fhTargetFile = fopen($targetFilePath, 'w');
			fwrite($fhTargetFile,"Tag ID\tCount\n");
			foreach($counts as $category => $row) {
				fwrite($fhTargetFile,"$category\t{$row[$dataset]}\n");
			}
			fclose($fhTargetFile);
			fwrite($inFile,"$targetFilePath\t$targetGroup\t\n");	
			
		}
		fclose($inFile);
		chmod($inFilePath, 0777);
		debug(RSCRIPT_PATH." --vanilla ".METAREP_WEB_ROOT."/scripts/r/edge_r_test.r $inFilePath $outFilePath ".RELATIVE_COUNT_PRECISION." ".PVALUE_PRECISION." $startIndexPopulationB $populationA $populationB");		
		exec(RSCRIPT_PATH." --vanilla ".METAREP_WEB_ROOT."/scripts/r/edge_r_test.r $inFilePath $outFilePath ".RELATIVE_COUNT_PRECISION." ".PVALUE_PRECISION." $startIndexPopulationB $populationA $populationB");
		chmod($outFilePath, 0777);		
		$this->readEdgeRTestResults($outFilePath,$datasetA,$datasetB,$counts,$totalCounts);
	}
		
	/**
	 * Executes non-parametric Wilcoxon Runk Sum Test comparing
	 * relative counts of two populations.
	 *
	 * @param array $selectedDatasets list of selected datasets
	 * @param reference $counts relative counts
	 * @return Array of counts with test results
	 * @access public
	 */
	function writeWilcoxonMatrix($selectedDatasets,&$counts) {

		$startIndexPopulationB = $this->Session->read('startIndexPopulationB');
		$populations 		   = $this->Session->read('populations');

		$categoryCount = count($counts);
		$populationA = $populations[0];
		$populationB = $populations[1];

		$id = uniqid();
		$inFile = "metarep_wilcox.in_".$id.'.txt';
		$outFile = "metarep_wilcox.out_".$id.'.txt';
		$fh = fopen(METAREP_TMP_DIR."/$inFile", 'w');
			
		$categoryCounter=1;

		$ids = array();
		#execute R code
		exec(R_PATH." --quiet --vanilla < ".METAREP_TMP_DIR."/$inFile > ".METAREP_TMP_DIR."/$outFile");

		$newCounts = array();

		foreach($counts as $category => $row) {
				
			//specify default values
			$newCounts[$category]['sum'] = 0;
			$newCounts[$category][$populationA]['median'] = 0;
			$newCounts[$category][$populationB]['median'] = 0;
			$newCounts[$category][$populationA]['mad'] = 0;
			$newCounts[$category][$populationB]['mad'] = 0;
			$newCounts[$category]['pvalue'] =1;
			$newCounts[$category]['bvalue'] =1;
				
			//specify R commands
			$ids[$categoryCounter] = $category;
			$vectorA 	= "wca_c".$categoryCounter."= c(";
			$vectorB	= "wcb_c".$categoryCounter."= c(";
			$medianA	= "median(wca_c".$categoryCounter.")\n";
			$medianB	= "median(wcb_c".$categoryCounter.")\n";
			$madA		= "mad(wca_c".$categoryCounter.")\n";
			$madB		= "mad(wcb_c".$categoryCounter.")\n";
			$testCommand="wilcox.test(wca_c".$categoryCounter.",wcb_c".$categoryCounter.",alternative=\"two.sided\")\$p.value\n";
				
			//create vector A string
			for($i =0;$i < ($startIndexPopulationB - 1); $i++) {
				$countCategory = $row[$selectedDatasets[$i]];
				//if last element
				if($i == $startIndexPopulationB - 2) {
					$vectorA .= "$countCategory)\n";
				}
				else {
					$vectorA .= "$countCategory,";
				}
			}
				
			//create vector B string
			for($i = $startIndexPopulationB - 1;$i < count($selectedDatasets); $i++) {
				$countCategory = $row[$selectedDatasets[$i]];
				//if last element
				if($i == count($selectedDatasets)-1) {
					$vectorB .= "$countCategory)\n";
				}
				else {
					$vectorB .= "$countCategory,";
				}
			}
				
			//write commands to input file
			fwrite($fh,$vectorA);
			fwrite($fh,$vectorB);
			fwrite($fh,$medianA);
			fwrite($fh,$medianB);
			fwrite($fh,$madA);
			fwrite($fh,$madB);
			fwrite($fh,$testCommand);
			$categoryCounter++;
		}
		fclose($fh);

		//execute R code
		exec(R_PATH." --quiet --vanilla < ".METAREP_TMP_DIR."/$inFile > ".METAREP_TMP_DIR."/$outFile");

		//open result file
		$fh = fopen(METAREP_TMP_DIR."/$outFile", 'r');

		$resultCounter=1;

		//read R results
		while (!feof($fh)) {

			$line = fgets($fh);

			if (substr($line,0,6) == '> wcb_') {
				$resultCounter = 1;
				$line =	str_replace('> wcb_c','',$line);
				$tmp= split("=",$line);
				$id = $tmp[0];
				$category = $ids[$id];
			}
			else if(substr($line,0,3) == '[1]') {
				$value =	round(trim(str_replace('[1] ','',$line)),RELATIVE_COUNT_PRECISION);

				if($resultCounter == 1) {
					
					$newCounts[$category][$populationA]['median'] = round($value,RELATIVE_COUNT_PRECISION)*100; //%median A
					
				}
				else if($resultCounter == 2) {
					$newCounts[$category][$populationB]['median'] = round($value,RELATIVE_COUNT_PRECISION)*100;//%median B
				}
				else if($resultCounter == 3) {
					$newCounts[$category][$populationA]['mad'] =  round($value,RELATIVE_COUNT_PRECISION)*100; //%mad A
				}
				else if($resultCounter == 4) {
					$newCounts[$category][$populationB]['mad'] =  round($value,RELATIVE_COUNT_PRECISION)*100; //%mad B
				}
				else if($resultCounter == 5) {
					$newCounts[$category]['pvalue'] = $value;
					$newCounts[$category]['name'] = $counts[$category]['name'];
						
					if($newCounts[$category][$populationB]['median'] !=0) {
						$newCounts[$category]['mratio']	= round($newCounts[$category][$populationA]['median']/$newCounts[$category][$populationB]['median'],RELATIVE_COUNT_PRECISION/2);
					}
					else {
						$newCounts[$category]['mratio']	 =0;
					}
					$bonfPValue = round($value*$categoryCount,RELATIVE_COUNT_PRECISION);
						
					if($bonfPValue > 1) {
						$bonfPValue = 1;
					}
					$newCounts[$category]['bvalue'] = $bonfPValue;
						
					if($newCounts[$category][$populationA]['median'] == 0 || $newCounts[$category][$populationB]['median'] == 0) {
						unset($newCounts[$category]);
					}
				}
				$resultCounter++;
			}
		}
		fclose($fh);

		$this->Session->write('selectedDatasets',$populations);

		$counts = $newCounts;

		return;
	}
}
?>
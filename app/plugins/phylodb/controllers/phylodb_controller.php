<?php
/***********************************************************
* File: phylodb_controller.php
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

class PhylodbController extends PhylodbAppController {
	var $uses 		= array();	
	var $helpers 		= array('Ajax','Dialog','Phylodb.PhylodbPaginator','PhylodbDialog');
	var $searchFields 	= array('Search By ID' => array( 
											'proteinId'		=> 'Protein ID',											
											'seguId'		=> 'Seguid ID',	
											'contigId' 		=> 'Contig ID',
											'taxonId'		=> 'NCBI Taxonomy (PhyloDB) ID',
											'uniprotId'		=> 'UniProt KB ID',		
											'jgiId'			=> 'IMG/M Object ID',		
											'ncbiId'		=> 'NCBI ID',			
											'ecId'			=> 'Eznyme Commission (EC) ID',		
											'koId'			=> 'KEGG Ortholog (KO) ID',		
											'pathwayId'		=> 'KEGG Pathway ID',
											'pfamId'		=> 'Pfam ID',
											'tigrfamId'		=> 'Tigrfam ID',
											'eggnogId'		=> 'Eggnog ID',
											'extdClusterId'	=> 'GOS Extended Cluster ID',
											'coreClusterId'	=> 'GOS Core Cluster ID',),
	
								'Search By Name' => array( 
											'taxonName'		 => 'NCBI Taxonomy (PhyloDB) Name',
											//'taxonLineage'	 => 'PhyloDB Taxonomy Lineage',
											'extdClusterName'=> 'GOS Extended Cluster Name',
											'pfamId'		 => 'Pfam Name',
											'tigrfamName'	 => 'Tigrfam Name',
											),																															
								);								
	var $numSearchResults = 10;							

	
	/**
	 * protein
	 * 
	 * @param int $peptideId phylodb protein id
	 * @return void
	 * @access public
	 */	
	function protein($id = null,$field=null,$query=null,$page=null) {
		$id = $this->phylodbDecodeUrlString($id);
		
		$this->loadModel('Phylodb.Protein');
		$this->loadModel('KeggOrtholog');
		$this->loadModel('Enzyme');
		$this->loadModel('Hmm');
		$this->loadModel('Cluster');
				   
      	$this->set('title', 'PhyloDB Protein Page');
		
      	## do not load the whole contig sequence 
		$this->Protein->contain('Transcript','Contig.species','Contig.taxon_id',
		'Contig.species','Contig.taxonomy','Contig.name','Contig.description',
		'Contig.form','GeneOrder','KeggAnnotation','PhylodbAnnotation'); 
		
		if(!$id) {
			$this->Session->setFlash(__('Invalid PhyloDB Protein ID.', true));
			$this->redirect(array('action'=>'index'),null,true);
		}
		else {
			$protein =  $this->Protein->read(null, $id);	
		
			if(!empty($protein['KeggAnnotation']['seq_id'])) {
				//split multi-valued pathway fields
				if(!empty($protein['KeggAnnotation']['Pathway'])) {
					$protein['KeggAnnotation']['Pathway'] = explode('||',$protein['KeggAnnotation']['Pathway']);
				}
				//split multi-valued KO fields
				if(!empty($protein['KeggAnnotation']['KO'])) {
					$kos = explode('||',$protein['KeggAnnotation']['KO']);				
					$protein['KeggAnnotation']['KO'] = null;
					foreach($kos as $koAcc) {
						$koEntry = $this->KeggOrtholog->findByKoId($koAcc);
						$protein['KeggAnnotation']['KO'][$koEntry['KeggOrtholog']['ko_id']] = $koEntry['KeggOrtholog']['name'];			
					}		
				}		
				//split multi-valued EC fields
				if(!empty($protein['KeggAnnotation']['EC'])) {
					$enzymes = explode('||',$protein['KeggAnnotation']['EC']);
					$protein['KeggAnnotation']['EC'] = null;
					foreach($enzymes as $enzymeId) {
						$enzymeEntry = $this->Enzyme->findByEcId($enzymeId);
						
						$protein['KeggAnnotation']['EC'][$enzymeEntry['Enzyme']['ec_id']] = $enzymeEntry['Enzyme']['name'];			
					}
				}
			}
			if(!empty($protein['PhylodbAnnotation']['seguid'])) {
				if(!empty($protein['PhylodbAnnotation']['pfam'])) {
					$hmms = explode('||',$protein['PhylodbAnnotation']['pfam']);				
					$protein['PhylodbAnnotation']['pfam'] = null;
					foreach($hmms as $hmmAcc) {
						if(!empty($hmmAcc) && $hmmAcc != '_GAP_') {
							$hmmEntry = $this->Hmm->findByAcc($hmmAcc);
							$protein['PhylodbAnnotation']['pfam'][$hmmEntry['Hmm']['acc']] = $hmmEntry['Hmm']['name'];								
						}		
					}	
				}	
				if(!empty($protein['PhylodbAnnotation']['tigrfam'])) {			
					$hmms = explode('||',$protein['PhylodbAnnotation']['tigrfam']);			
					$protein['PhylodbAnnotation']['tigrfam'] = null;
					foreach($hmms as $hmmAcc) {
						if(!empty($hmmAcc) && $hmmAcc != '_GAP_') {
							$hmmEntry = $this->Hmm->findByAcc($hmmAcc);
							$protein['PhylodbAnnotation']['tigrfam'][$hmmEntry['Hmm']['acc']] = $hmmEntry['Hmm']['name'];			
						}
					}	
				}
				if(!empty($protein['PhylodbAnnotation']['gos_cluster_id'])) {			
					$clusters = explode('||',$protein['PhylodbAnnotation']['gos_cluster_id']);			
					$protein['PhylodbAnnotation']['gos_cluster_id'] = null;
					foreach($clusters as $clusterAcc) {
						if(!empty($clusterAcc)) {						
							$clusterEntry = $this->Cluster->findByClusterId($clusterAcc);		
							$name = $this->Cluster->getDescription($clusterAcc);						
							$protein['PhylodbAnnotation']['gos_cluster_id'][$clusterAcc] =  $name	;		
						}
					}		
				}
				if(!empty($protein['PhylodbAnnotation']['gos_core_cluster_id'])) {	
					$protein['PhylodbAnnotation']['gos_core_cluster_id'] = explode('||',$protein['PhylodbAnnotation']['gos_core_cluster_id']);		
				}		
				if(!empty($protein['PhylodbAnnotation']['jgi_id'])) {
					$protein['PhylodbAnnotation']['jgi_id'] = explode('||',$protein['PhylodbAnnotation']['jgi_id']);
				}						
			}				
		}	
	
		$this->addSaveUrls($protein);
		
		if(sizeof($protein['PhylodbAnnotation']['pfam']) > 0 || sizeof($protein['PhylodbAnnotation']['tigrfam']) > 0) {
			$this->drawFeatureBitMap($protein);
		}
		
		$this->set('protein',$protein);
		$this->set('cleanProteinId',$protein);
		$this->set('cleanSeguid',$protein);
		
		//if parent request came from search page
		$this->set('query',$query);
		$this->set('field',$field);
		$this->set('page',$page);		
	}	
	

	function seguid($id,$field=null,$query=null,$page=null) {
		$id = $this->phylodbDecodeUrlString($id);
		$this->set('title', 'PhyloDB Seguid Page');
		$this->loadModel('Phylodb.Protein');
		$this->loadModel('Phylodb.Contig');
		$this->Protein->contain('Contig.species','Contig.taxon_id',
		'Contig.species','Contig.taxonomy','Contig.name','Contig.description',
		'Contig.form'); 
		$proteins = $this->Protein->find('all', array('conditions' =>array('seguid'=>$id)));
		$this->addSaveUrls($proteins);
		$this->set('proteins',$proteins);
		$this->set('seguid',$id);

		//if parent request came from search page
		$this->set('query',$query);
		$this->set('field',$field);
		$this->set('page',$page);
	}
	
	function search($field="all",$query="*",$page=1) {
		$query = $this->phylodbDecodeUrlString($query);
		
		$this->set('title', 'PhyloDB Search Page');
		$this->loadModel('Phylodb.Protein');
		$this->Protein->contain('Contig.species','Contig.taxon_id',
		'Contig.species','Contig.taxonomy','Contig.name','Contig.description',
		'Contig.form'); 
		
		if($this->data) {
			$field = $this->data['Search']['field'];
			$query = $this->data['Search']['query'];
		}
		
		$query = trim($query);
		

		
		$start = ($page-1)*$this->numSearchResults;
		$stop = $this->numSearchResults;
		
		$proteinFields = array('Protein.name','Protein.contig_name','Protein.annotation','Protein.taxon_id','Protein.seguid');
		
		switch($field) {
			case 'all':	
				$numHits  = 13926004;
				$proteins = $this->Protein->find('all', array(
											 	 'contain'	 => array('Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),											 	 
											 	 'fields'	 => $proteinFields,											 
											 	 'limit' 	 => $start.','.$stop));
				break;			
			case 'proteinId':	
				$numHits  = $this->Protein->find('count',array('fields' => 'COUNT(DISTINCT Protein.name) as count','conditions' =>array("Protein.name"=>$query)));		
				$proteins = $this->Protein->find('all', array(
											 	 'contain'	 => array('Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),
											 	 'conditions'=> array('Protein.name'=>$query),
											 	 'fields'	 => $proteinFields,
											 	 'order' 	 => array('Protein.name'),
											 	 'limit' 	 => $start.','.$stop));
				break;
			case 'seguId':
				$numHits  = $this->Protein->find('count',array('fields' => 'COUNT(DISTINCT Protein.name) as count','conditions' =>array("Protein.seguid"=>$query)));				
				$proteins = $this->Protein->find('all', array(
											 	 'contain'	 => array('Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),
											 	 'conditions'=> array('Protein.seguid'=>$query),
											 	 'fields'	 => $proteinFields,
											 	 'order' 	 => array('Protein.name'),
											 	 'limit' 	 => $start.','.$stop));
				break;
			case 'contigId':
				$numHits  = $this->Protein->find('count',array('fields' => 'COUNT(DISTINCT Protein.name) as count','conditions' =>array("Protein.contig_name"=>$query)));				
				$proteins = $this->Protein->find('all', array(
											 	 'contain'	 => array('Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),
											 	 'conditions'=> array('Protein.contig_name'=>$query),
											 	 'fields'	 => $proteinFields,
											 	 'order' 	 => array('Protein.name'),
											 	 'limit'	 => $start.','.$stop));
				break;				
			case 'taxonId':
				$numHits  = $this->Protein->find('count',array('fields' => 'COUNT(DISTINCT Protein.name) as count','conditions' =>array("Protein.taxon_id"=>$query)));				
				$proteins = $this->Protein->find('all', array(
											 	 'contain'	 => array('Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),
											 	 'conditions'=> array('Protein.taxon_id'=>$query),
											 	 'fields'	 => $proteinFields,
											 	 'order' 	 => array('Protein.name'),
											 	 'limit' 	 => $start.','.$stop));
				break;	
			case 'koId':				
				$result   = $this->Protein->findByAttribute('KeggAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;
			case 'pathwayId':
				$result   = $this->Protein->findByAttribute('KeggAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;	
			case 'uniprotId':
				$result   = $this->Protein->findByAttribute('KeggAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;	
			case 'ecId':
				$result   = $this->Protein->findByAttribute('KeggAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;			
			case 'extdClusterId':
				$result   = $this->Protein->findByAttribute('PhylodbAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;	
			case 'coreClusterId':								 			
				$result   = $this->Protein->findByAttribute('PhylodbAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;	
			case 'jgiId':								 			
				$result   = $this->Protein->findByAttribute('PhylodbAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;	
			case 'ncbiId':								 			
				$result   = $this->Protein->findByAttribute('PhylodbAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;	
			case 'eggnogId':								 			
				$result   = $this->Protein->findByAttribute('PhylodbAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;	
			case 'pfamId':								 			
				$result   = $this->Protein->findByAttribute('PhylodbAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;			
			case 'tigrfamId':								 			
				$result   = $this->Protein->findByAttribute('PhylodbAttribute',$field,$query,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];
				break;																																																
			case 'taxonName':					 
				$numHits  = $this->Protein->find('count',array('contain'=>array('Taxonomy'),
												 'fields' => 'COUNT(DISTINCT Protein.name) as count',
												 'conditions' =>array('Taxonomy.used_taxon' =>1,'Taxonomy.name LIKE'=>"$query%")));				
				$proteins = $this->Protein->find('all', array(
											 	 'contain'	 => array('Taxonomy','Contig.species','Contig.taxon_id','Contig.species','Contig.taxonomy','Contig.name','Contig.description','Contig.form'),
											 	 'conditions'=> array('Taxonomy.used_taxon' =>1,'Taxonomy.name LIKE'=>"$query%"),
											 	 'fields'	 => $proteinFields,
											 	 'order' 	 => array('Protein.name'),
											 	 'limit' 	 => $start.','.$stop)
												);
				break;	
			case 'taxonLineage':	
				$this->Protein->contain('Contig.species','Contig.taxon_id',
				'Contig.species','Contig.taxonomy','Contig.name','Contig.description',
				'Contig.form'); 
				$result = $this->Protein->query("SELECT COUNT(DISTINCT proteins.name) as numHits from proteins INNER JOIN contigs ON(proteins.contig_name=contigs.name) WHERE contigs.taxonomy =\"%$query%\"");
				$numHits = $result[0][0]['numHits'];
				$proteins = $this->Protein->find('all', array('conditions' =>array('Contig.taxonomy LIKE'=>"%$query%"),'fields'=>$proteinFields,'order' => array('Protein.name'),'limit' => $start.','.$stop));				
				break;		
			case 'extdClusterName':
				$this->loadModel('Cluster');
				$nameRes = $this->Cluster->find('list',array(
													'conditions' => array('Cluster.name LIKE' =>"%$query%"),
													'fields'=>'cluster_id'));
				
				$nameArr = array();
				
				foreach ($nameRes as $namePos => $nameId) {
					array_push($nameArr,$nameId);
				}
				
				$result   = $this->Protein->findByAttributes('PhylodbAttribute','extdClusterId',$nameArr,$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];				
				
			break;
			case 'pfamName':
				$this->loadModel('Hmm');
				$list 	  = $this->Hmm->find('list',array(
											 'conditions' => array('Hmm.name LIKE' =>"%$query%", 'Hmm.model'=>'PF'),'fields'=>'acc')
											);
											
				$result   = $this->Protein->findByAttributes('PhylodbAttribute','pfamId',array_keys($list),$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];				

			break;	
			case 'tigrfamName':
				$this->loadModel('Hmm');
				$list 	  = $this->Hmm->find('list',array(
											 'conditions' => array('Hmm.name LIKE' =>"%$query%", 'Hmm.model'=>'TIGR'),'fields'=>'acc')
											);
											
				$result   = $this->Protein->findByAttributes('PhylodbAttribute','tigrfamId',array_keys($list),$proteinFields,$start,$stop);
				$numHits  = $result['numHits'];
				$proteins = $result['proteins'];					
			break;
			
		}
		$this->addSaveUrls($proteins);
		
		$this->set('queryUrlString',$this->phylodbEncodeUrlString($query));
		$this->set('proteins',$proteins);
		$this->set('seguid','');
		$this->set('numHits',$numHits);
		$this->set('query',$query);
		$this->set('searchFields',$this->searchFields);
		$this->set('field',$field);
		$this->set('page',$page);
		$this->set('limit',$this->numSearchResults);
		
	}
	
	private function addSaveUrls(&$proteins) {
		if(isset($proteins['Protein'])) {
			$proteins['Protein']['seguidCount'] = $this->seguidCount($proteins['Protein']['seguid']);
			$proteins['Protein']['seguidUrlString'] = $this->phylodbEncodeUrlString($proteins['Protein']['seguid']);
			$proteins['Protein']['proteinIdUrlString'] = $this->phylodbEncodeUrlString($proteins['Protein']['name']);			
		}
		else {
			foreach($proteins as &$protein) {
				$protein['Protein']['seguidCount'] = $this->seguidCount($protein['Protein']['seguid']);
				$protein['Protein']['seguidUrlString'] = $this->phylodbEncodeUrlString($protein['Protein']['seguid']);
				$protein['Protein']['proteinIdUrlString'] = $this->phylodbEncodeUrlString($protein['Protein']['name']);
				
			}
		}
	}
	
	private function seguidCount($seguid) {
		$this->loadModel('Phylodb.Protein');
		return $this->Protein->find('count',array(
									'fields' => 'COUNT(DISTINCT Protein.name) as count',
									'conditions' => array('Protein.seguid' =>$seguid)));
	}
	
	private function phylodbEncodeUrlString($id) {
		$id = str_replace(':','<>',$id);	
		$id = str_replace('/','[]',$id);	
		$id = str_replace('+','()',$id);	
		return($id);		
	}
	
	private function drawFeatureBitMap(&$protein) {
		$pfamHits 	 = explode("||",$protein['PhylodbAnnotation']['pfamAuto']);
		$tigrfamHits = explode("||",$protein['PhylodbAnnotation']['tigrfamAuto']);
		
		if(sizeof($pfamHits) > 0 || sizeof($tigrfamHits) > 0) {
			$this->loadModel('Phylodb.PhylodbPfam');
			$this->loadModel('Phylodb.PhylodbTigrfam');
			
		
			
			$proteinSeqlength = strlen($protein['Protein']['seq']);
			
			$id 	 = uniqid();
			$inFile  = "phylo_metarep_feature_in_".$id.'.txt';
			$outFile  = "phylo_metarep_feature_in_".$id.'.png';
			$fh = fopen(METAREP_TMP_DIR."/$inFile", 'w');		
			
			fwrite($fh,"TYPE\tHIT\tEVALUE\tSTART\tEND\n");
			
			foreach($pfamHits as $pfamHit) {
				if(!empty($pfamHit)) {
					$hit = $this->PhylodbPfam->findByAuto($pfamHit);
					$pfam  = $hit['PhylodbPfam'];				
					$regions = explode("-",$pfam['region']); 
					fwrite($fh,"PFAM\t{$pfam['pfam']}\t{$pfam['evalue']}\t{$regions[0]}\t{$regions[1]}\n");
				}
			}
			foreach($tigrfamHits as $tigrfamHit) {
				if(!empty($tigrfamHit)) {
					$hit = $this->PhylodbTigrfam->findByAuto($tigrfamHit);
					$tigrfam  = $hit['PhylodbTigrfam'];
					$regions = explode("-",$tigrfam['region']); 
					fwrite($fh,"TIGRFAM\t{$tigrfam['tigrfam']}\t{$tigrfam['evalue']}\t{$regions[0]}\t{$regions[1]}\n");
				}
			}
			
			fclose($fh);	
			exec(PERL_PATH." ".METAREP_WEB_ROOT."/app/plugins/phylodb/scripts/perl/draw_feature_map.pl ".METAREP_TMP_DIR."/$inFile $proteinSeqlength  > ".METAREP_TMP_DIR."/$outFile");
					
			$webRootParts = explode('/',METAREP_WEB_ROOT);
			$webRootDir = $webRootParts[sizeof($webRootParts)-1];
			$protein['featureMapPng'] = DS.$webRootDir.DS.'tmp'.DS.$outFile;			
		}		
	}
	
	private function phylodbDecodeUrlString($id) {
		$id = str_replace('<>',':',$id);	
		$id = str_replace('[]','/',$id);	
		$id = str_replace('()','+',$id);			
		return($id);		
	}	
}
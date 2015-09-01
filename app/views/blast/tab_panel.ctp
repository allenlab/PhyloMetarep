<!----------------------------------------------------------

  File: tab_panel.ctp
  Description: Compare Tab Panel

  The Compare Tab Panel generate several tabs that allows 
  users to indicate the annotation data type they wish to
  compare. Choices are NCBI Taxonomy, Gene Ontology terms,
  KEGG metabolic pathways, Enzyme Classification, HMMs, and
  functional descriptions.

  PHP versions 4 and 5

  METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
  Copyright(c)  J. Craig Venter Institute (http://www.jcvi.org)

  Licensed under The MIT License
  Redistributions of files must retain the above copyright notice.

  @link http://www.jcvi.org/metarep METAREP Project
  @package metarep
  @version METAREP v 1.3.2
  @author Johannes Goll
  @lastmodified 2010-07-09
  @license http://www.opensource.org/licenses/mit-license.php The MIT License
  
<!---------------------------------------------------------->

<?php 

//read session variables
$selectedDatasets 	= $session->read('selectedDatasets'); 
$option				= $session->read('option'); 
$wordCount			= $session->read('wordCount'); 
$optionalDatatypes	= $session->read('optionalDatatypes');
$mode				= $session->read('mode');
$tabs 				= $session->read('tabs');

echo("
<fieldset class=\"comparator-main-panel\">
	<legend>Result Panel</legend>");


echo $ajax->div('tabs');
echo("<ul>");

$inactiveTabs = array();
$currentTabPosition =0;
$tabPosition = 0;

//generate tabs
foreach($tabs as $tab) {
	
	if($tab['function'] === $mode) {
		$currentTabPosition = $tabPosition;
	}
	if(!$tab['isActive']) {
		array_push($inactiveTabs,$tabPosition);
	}
	
	echo("<li >");
		echo $ajax->link("<span>{$tab['tabName']}</span>",array('action'=>$tab['function']), array('update' => 'comparison-results', 'indicator' => 'spinner','title' => 'comparison-results','loading' => 'Element.show(\'spinner\')', 'complete' => 'Element.hide(\'spinner\'); Effect.Appear(\'comparison-results\',{ duration: 0.5 })', 'before' => 'Element.hide(\'comparison-results\')'), null, null, false); 
	echo("</li>");
	$tabPosition ++;
}

echo("<ul>");	
echo $ajax->divEnd('tabs');	
echo("</fieldset>");
?>

<script type="text/javascript">
jQuery(function() {
	jQuery("#tabs").tabs({ spinner: '<img src="/phylo-metarep/img/ajax.gif\"/>' });
	jQuery("#tabs").tabs( "option", "disabled", <?php echo('['.implode(',',$inactiveTabs).']');?>);
	jQuery("#CompareMinCount").val(<?php echo($wordCount);?>);
	 
});

jQuery('img[src$="download-medium.png"]').qtip({
	   content: 'Click to download result panel contents in tab delimited format.',
	   style: 'mystyle' });	
</script>	
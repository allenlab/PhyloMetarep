<!----------------------------------------------------------
  
  File: index.ctp
  Description: Project Index Page
  
  The Project Index Page lists all projects.
  
  PHP versions 4 and 5

  METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
  Copyright(c)  J. Craig Venter Institute (http://www.jcvi.org)

  Licensed under The MIT License
  Redistributions of files must retain the above copyright notice.

  @link http://www.jcvi.org/metarep METAREP Project
  @package metarep
  @version METAREP v 1.3.1
  @author Johannes Goll
  @lastmodified 2010-07-09
  @license http://www.opensource.org/licenses/mit-license.php The MIT License
  
<!---------------------------------------------------------->

<div class="phylodb">
<ul id="breadcrumb">
 	<li><a href="/metarep/dashboard/index" title="Dashboard"><img src="/metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
     <li><?php echo $html->link('Investigators', "/projects/index");?></li>
    <li><?php echo $html->link("$parentPage Dataset", "/$parentPage/index/$dataset");?></li>
    <li><?php echo $html->link('Protein Feature Map', "/features/index/$projectId/$dataset/$peptideId/$parentPage");?></li>
</ul>
<h2><?php __("Protein Feature Map"); ?><span class="selected_library"><?php echo "$peptideId"; ?></span>
</h2>
	<BR>	
	<?php if(!empty($featureMapPng)) :?>			
		<?php 
		if(!empty($featureMapPng)) {				
			echo ("<td style=\"text-align:center;\"><img src=\"$featureMapPng\" name=\"ci_chart\"></td>");	
			
		}
		?>	
	<?php endif;?>					
	</div>
</div>


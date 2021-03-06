<?php
/***********************************************************
* File: go_graph.php
* Description: Gp Graph model
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

class GoGraph extends AppModel {
	var $name 		 = 'GoGraph';
	var $useTable 	 = 'go_graph_path';
	var $primaryKey  = 'id';
	var $recursive  = 0;
	
    var $belongsTo = array(
        'Ancestor' => array(
            'className'    => 'GoTerm',
            'foreignKey' => 'term1_id',
            'dependent'    => true
        ),
        'Descendant' => array(
            'className'    => 'GoTerm',
            'foreignKey' => 'term2_id',
            'dependent'    => true
        )
    );	 
}
?>
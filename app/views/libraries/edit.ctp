<!----------------------------------------------------------
  
  File: edit.ctp
  Description: The Edit Library page lets project admin users
  edit the library description.
   
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

<ul id="breadcrumb">
  	<li><a href="/phylo-metarep/dashboard/index" title="Dashboard"><img src="/phylo-metarep/img/home.png" alt="Dashboard" class="home" /></a></li>
    <li><?php echo $html->link('Investigators', "/projects/index");?></li>
    <li><?php echo $html->link('View Investigator Project', "/projects/view/{$this->data['Library']['project_id']}");?></li>
    <li><?php echo $html->link('Edit Library', "/libraries/edit/{{$this->data['Library']['id']}}");?></li>
</ul>

<style type="text/css">
	.form
	{
		width:80%;
		float:left;
		padding: 0px;
		margin: 0px !important;
		padding: 0px !important;
		
	}
	input {
		width:70%;
		margin: 0px !important;
		padding: 0px !important;
	} 

</style>

<div class="form">
<h2><?php  __('Edit Library');?><span class="selected_library"><?php echo "{$this->data['Library']['name']}"; ?></span></h2>
<?php echo $form->create('Library');?>
	<fieldset>
 		<legend></legend>
	<?php	
		$currentUser 	= Authsome::get();
		$currentUserId 	= $currentUser['User']['id'];	    	        	
       	$userGroup  	= $currentUser['UserGroup']['name'];		
		
		echo $form->input('id');		
		echo $form->input('label',array('type' => 'text','size'=>'30','label' => 'Label (max 30 characters)'));
		echo $form->input('description',array('type' => 'textaerea'));
		echo $form->input('apis_link',array('type' => 'text'));
		
		if($userGroup === ADMIN_USER_GROUP) {
			echo $form->input('project_id');
		}
		else {
			echo $form->hidden('project_id');
		}		
	?>
	</fieldset>
		<fieldset>
 		<legend>Sample Meta Information</legend>
 		<?php
 		echo $form->input('sample_id',array('type' => 'text','size'=>'50','label' => 'Sample Id (max 30 characters)'));
		echo $form->input('sample_date',array('type' => 'date'));
		echo $form->input('sample_habitat',array('type' => 'text','size'=>'50','label' => 'Sample Habitat (use Environmental Ontology)') );
		echo $form->input('sample_depth',array('type' => 'text','size'=>'50','label' => 'Sample Depth [m]') );
		echo $form->input('sample_alitude',array('type' => 'text','size'=>'50','label' => 'Altitude [m]') );
		echo $form->input('sample_latitude',array('type' => 'text','size'=>'50','label' => 'Sample Latitute [ -76.423333]'));
		echo $form->input('sample_longitude',array('type' => 'text','size'=>'50','label' => 'Sample Longitude [38.946833]'));
		echo $form->input('sample_organism', array('type' => 'text','size'=>'50','label' => 'Sample Organism (Pyrmesium parvum Texoma1)'));
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>

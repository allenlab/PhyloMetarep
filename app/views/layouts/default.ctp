<?php
/* SVN FILE: $Id: default.ctp 7945 2008-12-19 02:16:01Z gwoo $ */
/**
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.view.templates.layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @version       $Revision: 7945 $
 * @modifiedby    $LastChangedBy: gwoo $
 * @lastmodified  $Date: 2008-12-18 18:16:01 -0800 (Thu, 18 Dec 2008) $
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php echo $html->charset(); ?>
<title>
	<?php __(METAREP_RUNNING_TITLE.' - '); ?>
	<?php echo $title_for_layout; ?>
</title>

<?php
	echo $html->css('jquery-ui-1.7.2.custom.css');
	echo $html->css('cake.generic');		
	echo $scripts_for_layout;		

	echo $javascript->link(array('prototype'));
	echo $javascript->link(array('scriptaculous'));
	echo $javascript->link(array('jquery/js/jquery-1.3.2.min.js'));		
	echo $javascript->link(array('jquery/js/jquery-ui-1.7.2.custom.min.js'));
	echo $javascript->link(array('jquery.qtip-1.0.0-rc3.min.js'));	
?>
	
<script type="text/javascript">
 jQuery.noConflict();
	
	jQuery(function(){			

		// Dialog			
		jQuery('#dialog').dialog({
			autoOpen: false,
			width: 850,
			modal: true,
			buttons: {
				"Ok": function() { 
					jQuery(this).dialog("close"); 
				},
			}
		});
		
		// Dialog Link
		jQuery('#dialog_link').click(function(){
			jQuery('#dialog').dialog('open');
			return false;
		});

		// Datepicker
		jQuery('#datepicker').datepicker({
			inline: true
		});
		
		// Slider
		jQuery('#slider').slider({
			range: true,
			values: [17, 67]
		});
		
		// Progressbar
		jQuery("#progressbar").progressbar({
			value: 20 
		});
		
		//hover states on the static widgets
		jQuery('#dialog_link, ul#icons li').hover(
			function() { jQuery(this).addClass('ui-state-hover'); }, 
			function() { jQuery(this).removeClass('ui-state-hover'); }
		);
		
	});
</script>


 <script type="text/javascript">
	  var is_production = true;
	  var dev_test = /(-dev)|(-test)/;
	  var hostname = location.hostname;
	
	  if(hostname.search(dev_test) != -1) {
	    is_production = false;
	  } // end if(hostname.search(dev_test) != -1)
	
	  if(is_production) {
	    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	  } // end if(is_production)
	</script>
	<script type="text/javascript">
	  if(is_production) {
	    try {
	      var pageTracker = _gat._getTracker("UA-9809410-3");
	      pageTracker._setDomainName(".jcvi.org");
	      pageTracker._trackPageview();
	    } catch(err) {}
	  } // end if(is_production)
	  
	  jQuery.fn.qtip.styles.mystyle = { // Last part is the name of the style
			   width: 250,
			   background: '#A2D959',
			   color: 'black',
			   textAlign: 'center',
			   border: {
			      width: 1,
			      radius: 4,
			      color: '#6DB33F'
			   },
			   tip: { // Now an object instead of a string
			         corner: 'topLeft', // We declare our corner within the object using the corner sub-option
			         color: '#F99D31',
			         size: {
			            x: 20, // Be careful that the x and y values refer to coordinates on screen, not height or width.
			            y : 8 // Depending on which corner your tooltip is at, x and y could mean either height or width!
			         }
				},
			   name: 'dark' // Inherit the rest of the attributes from the preset dark style
			}
	  
</script>       	
      
<style type="text/css">
	/*demo page css*/	
	.demoHeaders { margin-top: 2em; }
	#dialog_link {padding: .4em 1em .4em 20px;text-decoration: none;position: relative;}
	#dialog_link span.ui-icon {margin: 0 5px 0 0;position: absolute;left: .2em;top: 50%;margin-top: -8px;}
	ul#icons {margin: 0; padding: 0;}
	ul#icons li {margin: 2px; position: relative; padding: 4px 0; cursor: pointer; float: left;  list-style: none;}
	ul#icons span.ui-icon {float: left; margin: 0 4px;}
</style>
</head>
<body>
	
	<div id="container">
		<div id="header">
			<h1></h1>			
		</div>	
		<div id="header-url-panel">
			<ul>
				<li>website <span style="color: #00A4E4;"><a href="<?php echo(METAREP_URL_ROOT)?>"><?php echo(METAREP_URL_ROOT)?></a></span></li>
				<li>source code <span style="color: #00A4E4;"><a href="http://github.com/jcvi/METAREP">http://github.com/jcvi/METAREP</a></span></li>
				<li>blog <span style="color: #00A4E4;"><a href="http://blogs.jcvi.org/tag/metarep">http://blogs.jcvi.org/tag/metarep</a></span></li>
				<li>contact <span style="color: #00A4E4;"><a href="mailto:<?php echo(METAREP_SUPPORT_EMAIL)?>"><?php echo(METAREP_SUPPORT_EMAIL)?></a></span></li>
				</ul>	
		</div>		
		<? 
		if (Authsome::get()):?>
			<?php 
				$currentUser 	= Authsome::get();
				$currentUserId 	= $currentUser['User']['id'];	  
				$username	 	= $currentUser['User']['username'];	    	        	
	       		$userGroup  	= $currentUser['UserGroup']['name'];
	       	?>	       					
		<ul id="menu">			
			<li><?php echo $html->link(__('Quick Navigation', true), array('plugin' => null,'controller'=> 'menus', 'action'=>'quick')); ?></li>
			<li><?php echo $html->link(__('Dataset Search', true), array('plugin' => null,'controller'=> 'search', 'action'=>'all')); ?></li>
			<? if (	$userGroup === ADMIN_USER_GROUP):?>
				<li><?php echo $html->link(__('New Project', true), array('plugin' => null,'controller'=> 'projects', 'action'=>'add')); ?></li>
			<?endif;?>			
			<li><?php echo $html->link(__('Investigators', true), array('plugin' => null,'controller'=> 'projects', 'action'=>'index')); ?> </li>			
			<? if (	$userGroup === ADMIN_USER_GROUP || $userGroup === INTERNAL_USER_GROUP):?>
			<?endif;?>		
			<? if (	$userGroup === ADMIN_USER_GROUP || $userGroup === INTERNAL_USER_GROUP || $userGroup === EXTERNAL_USER_GROUP):?>				
			<li><?php echo $html->link(__('PhyloDB Search', true), array('plugin' => 'phylodb','controller'=> 'phylodb', 'action'=>'search','all')); ?> </li>			
			<?endif;?>		
			<li><?php if($userGroup != GUEST_USER_GROUP && $username != 'jamboree') {echo $html->link(__('Dashboard', true), array('plugin' => null,'controller'=> 'dashboard'));} ?></li>
			<li><?php echo $html->link(__('Log Out', true), array('plugin' => null,'controller'=> 'users', 'action'=>'logout')); ?> </li>
		</ul>	
		<?endif;?>		
		
		<div id="content">	
			<?php echo $html->getCrumbs(' > ','Dash Board'); ?>
			<?php
			   if ($session->check('Message.flash')): $session->flash(); endif; // this line displays our flash messages
			   echo $content_for_layout;
			?>	
		</div>			
		<div id="footer">
		</div>
	</div>
	<?php echo $cakeDebug;  ?>
</body>
</html>
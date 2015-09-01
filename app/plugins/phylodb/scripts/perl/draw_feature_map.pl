#!/usr/local/bin/perl

##################################################################
# Description: phylo-draw-feature-map.pl
# --------------
# Author: jgoll
# Date:   Mar 1, 2012  
##################################################################

use strict;
use warnings;
 
use FindBin qw($Bin);
use lib "$Bin/lib"; 
use DBI;

my $db = DBI->connect("DBI:mysql:phylodb;host=<your-phylodb-host-server>",
			"<your-phylodb-username>", "<your-phylodb-password>", { 'RaiseError' => 0 });
	
my $query ="select description from features where feature_id=?";
my $sth = $db->prepare($query);
 
my $featureFile   = $ARGV[0];   
my $proteinLength = $ARGV[1];  
my $mode = $ARGV[2];  

use Bio::Graphics;
use Bio::SeqFeature::Generic;
 
my $panel = Bio::Graphics::Panel->new(
                                      -length    => $proteinLength,
                                      -width     => 700,
                                      -pad_left  => 10,
                                      -pad_right => 200,
                                      -grid => 1,
                                      -key_style => 'between',
                                      -gridmajorcolor => 'lightgrey',
                                      -gridcolor => 'white'                                        
                                     );
                               
my $full_length = Bio::SeqFeature::Generic->new(
                                                -start => 1,
                                                -end   => $proteinLength,
                                             );
$panel->add_track($full_length,
                  -glyph   => 'arrow',
                  -tick    => 2,
                  -fgcolor => 'black',
                  -double  => 0,
                 );
 
my $pfam = $panel->add_track(
                              -key => 'PFAM',
                              -glyph     => 'graded_segments',
                              -label     => 1,
                              -bgcolor   => '#E31B23',
                              -double  	 => 1, 
                              -min_score => 5,
                              -max_score => 50,                                
                              -font2color  => 'grey',                             
							  -description => sub {
	                                my $feature = shift;
	                                return $feature->each_tag_value('description');	                                	                              
	                               },                                                      
                             );
                             
my $tigrfam = $panel->add_track(
							  -key => 'TIGRFAM',
                              -glyph       => 'graded_segments',
                              -label       => 1,
                              -bgcolor     => '#00A4E4',
                              -font2color  => 'grey',
                              -min_score => 5,
                              -max_score => 50,                              
                              -double  	   => 1,                              
							  -description => sub {
	                                my $feature = shift;
	                                return $feature->each_tag_value('description');	                                	                              
	                               },                             
                             );  
 
my $superfam = $panel->add_track(
							  -key => 'SUPERFAM',
                              -glyph       => 'graded_segments',
                              -label       => 1,
                              -bgcolor     => '#F99D31',
                              -font2color  => 'grey',
                              -min_score => 5,
                              -max_score => 50,                              
                              -double  	   => 1,                              
							  -description => sub {
	                                my $feature = shift;
	                                return $feature->each_tag_value('description');	                                	                              
	                               },                             
                             ); 
                              
my ($phylodb, $tmhmm,$swissprot,$targetp) = undef;
 
if($mode eq 'denovo') {                             
	$phylodb = $panel->add_track(
								  -key => 'PhyloDB (best blast hit)',
	                              -glyph       => 'graded_segments',
	                              -label       => 1,
	                              -bgcolor     => '#6DB33F',
	                              -font2color  => 'grey',
	                              -min_score => 5,
	                              -max_score => 50,                              
	                              -double  	   => 1,                                                 
							  -description => sub {
	                                my $feature = shift;
	                                return $feature->each_tag_value('description');	                                	                              
	                               },                                 
	                             );           

	$swissprot = $panel->add_track(
	 							  -key => 'Swissprot (best blast hit)',
	                              -glyph       => 'graded_segments',
	                              -label       => 1,
	                              -bgcolor     => '#00a4e4',
	                              -font2color  => 'grey',
	                              -min_score => 5,
	                              -max_score => 50,                              
	                              -double  	   => 1,                              
							  -description => sub {
	                                my $feature = shift;
	                                return $feature->each_tag_value('description');	                                	                              
	                               },                               
	                             );   
 	                              
	                             
	$tmhmm = $panel->add_track(
	 							   -key 	   => 'TMHMM',
	                              -glyph       => 'generic',
	                              -label       => 1,
	                              -bgcolor     => 'grey',
	                              -font2color  => 'black',                                              
	                              -double  	   => 1,                                                           
	                             );   

	$targetp = $panel->add_track(
	 							   -key 	   => 'TARGETP',
	                              -glyph       => 'generic',
	                              -label       => 1,
	                              -bgcolor     => 'grey',
	                              -font2color  => 'black',                                              
	                              -double  	   => 1,                                                           
	                             );   	                              
	                             
	;                                                                                                       
}                                                      
open(FILE,$featureFile) ;
while (<FILE>) { # read blast file
	  chomp;
	  next if /^\#/;  # ignore comments
	  my($type,$name,$evalue,$start,$end,$desc) = split(/\t/);
				
		my $feature = undef;
		
		if($type ne 'TMHMM2.0' ) {
			my $description = undef;
			
			if($type eq 'BEST SWISSPROT BLAST HIT' || $type eq 'SUPERFAM' || $type eq 'BEST BLAST HIT' || $type eq 'TargetP Prediction') {
				$description = $desc;
			}
			else {
				$description = &getDescription($name);
			}
			
			my $score= $evalue ;
			if($score == 0 ) { 
			 	$score = 1.0e-99;
			}
			my @tmp = split('e-',lc($score));	
			use POSIX qw( floor );
			$score  = floor($tmp[1]);  
		 
		 	 $feature = Bio::SeqFeature::Generic->new(
		                                              -display_name =>$description ,
		                                              -label       => 1,                                              
		                                              -score        => $score,                                     
		                                              -start        => $start,
		                                              -end          => $end,
													  -tag          => {
                                                                description => "$name ($evalue)",
                                                                evalue => $evalue
                                                               },

		                                             );
		}
		else {
			$feature = Bio::SeqFeature::Generic->new(
		                                              -display_name => $name,
		                                              -label       => 1,                                                                                 
		                                              -start        => $start,
		                                              -end          => $end,
		                                             );			
		}
		
	  if($type eq 'PFAM') {
	  	$pfam->add_feature($feature);
	  }
	  if($type eq 'TIGRFAM') {
	  	$tigrfam->add_feature($feature);
	  } 
	  if($type eq 'SUPERFAM') {
	  	$superfam->add_feature($feature);
	  } 	  
	  
	  if($mode eq 'denovo') { 	
		  if($type eq 'BEST KEGG BLAST HIT' || $type eq 'BEST BLAST HIT') {
		  	$phylodb->add_feature($feature);
		  } 
		  if($type eq 'BEST SWISSPROT BLAST HIT') {
		  	$swissprot->add_feature($feature);
		  } 		  
		  if($type eq 'TMHMM2.0') {
		  	$tmhmm->add_feature($feature);
		  } 
		  if($type eq 'TargetP Prediction') {
		  	$targetp->add_feature($feature);
		  } 		  
	  }      
  
}
close FILE; 
print $panel->png;

sub getDescription() {
	my $id = shift;
	my $description = undef;
	
	## execute query
	my $goId = undef;
	
	$sth->execute($id);
	$sth->bind_col(1, \$description);  	
	
	$sth->fetch;
	return $description;
}
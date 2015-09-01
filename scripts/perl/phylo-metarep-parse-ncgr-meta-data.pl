#! /usr/bin/env perl

##################################################################
# Description: phylo-metarep-parse-ncgr-meta-data.pl
# --------------
# Author: jgoll 
# Date:   Jan 26, 2012
##################################################################

##(1)perl /export/projects/phylo-metagenomics-reports/scripts/perl/phylo-metarep-parse-ncgr-meta-data.pl /usr/local/projects/PHYLO-METAREP/ncgr/meta-data/tab > meta_data_table.tab
## (2)  update ignore  libraries as l,ncgr_meta_data as m set l.description=m.description,l.sample_date=m.date,l.sample_habitat=m.site, l.sample_organism=concat(m.genus,' ',m.species,' ',strain),sample_longitude=m.long,sample_latitude=m.lat,sample_depth=depth,l.sample_ncbi_taxon_id=m.ncbi_taxon_id   where l.sample_id=m.id;
## update ignore  libraries as l,ncgr_meta_data as m set  where l.sample_id=m.id;
use strict;
use warnings;

my $projecDir = $ARGV[0]; 

opendir(DIR,$projecDir);
my @files = readdir(DIR);

foreach my $file (@files) {
	if($file=~ m/\.txt$/) {
		my $library = $file;
		$library=~ s/\.txt//;
	
		open(FILE,"$projecDir/$file");
		my ($extId,$genus,$species,$strain,$ncbiTaxonId,$date,$description,$site,$long,$lat,$depth) = undef;
		
		while(<FILE>) {
			chomp;
			my $line = $_;
			my (undef,$key,undef,$value,undef) = split(/\t/,$line,5);
			
		
			if($key) {
				if($key == 3) {
					$extId = $value;
				}
				elsif($key == 5) {
					$genus = $value;
				}	
				elsif($key == 6) {
					$species = $value;
				}
				elsif($key == 7) {
					$strain = $value;
				}
				elsif($key == 8) {
					$ncbiTaxonId = $value;
				}							
				elsif($key == 10) {
					$date = $value;
				}		
				elsif($key ==35) {
					$lat = $value;
				}	
				elsif($key == 36) {
					$long = $value;
				}	
				elsif($key == 37) {
					$depth = $value;
				}					
				elsif($key == 63) {
					$description = $value;
				}
				elsif($key == 45) {
					$site = $value;
				}					
				
			}
			
		#	
		#	
		#	if($fields[1]) {
		#		$extId = $fields[]
		#		
		#		print $line."\n";
		#	}
		}
		close FILE;
		print "$library\t$extId\t$genus\t$species\t$strain\t$ncbiTaxonId\t$date\t$description\t$site\t$lat\t$long\t$depth\n";
		
	}
}

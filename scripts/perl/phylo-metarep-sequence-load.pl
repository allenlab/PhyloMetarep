#! /usr/bin/env perl

##################################################################
# Description: metarep_sequence_load.pl
# --------------
# Author: jgoll 
# Date:   Nov 8, 2012
##################################################################

use strict;
use warnings;

BEGIN {
    unshift(@INC, '/export/projects/metagenomics-reports/parsers/solr/lib');
}
use MetarepDAO;
use strict;
use Net::FTP;

my $projectId = $ARGV[0];

my $metarepDao  = MetarepDAO->new();
my %paths = $metarepDao->getPaths();

foreach my $library ( keys %paths) {	

	my $result = $paths{$library};
	
	my @tmp = split('\t',$result,2);
	
	my $projectId = $tmp[0];
	my $libraryPath = $tmp[1];
	
	my $pepFile = `ls -t $libraryPath/annotation/input/metagene_mapped_pep.* | head -n 1`;
	chomp $pepFile;

	if($pepFile) {
		if($pepFile =~ m/.gz$/) {
			my $size = `du -sh $pepFile`;
			chomp $size;
			
			my @tmp = split(' ',$size,2);
			print "$projectId\t$pepFile\t$tmp[0]\n";
		}
		else {

		}
	}
	
}

sub clean {
	my ($self,$tmp) = @_;
	chomp($tmp);
	#remove white spaced
	$tmp =~ s/^\s+//g;
	$tmp =~ s/\s+$//g;
		
	return $tmp;
}

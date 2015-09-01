#! /usr/bin/env perl

##################################################################
# Description: metarep_validate_format.pl
# --------------
# Author: jgoll 
# Date:   Aug 20, 2012
##################################################################

use strict;
use warnings;

my $synonymsFile = '<your-installation-path>/db/synonyms.tab';

my $synonyms = &readSynonyms($synonymsFile);

while(<>) {
	chomp;
	my @fields = split(/\t/);	
           
	@fields = &trimArray(@fields); 
	
	my @fieldNames = qw(            
			peptideId 
            libraryId 
            comName 
            comNameSrc
            goId 
            goSrc 
            ecId
            ecSrc
            hmmId
            bestPhylodbTaxon
            blastEvalue
            blastPid
            blastCov
			filter
			koId
			koSrc
			clusterId
			clusterSrc
			apisPhylodbTaxon
			cogId
			cogSrc
			seguidId
			tmhmmId
			locName
			transName
			transSub
			organId
			organTaxon
			organEvalue
			contgId
			weight);
	
	my (
            $peptideId, 
            $libraryId, 
            $comName, 
            $comNameSrc,
            $goId, 
            $goSrc, 
            $ecId,
            $ecSrc,
            $hmmId,
            $bestPhylodbTaxon,
            $blastEvalue,
            $blastPid,
            $blastCov,
			$filter,
			$koId,
			$koSrc,
			$clusterId,
			$clusterSrc,
			$apisPhylodbTaxon,
			$cogId,
			$cogSrc,
			$seguidId,
			$tmhmmId,
			$locName,
			$transName,
			$transSub,
			$organId,
			$organTaxon,
			$organEvalue,
			$contgId,
			$weight,
           ) = @fields;	
	#print $peptideId."\n";  	
	

	
	for(my $i = 0; $i<= @fields;$i++) {		
		print $fieldNames[$i]."\t:".$fields[$i].":\n";   
		
	}
	               	      
}

sub readSynonyms {
	my $file = shift;
	open(FILE,$file);
	my $result = undef;
	while(<FILE>) {
		my ($badName, $goodName) = split(/\t/);
		if(defined($badName) && defined($goodName)) {
			$result->{lc($badName)} = $goodName;
		}
	}
	return $result;
}

sub trimArray() {
	my (@array) = @_;
	my @cleanArray=();
	foreach(@array) {
		push(@cleanArray,&clean($_));
	}
	return @cleanArray;
}

sub clean {
	my $tmp = shift;
	unless($tmp) {
		return $tmp;
	}
	
	## remove white spaces
	$tmp =~ s/^\s+//g;
	$tmp =~ s/\s+$//g;		
	return $tmp;
}

#! /usr/bin/env perl

##################################################################
# Description: phylo-metarep-index-sequences.pl
# --------------
# Author: jgoll 
# Date:   Jan 24, 2012
##################################################################

use strict;
use warnings;
use DBI;
use Cwd;

my $sequenceStore = "<your-seqstor-path>";

my $db = DBI->connect("DBI:mysql:<your-mysql-database>;host=<your-mysql-host-server>",
		"<your-mysql-username>", "<your-mysql-password>", { 'RaiseError' => 1 });
		
my $projectDir = $ARGV[0];
my $projectId  = $ARGV[1];

opendir(DIR,$projectDir) || die "could not open $projectDir";

my $cwd = getcwd();

my @libraries = readdir(DIR);

foreach my $library(@libraries) {
	
	my $pepFile =  "$projectDir/$library/peptides.fa";

	if(-e $pepFile) {
		print $pepFile."\n";
		print "$sequenceStore/$projectId/$library\n";
		
		if(-d "$sequenceStore/$projectId/$library") {
			`rm -rf $sequenceStore/$projectId/$library`;
		}
		
		`mkdir $sequenceStore/$projectId/$library`;
		
		`cp -a $pepFile $sequenceStore/$projectId/$library/$library.fasta`;
		
		## replace controlled character | in sequence 
		`sed -i 's/|/@/'  $sequenceStore/$projectId/$library/$library.fasta`;		
		
		## change directory to sequence store; needed for formatdb
		chdir("$sequenceStore/$projectId/$library");
		
		## start indexing process	
		print("indexing $library peptides...\n");
		print "formatdb -i $library.fasta -p T -o T -t $library -n $library\n";
		`formatdb -i $library.fasta -p T -o T -t $library -n $library`;
		
		## clean-up
		`rm $sequenceStore/$projectId/$library/$library.fasta`;
		chdir("$cwd");
		
		## query
		my $query ="update libraries set has_sequence=1 where name = ?";
		
		## prepare query
		my $sth =$db->prepare($query);
		
		## execute query
		$sth->execute($library) or die "Couldn't execute: $DBI::errstr";		
	}
}

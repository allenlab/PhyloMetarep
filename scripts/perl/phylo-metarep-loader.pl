#! /usr/bin/env perl

###############################################################################
# File: phylo-metarep-loader.pl
# Description: Loads annotation data into METAREP.

# METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
# Copyright(c)  J. Craig Venter Institute (http://www.jcvi.org)
#
# Licensed under The MIT License
# Redistributions of files must retain the above copyright notice.
#
# link http://www.jcvi.org/metarep METAREP Project
# package metarep
# version METAREP v 1.3.4
# author Johannes Goll
# lastmodified 2011-06-02
# license http://www.opensource.org/licenses/mit-license.php The MIT License
###############################################################################

use strict;
use DBI();
use File::Basename;
use Encode;
use utf8;
use Getopt::Long qw(:config no_ignore_case no_auto_abbrev);
use Pod::Usage;
use Log::Log4perl qw(:easy);
use Cwd 'abs_path';
use Cwd;


=head1 NAME
phylo-metarep-loader.pl script to import transcriptome annotations into PHYLO-METAREP projects

=head1 SYNOPSIS
phylo_metarep_import.pl --project_id 1 --format uniref --mode prok --project_dir /usr/local/depot/projects/GOS 

perl scripts/perl/metarep_loader.pl --project_id 1 --project_dir data/tab --format=tab --sqlite_db db/metarep.sqlite3.db 
--solr_master_url http://localhost:1234 --solr_instance_dir <SOLR_SERVER_HOME_DIR>/metarep-solr 
--mysql_host localhost --mysql_db ifx_hmp_metagenomics_reports --mysql_username metarep --mysql_password metarep
--tmp_dir /usr/local/scratch 

=head1 OPTIONS
B<--project_id (required), -i>
	Specify the Phylo-METAREP project id. 

B<--ncgr_dir (required), -d>
	Specify the NCGR project directory that contains the peptide files. 
	
B<--jcvi_dir (required), -d>
	Specify the JCVI project directory that contains the annotation files. 

B<--format (optional), -f>
	Specify the annotation format to import legacy annotations
	Default: phylodb-1.0.5 
				
B<--sqlite_db (optional), -b>
	Specify the phylo-metarep sqlite database to use to fetch lookup information from; 
	default location: /usr/local/projects/MTP/shared/db/metarep/metarep.sqlite3.phylodb1.05.db

B<--seq_store, -o>
	Define location for web server seq-store directory to store sequences and other lookup information
	e.g. spike:/opt/www/phylo-metarep/htdocs/phylo-metarep/app/webroot/seq-stor
	Needs to be synchronized with metarep seq-stor configuration property
		
B<--solr_url, -m>
	METAREP solr master server URL incl. port [default: http://localhost:1234]

B<--solr_slave_url, -s>
	METAREP solr slave server URL incl. port [default: http://localhost:1234]

B<--solr_instance_dir, -w>
	Solr instance (configuration) directory (<SOLR_HOME>/metarep-solr)

B<--solr_data_dir, -y>
	Solr index directory [default: <solr-instance-dir>/data]
	
B<--solr_max_mem, -z>
	Solr maximum memory allocation [default: 1G]
	
B<--mysql_host, -s>
	METAREP MySQL host incl. port [default: localhost:3306]

B<--mysql_db, -b>
	METAREP MySQL database name [default: metarep]
	
B<--mysql_username, -u>
	METAREP MySQL username
	
B<--mysql_password, -p>
	METAREP MySQL password

B<--tmp_dir, -y>
	Directory to store temporary files (XML files generated before the Solr load)

B<--xml_only (optional), -x>
	Specify this flag for debugging the METAREP enriched annotations. Datatsets are not created 
	and data is not loaded but XML files are generated under /usr/local/scratch/metarep/<project_id> 
	that are the precursor files for the indexing process. 
	
B<--max_num_docs, -x>
	The maximum number of docs to split XML files into.


=head1 AUTHOR

Johannes Goll

=cut

my $initialJavaHeapSize = '250M';

## specify log4j logger
Log::Log4perl->easy_init(
		{ level => $INFO, file => 'stdout', layout=>"%d{yyyy-M-d hh:mm:ss} METAREP-%p\t%m%n"});
		
my $log = get_logger();

my %args = ();

## handle user arguments
GetOptions(
	\%args,                
	'version', 	
	'project_id|i=s',
	'format|f=s',
	'project_dir|d=s',
	'jcvi_dir|j=s',
	'ncgr_dir|n=s',		
	'sqlite_db|q=s',
	'solr_url|m=s',
	'solr_slave_url|s=s',
	'solr_instance_dir|w=s',
	'solr_data_dir|h=s',
	'solr_max_mem|z=s',
	'mysql_host|m=s',
	'mysql_db|b=s',
	'mysql_username|u=s',
	'mysql_password|p=s',
	'seq_store|o=s',
	'tmp_dir|y=s',	
	'xml_only|x',	
	'help|man|?',
	'solr_home_dir|h=s', ## legacy argument
) || pod2usage(2);


my $jcviLibName = basename($args{jcvi_dir});
my $ncgrLibName = basename($args{ncgr_dir});


## print help
if($args{help}) {
	pod2usage(-exitval => 1, -verbose => 2);
}

## validate arguments
if ($jcviLibName ne $ncgrLibName) {
	pod2usage(
		-message => "\n\nFORMAT EXCEPTION: The JCVI and NCGR directories do not match.\n",
		-exitval => 1,
		-verbose => 1
	);
}
elsif(!defined($args{jcvi_dir}) || !(-d $args{jcvi_dir})) {		
		pod2usage(
			-message =>
"\n\nERROR: A valid JCVI project directory needs to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}	
elsif(!defined($args{seq_store})) {		
		pod2usage(
			-message =>
"\n\nERROR: A sequence store needs to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}	
if(!defined($args{ncgr_dir}) || !(-d $args{ncgr_dir})) {		
		pod2usage(
			-message =>
"\n\nERROR: A valid NCGR project directory needs to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}
if(defined($args{format}) && $args{format} ne 'phylodb-1.0.5' ) {
	pod2usage(
		-message => "\n\nINCORRECT ARGUMENT: Format not supported. Supported mode arguments are [phylodb-1.0.5].\n",
		-exitval => 1,
		-verbose => 1
	);
}
if(!defined($args{sqlite_db})) {
	pod2usage(
			-message =>
"\n\nERROR: A sqlite database neeeds to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}
if(defined($args{slite_db}) && ! -e $args{slite_db}) {
	pod2usage(
		-message => "\n\nERROR: Specified phylo-metarep sqlite database does not exist.\n",
		-exitval => 1,
		-verbose => 1
	);
}
if(!defined($args{project_id})) {
	pod2usage(
		-message => "\n\nERROR: A project id needs to be defined.\n",
		-exitval => 1,
		-verbose => 1
	);
}	
elsif(!defined($args{tmp_dir}) || !(-d $args{tmp_dir})) {
	pod2usage(
			-message =>
"\n\nERROR: A valid tmp directory needs to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}	
elsif(!defined($args{mysql_username})) {
	pod2usage(
			-message =>
"\n\nERROR: A METAREP MySQL username needs to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}
elsif(!defined($args{mysql_password})) {
	pod2usage(
			-message =>
"\n\nERROR: A METAREP MySQL password needs to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}
elsif(!defined($args{solr_instance_dir}) && !$args{xml_only}) {
	pod2usage(
			-message =>
"\n\nERROR: A Solr instance directory needs to be defined.\n",
			-exitval => 1,
			-verbose => 1
	);
}




## get installation directory
my $scriptPath =  abs_path($0)."\n\n";
my $rootDir = $scriptPath;
$rootDir =~ s/\/scripts\/perl\/phylo-metarep-loader\.pl//;
$rootDir = &clean($rootDir);

## set global variables
my $koAncestorHash = undef;
my $goAncestorHash = undef;
my $taxonAncestorHash = undef;

## set default arguments
if(!defined($args{solr_url})) {
	$args{solr_url} = "http://localhost:1234";
}
if(!defined($args{mysql_host})) {
	$args{mysql_host} = "localhost:1235";
}
if(!defined($args{mysql_db})) {
	$args{mysql_db} = "phylo-metarep";
}
if(!defined($args{solr_max_mem})) {
	$args{solr_max_mem} = '1G';
}
if(!defined($args{solr_data_dir})) {
	$args{solr_data_dir} = "$args{solr_instance_dir}/data";
}
if(!defined($args{max_num_docs})) {
	$args{max_num_docs} = 300000;
}
if(!defined($args{sqlite_db})) {
	if( -e "$rootDir/db/phylo-metarep.sqlite3.db.gz") {
		pod2usage(
				-message =>
"\n\nERROR: Please unzip SQLite database at $rootDir/db/phylo-metarep.sqlite3.db.gz.\n",
				-exitval => 1,
				-verbose => 1
		);		
	}
	elsif( -e "$rootDir/db/phylo-metarep.sqlite3.db") {
		$args{sqlite_db} = "$rootDir/db/phylo-metarep.sqlite3.db";
	}
	else {
		pod2usage(
				-message =>
"\n\nERROR: Cannot find SQLite database at $rootDir/db/phylo-metarep.sqlite3.db.\n",
				-exitval => 1,
				-verbose => 1
		);		
	}
}

## connect to Phylo-METAREP MySQL database
$log->info("Trying to connect to MySQL database=".$args{mysql_db}." host=".$args{mysql_host});
my $metarepDbConnection = DBI->connect_cached("DBI:mysql:".$args{mysql_db}.";host=".$args{mysql_host}."",$args{mysql_username},$args{mysql_password}, { 'RaiseError' => 1 });

if(!$metarepDbConnection) {
	$log->logdie("ERROR: Could not connect to $args{mysql_db} MySQL database.");
}
$log->info("Sucessfully connected to MySQL database.");


## connect to Phylo-METAREP SQLite database
$log->info("Trying to connect to SQLite database=$args{sqlite_db}.");

my $sqliteDbConnection = DBI->connect( "dbi:SQLite:$args{sqlite_db}",
									 "", "", {PrintError=>1,RaiseError=>1,AutoCommit=>0} );	
if(!$sqliteDbConnection) {	
	$log->logdie("ERROR: :Could not connect to SQLite database $args{sqlite_db}.");
}
$log->info("Sucessfully connected to Sqlite database.");

## increase memory by increasing SQLITE cache_size
$sqliteDbConnection->do("PRAGMA cache_size = 40000");
$log->info("Set Sqlite PRAGMA cache_size = 40000.");

## create lucene index from phylodb annotation file
&createIndexPhylodbTabFile("$args{jcvi_dir}/$jcviLibName.phylodb.tab");

## clean up xml files
`rm $args{tmp_dir}/*.xml`;

## index sequences
&indexSequences($args{project_id},$ncgrLibName,"$args{ncgr_dir}/$ncgrLibName/peptides.fa");

## copy apis tree files
$log->info("cp $args{jcvi_dir}/$jcviLibName.feature.tab  $args{tmp_dir}/$args{project_id}/$jcviLibName/feature.tab");
`cp -a $args{jcvi_dir}/$jcviLibName.feature.tab  $args{tmp_dir}/$args{project_id}/$jcviLibName/feature.tab`;
`mkdir $args{tmp_dir}/$args{project_id}/$jcviLibName/tree`;

## copy apis tree files
$log->info("cp -a $args{jcvi_dir}/apis_tree/* $args{tmp_dir}/$args{project_id}/$jcviLibName/tree");
`cp -a $args{jcvi_dir}/apis_tree/* $args{tmp_dir}/$args{project_id}/$jcviLibName/tree`;

## synchronize seq-store on server
$log->info("rsync -r  $args{tmp_dir}/* $args{seq_store}");
`rsync -r  $args{tmp_dir}/* $args{seq_store}`;

$log->info("Successfully completed the load.");

########################################################
## Parses PhyloDB tab delimited input file.
########################################################

sub createIndexPhylodbTabFile() {

	my $file =shift;
	
	my $fistLine= 1;
	my $hasKo = 1;
	my $isWeighted = 1;
			
	open FILE, "$file" or die "Could not open file $file.";
		
	## parse dataset name from file		
	my $datsetName = basename($file);
	$datsetName =~ s/\.phylodb\.tab//;
	
	## create index file
	&openIndex($datsetName);
	
	## count documents and XML files
	my $xmlSplitSet  = 2;
	my $numDocuments = 1;	
		
	while(<FILE>) {
		## ignore first line	
		if($fistLine) {
			$fistLine=0;
			next;
		}		
	
		chomp $_;
			
		##init local variables	
		my ($blastTree,$blastSpecies,$blastEvalueExponent,$goTree,$koTree,$apisTree);
		
		## get fields and trim white spaces
		my @fields = split("\t",  $_);	
		@fields = &trimArray(@fields);				
			
		## read phylodb annotation fields		 
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
			$contigId,
			$weight,
           ) = split("\t",$_);	 
	       
       	## get GO lineage     
       	if($goId) {         		  
        	$goTree = join('||',&getGoAncestors($goId));
       	}
       	
       	## get KO lineage           
        if($koId) {        	
        	$koTree = join('||',&getKoAncestors($koId));  
        }    
	               
        ## set Blast tree and Blast species based on phylodb taxon
        if($bestPhylodbTaxon) {        	
        
        	## get all parent taxa	
	        my @taxonAncestors = &getTaxonAncestors($bestPhylodbTaxon);
	        $blastTree = join('||',@taxonAncestors);
	        
	        ## set the species 
	 		$blastSpecies = &getSpecies(\@taxonAncestors);	
        }	
		
		## set Blast percent identity
	    if($blastPid =~ m/^[1-9].*/) {
	    	$blastPid = $blastPid/ 100;
	    }
           
        ## set Blast Evalue exponent 
		if($blastEvalue == 0) {
				#set evalue to high default value
			$blastEvalueExponent = 9999;
		}	
		else {
			my @tmp = split('e-',lc($blastEvalue));	
			use POSIX qw( floor );
			$blastEvalueExponent  = floor($tmp[1]);
		}
			
		## get APIS tree	
		my @apisAncestors = &getTaxonAncestors($apisPhylodbTaxon);
		$apisTree = join('||',@apisAncestors);
		 
		 	       
		print INDEX "<doc>\n";		
			
		## write core fields
		&printSingleValue('peptide_id',$peptideId);
		&printSingleValue('library_id',$libraryId);
		&printMultiValue('com_name',$comName);
		&printMultiValue('com_name_src',$comNameSrc);
		&printMultiValue('go_id',$goId);
		&printMultiValue('go_src',$goSrc);
		&printMultiValue('go_tree',$goTree);
		&printMultiValue('ec_id',$ecId);
		&printMultiValue('ec_src',$ecSrc);		
		&printMultiValue('hmm_id',$hmmId);		
		&printMultiValue('filter',$filter);
		&printMultiValue('ko_id',$koId);
		&printMultiValue('ko_src',$koSrc);
		&printMultiValue('kegg_tree',$koTree);
		&printSingleValue('weight',$weight);
		
		## write best Blast hit fields
		&printSingleValue('blast_species',$blastSpecies);
		&printSingleValue('blast_evalue',$blastEvalue);
		&printSingleValue('blast_evalue_exp',$blastEvalueExponent);
		&printSingleValue('blast_pid',$blastPid);
		&printSingleValue('blast_cov',$blastCov);	
		&printMultiValue( 'blast_tree',$blastTree);			
		
		## optional fields
		&printMultiValue('apis_tree',$apisTree);		
		&printMultiValue('filter',$filter);	
		&printMultiValue('cluster_id',$clusterId);		
		&printMultiValue('scaf_id',$contigId);		
						
		print INDEX "</doc>\n";		        

		if(($numDocuments % $args{max_num_docs}) == 0) {
				&nextIndex($datsetName,$xmlSplitSet);
				$xmlSplitSet++;
		} 	
			
		$numDocuments++;	

	}
		
	&closeIndex();
	
	## push index if xml only option has not been selected
	unless($args{xml_only}) {
		&pushIndex($datsetName,$xmlSplitSet,$isWeighted,$hasKo);
	}
}

sub indexSequences() {
	my ($projectId,$library,$pepFile) =  @_;
	my $cwd = getcwd();
	$log->info("Indexing $pepFile\n");
	print "$args{tmp_dir}/$projectId/$library\n";
			
	## create library folder	
	`mkdir -p $args{tmp_dir}/$projectId/$library`;
		
	## copy sequence file	
	`cp -a $pepFile $args{tmp_dir}/$projectId/$library/$library.fasta`;
		
	## replace controlled character | in sequence 
	`sed -i 's/|/@/'   $pepFile $args{tmp_dir}/$projectId/$library/$library.fasta`;		
		
	## change directory to sequence store; needed for formatdb
	chdir("$args{tmp_dir}/$projectId/$library");
		
	## start indexing process	
	$log->info("formatdb -i $library.fasta -p T -o T -t $library -n $library");
	`formatdb -i $library.fasta -p T -o T -t $library -n $library`;
	
	## clean-up
	`rm $args{tmp_dir}/$projectId/$library/$library.fasta`;
	chdir("$cwd");
	
	## query
	my $query ="update libraries set has_sequence=1 where name = ?";
	
	## prepare query
	my $sth =$metarepDbConnection->prepare($query);
	
	## execute query
	$sth->execute($library) or die "Couldn't execute: $DBI::errstr";	
		
}

########################################################
## Clean store
########################################################

$metarepDbConnection->disconnect;
$sqliteDbConnection->disconnect();

########################################################
## Creates new index file
########################################################

sub openIndex {
	my $dataset = shift;
	my $outFile	= "$args{tmp_dir}/$dataset"."_1.xml";
	$log->info("Creating Solr XML file $outFile");	
	open(INDEX, ">$outFile") || die("Could not create file $outFile.");
	print INDEX "<add>\n";
}

########################################################
## Closed current index file and opens a new index file.
########################################################

sub nextIndex {
	my ($dataset,$xmlSplitSet) = @_;
	
	## close existing index
	&closeIndex();
	
	## define next index file
	my $outFile	= "$args{tmp_dir}/$dataset"."_".$xmlSplitSet.".xml";
	
	## save filehandle in variable	
	open(INDEX, ">$outFile") || die("Could not create file $outFile.");	

	print INDEX "<add>\n";
}

########################################################
## Closed index file.
########################################################

sub closeIndex {
	print INDEX "</add>";
	close INDEX;
}

########################################################
## Pushes new index file to Solr server; adds MySQL dataset
########################################################
sub pushIndex() {
	my ($dataset,$xmlSplitSet,$isWeighted,$hasKo) = @_;

	$log->info("Deleting dataset $dataset from METAREP MySQL database.");
	&deleteMetarepDataset($dataset);
	
	$log->info("Deleting Solr master core $dataset (if exists).");
	&deleteSolrCore($dataset,$args{solr_url});
	
	$log->info("Creating Solr master core $dataset (if exists).");
	&createSolrCore($dataset,$args{solr_url});
	
	## do the same for the slave server if defined	
	if(defined($args{solr_slave_url})) {
		$log->info("Deleting/creating Solr slave core $dataset (if exists).");
		&deleteSolrCore($dataset,$args{solr_slave_url});
		&createSolrCore($dataset,$args{solr_slave_url});
	}
	
	for(my $set = 1; $set < $xmlSplitSet;$set++){
		my $xmlFile = "$dataset"."_".$set.".xml";
		
		$log->info("Posting Solr XML file to Solr master server $dataset core.");
		&loadSolrIndex($dataset,$xmlFile);
	
		$log->info("Optimizing Solr master $dataset core index.");
		&optimizeIndex($dataset);
	}
	
	$log->info("Insert dataset $dataset into METAREP MySQL database.");
	&createMetarepDataset($dataset,$isWeighted,$hasKo);	
}

########################################################
## Adds lucene document to lucene index
########################################################

sub addDocument() {
	my ($peptideId,$libraryId,$comName,$comNameSrc,$goId,$goSrc,$goTree,$ecId,$ecSrc,
        $hmmId,$blastSpecies,$blastEvalue,$blastEvalueExponent,$blastPid,$blastCov,
        $blastTree,$filter,$weight,$koId,$koSrc,$koTree) = @_;	 
	

}

########################################################
## Writes a single values field to the lucene index.
########################################################

sub printSingleValue {
	my ($field,$value) = @_;
	
	$value = &clean($value);
	
	if($value ne '') {		
		print INDEX "<field name=\"$field\">$value</field>\n";	
	}
}

########################################################
## Writes multi-valued fields.
########################################################

sub printMultiValue() {
	my ($field,$value) = @_;
	
	$value = &clean($value);
		
	my @values = split(/\|\|/, $value);
	
	if(@values>0) {
		foreach(@values){
			$value = &clean($_);
			
			if($value) {								
				print INDEX "<field name=\"$field\">". $value."</field>\n";	
			}
		}
	}
}

########################################################
## Takes a species taxon id and returns an array that contains its lineage
########################################################

sub getTaxonAncestors() {
	my $taxonId = shift;
	my @ancestors = ();

	if(exists $taxonAncestorHash->{$taxonId}) { 
		@ancestors = @{$taxonAncestorHash->{$taxonId}};
	}
	else {
		## add taxon id to the front of the array
		unshift(@ancestors,$taxonId);
			
		## loop through tree until root has been reached
		while(1) {
			my $parentTaxonId = &getParentTaxonId($taxonId);
			
			## add parent to the front of the array if is non-empty
			if($parentTaxonId ne '') {			
				unshift(@ancestors,$parentTaxonId);			
			}	
			
			## stop if root has been reached or empty taxon ID has been returned	
			if($parentTaxonId == 1 || $parentTaxonId eq ''){
				last;
			}	
					
			$taxonId = $parentTaxonId;
		} 
		
		$taxonAncestorHash->{$taxonId} = \@ancestors;		
	}
	return @ancestors;
}

########################################################
## Returns array of KO ancestors (integer part of the ID).
########################################################
sub getKoAncestors(){
	
	my $koTerms = shift;
	
	my @ancestors=();
	
	if(exists $koAncestorHash->{$koTerms}) { 
		@ancestors = @{$koAncestorHash->{$koTerms}};
	}
	else {
			
		my $id = undef;	
		
		my @koTerms = split (/\|\|/, $koTerms);
		
		@koTerms = &cleanArray(@koTerms);
		
		my $koTermSelection = join ',',map{qq/'$_'/} @koTerms;
		
		$koTermSelection = "($koTermSelection)";	

		my $query = "select distinct parent_pathway_id from pathway_ko where pathway_id in (select parent_pathway_id from pathway_ko where pathway_id in(select parent_pathway_id from pathway_ko where pathway_id in (select parent_pathway_id from pathway_ko where ko_id in $koTermSelection))) union
					select distinct parent_pathway_id from pathway_ko where pathway_id in(select parent_pathway_id from pathway_ko where pathway_id in (select parent_pathway_id from pathway_ko where ko_id in $koTermSelection)) union
					select distinct parent_pathway_id from pathway_ko where pathway_id in (select parent_pathway_id from pathway_ko where ko_id in $koTermSelection) union 
					select distinct parent_pathway_id from pathway_ko where ko_id in $koTermSelection";
							
		my $sth = $sqliteDbConnection->prepare($query);	
		$sth->bind_col(1, \$id);
		$sth->execute();	
		
		while ($sth->fetch) {		
			push(@ancestors,$id);
		}
		$koAncestorHash->{$koTerms} = \@ancestors;
	}
		
	return @ancestors;
}

########################################################
# Returns array of GO ancestors (integer part of the ID).
########################################################

sub getGoAncestors(){

	my $goTerms = shift;
	my @ancestors=();
	
	if(exists $goAncestorHash->{$goTerms}) { 
		@ancestors = @{$goAncestorHash->{$goTerms}};
	}
	else {
		
		my @goTerms = split (/\|\|/, $goTerms);

		@goTerms = &cleanArray(@goTerms);
		
		my $goTermSelection = join 'or term.acc=',map{qq/'$_'/} @goTerms;
		
		$goTermSelection = "(go_term.acc=$goTermSelection)";
		
		my $ancestor;

		## SQLITE query
		my $query = "select DISTINCT substr(ancestor.acc,4,length(ancestor.acc)) 
		FROM go_term INNER JOIN go_graph_path ON (go_term.go_term_id=go_graph_path.go_term2_id) INNER JOIN go_term
		 AS ancestor ON (ancestor.go_term_id=go_graph_path.go_term1_id) WHERE $goTermSelection and
		  ancestor.acc!='all' order by distance desc;";

		my $sth = $sqliteDbConnection->prepare($query);	
		$sth->execute();
	
		$sth->bind_col(1, \$ancestor);
		
		while ($sth->fetch) {
				
			##check if numeric
			if ($ancestor =~ /^[0-9]+$/ ) {
				
				#remove trailing zeros
				$ancestor =~ s/^0*//;
			
				#print $ancestor ."\n";	
				push(@ancestors,$ancestor);
			}
		}	
		$goAncestorHash->{$goTerms} = \@ancestors;
	}	
	
	return @ancestors;
}

########################################################
## Returns parent taxon id (PhyloDB taxonomy).
########################################################

sub getParentTaxonId() {
	my $speciesId = shift;
	my $parentTaxonId;
 	
	my $query ="select parent_phylodb_taxon_id from phylodb_taxon where phylodb_taxon_id=?" ;
	
	#execute query
	my $sth = $sqliteDbConnection->prepare($query);
	$sth->execute($speciesId);

	$sth->bind_col(1, \$parentTaxonId);
	$sth->fetch;
	
	return $parentTaxonId;
}

########################################################
## Returns species level of taxon; returns 'unresolved' string if taxon is higher than species.
########################################################

sub getSpecies() {
	my $ancestors = shift;
	my $species = 'unresolved';
	my $query = '';
		
	my @ancestors = reverse(@$ancestors);
	
	if(@ancestors == 1) {
		$query ="SELECT name FROM phylodb_taxon WHERE rank = 'species' AND phylodb_taxon_id = $ancestors[0]" ;
	}
	elsif(@ancestors > 1) {
	 	$query ="SELECT name FROM phylodb_taxon WHERE rank = 'species' AND phylodb_taxon_id IN(".join(',',@ancestors).")" ;
	}
	else{
		return $species;	
	}
	 	
 	my $sth = $sqliteDbConnection->prepare($query);
 	$sth->execute();	
	$sth->bind_col(1, \$species);
	$sth->fetch;

	return $species;
}



########################################################
## Get least common ancestor.
########################################################

sub getLeastCommonAncestor() {
	my ($taxonIdA,$taxonIdB) = @_;
	
	## if both are at the same taxon level
	if($taxonIdA == $taxonIdB) {
		return $taxonIdA;
	}
	else {
		## get lineage for both taxa sorted by lowest (species) to highest taxa (root)
		my @lineageA = reverse(&getTaxonAncestors($taxonIdA));		
		my @lineageB = reverse(&getTaxonAncestors($taxonIdB));

		foreach my $taxonA(@lineageA) {
			
			foreach my $taxonB(@lineageB) {
				if($taxonA == $taxonB) {
					return $taxonA;
				}
			}
		}
	}
}

########################################################
## Deletes dataset from METAREP MySQL database.
########################################################

sub deleteMetarepDataset() {
	my $name = shift;
	
	my $query ="delete from libraries where name = ?";

	## reconnect to avoid mysql time-out
	$metarepDbConnection = DBI->connect_cached("DBI:mysql:".$args{mysql_db}.";host=".$args{mysql_host}."",$args{mysql_username},$args{mysql_password}, { 'RaiseError' => 0 });
	
	## prepare query
	my $sth =$metarepDbConnection->prepare($query);
	
	$sth->execute($name) or die "Couldn't execute: $DBI::errstr";
}

########################################################
## Deletes Solr core (if exists)
########################################################

sub deleteSolrCore() {
	my ($core,$solrUrl) = @_;
	
	## delete all documents of existing index
	$log->debug("Deleting index: java -Durl=$solrUrl/solr/$core/update -Xms$initialJavaHeapSize -Xmx$args{solr_max_mem} -jar $rootDir/solr/post.jar $rootDir/solr/delete.xml.");
	system("java -Durl=$solrUrl/solr/$core/update -Xms$initialJavaHeapSize -Xmx$args{solr_max_mem} -jar $rootDir/solr/post.jar $rootDir/solr/delete.xml 1>/dev/null");
		
	## unload core from core registry
	$log->debug("Unloading index: curl $solrUrl/solr/admin/cores?action=UNLOAD&core=$core.");
	system("curl \"$solrUrl/solr/admin/cores?action=UNLOAD&core=$core\" 1>/dev/null 2>/dev/null");
}

########################################################
## Creates Solr core
########################################################

sub createSolrCore() {
	my ($core,$solrUrl) = @_;
	
	## create core
	$log->debug("Creating new core: curl $solrUrl/solr/admin/cores?action=CREATE&name=$core&instanceDir=$args{solr_instance_dir}&dataDir=$args{solr_data_dir}/$core.");
	system("curl \"$solrUrl/solr/admin/cores?action=CREATE&name=$core&instanceDir=$args{solr_instance_dir}&dataDir=$args{solr_data_dir}/$core\" 1>/dev/null 2>/dev/null");	
}

########################################################
## Creates Solr index.
########################################################

sub loadSolrIndex() {
	my ($core,$xmlFile) = @_;
	
	my $file = "$args{tmp_dir}/$xmlFile";

	## post Solr xml file to master server core	
	$log->debug("Loading Dataset Index: java -Durl=$args{solr_url}/solr/$core/update -Xms$initialJavaHeapSize -Xmx$args{solr_max_mem} -jar $rootDir/solr/post.jar $file.");
	system("java -Durl=$args{solr_url}/solr/$core/update -Xms$initialJavaHeapSize -Xmx$args{solr_max_mem} -jar $rootDir/solr/post.jar $file 1>/dev/null");
}

########################################################
## Optimizes Solr index.
########################################################

sub optimizeIndex() {
	my $core = shift;
	
	$log->debug("Optimize Dataset Index: java -Durl=$args{solr_url}/solr/$core/update -Xms$initialJavaHeapSize -Xmx$args{solr_max_mem} -jar $rootDir/solr/post.jar $rootDir/solr/optimize.xml.");
	system("java -Durl=$args{solr_url}/solr/$core/update -Xms$initialJavaHeapSize -Xmx$args{solr_max_mem}  -jar $rootDir/solr/post.jar $rootDir/solr/optimize.xml 1>/dev/null");
}

########################################################
## Creates dataset in METAREP MySQL database
########################################################

sub createMetarepDataset() {
	my ($dataset,$isWeighted,$hasKo) = @_;
	my $srsId = $dataset;
	
	$srsId =~ s/-pga$//;
	
	my $projectId = $args{project_id};
	
	my $pipeline = $args{format};
	
	my $query ="insert ignore into libraries (name,project_id,created,updated,pipeline,is_weighted,has_ko) VALUES (?,?,curdate(),curdate(),'$pipeline',$isWeighted,$hasKo)";
	$log->debug("$query.");

	## reconnect to avoid time-out
	$metarepDbConnection = DBI->connect_cached("DBI:mysql:".$args{mysql_db}.";host=".$args{mysql_host}."",$args{mysql_username},$args{mysql_password}, { 'RaiseError' => 0 });
		
	## prepare query
	my $sth =$metarepDbConnection->prepare($query);
	
	## execute query
	$sth->execute($dataset,$projectId) or die "Couldn't execute: $DBI::errstr";
}

########################################################
## Trims and escapes array values.
########################################################

sub cleanArray() {
	my @array = shift;
	my @cleanArray=();
	foreach(@array) {
		push(@cleanArray,&clean($_));
	}
	return @cleanArray;
}

########################################################
## Trims and escapes special xml characters.
########################################################

sub clean {
	my $tmp = shift;
	
	## escape special xml characters
	$tmp =~ s/&/&amp;/g;
	$tmp =~ s/</&lt;/g;
	$tmp =~ s/>/&gt;/g;
	$tmp =~ s/\"/&quot;/g;
	$tmp =~ s/'/&apos;/g;
	
	## remove white spaces
	$tmp =~ s/^\s+//g;
	$tmp =~ s/\s+$//g;
		
	## remove other invalid characters
	$tmp =~ s/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]//;	
		
	return $tmp;
}

########################################################
# Returns PhyloDB taxon ID by name.
########################################################

sub getTaxonIdByName() {
	my $name = shift;
	
	my $taxonId = '';
	
	my $query ="select phylodb_taxon_id from phylodb_taxon where name=?";
		
	## execute query
	my $sth = $sqliteDbConnection->prepare($query);
	$sth->execute($name);

	$sth->bind_col(1, \$taxonId);
	$sth->fetch;
	
	return $taxonId;
}

sub isExistingSolrCore() {
	my $core = shift;
	my $res = `wget '$args{solr_url}/solr/$core/admin/ping' 2>&1`;
	if($res =~ /ERROR 404/) {
		return 0;
	}
	else {
		return 1;
	}
}

########################################################
# Loop through array and trim white spaces.
########################################################

sub trimArray() {
	my (@array) = @_;
	my @cleanArray=();
	foreach(@array) {
		push(@cleanArray,&clean($_));
	}
	return @cleanArray;
}

##Phylo-METAREP DB DIRECTORY

1. metarep.mysql.db mysql database is accessed by the webinterface
2. phylo-metarep.sqlite.db.gz sqlite3 database is accessed during the indexing process
<p>
Both DBs contain the following lookup data and version:

| lookup data | version
|------------|-----------
|KEGG             | 2011-09-01
|GENE ONTOLOGY    | 2011-09-05
|PhyloDB TAXONOMY | PhyloDB v1.0.5	
<br>

To update both databases, you can use the scripts/perl/phylo_metarep_update_database.pl
script.
<br>

The latest version of PhyloDB and other data can be found here:  https://scripps.ucsd.edu/labs/aallen/data

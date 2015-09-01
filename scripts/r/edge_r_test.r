#!/usr/local/bin/Rscript --vanilla

###############################################################################
# METAREP : High-Performance Comparative Metagenomics Framework (http://www.jcvi.org/metarep)
# Copyright(c)  J. Craig Venter Institute (http://www.jcvi.org)
#
# Licensed under The MIT License
# Redistributions of files must retain the above copyright notice.
#
# link http://www.jcvi.org/metarep METAREP Project
# package metarep
# version METAREP v 1.3.2
# author Johannes Goll
# lastmodified 2010-07-09
# license http://www.opensource.org/licenses/mit-license.php The MIT License
###############################################################################

library(edgeR);
library(methods);

## get command line arguments
args 					= commandArgs(TRUE)

infile 					= args[1];
outfile 				= args[2];
propround 				= as.numeric(args[3]);
pvalround 				= as.numeric(args[4]);
startIndexPopulationB	= as.numeric(args[5]);
populationNameA			= args[6];
populationNameB			= args[7];

## get edgeR DGE List object
targets = read.delim(infile,stringsAsFactors=F);
dgeList = readDGE(targets);
features = rownames(dgeList$counts); 
m  = as.matrix(dgeList$counts);

## get number of datasets
ncols = as.numeric(length(m[1,]));
nrows = as.numeric(length(m[,1]));

## get counts for each population
posA = seq(1,(startIndexPopulationB-1));

posB = seq(startIndexPopulationB,ncols);
cntA = apply(m[,posA],1,sum);
cntB = apply(m[,posB],1,sum);

## estimate common dispersion
commonDispersion = estimateCommonDisp(dgeList);

## execute exact negative binomial test
edgeResults = exactTest(commonDispersion,pair=c(populationNameA,populationNameB));
edgeResultTable = edgeResults$table;

## top ten results for smear plot
#topTenEdgeResults = topTags(edgeResults);
#pdf(outfile||".pdf");

## init metarep result matrix
columns  = c("id","count1","count2","logConc","logFC","disp","p_value","b_value","q_value");
metarepResults = data.frame(matrix(rep(NA,9*nrows),ncol=9),stringsAsFactors=F);		
colnames(metarepResults) = columns;

## set metarep result values
metarepResults[,1] = features;
metarepResults[,2] = cntA;
metarepResults[,3] = cntB;
metarepResults[,4] = round(edgeResultTable$logConc,4);
metarepResults[,5] = round(edgeResultTable$logFC,4);
metarepResults[,6] = round(commonDispersion$common.dispersion,4);
metarepResults[,7] = round(edgeResultTable$p.value,pvalround);
metarepResults[,8] = round(p.adjust(edgeResultTable$p.value, method = "fdr"),pvalround);
metarepResults[,9] = round(p.adjust(edgeResultTable$p.value, method = "bonferroni"),pvalround);

## write results to tab delimites output file
write.table(metarepResults,file=outfile,row.names=F,sep ="\t",eol="\n",append=F,quote=F);

## exit
q(status=0)
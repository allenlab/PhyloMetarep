#!/usr/local/bin/Rscript
source("/opt/wwww/phylo-metarep/htdocs/phylo-metarep/app/webroot/files/r/metastats/detect_DA_features.r")
jobj <- load_frequency_matrix("/opt/wwww/phylo-metarep/tmp/jrw.manmouse.class.matrix")
detect_differentially_abundant_features(jobj, 8,"/opt/wwww/phylo-metarep/htodocs/phylo-metarep/app/webroot/files/r/metastats/Routput.diffAb")
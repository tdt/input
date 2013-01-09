#!/bin/bash

[ $# -eq 1 ] && echo -e "Alright, we're going to install this!\n" || { echo -e "Please give an install location and provide root access if the destination is in a directory that only root can access.\n"; exit 0; } 

if [ ! -d .TDT ];
then 
    mkdir .TDT/;
    cd .TDT;
    git clone https://github.com/TDT/Framework.git;
    cp Framework/.htaccess $1;
    cp -r Framework/* $1 ;
    cd ..;
else
    cd .TDT/Framework;
    git pull;
    cp .htaccess $1;
    cp -r * $1;
    cd ../../;
fi

cp -r * $1 ;

echo "All done! If you haven't done so yet, please fill out your ini files in $1/custom/"

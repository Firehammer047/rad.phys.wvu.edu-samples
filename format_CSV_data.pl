#! /usr/bin/perl

# format_CSV_data.pl
# v1.0
# Proggy to format CSV data to tab data
# JAN 2018

# Changelog

use strict;
my $DEBUG = 1;

if(!$ARGV[0]){
	print "usage: format_CSV_data.pl <infile>\n";
	exit 0;
}

my $filename = shift(@ARGV);
my $f_filename = shift(@ARGV);

# OPEN FILE TO FORMAT
open(my $fh_in, "<", $filename);
open(my $fh_out, ">", $f_filename);
while(!eof($fh_in)){

	my $line = readline($fh_in);
	my $newline = join("\t", split(/,/,$line));
	print $fh_out "$newline\n";
}
close($fh_in);
close($fh_out);



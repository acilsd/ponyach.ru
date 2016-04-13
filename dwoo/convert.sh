#!/bin/bash


file="$1"

sed -i -re 's/\{\$([a-z0-9_\.\-]+)\}/{{ \1 }}/g' $file

sed -i -re 's/\{if(.*)\}/{% if \1 %}/g' $file
sed -i -re 's/\{elseif(.*)\}/{% elif \1 %}/g' $file

sed -i -re 's/\{\/if\}/{% endif %}/g' $file

sed -i 's/{else}/{% else %}/g' $file

sed -i 's/{strip}//g' $file
sed -i 's/{\/strip}//g' $file

for i in `seq 1 10`; do
sed -i -re 's/(\{% if.*)(&&)(.* %\})/\1 AND \3/g' $file
sed -i -re 's/(\{% if.*)(\|\|)(.* %\})/\1 OR \3/g' $file
sed -i -re 's/(\{% if.*)( eq )(.* %\})/\1 == \3/g' $file
sed -i -re 's/(\{% if.*)( neq )(.* %\})/\1 != \3/g' $file
sed -i -re 's/(\{% elif.*)(&&)(.* %\})/\1 AND \3/g' $file
sed -i -re 's/(\{% elif.*)(\|\|)(.* %\})/\1 OR \3/g' $file
sed -i -re 's/(\{% elif.*)( eq )(.* %\})/\1 == \3/g' $file
sed -i -re 's/(\{% elif.*)( neq )(.* %\})/\1 != \3/g' $file
done


sed -i -re 's/\{t.*}(.*)\{\/t\}/{% trans _("\1") %}/g' $file

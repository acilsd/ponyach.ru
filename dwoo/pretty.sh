#!/bin/bash

file=$1

sed -i 's/^\s*//g' $file

i=0

function xecho() {
printf "%${i}s%s\n" '' "$1"
}


while read line; do

if echo $line| grep -q -E '^{% (if|for)'; then
  xecho "$line"
  let i=$i+2
elif echo $line| grep -q -E '^{% (elif|else)'; then
  let i=$i-2
  xecho "$line"
  let i=$i+2
elif  echo $line|grep -q -E '^{% (endif|endfor)'; then
  let i=$i-2
  xecho "$line"
else
  xecho "$line"
fi

done<$file

#!/bin/bash
HOME="$(dirname "$0")"
PARENT="$(dirname "$HOME")"
SCRIPTBASE="$PARENT/Api_Scripts/"

SCRIPTCALL="ApiBashScript"

SCRIPT="$SCRIPTBASE$SCRIPTCALL.php"

if [ $# -lt 1 ]; then 
	ADD="--bash"
else
	ADD=""
fi

/usr/bin/php "$SCRIPT" "Job" "Post" "$@" $ADD

 

#!/bin/bash
HOME="$(dirname "$0")"
PARENT="$(dirname "$HOME")"
SCRIPTBASE="$PARENT/Api_Scripts/"

SCRIPTCALL="PinningCompatibility/PinningCompatibility"

SCRIPT="$SCRIPTBASE$SCRIPTCALL.php"

if [ $# -lt 1 ]; then 
	ADD="-b -b"
else
	ADD=""
fi

/usr/bin/php "$SCRIPT" "$@" $ADD
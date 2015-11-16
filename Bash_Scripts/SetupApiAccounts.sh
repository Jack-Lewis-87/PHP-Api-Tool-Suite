#!/bin/bash
HOME="$(dirname "$0")"
PARENT="$(dirname "$HOME")"
SCRIPTBASE="$PARENT/Setup_Files/"

SCRIPTCALL="Setup.sh"

SCRIPT="$SCRIPTBASE$SCRIPTCALL"

"$SCRIPT"

 

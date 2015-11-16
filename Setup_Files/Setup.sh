#!/bin/bash
STRUCT="`dirname \"$0\"`"              # relative
STRUCT="`( cd \"$STRUCT\" && pwd )`"  # absolutized and normalized
if [ -z "$STRUCT" ] ; then
  echo "Failed to run successfully. Go ask a dev or jlewis@sailthru.com."
  exit 1  # fail
fi
PARENT="$(dirname "$STRUCT")"
SCRIPTBASE="$PARENT/Bash_Scripts/"
PATHBASE="$PARENT/Bash_Scripts"
SETUPBASE="$PARENT/Setup_Files"

php "$SETUPBASE/Setup.php" "$SETUPBASE"
#The php file returns information about what action was taken so this script can respond accordingly
#skip of 0 means it was the first time the script was run, do full execution
#skip of 1 means there were updates made, no further action required here
#skip of 2 means there was no action made, update the bash files so new ones are executable.
SKIP=$?

if [ $SKIP -eq 0 ] || [ $SKIP -eq 2 ] ; then
	chmod +x "$SCRIPTBASE"*
	if [ $SKIP -eq 2 ] ; then 
		echo "All new command line files updated to be executable."
	else 
		echo "All command line files are executable."
	fi
fi

if [ $SKIP -eq 0 ]
then
	printf "\nexport PATH="'$PATH'":""'""$PATHBASE""'""" >> "$HOME/.bash_profile"
	echo "Command line files have been added to your bash profile."
	echo "Restart the terminal window to be able to use the files from anywhere. Eg: ListGet.sh"
fi
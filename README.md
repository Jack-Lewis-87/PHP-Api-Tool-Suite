# API Tool Suite (PHP)

This tool is great for two sets of Sailthru Employees. <br>
-The CSM who has a curiousity about what API call and responses look like or has a desire to get hands on intuition and experience with our APIs.<br>
-The Implementation Engineer, Support Engineer, or Dev who wants an easy preconfigured tool for accessing the APIs as well as a framework to neatly build customized scripts into.

### Instructions to download and configure.

Open Finder and navigate to where you downloaded this folder. Although the PHP Api Tool Suite folder can live anywhere, consider moving it into your Home Folder if you don't have somewhere else in mind.<br>
Then press command + spacebar and type 'terminal' into the search box. Resize the terminal window so half the screen is the terminal and the other half is Finder.<br>
Type "chmod +x " (don't press enter yet) in the terminal window then locate the file below in finder and drag and drop it into the terminal window. Press enter. The next line should return without a message. Note: There should be a space between the +x and the start of the file name. 

**Setup_Files/setup.sh**

It should look somthing like this:<br>
M8234:~ johnlewis$ chmod +x /Users/johnlewis/Documents/implementation/PHP_Api_Tool_Suite/Setup_Files/Setup.sh <br>

Then drag and drop the setup.sh file into the terminal again on its own and press enter.<br>
M8234:~ johnlewis$ /Users/johnlewis/Documents/toolbox/implementation/PHP_Api_Tool_Suite/Setup_Files/Setup.sh <br>

The Setup will guide you through creating a default config for your test account's Key and Secret. That will make your account the default in subsequent use. You can add other clients so they are also easily accessible. Run setup.sh again or adding them directly to Classes/Api/Account_Credentials/DefaultKeysAndSecrets.php. If you don't have a test account, email sgiordano@sailthru.com about setting one up for you. 

You are now set up to use the library from anywhere. Type in the API type, eg List or User, followed by the type, eg Get or Post, then press tab. You will know it worked if a '.sh' is added to the end. Hit enter and the rest of the options for that call will print to the screen.

**ListGet.sh**

For more advanced scripting use, you can save copies of the 'Api_Scripts' files to Custom_Script_Mods and edit them there. You can run these by typing php into the terminal and drag and dropping the new file into the terminal. Alternatively, learn some command line navigation! <br>

http://www.macworld.com/article/2042378/master-the-command-line-navigating-files-and-folders.html



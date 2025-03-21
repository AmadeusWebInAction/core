rem this based on mention here
rem https://v7.amadeusweb.com/readme/
rem (or)
rem 	https://github.com/AmadeusWebInAction/core?tab=readme-ov-file

rem INSTRUCTIONS
rem assumes you have setup the environment (Open Source Tools)
rem and have created a new folder:
rem 	D:\AmadeusWeb\all\awe\

rem then copy this batch file into that and run it
rem it should create these folders
rem 	core, themes, static

git clone https://github.com/AmadeusWebInAction/core.git ./core

git clone https://github.com/AmadeusWebInAction/themes.git ./themes

git clone https://github.com/AmadeusWebInAction/static.git ./static

rem please see the clone-amadeus-com and -world for more
rem 	or the clone-amadeus-demo (not in awe but sites)
rem  	or clone-amadeus-network-demo (not in awe but networks)

rem press any key after checking if "cloning" was successful to see the parting messages 
pause

cls
rem follow the instruction to extract theme assets before running
rem press any key to close
pause


rem https://tortoisegit.org/docs/tortoisegit/tgit-automation.html
rem check for modifications
start /b TortoiseGitProc.exe /command:repostatus /path:core
start /b TortoiseGitProc.exe /command:repostatus /path:static
start /b TortoiseGitProc.exe /command:repostatus /path:themes
pause

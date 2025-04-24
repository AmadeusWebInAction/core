rem https://tortoisegit.org/docs/tortoisegit/tgit-automation.html
rem check for modifications
start /b TortoiseGitProc.exe /command:repostatus /path:core
start /b TortoiseGitProc.exe /command:repostatus /path:static
start /b TortoiseGitProc.exe /command:repostatus /path:themes
start /b TortoiseGitProc.exe /command:repostatus /path:web
start /b TortoiseGitProc.exe /command:repostatus /path:world
start /b TortoiseGitProc.exe /command:repostatus /path:world/people/imran
start /b TortoiseGitProc.exe /command:repostatus /path:world/organizations/vishwas
start /b TortoiseGitProc.exe /command:repostatus /path:world/integral-living
start /b TortoiseGitProc.exe /command:repostatus /path:world/social
pause

; https://tortoisegit.org/docs/tortoisegit/tgit-automation.html
; check for modifications
start /b TortoiseGitProc.exe /command:repostatus /path:core
start /b TortoiseGitProc.exe /command:repostatus /path:social
start /b TortoiseGitProc.exe /command:repostatus /path:static
start /b TortoiseGitProc.exe /command:repostatus /path:themes
start /b TortoiseGitProc.exe /command:repostatus /path:web
start /b TortoiseGitProc.exe /command:repostatus /path:world
pause

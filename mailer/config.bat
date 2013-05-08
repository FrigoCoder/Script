set MAILERPATH=%~dp0
set PHPPATH=\server\wamp\bin\php\php5.3.0
set SVNPATH=\server\svn
set WINPATH=\windows

set MAILER=%MAILERPATH%\mailer.php
set RUN=%MAILERPATH%\run.bat
set PHP=%PHPPATH%\php-win.exe
set SVN=%SVNPATH%\bin\svn.exe
set SCH=%WINPATH%\system32\schtasks.exe

set REPO=%1
set REV=%2

set TASK=%REPO%-%REV%
set TASK=%TASK:<=%
set TASK=%TASK:>=%
set TASK=%TASK::=%
set TASK=%TASK:/=%
set TASK=%TASK:\=%
set TASK=%TASK:|=%

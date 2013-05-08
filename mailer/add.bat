cd /d %~dp0
call logconfig.bat
call config.bat %*
%SCH% /delete /tn %TASK% /f
%SCH% /create /tn %TASK% /tr "%RUN% %* >> %LOG% 2>> %ERR%" /sc minute /mo 1 /ru ""
exit 0

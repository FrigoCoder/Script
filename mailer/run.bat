cd /d %~dp0
call logconfig.bat
call config.bat %*
%SCH% /delete /tn %TASK% /f
%PHP% %MAILER% %*
exit 0

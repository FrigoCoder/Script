set RECIPIENTS=frigocoder@gmail.com
call %~dp0\..\..\logconfig.bat
call %~dp0\..\..\add.bat %1 %2 %RECIPIENTS% >> %LOG% 2>> %ERR%

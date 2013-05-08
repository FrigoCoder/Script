set DATUM=%DATE%
set DATUM=%DATUM:-=%
set DATUM=%DATUM:.=%
set DATUM=%DATUM: =%
set LOG="%~dp0\log\%DATUM%.log"
set ERR="%~dp0\log\%DATUM%.err"

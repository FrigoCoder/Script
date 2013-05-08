call checkVariable.cmd SOURCE_DRIVE || goto :eof
call checkVariable.cmd DESTINATION_DRIVE || goto :eof
call checkVariable.cmd SHADOW_DRIVE || goto :eof

call restartServices.cmd || goto :eof

call util\vscsc.exe -exec=copyDrive.cmd %SOURCE_DRIVE% || goto :eof

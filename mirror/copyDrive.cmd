util\dosdev.exe %SHADOW_DRIVE% %1
robocopy.exe %SHADOW_DRIVE%\ %DESTINATION_DRIVE%\ /zb /copyall /mir /xj /r:5 /ndl /np /unicode
util\dosdev.exe -r -d %SHADOW_DRIVE%

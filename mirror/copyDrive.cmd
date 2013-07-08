
util\dosdev.exe %SHADOW_DRIVE% %1

set copy_options=/nocopy /mir
set file_selection_options=/xc /xn /xo /xl /xj
set retry_options=/r:5
set logging_options=/x /ndl /np /unicode
robocopy.exe %SHADOW_DRIVE%\ %DESTINATION_DRIVE%\ %copy_options% %file_selection_options% %retry_options% %logging_options%

set copy_options=/b /dcopy:t /copyall /secfix /timfix /mir
set file_selection_options=/xj
set retry_options=/r:5
set logging_options=/x /ndl /np /unicode
robocopy.exe %SHADOW_DRIVE%\ %DESTINATION_DRIVE%\ %copy_options% %file_selection_options% %retry_options% %logging_options%

util\dosdev.exe -r -d %SHADOW_DRIVE%

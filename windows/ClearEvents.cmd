for /f %%a in ('WEVTUTIL EL') do WEVTUTIL CL "%%a"

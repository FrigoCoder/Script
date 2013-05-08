sc config COMsysAPP start= auto
sc config SENS start= auto
sc config EventSystem start= auto
sc config SwPrv start= auto
sc config VSS start= auto

net stop COMsysAPP
net stop SENS
net stop EventSystem
net stop SwPrv
net stop VSS

net start VSS
net start SwPrv
net start EventSystem
net start SENS
net start COMsysAPP

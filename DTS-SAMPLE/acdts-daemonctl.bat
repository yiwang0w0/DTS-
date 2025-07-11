@echo off
setlocal EnableDelayedExpansion

echo DTS Daemon system for WIN
echo version 0.3

powershell $PSVersionTable.PSVersion.Major > .\gamedata\tmp\daemon.temp
set /p psversion=<.\gamedata\tmp\daemon.temp
if %psversion% lss 3 (
  echo Powershell version too low. 3 or higher is needed.
  pause
  exit
)
REM ���������û�������
REM ��������ַ
php -r "error_reporting(0); include './include/modules/core/sys/config/server.config.php'; echo $server_address;" > .\gamedata\tmp\daemon.temp
set /p sv=<.\gamedata\tmp\daemon.temp
REM ��ȡ��������
php -r "error_reporting(0); include './include/modulemng/modulemng.config.php'; echo $___MOD_CONN_PASSWD;" > .\gamedata\tmp\daemon.temp
set /p pw=<.\gamedata\tmp\daemon.temp
set dmd0=0

:LOOP
if exist .\gamedata\tmp\server\script_quit (
  del /q .\gamedata\tmp\server\script_quit
  echo 0 > .\gamedata\tmp\server\scriptalive.txt
  echo Exit request received. Daemon system will be shut down.
  del /q .\gamedata\tmp\daemon.temp
  del /q .\gamedata\tmp\daemon_start.temp
  pause
  exit
) else (
  REM win�»�ȡʱ�����ֱ���ۣ����˸�php
  set timestamp=1
  echo !timestamp! > .\gamedata\tmp\server\scriptalive.txt
  REM ��ȡ��Ϸ�Ƿ���daemon
  php -r "error_reporting(0); include './include/modulemng/modulemng.config.php'; echo $___MOD_SRV;" > .\gamedata\tmp\daemon.temp
  set /p dmd=<.\gamedata\tmp\daemon.temp
  if !dmd! equ 1 (
    if !dmd0! equ 0 (
      echo Daemon system is now ON.
      set dmd0=1
    )
    REM ���ļ�״̬�ж�����tp������Ϊ1��ʾ��root daemon��Ϊ0��ʾ��һ��daemon
    set tp=-1
    if exist .\gamedata\tmp\server\request_new_root_server (
      set tp=1
      del /q .\gamedata\tmp\server\request_new_root_server
      echo %time% New root-server request received. Launching a new root-daemon...
    ) else if exist .\gamedata\tmp\server\request_new_server (
      set tp=0
      del /q .\gamedata\tmp\server\request_new_server
      echo %time% New server request received. Launching a new daemon...
    )  
    if !tp! neq -1 (
      REM ΢��Ű��ǧ�ٱ飬�Ҵ�΢����������г�һ����΢��Ű������ITԺ��
      REM ƴ����ַ������powershell��invoke-webrequest��������Ǹ����
      REM ���command.phpִ��������Ҫô����received��Ҫô��һֱִ��ֱ��invoke-webrequest��ʱ����󷵻�0
      REM ���command.phpִ�г����᷵��һ��������Ϣ
      start /min powershell "$postParams = @{conn_passwd=%pw%;command='start';is_root=!tp!};$res=invoke-webrequest -uri '%sv%/command.php' -timeoutsec 10 -method POST -body $postParams;if($res){$res=$res.content;}else{$res=0;};echo $res | out-file -encoding ascii .\gamedata\tmp\daemon_start.temp"
    )
  ) else (
    if !dmd0! equ 1 (
      echo Daemon system is now OFF.
      set dmd0=0
    )
  )
  if exist .\gamedata\tmp\daemon_start.temp (
    set /p res=<.\gamedata\tmp\daemon_start.temp
    if !res! neq 0 (
    	if !res! neq received (
    	  REM �������ֵ�����㣨��ʱ��Ҳ����received˵��������
    	  echo It seems an error has occurred when executing command.php...Please check it out and then continue.
        echo !res!
        del /q .\gamedata\tmp\daemon_start.temp
    	)
    ) 
  )
)
powershell Start-Sleep -milliseconds 500
goto LOOP 
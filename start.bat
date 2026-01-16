@echo off
setlocal EnableExtensions

echo ================================================
echo Starting Intellectual Property Management System
echo ================================================
echo.

REM Project directory (folder containing this script)
set "PROJECT_DIR=%~dp0"
for %%I in ("%PROJECT_DIR%.") do set "PROJECT_NAME=%%~nxI"

REM XAMPP install directory (adjust if yours is different)
set "XAMPP_DIR=C:\xampp"

if not exist "%XAMPP_DIR%\" (
	echo ERROR: XAMPP not found at "%XAMPP_DIR%".
	echo Edit start.bat and set XAMPP_DIR to your actual XAMPP folder.
	echo.
	pause
	exit /b 1
)

REM Preferred: start services using XAMPP helper
if exist "%XAMPP_DIR%\xampp_start.exe" (
	echo Starting Apache and MySQL via xampp_start.exe...
	start "XAMPP Start" /MIN "%XAMPP_DIR%\xampp_start.exe"
) else (
	echo xampp_start.exe not found. Falling back to direct binaries...

	echo Starting Apache...
	if exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
		start "Apache" /MIN "%XAMPP_DIR%\apache\bin\httpd.exe"
	) else (
		echo ERROR: Apache binary not found at "%XAMPP_DIR%\apache\bin\httpd.exe".
		echo.
		pause
		exit /b 1
	)

	timeout /t 3 /nobreak >nul

	echo Starting MySQL...
	if exist "%XAMPP_DIR%\mysql\bin\mysqld.exe" (
		if exist "%XAMPP_DIR%\mysql\bin\my.ini" (
			start "MySQL" /MIN "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini"
		) else (
			start "MySQL" /MIN "%XAMPP_DIR%\mysql\bin\mysqld.exe"
		)
	) else (
		echo ERROR: MySQL binary not found at "%XAMPP_DIR%\mysql\bin\mysqld.exe".
		echo.
		pause
		exit /b 1
	)
)

REM Wait a moment for services to initialize
timeout /t 2 /nobreak >nul

set "APP_URL=http://localhost/%PROJECT_NAME%/public/"

echo.
echo Services started. Opening application in browser...
echo URL: %APP_URL%
echo.

start "Open App" "%APP_URL%"

echo ================================================
echo Application should be running.
echo If you see 404s, confirm Apache mod_rewrite is enabled.
echo ================================================
echo.
pause

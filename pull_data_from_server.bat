<# : batch portion
@echo off & setlocal

set "URL=http://localhost:81/sunfix/hms/synchronize/pull"
powershell -noprofile "iex (${%~f0} | out-string)"

goto :EOF

: end batch / begin PowerShell hybrid chimera #>

$request = [Net.WebRequest]::Create($env:URL)
$response = $request.GetResponse()
$stream = $response.GetResponseStream()
$reader = new-object IO.StreamReader($stream)
[void]$reader.ReadToEnd()
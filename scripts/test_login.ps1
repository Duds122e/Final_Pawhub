$base = "https://finalpawhub-production.up.railway.app"
$jar = "$env:TEMP\pawhub_cookies.txt"
Remove-Item $jar -ErrorAction SilentlyContinue

$html = curl.exe -s -c $jar "$base/login"
if ($html -match 'name="_csrf_token" value="([^"]+)"') { $csrf = $Matches[1] } else { $csrf = "" }
Write-Host "CSRF: $csrf"

curl.exe -s -b $jar -c $jar -X POST "$base/login" `
  -d "username=admin&password=admin123&_csrf_token=$csrf" `
  -w "`nPOST login HTTP:%{http_code}`n" -o "$env:TEMP\login_post.html" -L

curl.exe -s -b $jar "$base/dashboard" -w "`nDashboard HTTP:%{http_code}`n" -o "$env:TEMP\dashboard.html"
Get-Content "$env:TEMP\dashboard.html" -TotalCount 30

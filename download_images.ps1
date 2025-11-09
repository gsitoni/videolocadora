Param(
  [string]$MysqlBin = "C:\xampp\mysql\bin\mysql.exe",
  [string]$DbName = "video_locadora",
  [string]$User = "root",
  [securestring]$PasswordSecure,
  [string]$OutDir = "images",
  [int]$Width = 300,
  [int]$Height = 450
)

# Verificações iniciais
if (-not (Test-Path $MysqlBin)) {
  Write-Error "MySQL client not found at $MysqlBin"
  exit 1
}

# Criar pasta de saída
$fullOut = Join-Path -Path (Get-Location) -ChildPath $OutDir
if (-not (Test-Path $fullOut)) {
  New-Item -ItemType Directory -Path $fullOut | Out-Null
}

# Consulta títulos no banco
$plainPassword = if ($PasswordSecure) { ([Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToGlobalAllocUnicode($PasswordSecure))) } else { '' }
$passArg = if ($plainPassword -ne '') { "--password=$plainPassword" } else { "--password=" }
$titles = & $MysqlBin -h localhost -u $User $passArg -N -B -e "SELECT ident_titulo FROM $DbName.filme;" 2>$null
if ($LASTEXITCODE -ne 0 -or -not $titles) {
  Write-Error "Failed to fetch titles from database $DbName"
  exit 1
}

function Remove-Diacritics([string]$textInput) {
  $normalized = $textInput.Normalize([Text.NormalizationForm]::FormD)
  $sb = New-Object Text.StringBuilder
  foreach ($c in $normalized.ToCharArray()) {
    $uc = [Globalization.CharUnicodeInfo]::GetUnicodeCategory($c)
    if ($uc -ne [Globalization.UnicodeCategory]::NonSpacingMark) {
      [void]$sb.Append($c)
    }
  }
  return $sb.ToString().Normalize([Text.NormalizationForm]::FormC)
}

function New-Slug([string]$title) {
  $t1 = Remove-Diacritics $title
  $t2 = $t1.ToLower()
  $t3 = ($t2 -replace "[^a-z0-9]+","-").Trim('-')
  return $t3
}

[int]$ok=0; [int]$skipped=0; [int]$fail=0
foreach ($t in $titles) {
  if ([string]::IsNullOrWhiteSpace($t)) { continue }
  $slug = New-Slug $t
  if ([string]::IsNullOrWhiteSpace($slug)) { $skipped++; continue }
  $file = Join-Path $fullOut ("$slug.jpg")
  if (Test-Path $file) { $skipped++; continue }
  $encoded = [uri]::EscapeDataString($t)
  $url = "https://via.placeholder.com/${Width}x${Height}.jpg?text=$encoded"
  try {
    Invoke-WebRequest -Uri $url -OutFile $file -UseBasicParsing -TimeoutSec 20
    $ok++
  } catch {
    Write-Warning "Failed to download for '$t' -> $url : $_"
    $fail++
  }
}

Write-Host ("Downloaded: {0}, Skipped: {1}, Failed: {2}" -f $ok,$skipped,$fail)
exit 0
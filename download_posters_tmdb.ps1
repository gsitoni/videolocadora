Param(
  [string]$TmdbApiKey,
  [string]$MysqlBin = "C:\xampp\mysql\bin\mysql.exe",
  [string]$DbName = "video_locadora",
  [string]$User = "root",
  [securestring]$PasswordSecure,
  [string]$OutDir = "images",
  [string]$Language = "pt-BR",
  [string]$ImageSize = "w500",
  [int]$DelayMs = 300
)

# Respect TMDB terms: https://www.themoviedb.org/documentation/api/terms-of-use
Write-Host "This product uses the TMDB API but is not endorsed or certified by TMDB." -ForegroundColor Yellow

if (-not $TmdbApiKey -and $env:TMDB_API_KEY) {
  $TmdbApiKey = $env:TMDB_API_KEY
}
if (-not $TmdbApiKey) {
  Write-Warning "TMDB API key not provided. Set -TmdbApiKey or environment variable TMDB_API_KEY. Falling back to placeholders if needed."
}

if (-not (Test-Path $MysqlBin)) {
  Write-Error "MySQL client not found at $MysqlBin"
  exit 1
}

$fullOut = Join-Path -Path (Get-Location) -ChildPath $OutDir
if (-not (Test-Path $fullOut)) { New-Item -ItemType Directory -Path $fullOut | Out-Null }

$plainPassword = if ($PasswordSecure) { ([Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToGlobalAllocUnicode($PasswordSecure))) } else { '' }
$passArg = if ($plainPassword -ne '') { "--password=$plainPassword" } else { "--password=" }

# Fetch titles and years from DB
$query = "SELECT ident_titulo, YEAR(ident_data) AS ano FROM $DbName.filme;"
$titles = & $MysqlBin -h localhost -u $User $passArg -N -B -e $query 2>$null
if ($LASTEXITCODE -ne 0) { Write-Error "Failed to query MySQL. Ensure DB exists and credentials are correct."; exit 1 }
if (-not $titles) { Write-Warning "No titles found."; exit 0 }

function Remove-Diacritics([string]$textInput) {
  $normalized = $textInput.Normalize([Text.NormalizationForm]::FormD)
  $sb = New-Object Text.StringBuilder
  foreach ($c in $normalized.ToCharArray()) {
    $uc = [Globalization.CharUnicodeInfo]::GetUnicodeCategory($c)
    if ($uc -ne [Globalization.UnicodeCategory]::NonSpacingMark) { [void]$sb.Append($c) }
  }
  return $sb.ToString().Normalize([Text.NormalizationForm]::FormC)
}
function New-Slug([string]$title) {
  $t1 = Remove-Diacritics $title
  $t2 = $t1.ToLower()
  $t3 = ($t2 -replace "[^a-z0-9]+","-").Trim('-')
  return $t3
}

[int]$nOk=0; [int]$nSkip=0; [int]$nFail=0; [int]$nPh=0
foreach ($line in $titles) {
  if ([string]::IsNullOrWhiteSpace($line)) { continue }
  $parts = $line -split "\t"
  $title = $parts[0]
  $year = if ($parts.Length -ge 2) { $parts[1] } else { '' }
  $slug = New-Slug $title
  $file = Join-Path $fullOut ("$slug.jpg")
  if (Test-Path $file) { $nSkip++; continue }

  $downloaded = $false
  if ($TmdbApiKey) {
    try {
      $encodedTitle = [uri]::EscapeDataString($title)
      $url = "https://api.themoviedb.org/3/search/movie?api_key=$TmdbApiKey&query=$encodedTitle&include_adult=false"
      if ($year -and $year -match '^[0-9]{4}$') { $url += "&year=$year" }
      if ($Language) { $url += "&language=$Language" }
      $resp = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 20
      $obj = $resp.Content | ConvertFrom-Json
  if ($obj -and $obj.results -and ($obj.results.Count -gt 0)) {
        $posterPath = $obj.results[0].poster_path
        if ($posterPath) {
          $imgUrl = "https://image.tmdb.org/t/p/$ImageSize$posterPath"
          Invoke-WebRequest -Uri $imgUrl -OutFile $file -UseBasicParsing -TimeoutSec 30
          $downloaded = $true
          $nOk++
        }
      }
    } catch {
      Write-Warning "TMDB fetch failed for '$title' ($year): $_"
    }
    Start-Sleep -Milliseconds $DelayMs
  }

  if (-not $downloaded) {
    # Fallback placeholder
    try {
      $enc = [uri]::EscapeDataString($title)
      $ph = "https://via.placeholder.com/300x450.jpg?text=$enc"
      Invoke-WebRequest -Uri $ph -OutFile $file -UseBasicParsing -TimeoutSec 20
      $nPh++
    } catch {
      Write-Warning "Placeholder download failed for '$title': $_"
      $nFail++
    }
  }
}

Write-Host ("Done. Posters: {0}, Placeholders: {1}, Skipped: {2}, Failed: {3}" -f $nOk,$nPh,$nSkip,$nFail)
exit 0

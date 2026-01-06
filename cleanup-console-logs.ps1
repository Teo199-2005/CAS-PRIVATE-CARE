# Remove all console.log statements from Vue files
# CAS Private Care LLC - Production Cleanup Script

$vueFiles = Get-ChildItem -Path "resources\js" -Filter "*.vue" -Recurse

$totalRemoved = 0

foreach ($file in $vueFiles) {
    Write-Host "Processing: $($file.Name)" -ForegroundColor Cyan
    
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Remove console.log, console.error, console.warn lines
    $lines = $content -split "`r?`n"
    $newLines = @()
    
    foreach ($line in $lines) {
        if ($line -notmatch '^\s*console\.(log|error|warn|info|debug)\(') {
            $newLines += $line
        }
    }
    
    $content = $newLines -join "`n"
    
    # Clean up multiple empty lines
    $content = $content -replace "(`n\s*){3,}", "`n`n"
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $removed = ($originalContent -split "`n").Count - ($content -split "`n").Count
        Write-Host "  Removed $removed lines" -ForegroundColor Green
        $totalRemoved += $removed
    } else {
        Write-Host "  No console statements found" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Yellow
Write-Host "TOTAL CONSOLE STATEMENTS REMOVED: $totalRemoved" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Yellow
Write-Host ""

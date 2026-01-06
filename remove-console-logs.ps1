# Remove all console.log statements from Vue files
# CAS Private Care LLC - Production Cleanup Script

$vueFiles = Get-ChildItem -Path "resources\js" -Filter "*.vue" -Recurse

$totalRemoved = 0

foreach ($file in $vueFiles) {
    Write-Host "Processing: $($file.Name)" -ForegroundColor Cyan
    
    $content = Get-Content $file.FullName -Raw
    $originalLines = ($content -split "`n").Count
    
    # Remove console.log lines (single line)
    $content = $content -replace "^\s*console\.log\(.*\);\s*$", ""
    
    # Remove console.error lines
    $content = $content -replace "^\s*console\.error\(.*\);\s*$", ""
    
    # Remove console.warn lines
    $content = $content -replace "^\s*console\.warn\(.*\);\s*$", ""
    
    # Remove multi-line console.log statements
    $content = $content -replace "(?m)^\s*console\.(log|error|warn|info|debug)\([^)]*\);\s*[\r\n]*", ""
    
    # Clean up multiple empty lines (replace 3+ empty lines with 2)
    $content = $content -replace "(\r?\n){3,}", "`n`n"
    
    $newLines = ($content -split "`n").Count
    $removed = $originalLines - $newLines
    
    if ($removed -gt 0) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "  ✓ Removed $removed lines" -ForegroundColor Green
        $totalRemoved += $removed
    } else {
        Write-Host "  - No console statements found" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "TOTAL CONSOLE STATEMENTS REMOVED: $totalRemoved" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

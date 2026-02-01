/**
 * CAS Private Care - Bundle Analyzer Script
 * 
 * This script analyzes the production bundle to identify:
 * - Large dependencies
 * - Unused code opportunities
 * - Code splitting recommendations
 * 
 * Usage:
 *   node scripts/analyze-bundle.js
 *   node scripts/analyze-bundle.js --json > report.json
 */

import { readFileSync, readdirSync, statSync, existsSync } from 'fs';
import { join, basename, extname } from 'path';

// Configuration
const BUILD_DIR = 'public/build/assets';
const SIZE_THRESHOLD_KB = 100; // Files larger than this get flagged
const CHUNK_THRESHOLD_KB = 250; // Recommended max chunk size

// Colors for terminal output
const colors = {
    reset: '\x1b[0m',
    red: '\x1b[31m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    cyan: '\x1b[36m',
    dim: '\x1b[2m',
};

// Output format
const jsonOutput = process.argv.includes('--json');

function log(message, color = '') {
    if (!jsonOutput) {
        console.log(color + message + colors.reset);
    }
}

function formatSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(2)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

function getGzipEstimate(bytes) {
    // Rough estimate: gzip typically achieves 60-70% compression on JS
    return Math.round(bytes * 0.35);
}

function getAllFiles(dir, files = []) {
    if (!existsSync(dir)) return files;
    
    const items = readdirSync(dir);
    
    for (const item of items) {
        const fullPath = join(dir, item);
        const stat = statSync(fullPath);
        
        if (stat.isDirectory()) {
            getAllFiles(fullPath, files);
        } else {
            files.push({
                path: fullPath,
                name: basename(fullPath),
                size: stat.size,
                ext: extname(fullPath),
            });
        }
    }
    
    return files;
}

function analyzeBundle() {
    log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', colors.blue);
    log('  CAS Private Care - Bundle Analysis Report', colors.blue);
    log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', colors.blue);
    
    // Check if build exists
    if (!existsSync(BUILD_DIR)) {
        log('\n❌ Build directory not found. Run "npm run build" first.\n', colors.red);
        process.exit(1);
    }
    
    const files = getAllFiles(BUILD_DIR);
    
    if (files.length === 0) {
        log('\n❌ No files found in build directory.\n', colors.red);
        process.exit(1);
    }
    
    // Categorize files
    const jsFiles = files.filter(f => f.ext === '.js');
    const cssFiles = files.filter(f => f.ext === '.css');
    const otherFiles = files.filter(f => !['.js', '.css'].includes(f.ext));
    
    // Calculate totals
    const totalSize = files.reduce((sum, f) => sum + f.size, 0);
    const jsSize = jsFiles.reduce((sum, f) => sum + f.size, 0);
    const cssSize = cssFiles.reduce((sum, f) => sum + f.size, 0);
    const otherSize = otherFiles.reduce((sum, f) => sum + f.size, 0);
    
    const report = {
        timestamp: new Date().toISOString(),
        summary: {
            totalFiles: files.length,
            totalSize: totalSize,
            totalSizeFormatted: formatSize(totalSize),
            gzipEstimate: getGzipEstimate(totalSize),
            gzipEstimateFormatted: formatSize(getGzipEstimate(totalSize)),
        },
        breakdown: {
            javascript: {
                count: jsFiles.length,
                size: jsSize,
                sizeFormatted: formatSize(jsSize),
                percentage: ((jsSize / totalSize) * 100).toFixed(1),
            },
            css: {
                count: cssFiles.length,
                size: cssSize,
                sizeFormatted: formatSize(cssSize),
                percentage: ((cssSize / totalSize) * 100).toFixed(1),
            },
            other: {
                count: otherFiles.length,
                size: otherSize,
                sizeFormatted: formatSize(otherSize),
                percentage: ((otherSize / totalSize) * 100).toFixed(1),
            },
        },
        largeFiles: [],
        recommendations: [],
    };
    
    // Output summary
    log('\n📊 Summary', colors.cyan);
    log('────────────────────────────────────────────────────────────');
    log(`  Total Files:     ${files.length}`);
    log(`  Total Size:      ${formatSize(totalSize)}`);
    log(`  Gzip Estimate:   ${formatSize(getGzipEstimate(totalSize))}`, colors.dim);
    
    // Breakdown
    log('\n📦 Breakdown by Type', colors.cyan);
    log('────────────────────────────────────────────────────────────');
    log(`  JavaScript:  ${formatSize(jsSize)} (${report.breakdown.javascript.percentage}%) - ${jsFiles.length} files`);
    log(`  CSS:         ${formatSize(cssSize)} (${report.breakdown.css.percentage}%) - ${cssFiles.length} files`);
    log(`  Other:       ${formatSize(otherSize)} (${report.breakdown.other.percentage}%) - ${otherFiles.length} files`);
    
    // Large files
    const largeJS = jsFiles.filter(f => f.size > SIZE_THRESHOLD_KB * 1024);
    const largeCSS = cssFiles.filter(f => f.size > SIZE_THRESHOLD_KB * 1024);
    
    if (largeJS.length > 0 || largeCSS.length > 0) {
        log('\n⚠️  Large Files (>' + SIZE_THRESHOLD_KB + 'KB)', colors.yellow);
        log('────────────────────────────────────────────────────────────');
        
        [...largeJS, ...largeCSS]
            .sort((a, b) => b.size - a.size)
            .forEach(f => {
                const gzip = formatSize(getGzipEstimate(f.size));
                log(`  ${f.name}`);
                log(`     Size: ${formatSize(f.size)} (gzip: ~${gzip})`, colors.dim);
                report.largeFiles.push({
                    name: f.name,
                    size: f.size,
                    sizeFormatted: formatSize(f.size),
                    gzipEstimate: getGzipEstimate(f.size),
                });
            });
    }
    
    // Vendor analysis for JS files
    log('\n📚 JavaScript Chunks', colors.cyan);
    log('────────────────────────────────────────────────────────────');
    
    jsFiles
        .sort((a, b) => b.size - a.size)
        .slice(0, 10)
        .forEach((f, i) => {
            const indicator = f.size > CHUNK_THRESHOLD_KB * 1024 ? '⚠️ ' : '  ';
            const color = f.size > CHUNK_THRESHOLD_KB * 1024 ? colors.yellow : '';
            log(`${indicator}${i + 1}. ${f.name}`, color);
            log(`     ${formatSize(f.size)} (gzip: ~${formatSize(getGzipEstimate(f.size))})`, colors.dim);
        });
    
    // Recommendations
    log('\n💡 Recommendations', colors.cyan);
    log('────────────────────────────────────────────────────────────');
    
    // Check main bundle size
    const mainBundle = jsFiles.find(f => f.name.includes('app') || f.name.includes('main'));
    if (mainBundle && mainBundle.size > CHUNK_THRESHOLD_KB * 1024) {
        const rec = `Consider code splitting: Main bundle (${formatSize(mainBundle.size)}) exceeds ${CHUNK_THRESHOLD_KB}KB`;
        log(`  • ${rec}`, colors.yellow);
        report.recommendations.push(rec);
    }
    
    // Check vendor chunk
    const vendorChunk = jsFiles.find(f => f.name.includes('vendor') || f.name.includes('chunk-'));
    if (vendorChunk && vendorChunk.size > 500 * 1024) {
        const rec = `Large vendor chunk (${formatSize(vendorChunk.size)}). Consider splitting dependencies.`;
        log(`  • ${rec}`, colors.yellow);
        report.recommendations.push(rec);
    }
    
    // CSS check
    if (cssSize > 200 * 1024) {
        const rec = `CSS bundle (${formatSize(cssSize)}) is large. Consider splitting or purging unused styles.`;
        log(`  • ${rec}`, colors.yellow);
        report.recommendations.push(rec);
    }
    
    // Tree shaking check
    if (jsFiles.length < 3) {
        const rec = 'Enable code splitting for better caching and parallel loading.';
        log(`  • ${rec}`, colors.yellow);
        report.recommendations.push(rec);
    }
    
    if (report.recommendations.length === 0) {
        log('  ✅ Bundle looks good! No immediate optimizations needed.', colors.green);
    }
    
    // Score calculation
    log('\n📈 Performance Score', colors.cyan);
    log('────────────────────────────────────────────────────────────');
    
    let score = 100;
    
    // Deduct for large total size
    if (totalSize > 2 * 1024 * 1024) score -= 20;
    else if (totalSize > 1 * 1024 * 1024) score -= 10;
    
    // Deduct for large individual chunks
    score -= largeJS.length * 5;
    score -= largeCSS.length * 3;
    
    // Bonus for multiple chunks (good code splitting)
    if (jsFiles.length >= 5) score += 5;
    
    score = Math.max(0, Math.min(100, score));
    report.score = score;
    
    const scoreColor = score >= 80 ? colors.green : score >= 60 ? colors.yellow : colors.red;
    const scoreEmoji = score >= 80 ? '🟢' : score >= 60 ? '🟡' : '🔴';
    
    log(`  ${scoreEmoji} Bundle Score: ${score}/100`, scoreColor);
    
    // Next steps
    log('\n📝 Next Steps', colors.cyan);
    log('────────────────────────────────────────────────────────────');
    log('  • Run "npx vite-bundle-visualizer" for interactive analysis');
    log('  • Check for duplicate dependencies: npx npm-dedupe-analyzer');
    log('  • Analyze imports: npx source-map-explorer build/assets/*.js');
    
    log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n', colors.blue);
    
    // JSON output
    if (jsonOutput) {
        console.log(JSON.stringify(report, null, 2));
    }
    
    return report;
}

// Run analysis
analyzeBundle();

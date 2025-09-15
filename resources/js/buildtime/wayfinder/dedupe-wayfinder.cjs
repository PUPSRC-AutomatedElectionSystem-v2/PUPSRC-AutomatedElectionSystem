const fs = require('fs');
const path = require('path');

function dedupeFile(filePath) {
    const src = fs.readFileSync(filePath, 'utf8');

    const lines = src.split(/\r?\n/);

    const seen = new Set();
    const out = [];

    const exportRegex = /^\s*export\s+const\s+([A-Za-z$_][A-Za-z0-9$_]*)\b/;
    const declarationRegex = /^\s*(const|let|var)\s+([A-Za-z$_][A-Za-z0-9$_]*)\b/;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];

        const m = line.match(exportRegex);
        if (m) {
            const name = m[1];
            if (seen.has(name)) {
                continue;
            }
            seen.add(name);
            out.push(line);
            continue;
        }

        const m2 = line.match(declarationRegex);
        if (m2) {
            const name = m2[2];
            if (seen.has(name)) {
                continue;
            }
        }

        out.push(line);
    }

    const result = out.join('\n');
    if (result !== src) {
        fs.writeFileSync(filePath, result, 'utf8');
        return true;
    }

    return false;
}

function walk(dir, cb) {
    for (const name of fs.readdirSync(dir)) {
        const p = path.join(dir, name);
        const stat = fs.statSync(p);
        if (stat.isDirectory()) {
            walk(p, cb);
        } else if (stat.isFile() && p.endsWith('.ts')) {
            cb(p);
        }
    }
}

function main() {
    const base = path.resolve(__dirname, '..', '..', 'routes');
    if (!fs.existsSync(base)) {
        console.warn('[dedupe-wayfinder] no routes directory found at', base);
        return;
    }

    let changed = 0;
    walk(base, (file) => {
        if (dedupeFile(file)) {
            changed++;
            console.log('[dedupe-wayfinder] fixed', file);
        }
    });

    if (changed === 0) {
        console.log('[dedupe-wayfinder] nothing to fix');
    } else {
        console.log('[dedupe-wayfinder] fixed', changed, 'files');
    }
}

if (require.main === module) {
    main();
}

module.exports = { dedupeFile, main };

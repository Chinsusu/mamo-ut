#!/usr/bin/env node
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { extname, join, normalize, resolve } from 'node:path';

const args = process.argv.slice(2);
const valueAfter = (flag, fallback) => {
  const i = args.indexOf(flag);
  return i >= 0 && args[i + 1] ? args[i + 1] : fallback;
};
const host = valueAfter('--host', process.env.HOST || '127.0.0.1');
const port = Number(valueAfter('--port', process.env.PORT || '4173'));
const root = resolve(process.cwd());
const types = {
  '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8', '.mjs': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8', '.svg': 'image/svg+xml',
  '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg',
  '.webp': 'image/webp', '.ico': 'image/x-icon', '.txt': 'text/plain; charset=utf-8',
};

function safePath(urlPath) {
  const decoded = decodeURIComponent((urlPath || '/').split('?')[0]);
  const clean = normalize(decoded).replace(/^([.][.][/\\])+/, '');
  const requested = resolve(join(root, clean));
  return requested.startsWith(root) ? requested : null;
}

const server = createServer(async (req, res) => {
  try {
    let file = safePath(req.url);
    if (!file) throw new Error('Unsafe path');
    let info;
    try { info = await stat(file); } catch { info = null; }
    if (info?.isDirectory()) file = join(file, 'index.html');
    if (!info && !extname(file)) file += '.html';
    const content = await readFile(file);
    const type = types[extname(file).toLowerCase()] || 'application/octet-stream';
    res.writeHead(200, {
      'Content-Type': type,
      'Cache-Control': type.startsWith('text/html') ? 'no-cache' : 'public, max-age=3600',
      'X-Content-Type-Options': 'nosniff',
    });
    res.end(content);
  } catch {
    res.writeHead(404, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end('<h1>404</h1><p>Không tìm thấy trang.</p>');
  }
});

server.listen(port, host, () => {
  console.log(`O Ut FE UI/UX: http://${host}:${port}`);
});

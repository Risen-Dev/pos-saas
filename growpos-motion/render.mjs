import { mkdir } from 'node:fs/promises';
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '..');
const source = path.join(root, 'public/video/growpos-demo.mp4');
const outDir = path.join(here, 'dist');
const workDir = path.join(outDir, 'render-work');
const width = 1080;
const height = 1920;
const fps = 60;
const duration = 35;
const logo = path.join(root, 'public/img/growpos_logo.png');
const logoWhite = path.join(root, 'public/img/growpos-white.png');
const font = '/System/Library/Fonts/Supplemental/Arial.ttf';

await mkdir(workDir, { recursive: true });
await mkdir(outDir, { recursive: true });

const scenes = [
  { name: '01-intro', clip: 0, seconds: 4.4, title: 'Satu Sistem,', subtitle: 'Tumbuh Bersama.', eyebrow: 'GROWPOS UNTUK OPERASIONAL USAHA', note: 'Kasir • Stok • Laporan' },
  { name: '02-kasir', clip: 5.2, seconds: 9.4, title: 'Penjualan lebih', subtitle: 'tertata.', eyebrow: 'ALUR KASIR', note: 'Katalog dan keranjang dalam satu alur' },
  { name: '03-stok', clip: 15.0, seconds: 6.4, title: 'Stok lebih', subtitle: 'terpantau.', eyebrow: 'PENGELOLAAN STOK', note: 'Cari dan pantau persediaan produk' },
  { name: '04-laporan', clip: 20.1, seconds: 6.8, title: 'Pahami aktivitas', subtitle: 'usahamu.', eyebrow: 'LAPORAN OPERASIONAL', note: 'Angka demo • Kedai Senja Demo' },
  { name: '05-outro', seconds: 9.6, title: 'GrowPOS', subtitle: 'Satu Sistem, Tumbuh Bersama.', eyebrow: 'DIBUAT UNTUK USAHA YANG TERUS TUMBUH', note: 'Kasir • Stok • Transaksi • Laporan', endCard: true },
];

function rasterize(scene, png, foreground = false) {
  const isEnd = scene.endCard;
  const main = isEnd ? '#f5fbf7' : '#16382e';
  const muted = isEnd ? '#b8d0c5' : '#698078';
  const logoPath = isEnd ? logoWhite : logo;
  const args = ['-size', `${width}x${height}`, foreground ? 'xc:none' : (isEnd ? 'xc:#123c30' : 'gradient:#f4f7f4-#e9f1eb')];
  if (!foreground) args.push('-fill', 'none', '-stroke', isEnd ? '#4e8b70' : '#ffffff', '-strokewidth', '2', '-draw', 'circle 540,1080 540,430');
  if (!foreground) {
    args.push('-fill', isEnd ? '#0c2d25' : '#e8efea', '-stroke', 'none', '-draw', 'ellipse 830,390 1080,540 0,360');
    args.push('-fill', isEnd ? '#ffffff' : '#16382e', '-font', font, '-pointsize', '28', '-gravity', 'north', '-annotate', '+0+131', 'GrowPOS');
    args.push('(', logoPath, '-resize', '74x74', ')', '-gravity', 'northwest', '-geometry', '+76+112', '-composite');
    args.push('-fill', muted, '-pointsize', '17', '-gravity', 'northeast', '-annotate', '+76+140', `${scene.name.slice(0, 2)} / 05`);
    args.push('-fill', muted, '-pointsize', '16', '-gravity', 'north', '-annotate', '+0+342', scene.eyebrow.toUpperCase());
    if (isEnd) {
      args.push('-fill', main, '-pointsize', '100', '-annotate', '+0+690', 'GrowPOS');
      args.push('-fill', '#65d3a4', '-stroke', 'none', '-draw', 'roundrectangle 113,985 967,988 3,3');
      args.push('-fill', '#f5fbf7', '-pointsize', '29', '-annotate', '+0+1030', 'Satu Sistem, Tumbuh Bersama.');
      args.push('-fill', '#b8d0c5', '-pointsize', '23', '-annotate', '+0+1145', 'Kasir · Stok · Transaksi · Laporan');
      args.push('-fill', '#f4f7f4', '-stroke', 'none', '-draw', 'roundrectangle 287,1290 793,1372 41,41');
      args.push('-fill', '#16382e', '-pointsize', '24', '-annotate', '+0+1317', 'Kelola usaha lebih terarah');
      args.push('-fill', '#b8d0c5', '-pointsize', '17', '-annotate', '+0+1590', 'GROWPOS • KASIR DAN OPERASIONAL USAHA');
    } else {
      args.push('-fill', main, '-pointsize', '70', '-interline-spacing', '8', '-annotate', '+0+424', `${scene.title}\n${scene.subtitle}`);
      args.push('-fill', muted, '-pointsize', '23', '-annotate', '+0+626', scene.note);
      args.push('-fill', '#174334', '-stroke', 'none', '-draw', 'roundrectangle 44,706 1036,1280 34,34');
      args.push('-fill', '#ffffff', '-draw', 'roundrectangle 54,716 1026,1270 27,27');
      args.push('-fill', '#173c31', '-draw', 'rectangle 54,1292 1026,1344');
      args.push('-fill', '#65d3a4', '-draw', 'circle 85,1318 90,1318');
      args.push('-fill', '#f5fbf7', '-pointsize', '17', '-annotate', '+0+1304', 'CAPTURE UI DEMO • KEDAI SENJA');
      args.push('-fill', muted, '-pointsize', '17', '-annotate', '+0+1408', 'KASIR · STOK · TRANSAKSI · LAPORAN');
      args.push('-fill', muted, '-pointsize', '17', '-annotate', '+0+1743', scene.name.toUpperCase().replaceAll('-', ' '));
    }
  } else if (!isEnd) {
    args.push('-fill', 'none', '-stroke', '#ffffff', '-strokewidth', '3', '-draw', 'roundrectangle 53,715 1027,1271 28,28');
  }
  args.push(png);
  const result = spawnSync('magick', args, { stdio: 'inherit' });
  if (result.status !== 0) throw new Error(`Could not rasterize ${png}`);
}

for (const scene of scenes) {
  const base = path.join(workDir, `${scene.name}-base.png`);
  const foreground = path.join(workDir, `${scene.name}-front.png`);
  rasterize(scene, base);
  rasterize(scene, foreground, true);
  const segment = path.join(workDir, `${scene.name}.mp4`);
  const args = ['-hide_banner', '-loglevel', 'error', '-y', '-loop', '1', '-framerate', String(fps), '-i', base];
  if (!scene.endCard) {
    args.push('-ss', String(scene.clip), '-t', String(scene.seconds), '-i', source);
    args.push('-loop', '1', '-framerate', String(fps), '-i', foreground, '-filter_complex');
  } else {
    args.push('-filter_complex');
  }
  if (scene.endCard) {
    args.push(`[0:v]scale=${width}:${height},fps=${fps},format=yuv420p[v]`);
  } else {
    args.push(`[1:v]scale=952:536:flags=lanczos,fps=${fps},format=yuv420p[ui];[0:v][ui]overlay=64:725:shortest=1[bg];[bg][2:v]overlay=0:0:shortest=1,scale=${width}:${height},fps=${fps},format=yuv420p[v]`);
  }
  args.push('-map', '[v]', '-t', String(scene.seconds), '-an', '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '19', '-movflags', '+faststart', segment);
  const rendered = spawnSync('ffmpeg', args, { stdio: 'inherit' });
  if (rendered.status !== 0) throw new Error(`Failed to render ${scene.name}`);
}

const parts = scenes.map((scene) => path.join(workDir, `${scene.name}.mp4`));
const inputs = parts.flatMap((part) => ['-i', part]);
const transition = 0.4;
let filters = '';
let last = '0:v';
let elapsed = scenes[0].seconds;
for (let i = 1; i < scenes.length; i++) {
  const next = `x${i}`;
  const offset = elapsed - transition;
  filters += `[${last}][${i}:v]xfade=transition=fade:duration=${transition}:offset=${offset}[${next}];`;
  last = next;
  elapsed += scenes[i].seconds - transition;
}
const finalPath = path.join(outDir, 'growpos-reels.mp4');
const finalArgs = ['-hide_banner', '-loglevel', 'error', '-y', ...inputs, '-filter_complex', `${filters}[${last}]fps=${fps},format=yuv420p[v]`, '-map', '[v]', '-t', String(duration), '-an', '-c:v', 'libx264', '-preset', 'slow', '-crf', '18', '-movflags', '+faststart', finalPath];
const finalRender = spawnSync('ffmpeg', finalArgs, { stdio: 'inherit' });
if (finalRender.status !== 0) throw new Error('Failed to assemble final video');
console.log(`Rendered ${finalPath}`);

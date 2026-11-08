import { mkdir, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '..');
const work = '/private/tmp/growpos-v3-film-work';
const sourceFrames = path.join(work, 'ui-frames');
const outputFrames = path.join(work, 'scene-frames');
const source = path.join(root, 'public/video/growpos-demo.mp4');
const logo = path.join(root, 'public/img/growpos_logo.png');
const output = path.join(here, 'dist/growpos-everything-flows-v3.mp4');
const contact = path.join(here, 'dist/growpos-everything-flows-v3-contact-sheet.png');
const fps = 60;
const duration = 28;
const totalFrames = fps * duration;
const font = '/System/Library/Fonts/Supplemental/Arial.ttf';

if (process.env.GROWPOS_FORCE_RENDER === '1') await rm(work, { recursive: true, force: true });
await mkdir(sourceFrames, { recursive: true });
await mkdir(outputFrames, { recursive: true });

function run(bin, args, label) {
  const result = spawnSync(bin, args, { stdio: 'inherit' });
  if (result.status !== 0) throw new Error(`${label} failed (status ${result.status}).`);
}

if (process.env.GROWPOS_FORCE_RENDER === '1' || !existsSync(path.join(sourceFrames, 'ui-0001.png'))) {
  run('ffmpeg', [
    '-hide_banner', '-loglevel', 'error', '-y', '-ss', '7.4', '-i', source, '-t', '19.0',
    '-vf', 'fps=30', '-start_number', '1', path.join(sourceFrames, 'ui-%04d.png'),
  ], 'Extracting GrowPOS demo interface');
}
if (process.env.GROWPOS_FORCE_RENDER === '1' || !existsSync(path.join(outputFrames, 'frame-1679.png'))) {
  run('swift', [
    '-module-cache-path', '/private/tmp/swift-cache', path.join(here, 'render-v3-scene.swift'),
    outputFrames, path.join(sourceFrames, 'ui-0001.png'), '7.5', sourceFrames,
    String(totalFrames), '1080', '1920', 'film',
  ], 'Rendering 3D scenes and camera choreography');
}

function makeCaption(filename, text) {
  const file = path.join(work, filename);
  run('magick', [
    '-size', '1080x1920', 'xc:none', '-font', font, '-stroke', '#263831', '-strokewidth', '1.2',
    '-fill', '#f2f7f3', '-pointsize', '46', '-gravity', 'northwest', '-annotate', '+88+390', text, file,
  ], `Creating ${filename}`);
  return file;
}

const cashierCaption = makeCaption('cashier-caption.png', 'Jualan jadi lebih praktis.');
const stockCaption = makeCaption('stock-caption.png', 'Stok ikut terpantau.');
const reportCaption = makeCaption('report-caption.png', 'Semua lebih terpantau.');
const endcard = path.join(work, 'endcard.png');
run('magick', [
  '-size', '1080x1920', 'xc:none', '(', logo, '-resize', '125x125', ')', '-geometry', '+88+285', '-composite',
  '-font', '/System/Library/Fonts/Supplemental/Arial Bold.ttf', '-stroke', 'none', '-fill', '#f4faf6',
  '-pointsize', '76', '-gravity', 'northwest', '-annotate', '+238+300', 'GrowPOS',
  '-font', font, '-fill', '#c3d5cb', '-pointsize', '35', '-annotate', '+240+397', 'Satu Sistem, Tumbuh Bersama.', endcard,
], 'Creating the GrowPOS end card');

const graph = [
  "[0:v]drawbox=x=150:y=1370:w='min(760\\,1+t*900)':h=4:color=0x2bb77a@0.95:t=fill:enable='between(t,0.08,2.9)'[flow]",
  "[1:v]format=rgba,fade=t=in:st=9.0:d=0.6:alpha=1,fade=t=out:st=12.1:d=0.6:alpha=1[cap1]",
  "[2:v]format=rgba,fade=t=in:st=13.0:d=0.5:alpha=1,fade=t=out:st=14.3:d=0.5:alpha=1[cap2]",
  "[3:v]format=rgba,fade=t=in:st=24.0:d=0.45:alpha=1,fade=t=out:st=25.1:d=0.35:alpha=1[cap3]",
  "[4:v]format=rgba,fade=t=in:st=25.6:d=0.7:alpha=1[end]",
  '[flow][cap1]overlay=0:0:shortest=1[v1]',
  '[v1][cap2]overlay=0:0:shortest=1[v2]',
  '[v2][cap3]overlay=0:0:shortest=1[v3]',
  '[v3][end]overlay=0:0:shortest=1[v]',
].join(';');

run('ffmpeg', [
  '-hide_banner', '-loglevel', 'error', '-y', '-framerate', String(fps), '-i', path.join(outputFrames, 'frame-%04d.png'),
  '-loop', '1', '-framerate', String(fps), '-i', cashierCaption,
  '-loop', '1', '-framerate', String(fps), '-i', stockCaption,
  '-loop', '1', '-framerate', String(fps), '-i', reportCaption,
  '-loop', '1', '-framerate', String(fps), '-i', endcard,
  '-filter_complex', graph, '-map', '[v]', '-t', String(duration), '-an', '-r', String(fps),
  '-c:v', 'libx264', '-preset', 'slow', '-crf', '17', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', output,
], 'Encoding the 28-second Reels film');

run('ffmpeg', [
  '-hide_banner', '-loglevel', 'error', '-y', '-i', output,
  '-vf', "select='not(mod(n,120))',scale=154:274,tile=7x2", '-frames:v', '1', contact,
], 'Building the full-film contact sheet');

console.log(`Film: ${output}`);
console.log(`Contact sheet: ${contact}`);

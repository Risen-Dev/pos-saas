import { mkdir } from 'node:fs/promises';
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '..');
const work = path.join(here, 'dist/v2-work');
const output = path.join(here, 'dist/growpos-reels-v2.mp4');
const source = path.join(root, 'public/video/growpos-demo.mp4');
const device = path.join(here, 'public/laptop-studio-v2.png');
const logoWhite = path.join(root, 'public/img/growpos-white.png');
const font = '/System/Library/Fonts/Supplemental/Arial.ttf';
const w = 1080;
const h = 1920;
const fps = 60;
const cross = 0.4;

await mkdir(work, { recursive: true });
await mkdir(path.dirname(output), { recursive: true });

function magick(args, label) {
  const result = spawnSync('magick', args, { stdio: 'inherit' });
  if (result.status !== 0) throw new Error(`Image preparation failed: ${label}`);
}
function ffmpeg(args, label) {
  const result = spawnSync('ffmpeg', args, { stdio: 'inherit' });
  if (result.status !== 0) throw new Error(`Render failed: ${label}`);
}

const laptopPlate = path.join(work, 'laptop-portrait.png');
magick([
  '-size', `${w}x${h}`, 'gradient:#071713-#1c4938',
  '(', device, '-resize', '1680x945!', ')', '-geometry', '-300+440', '-composite',
  laptopPlate,
], 'laptop plate');

function makeText(name, lines, options = {}) {
  const file = path.join(work, `${name}-text.png`);
  const args = ['-size', `${w}x${h}`, 'xc:none', '-font', font];
  for (const line of lines) {
    args.push('-fill', line.color ?? '#f5fbf7', '-pointsize', String(line.size ?? 44), '-gravity', line.gravity ?? 'north', '-annotate', `+${line.x ?? 0}+${line.y}`, line.text);
  }
  if (options.logo) {
    args.push('(', logoWhite, '-resize', '64x64', ')', '-gravity', 'north', '-geometry', '+0+105', '-composite');
  }
  args.push(file);
  magick(args, `${name} typography`);
  return file;
}

const textLayers = {
  reveal: makeText('reveal', [{ text: 'Kenalkan GrowPOS.', size: 56, y: 240 }]),
  cashier: makeText('cashier', [{ text: 'Kasir lebih praktis.', size: 44, y: 150 }]),
  stock: makeText('stock', [{ text: 'Pantau stok dengan mudah.', size: 42, y: 150 }]),
  reports: makeText('reports', [{ text: 'Semua lebih terpantau.', size: 42, y: 150 }]),
  outro: makeText('outro', [
    { text: 'GrowPOS', size: 68, y: 180 },
    { text: 'Satu Sistem, Tumbuh Bersama.', size: 32, y: 265 },
  ], { logo: true }),
};

const scenes = [
  { name: '01-reveal', seconds: 4.4, kind: 'device', clip: 0, label: textLayers.reveal, zoom: 'min(1.18,1+0.18*on/(4.4*60))' },
  { name: '02-cashier', seconds: 7.6, kind: 'immersive', clip: 6.1, sourceSeconds: 6.9, stretch: 1.1, x: 350, drift: 550, cropWidth: 650, label: textLayers.cashier },
  { name: '03-stock', seconds: 6.4, kind: 'immersive', clip: 14.0, sourceSeconds: 5.2, stretch: 1.23, x: 200, drift: 30, cropWidth: 800, label: textLayers.stock },
  { name: '04-analytics', seconds: 10.2, kind: 'immersive', clip: 19.8, sourceSeconds: 7.0, hold: 3.2, x: 230, drift: 40, cropWidth: 900, label: textLayers.reports },
  { name: '05-hero', seconds: 8.0, kind: 'device', clip: 20.0, sourceSeconds: 6.8, hold: 1.2, label: textLayers.outro, zoom: 'max(1,1.18-0.18*on/(8*60))' },
];

for (const scene of scenes) {
  const outputClip = path.join(work, `${scene.name}.mp4`);
  const args = ['-hide_banner', '-loglevel', 'error', '-y'];
  if (scene.kind === 'device') {
    args.push('-loop', '1', '-framerate', String(fps), '-i', laptopPlate);
    args.push('-loop', '1', '-framerate', String(fps), '-i', scene.label);
    args.push('-ss', String(scene.clip), '-t', String(scene.sourceSeconds ?? scene.seconds), '-i', source);
    const hold = scene.hold ? `,tpad=stop_mode=clone:stop_duration=${scene.hold}` : '';
    const zoom = scene.zoom;
    args.push('-filter_complex',
      `[1:v]format=rgba,fade=t=in:st=0.2:d=0.5:alpha=1,fade=t=out:st=${scene.name === '01-reveal' ? 2.4 : 6.6}:d=0.5:alpha=1[text];`+
      `[2:v]scale=955:536:flags=lanczos${hold},fps=${fps},format=rgba,fade=t=in:st=${scene.name === '01-reveal' ? 0.8 : 0}:d=0.7:alpha=1[ui];`+
      `[0:v][text]overlay=0:0:shortest=1[plate];[plate][ui]overlay=67:557:shortest=1[shot];`+
      `[shot]zoompan=z='${zoom}':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1:s=${w}x${h}:fps=${fps},format=yuv420p[v]`);
  } else {
    args.push('-ss', String(scene.clip), '-t', String(scene.sourceSeconds + (scene.hold ?? 0)), '-i', source);
    const temporal = [
      scene.stretch ? `setpts=${scene.stretch}*(PTS-STARTPTS)` : null,
      scene.hold ? `tpad=stop_mode=clone:stop_duration=${scene.hold}` : null,
      `fps=${fps}`,
    ].filter(Boolean).join(',');
    const cropWidth = scene.cropWidth ?? 650;
    const cropHeight = 900;
    const displayHeight = Math.round((1080 * cropHeight / cropWidth) / 2) * 2;
    const displayY = Math.round((h - displayHeight) / 2);
    const x = `min(iw-${cropWidth},${scene.x}+${scene.drift}*min(t/${scene.seconds},1))`;
    args.push('-loop', '1', '-framerate', String(fps), '-i', scene.label);
    args.push('-filter_complex',
      `[0:v]${temporal},split=2[srcbg][srcui];`+
      `[srcbg]scale=${w}:${h}:force_original_aspect_ratio=increase,crop=${w}:${h},boxblur=40:18,eq=brightness=-0.62:saturation=0.58[bg];`+
      `[srcui]crop=${cropWidth}:${cropHeight}:x='${x}':y=0,scale=1080:${displayHeight}:flags=lanczos,format=yuv420p[ui];`+
      `[bg][ui]overlay=0:${displayY}[shot];[1:v]format=rgba,fade=t=in:st=0.25:d=0.45:alpha=1,fade=t=out:st=${scene.seconds-0.6}:d=0.5:alpha=1[text];`+
      `[shot][text]overlay=0:0,zoompan=z='min(1.08,1+0.08*on/${scene.seconds*fps})':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1:s=${w}x${h}:fps=${fps},format=yuv420p[v]`);
  }
  args.push('-map', '[v]', '-t', String(scene.seconds), '-an', '-r', String(fps), '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '17', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', outputClip);
  ffmpeg(args, scene.name);
}

const parts = scenes.map((scene) => path.join(work, `${scene.name}.mp4`));
const inputs = parts.flatMap((part) => ['-i', part]);
const transitions = ['smoothleft', 'smoothup', 'smoothright', 'fade'];
let filter = '';
let previous = '0:v';
let elapsed = scenes[0].seconds;
for (let i = 1; i < scenes.length; i++) {
  const label = `join${i}`;
  filter += `[${previous}][${i}:v]xfade=transition=${transitions[i-1]}:duration=${cross}:offset=${elapsed-cross}[${label}];`;
  previous = label;
  elapsed += scenes[i].seconds - cross;
}
const totalDuration = scenes.reduce((sum, scene) => sum + scene.seconds, 0) - cross * (scenes.length - 1);
ffmpeg([
  '-hide_banner', '-loglevel', 'error', '-y', ...inputs,
  '-filter_complex', `${filter}[${previous}]fps=${fps},format=yuv420p[v]`, '-map', '[v]', '-t', String(totalDuration),
  '-an', '-r', String(fps), '-c:v', 'libx264', '-preset', 'slow', '-crf', '17', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', output,
], 'final V2 assembly');
console.log(`Rendered V2: ${output} (${totalDuration.toFixed(1)} seconds)`);

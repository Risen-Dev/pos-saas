import fs from 'node:fs';
const sr=48000, seconds=30, N=sr*seconds;
const left=new Float32Array(N), right=new Float32Array(N);
const chords=[[146.83,185,220,277.18],[110,146.83,174.61,220],[123.47,155.56,185,233.08],[98,123.47,146.83,196]];
function env(t,a,d){const attack=Math.min(1,t/a);const release=Math.min(1,(d-t)/Math.min(.9,d*.25));return Math.max(0,Math.min(attack,release));}
function add(t0,dur,freq,amp,pan=0,wave='sine'){
 const start=Math.floor(t0*sr), end=Math.min(N,Math.floor((t0+dur)*sr));
 for(let i=start;i<end;i++) {const t=(i-start)/sr;const ph=2*Math.PI*freq*t;let v=wave==='tri'?(2/Math.PI)*Math.asin(Math.sin(ph)):Math.sin(ph); const e=env(t,dur>.3?.16:.008,dur);const a=amp*e;left[i]+=v*a*Math.sqrt((1-pan)/2);right[i]+=v*a*Math.sqrt((1+pan)/2);}
}
for(let bar=0;bar<15;bar++){
 const base=bar*2, chord=chords[Math.floor(bar/4)%4];
 chord.forEach((f,j)=>{add(base,1.98,f,.045,j*.18-.27);add(base,1.98,f*2,.012,.2-j*.13);});
 add(base,1.1,chord[0]/2,.065,-.15,'tri');
 // restrained pulse and a soft glassy note at the second beat
 for(let beat=0;beat<4;beat++){
  const t=base+beat*.5;
  add(t,.20,48,.06,.05,'tri');
  if(beat%2===1)add(t,.38,chord[(bar+beat)%4]*2,.018,.35);
 }
}
function click(t0,amp=.045){for(let j=0;j<sr*.08;j++){let i=Math.floor(t0*sr)+j;if(i<N){const t=j/sr;const v=Math.sin(2*Math.PI*(1800-900*t)*t)*Math.exp(-t*45)*amp;left[i]+=v*.8;right[i]+=v;}}}
[6.15,7.7,10.1,15.1,20.1,25.1].forEach(t=>click(t,.023));
click(9.58,.09);
// 30ms fade in, 450ms fade out and conservative peak limit.
let peak=0;for(let i=0;i<N;i++)peak=Math.max(peak,Math.abs(left[i]),Math.abs(right[i]));const gain=Math.min(1,.82/peak);
const data=Buffer.alloc(N*4);for(let i=0;i<N;i++){const fade=Math.min(1,i/(sr*.03),(N-i)/(sr*.45));data.writeInt16LE(Math.max(-32767,Math.min(32767,Math.round(left[i]*gain*fade*32767))),i*4);data.writeInt16LE(Math.max(-32767,Math.min(32767,Math.round(right[i]*gain*fade*32767))),i*4+2);}
const h=Buffer.alloc(44);h.write('RIFF',0);h.writeUInt32LE(36+data.length,4);h.write('WAVE',8);h.write('fmt ',12);h.writeUInt32LE(16,16);h.writeUInt16LE(1,20);h.writeUInt16LE(2,22);h.writeUInt32LE(sr,24);h.writeUInt32LE(sr*4,28);h.writeUInt16LE(4,32);h.writeUInt16LE(16,34);h.write('data',36);h.writeUInt32LE(data.length,40);
fs.writeFileSync('public/reel/growpos-original-score.wav',Buffer.concat([h,data]));

import React from 'react';
import {Easing, interpolate, staticFile, useCurrentFrame} from 'remotion';
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/inter/800.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import '@fontsource/plus-jakarta-sans/800.css';
import '@fontsource/ibm-plex-mono/500.css';
import '@fontsource/ibm-plex-mono/600.css';

const C = {
  green: '#17694d', ink: '#18372d', muted: '#65796f', border: '#e0e8df',
  canvas: '#f5f7f3', white: '#fffefa', beige: '#f2eee4', gold: '#867450',
  mint: '#dcece2', dark: '#103d2e', pale: '#e9f1e9',
};
const ease = Easing.bezier(.22, .8, .22, 1);
const clamp = {extrapolateLeft: 'clamp', extrapolateRight: 'clamp'};
const tx = (f,a,b,from,to) => interpolate(f,[a,b],[from,to],{...clamp,easing:ease});
const op = (f,a,b) => interpolate(f,[a,b],[0,1],{...clamp,easing:ease});
const shadow = '0 22px 64px rgba(24,55,45,.11), 0 3px 12px rgba(24,55,45,.04)';

function Wordmark(){return <div style={{display:'flex',alignItems:'center',gap:10}}>
  <img src={staticFile('growpos_logo.png')} style={{width:49,height:49,objectFit:'contain'}} />
  <div><div style={{fontSize:22,fontWeight:800,letterSpacing:-.65,color:C.ink}}>grow<span style={{color:C.green}}>POS</span></div><div style={{fontSize:11,letterSpacing:1.1,color:C.muted,fontWeight:600,marginTop:1}}>KEDAI SENJA DEMO</div></div>
</div>}

function ProductCard({item, index, frame, selected, style={}}){
  const enter = tx(frame, 8+index*5, 39+index*5, 38, 0);
  const visible = op(frame, 5+index*4, 28+index*4);
  const press = selected ? interpolate(frame,[150,162,176,198],[0,1,0,0],{...clamp,easing:ease}) : 0;
  const focus = selected ? interpolate(frame,[135,152,202,232],[0,1,1,0],clamp) : 0;
  return <div style={{position:'relative',height:300,borderRadius:25,background:C.white,border:`1.5px solid ${focus?C.green:C.border}`,boxShadow:focus?'0 22px 46px rgba(23,105,77,.15)':shadow,overflow:'hidden',transform:`translateY(${enter-press*5}px) scale(${1+focus*.012-press*.012})`,opacity:visible,...style}}>
    <div style={{height:164,margin:12,borderRadius:17,background:C.beige,display:'grid',placeItems:'center',position:'relative',overflow:'hidden'}}>
      <div style={{position:'relative',zIndex:1,fontSize:45,fontWeight:700,letterSpacing:-1.8,color:C.gold}}>{item.initial}</div>
    </div>
    <div style={{padding:'1px 17px 16px'}}><div style={{height:38,fontSize:15,fontWeight:700,lineHeight:1.28,letterSpacing:-.2,color:C.ink}}>{item.name}</div><div style={{display:'flex',justifyContent:'space-between',alignItems:'baseline',marginTop:6}}><div style={{fontSize:16,fontWeight:800,color:C.green,letterSpacing:-.4}}>{item.price}<span style={{fontSize:10,fontWeight:500,color:C.muted}}> / {item.unit}</span></div><div style={{fontSize:10,color:C.muted}}>Stok {item.stock}</div></div></div>
    {selected&&frame>142&&frame<217?<div style={{position:'absolute',right:16,top:16,width:30,height:30,borderRadius:'50%',background:C.green,color:'white',display:'grid',placeItems:'center',opacity:focus,transform:`scale(${.7+focus*.3})`,boxShadow:'0 5px 12px #17694d38'}}><svg width="15" height="15" viewBox="0 0 16 16"><path d="m3 8 3.2 3.2L13 4.5" fill="none" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/></svg></div>:null}
  </div>
}

function Cursor({frame}){
  const x=interpolate(frame,[20,100,136,150,163,220,260,280,300],[1030,465,340,330,330,790,918,540,540],clamp);
  const y=interpolate(frame,[20,100,136,150,163,220,260,280,300],[1130,650,650,646,646,1070,1212,1465,1465],clamp);
  const click=interpolate(frame,[150,158,168],[1,1,.78],clamp);
  const visible=interpolate(frame,[30,40,300,311],[0,1,1,0],clamp);
  return <div style={{position:'absolute',left:x,top:y,zIndex:20,opacity:visible,transform:`scale(${click})`,filter:'drop-shadow(0 4px 5px rgba(14,43,31,.28))',pointerEvents:'none'}}><svg width="43" height="53" viewBox="0 0 43 53"><path d="M5 3.5c-.3-1.8 1.7-2.8 3-1.4l30 29c1.7 1.6.5 4.2-1.8 4.1l-12.2-.6 6.5 11.2c.8 1.4.3 3.1-1.1 3.9l-3.1 1.8c-1.4.8-3.1.3-3.9-1.1l-6.7-11.5-6 9.1c-1.2 1.8-4 1.1-4.2-1.1L5 3.5Z" fill="white" stroke={C.ink} strokeWidth="2.5" strokeLinejoin="round"/><path d="m8 8 2.8 30" stroke="#ffffff" strokeWidth="1.2" opacity=".8"/></svg></div>
}

function FlyingProduct({frame}){
  const p=interpolate(frame,[203,216,250,267],[0,0,1,1],{...clamp,easing:Easing.bezier(.34,.05,.65,.98)});
  const fade=interpolate(frame,[254,270],[1,0],clamp)*op(frame,204,214);
  const x=interpolate(p,[0,.25,.55,1],[318,350,253,117],clamp);
  const y=interpolate(p,[0,.25,.55,1],[660,735,1015,1205],clamp);
  return <div style={{position:'absolute',left:x,top:y,zIndex:30,opacity:fade,transform:`translate(-50%,-50%) rotate(${interpolate(p,[0,1],[-1,2],clamp)}deg) scale(${1-p*.45})`,width:190,height:123,borderRadius:18,background:C.white,border:`1px solid ${C.border}`,boxShadow:'0 20px 46px #18372d2b',padding:12,display:'flex',gap:12,alignItems:'center'}}><div style={{width:72,height:91,borderRadius:13,background:'#eee7d9',display:'grid',placeItems:'center',fontWeight:800,color:C.gold,fontSize:24}}>KO</div><div><div style={{fontSize:13,fontWeight:750,color:C.ink,lineHeight:1.25}}>Kopi Susu<br/>Gula Aren</div><div style={{fontSize:13,fontWeight:800,color:C.green,marginTop:10}}>Rp 19.800</div></div></div>
}

function Cart({frame}){
  const reveal=tx(frame,220,294,1100,0);
  const settled=tx(frame,294,323,18,0);
  const qty=op(frame,275,294);
  const lift=interpolate(frame,[263,291,318],[18,0,0],{...clamp,easing:Easing.bezier(.3,.1,.25,1)});
  return <div style={{position:'absolute',left:58,right:58,top:1060,transform:`translate(${reveal}px,${settled-lift}px)`,borderRadius:29,background:C.white,border:`1px solid ${C.border}`,boxShadow:'0 26px 80px rgba(14,61,44,.16),0 5px 18px rgba(14,61,44,.05)',padding:'25px 27px 25px',opacity:op(frame,216,250),zIndex:10}}>
    <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',paddingBottom:20,borderBottom:`1px solid ${C.border}`}}><div><div style={{fontSize:21,fontWeight:800,letterSpacing:-.55,color:C.ink}}>Pesanan sekarang</div><div style={{fontSize:12,color:C.muted,marginTop:5}}>Langsung / ambil sendiri</div></div><div style={{padding:'8px 12px',borderRadius:12,background:C.pale,color:C.green,fontSize:12,fontWeight:750,opacity:qty,transform:`translateY(${(1-qty)*7}px)`}}>1 item</div></div>
    <div style={{height:105,display:'flex',alignItems:'center',gap:17,borderBottom:`1px solid ${C.border}`}}><div style={{width:63,height:69,borderRadius:14,background:'linear-gradient(145deg,#f5f0e5,#e9e0cf)',display:'grid',placeItems:'center',fontSize:17,fontWeight:800,color:C.gold}}>KO</div><div style={{flex:1}}><div style={{fontSize:15,fontWeight:750,color:C.ink}}>Kopi Susu Gula Aren</div><div style={{fontSize:12,color:C.muted,marginTop:5}}>1 gelas</div></div><div style={{fontSize:15,fontWeight:800,color:C.ink}}>Rp 19.800</div></div>
    <div style={{paddingTop:17,display:'grid',gap:12}}><MoneyRow label="Subtotal" value="Rp 22.000"/><div style={{opacity:op(frame,268,286),transform:`translateY(${tx(frame,268,286,6,0)}px)`}}><MoneyRow label="Diskon menu" value="−Rp 2.200" muted/></div><div style={{height:1,background:C.border,margin:'2px 0 1px'}}/><div style={{display:'flex',alignItems:'end',justifyContent:'space-between'}}><div style={{fontSize:17,fontWeight:800,color:C.ink}}>Total</div><CountUp frame={frame}/></div></div>
    <div style={{height:54,borderRadius:15,background:C.green,color:'white',display:'flex',alignItems:'center',justifyContent:'center',gap:9,marginTop:19,fontSize:14,fontWeight:750,boxShadow:'0 9px 17px rgba(23,105,77,.18)'}}>Lanjutkan pesanan <span style={{fontSize:18}}>→</span></div>
  </div>
}
function MoneyRow({label,value,muted}){return <div style={{display:'flex',justifyContent:'space-between',fontSize:13,color:muted?C.muted:C.ink}}><span>{label}</span><span style={{fontWeight:muted?550:700,color:muted?C.muted:C.ink}}>{value}</span></div>}
function CountUp({frame}){const n=Math.round(interpolate(frame,[258,306],[22000,19800],{...clamp,easing:ease}));const v='Rp '+n.toLocaleString('id-ID');return <div style={{fontFamily:'"IBM Plex Mono","SFMono-Regular",monospace',fontVariantNumeric:'tabular-nums',fontSize:23,fontWeight:800,letterSpacing:-.7,color:C.green}}>{v}</div>}

export const GrowPOSScene = () => {
  const f=useCurrentFrame();
  const titleY=tx(f,13,53,25,0), reveal=op(f,0,34);
  const camera=interpolate(f,[210,305,358,371],[0,-35,-35,-35],{...clamp,easing:ease});
  const products=[
    {initial:'KO',name:'Kopi Susu Gula Aren',price:'Rp 19.800',unit:'gelas',stock:'48 gelas'},
    {initial:'AM',name:'Americano',price:'Rp 16.200',unit:'gelas',stock:'31 gelas'},
    {initial:'MA',name:'Matcha Latte',price:'Rp 26.000',unit:'gelas',stock:'25 gelas'},
    {initial:'RO',name:'Roti Bakar Cokelat',price:'Rp 18.000',unit:'porsi',stock:'18 porsi'},
  ];
  return <div style={{width:1080,height:1920,position:'relative',overflow:'hidden',background:C.canvas,color:C.ink,fontFamily:'"Plus Jakarta Sans",Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif'}}>
    <div style={{position:'absolute',inset:0,background:'radial-gradient(ellipse at 78% 5%,rgba(197,223,201,.40),transparent 29%),radial-gradient(ellipse at 4% 66%,rgba(224,235,220,.56),transparent 36%)'}}/>
    <div style={{position:'absolute',left:0,right:0,top:0,height:11,background:C.green,transform:`scaleX(${op(f,0,26)})`,transformOrigin:'left'}}/>
    <div style={{position:'relative',padding:'74px 74px 90px',transform:`translateY(${camera}px) scale(${interpolate(f,[210,305,358],[1,.985,.985],{...clamp,easing:ease})})`,transformOrigin:'50% 12%',opacity:reveal}}>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',transform:`translateY(${titleY}px)`,opacity:op(f,8,40)}}>
        <Wordmark/><div style={{display:'flex',alignItems:'center',gap:9,padding:'10px 13px',background:'#fffefaaf',border:`1px solid ${C.border}`,borderRadius:14,color:C.muted,fontSize:11,fontWeight:650}}><span style={{width:7,height:7,borderRadius:9,background:'#41a66c',boxShadow:'0 0 0 4px #41a66c1d'}}/>Jaringan tersedia</div>
      </div>
      <div style={{marginTop:62,transform:`translateY(${titleY}px)`,opacity:op(f,17,52)}}><div style={{fontSize:11,fontWeight:800,letterSpacing:2,color:C.green}}>TERMINAL KASIR</div><div style={{fontSize:47,lineHeight:1.12,letterSpacing:-2.4,fontWeight:800,color:C.ink,marginTop:14}}>Ada pesanan<br/>apa?</div><div style={{fontSize:14,color:C.muted,marginTop:13}}>Pilih produk atau layanan untuk memulai.</div></div>
      <div style={{height:62,borderRadius:17,marginTop:34,padding:'0 19px',background:C.white,border:`1px solid ${C.border}`,boxShadow:'0 8px 20px rgba(24,55,45,.035)',display:'flex',alignItems:'center',gap:14,opacity:op(f,35,66),transform:`translateY(${tx(f,35,65,14,0)}px)`}}><svg width="19" height="19" viewBox="0 0 20 20" fill="none"><circle cx="8.8" cy="8.8" r="5.8" stroke={C.muted} strokeWidth="1.7"/><path d="m13.2 13.2 4.2 4.2" stroke={C.muted} strokeWidth="1.7" strokeLinecap="round"/></svg><span style={{fontSize:13,color:'#8d9b92'}}>Cari produk, layanan, atau barcode…</span></div>
      <div style={{display:'flex',gap:10,marginTop:17,opacity:op(f,43,72),transform:`translateX(${tx(f,40,69,22,0)}px)`}}>{['Semua item','★ Favorit','Minuman','Makanan'].map((x,i)=><div key={x} style={{whiteSpace:'nowrap',borderRadius:12,padding:'10px 14px',fontSize:11,fontWeight:700,background:i===0?C.green:C.white,color:i===0?'white':C.muted,border:i===0?'1px solid transparent':`1px solid ${C.border}`,boxShadow:i===0?'0 5px 12px #17694d20':'none'}}>{x}</div>)}</div>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginTop:21,marginBottom:14,opacity:op(f,55,83)}}><div style={{fontSize:11,color:C.muted,fontWeight:650}}>Tampilkan</div><div style={{display:'flex',alignItems:'center',gap:15,padding:'9px 13px',background:C.white,border:`1px solid ${C.border}`,borderRadius:11,fontSize:11,fontWeight:650,color:C.ink}}>Barang &amp; jasa <span style={{color:C.muted}}>⌄</span></div></div>
      <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:14}}>{products.map((item,i)=><ProductCard key={item.initial} item={item} index={i} frame={f} selected={i===0}/>)}</div>
    </div>
    <div style={{position:'absolute',left:74,top:1327,opacity:op(f,71,104)*(1-op(f,212,237)),pointerEvents:'none'}}>
      <div style={{width:62,height:2,background:C.green,marginBottom:18,transform:`scaleX(${op(f,75,101)})`,transformOrigin:'left'}}/>
      <div style={{fontFamily:'"Plus Jakarta Sans",sans-serif',fontSize:12,fontWeight:700,letterSpacing:3.2,color:C.green,marginBottom:11}}>GROWPOS / VISUAL STUDY 01</div>
      <div style={{fontSize:65,lineHeight:.98,fontWeight:500,letterSpacing:-3.2,color:C.ink}}>THE ART OF</div>
      <div style={{fontSize:91,lineHeight:.98,fontWeight:800,letterSpacing:-5.5,color:C.green}}>SELLING</div>
    </div>
    <Cart frame={f}/>
    <FlyingProduct frame={f}/>
    <Cursor frame={f}/>
    <div style={{position:'absolute',left:74,bottom:48,fontSize:10,letterSpacing:1.6,fontWeight:700,color:'#9ba99f',opacity:op(f,320,343)}}>GROWPOS <span style={{padding:'0 8px',color:'#c0cac2'}}>•</span> THE ART OF SELLING</div>
    <div style={{position:'absolute',right:74,bottom:48,fontFamily:'"IBM Plex Mono",monospace',fontSize:10,color:'#9ba99f',opacity:op(f,320,343)}}>01 / 01</div>
  </div>
};

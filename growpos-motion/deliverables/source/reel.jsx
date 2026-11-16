import React from 'react';
import '@fontsource/inter/400.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import {AbsoluteFill, Audio, Img, Sequence, interpolate, staticFile, useCurrentFrame} from 'remotion';

const scenes = [
  {start:0,duration:90,bg:'01-opening.png',ui:'ui-opening.png',line:'Setiap transaksi punya cerita.', kicker:'EVERY SALE MATTERS'},
  {start:90,duration:90,bg:'02-cashier.png',ui:'ui-cashier.png',line:'Jualan lebih simpel.', kicker:'DI BALIK SETIAP PESANAN'},
  {start:180,duration:120,bg:'03-laptop.png',ui:'ui-laptop.png',line:'Semua lebih tertata.', kicker:'PESANAN • STOK • OPERASIONAL'},
  {start:300,duration:150,bg:'04-devices.png',ui:'ui-laptop-stock.png',phone:'ui-phone.png',line:'Alur kerja yang terhubung.', kicker:'GROWPOS / POINT OF SALE'},
  {start:450,duration:150,bg:'05-analytics.png',ui:'ui-analytics.png',line:'Pahami perkembangan bisnismu.', kicker:'LIHAT USAHA LEBIH JELAS'},
  {start:600,duration:150,bg:'02-cashier.png',ui:'ui-cashier.png',line:'Fokus ke yang penting.',second:'Kembangkan bisnismu.',kicker:'WAKTU UNTUK MELAYANI'},
  {start:750,duration:150,bg:'06-hero.png',ui:'ui-hero-laptop.png',phone:'ui-hero-phone.png',brand:true},
];
const ease={extrapolateLeft:'clamp',extrapolateRight:'clamp'};
function Shot({item}){
  const f=useCurrentFrame();
  // Sequence resets useCurrentFrame() to zero for each shot.
  const local=f;
  const n=local/item.duration;
  const scale=interpolate(n,[0,1],[1.035,1.105],ease);
  const x=interpolate(n,[0,1],[0,-12],ease);
  const opacityIn=interpolate(local,[0,12],[0,1],ease);
  const opacityOut=interpolate(local,[item.duration-10,item.duration],[1,0],ease);
  const textOpacity=opacityIn*opacityOut;
  const secondOpacity=item.second?interpolate(local,[64,80,140,150],[0,1,1,0],ease):0;
  return <AbsoluteFill style={{overflow:'hidden',backgroundColor:'#0b2119'}}>
    <Img src={staticFile(`reel/${item.bg}`)} style={{position:'absolute',inset:0,width:'100%',height:'100%',objectFit:'cover',transform:`scale(${scale}) translateX(${x}px)`}} />
    {item.ui&&<Img src={staticFile(`reel/${item.ui}`)} style={{position:'absolute',inset:0,width:'100%',height:'100%',objectFit:'fill',transform:`scale(${scale}) translateX(${x}px)`}} />}
    {item.phone&&<Img src={staticFile(`reel/${item.phone}`)} style={{position:'absolute',inset:0,width:'100%',height:'100%',objectFit:'fill',transform:`scale(${scale}) translateX(${x}px)`}} />}
    <AbsoluteFill style={{background:'linear-gradient(180deg, rgba(5,19,14,.49) 0%, rgba(5,19,14,.07) 36%, rgba(5,19,14,0) 64%, rgba(5,19,14,.30) 100%)'}} />
    {!item.brand&&<div style={{position:'absolute',left:90,top:260,width:880,opacity:textOpacity,color:'#fff',fontFamily:'Inter, Arial, sans-serif',textShadow:'0 3px 22px rgba(0,0,0,.38)'}}>
      <div style={{fontSize:20,fontWeight:700,letterSpacing:4,color:'#b9e4cf',marginBottom:22}}>{item.kicker}</div>
      <div style={{fontSize:64,lineHeight:1.12,fontWeight:700,letterSpacing:-1.4,maxWidth:850}}>{item.line}</div>
      {item.second&&<div style={{position:'absolute',top:126,fontSize:57,lineHeight:1.12,fontWeight:700,letterSpacing:-1.2,opacity:secondOpacity}}>{item.second}</div>}
      <div style={{marginTop:31,width:70,height:4,background:'#75c89e',borderRadius:4}} />
    </div>}
    {item.brand&&<div style={{position:'absolute',left:100,right:90,top:220,color:'white',fontFamily:'Inter, Arial, sans-serif',textShadow:'0 3px 20px rgba(0,0,0,.35)',opacity:opacityIn*opacityOut}}>
      <div style={{display:'flex',alignItems:'center',gap:22}}><Img src={staticFile('reel/logo.png')} style={{width:86,height:86,objectFit:'contain'}}/><div style={{fontSize:70,fontWeight:800,letterSpacing:-2}}>GrowPOS</div></div>
      <div style={{fontSize:30,fontWeight:600,letterSpacing:.1,marginTop:28}}>Satu Sistem, Tumbuh Bersama.</div>
      <div style={{marginTop:30,display:'inline-block',padding:'16px 24px',border:'1.5px solid rgba(255,255,255,.75)',borderRadius:40,fontSize:21,fontWeight:600}}>Kenalan dengan GrowPOS</div>
    </div>}
  </AbsoluteFill>;
}
export function GrowPOSReel(){
  return <AbsoluteFill style={{background:'#071a12'}}>
    {scenes.map((s,i)=><Sequence key={i} from={s.start} durationInFrames={s.duration}><Shot item={s}/></Sequence>)}
    <Audio src={staticFile('reel/growpos-original-score.wav')} volume={0.78}/>
  </AbsoluteFill>;
}

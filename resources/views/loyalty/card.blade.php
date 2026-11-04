<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
            <meta name="theme-color" content="#eaf3ee">
    <title>Kartu Member {{ $tenant->name }} — GrowPOS</title>
    @vite(['resources/css/app.css', 'resources/js/loyalty-card.js'])
    <style>
        :root {
            color-scheme: light;
            --loyalty-paper: #eee9e1;
            --loyalty-paper-light: #f8f5ef;
            --loyalty-ink: #211e1a;
            --loyalty-muted: #777168;
            --loyalty-gold: #c8a66a;
            --loyalty-gold-bright: #f0d89c;
            --loyalty-card: #171614;
            --loyalty-motion: cubic-bezier(.2,.75,.25,1);
        }
        * { box-sizing: border-box; }
        html, body { width: 100%; min-width: 320px; height: 100%; margin: 0; }
        body { overflow: hidden; color: var(--loyalty-ink); background: radial-gradient(ellipse at 48% -34%, #fffdf9 2%, var(--loyalty-paper-light) 42%, var(--loyalty-paper) 100%); font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        button { font: inherit; }
        .loyalty-page { width: min(100%, 1440px); height: 100vh; height: 100dvh; margin-inline: auto; padding: max(18px, env(safe-area-inset-top)) clamp(20px, 4vw, 62px) max(16px, env(safe-area-inset-bottom)); display: grid; grid-template-rows: auto minmax(0, 1fr) auto; gap: clamp(14px, 2.4vh, 26px); }
        .loyalty-header { display:flex; align-items:center; justify-content:space-between; min-width:0; gap:18px; }
        .merchant { display:flex; align-items:center; min-width:0; gap:14px; }
        .merchant-mark { display:grid; width:46px; height:46px; flex:0 0 auto; overflow:hidden; place-items:center; border:1px solid rgba(183,145,82,.45); border-radius:14px; color:#f4dfad; background:linear-gradient(145deg,#2e2921,#11100f); box-shadow:0 7px 16px rgba(40,31,18,.16), inset 0 1px rgba(255,255,255,.17); font-family:Georgia,serif; font-size:20px; }
        .merchant-mark img { width:100%; height:100%; object-fit:contain; padding:5px; background:#fff; }
        .merchant-copy { min-width:0; padding-left:14px; border-left:1px solid rgba(128,113,90,.35); }
        .merchant-copy h1 { max-width:min(48vw,420px); overflow:hidden; margin:0; font-family:Georgia,"Times New Roman",serif; font-size:clamp(18px,2vw,25px); font-weight:600; letter-spacing:-.025em; line-height:1.15; text-overflow:ellipsis; white-space:nowrap; }
        .merchant-copy p { margin:4px 0 0; color:var(--loyalty-muted); font-size:11px; letter-spacing:.1em; }
        .membership-state { display:inline-flex; align-items:center; gap:9px; flex:0 0 auto; padding:10px 17px; border:1px solid rgba(174,132,64,.58); border-radius:999px; color:#765923; background:rgba(255,252,246,.68); font-size:10px; font-weight:750; letter-spacing:.16em; text-transform:uppercase; box-shadow:0 5px 14px rgba(89,66,31,.06); }
        .state-dot { width:7px; height:7px; border-radius:50%; background:#ae8845; box-shadow:0 0 0 3px rgba(197,166,106,.18); }
        .loyalty-content { display:grid; width:100%; min-height:0; grid-template-columns:minmax(0,1.23fr) minmax(330px,.77fr); align-items:center; gap:clamp(30px,5vw,76px); }
        .card-column { display:flex; min-width:0; flex-direction:column; align-items:center; justify-content:center; gap:clamp(13px,2.6vh,24px); perspective:1500px; }
        .card-shell { position:relative; width:min(100%, 760px); aspect-ratio:1.586; transform-style:preserve-3d; transition:transform .82s var(--loyalty-motion); will-change:transform; }
        .card-shell.is-flipped { transform:rotateY(180deg); }
        .card-face { position:absolute; inset:0; overflow:hidden; border:1px solid rgba(239,204,132,.62); border-radius:clamp(20px,2.5vw,31px); color:#fbf5e7; background:radial-gradient(ellipse at var(--light-x,72%) var(--light-y,8%), rgba(242,205,132,.27), transparent 34%), radial-gradient(ellipse at 8% 105%, rgba(164,122,57,.2), transparent 43%), linear-gradient(137deg,#24211d 0%,#151412 48%,#201c17 100%); box-shadow:0 28px 24px rgba(39,31,19,.23), inset 0 1px 0 rgba(255,255,255,.17), inset 0 -1px 0 rgba(255,255,255,.08); backface-visibility:hidden; -webkit-backface-visibility:hidden; transform-style:preserve-3d; }
        .card-face::before { position:absolute; inset:0; content:""; pointer-events:none; opacity:.24; background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.82' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.16'/%3E%3C/svg%3E"); mix-blend-mode:soft-light; }
        .card-face::after { position:absolute; width:66%; aspect-ratio:1; right:-25%; bottom:-72%; border:1px solid rgba(231,190,113,.55); border-radius:50%; content:""; box-shadow:0 0 0 1px rgba(231,190,113,.13),0 0 0 25px rgba(231,190,113,.055),0 0 0 52px rgba(231,190,113,.035); pointer-events:none; }
        .card-front { padding:clamp(21px,3.4vw,42px); }
        .card-ornament { position:absolute; z-index:0; top:10%; right:4%; width:37%; max-width:260px; height:auto; overflow:visible; color:rgba(192,255,220,.28); pointer-events:none; filter:drop-shadow(0 9px 24px rgba(0,34,23,.12)); transform:translateZ(7px); }
        .card-ornament .ornament-core { fill:rgba(202,255,221,.055); stroke:rgba(202,255,221,.23); }
        .card-ornament .ornament-leaf { fill:url(#leafFoil); stroke:rgba(225,255,235,.72); stroke-width:1.2; }
        .card-ornament .ornament-vein { fill:none; stroke:rgba(224,255,236,.73); stroke-width:1.2; stroke-linecap:round; }
        .card-front::before { opacity:.15; }
        .card-front::after { z-index:0; width:72%; right:-26%; bottom:-78%; }
        .card-back { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; gap:clamp(18px,4vw,44px); padding:clamp(22px,3.7vw,46px); transform:rotateY(180deg); background:radial-gradient(ellipse at 20% 12%,rgba(242,205,132,.19),transparent 37%),linear-gradient(145deg,#211e19,#111110 76%); }
        .card-front-inner { position:relative; z-index:1; display:flex; height:100%; flex-direction:column; justify-content:space-between; transform:translateZ(26px); }
        .card-topline { display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .card-brand { display:flex; align-items:center; min-width:0; gap:12px; }
        .card-brand-mark { display:grid; width:42px; height:42px; flex:0 0 auto; overflow:hidden; place-items:center; border:1px solid var(--loyalty-gold); border-radius:13px; color:var(--loyalty-gold-bright); background:linear-gradient(145deg,rgba(255,255,255,.13),rgba(255,255,255,.035)); font-family:Georgia,serif; font-size:21px; box-shadow:inset 0 1px rgba(255,255,255,.2); }
        .card-brand-mark img { width:100%; height:100%; object-fit:contain; padding:4px; background:#fff; }
        .card-brand strong { display:block; color:#fffdf6; font-size:clamp(15px,1.8vw,20px); font-weight:680; letter-spacing:.015em; }
        .card-brand small { display:block; margin-top:4px; color:#c7b58e; font-size:9px; font-weight:700; letter-spacing:.28em; }
        .card-status { padding:8px 12px; border:1px solid rgba(219,180,104,.76); border-radius:999px; color:#ead29e; font-size:9px; font-weight:750; letter-spacing:.17em; white-space:nowrap; }
        .card-member { margin-top:clamp(20px,3.5vh,38px); }
        .eyebrow { display:block; color:#c3b391; font-size:9px; font-weight:750; letter-spacing:.27em; text-transform:uppercase; }
        .member-name { display:block; max-width:80%; overflow:hidden; margin-top:7px; color:#fff9ed; font-family:Georgia,"Times New Roman",serif; font-size:clamp(29px,4.1vw,48px); font-weight:500; letter-spacing:-.035em; line-height:1.05; text-overflow:ellipsis; white-space:nowrap; }
        .member-code { display:inline-flex; align-items:center; gap:8px; margin-top:12px; padding:6px 10px; border:1px solid rgba(224,192,132,.55); border-radius:9px; color:#f2dfb6; background:rgba(0,0,0,.18); font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:10px; font-weight:700; letter-spacing:.18em; }
        .card-lower { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:end; gap:15px; padding-top:clamp(12px,2.5vh,24px); border-top:1px solid rgba(231,203,156,.28); }
        .balance-label { display:block; color:#c7b58e; font-size:9px; font-weight:700; letter-spacing:.25em; text-transform:uppercase; }
        .balance-value { margin:4px 0 0; color:#f4d895; font-family:Georgia,"Times New Roman",serif; font-size:clamp(31px,5vw,54px); font-weight:500; letter-spacing:-.055em; line-height:1; font-variant-numeric:tabular-nums; text-shadow:0 4px 20px rgba(213,173,94,.18); }
        .balance-value span { color:#eee2c9; font-family:inherit; font-size:clamp(13px,1.5vw,17px); letter-spacing:0; }
        .progress-copy { min-width:110px; padding-bottom:3px; text-align:right; }
        .progress-copy strong { display:block; color:#f8f0df; font-size:13px; font-variant-numeric:tabular-nums; }
        .progress-copy span { display:block; margin-top:3px; color:#bdb19b; font-size:9px; }
        .card-progress { grid-column:1/-1; height:7px; margin-top:-7px; overflow:hidden; border:1px solid rgba(244,223,183,.17); border-radius:999px; background:rgba(0,0,0,.35); }
        .card-progress-fill { width:var(--progress); height:100%; border-radius:inherit; background:linear-gradient(90deg,#a47d40,#f0d69b 68%,#fff0c7); box-shadow:0 0 13px rgba(229,189,109,.48); animation:progress-in .9s .35s var(--loyalty-motion) both; }
        .stamp-row { grid-column:1/-1; display:flex; align-items:center; gap:clamp(3px,.65vw,8px); margin-top:-7px; }
        .stamp-token { display:grid; width:clamp(18px,3.2vw,31px); aspect-ratio:1; flex:1 1 0; max-width:34px; place-items:center; border:1px solid rgba(219,255,234,.3); border-radius:50%; color:rgba(223,255,236,.48); background:radial-gradient(circle at 32% 24%,rgba(255,255,255,.12),rgba(0,46,32,.34) 70%); box-shadow:inset 0 1px rgba(255,255,255,.1),0 3px 8px rgba(0,0,0,.12); transition:transform .35s var(--loyalty-motion),background .35s ease,box-shadow .35s ease; }
        .stamp-token svg { width:52%; height:52%; }
        .stamp-token.is-earned { border-color:rgba(218,255,169,.92); color:#e9ffd0; background:radial-gradient(circle at 30% 20%,#d5f79e,#4bc987 62%,#087451); box-shadow:0 0 13px rgba(108,238,164,.55),inset 0 1px rgba(255,255,255,.62); animation:stamp-pop .55s var(--loyalty-motion) both; }
        @keyframes stamp-pop { 0%{transform:scale(.65) rotate(-18deg);opacity:.4} 70%{transform:scale(1.16) rotate(5deg)} 100%{transform:scale(1) rotate(0);opacity:1} }
        .reward-line { grid-column:1/-1; margin-top:-8px; color:#ded0b3; font-size:10px; }
        .flip-hint { display:inline-flex; align-items:center; gap:8px; min-height:38px; padding:8px 15px; border:1px solid rgba(133,110,70,.27); border-radius:999px; color:#6f6148; background:rgba(255,253,248,.48); font-size:11px; font-weight:650; cursor:pointer; transition:transform .2s ease, background .2s ease, box-shadow .2s ease; }
        .flip-hint:hover { transform:translateY(-2px); background:#fffdf9; box-shadow:0 8px 18px rgba(66,48,22,.09); }
        .flip-hint:focus-visible, .utility-button:focus-visible, .qr-trigger:focus-visible, .dialog-close:focus-visible { outline:3px solid #a9874e; outline-offset:3px; }
        .flip-hint svg { width:15px; height:15px; }
        .back-copy { position:relative; z-index:1; transform:translateZ(22px); }
        .back-copy .eyebrow { color:#cbb581; }
        .back-copy h2 { margin:14px 0 9px; color:#fcf6e9; font-family:Georgia,"Times New Roman",serif; font-size:clamp(21px,2.7vw,31px); font-weight:500; line-height:1.13; }
        .back-copy p { max-width:260px; margin:0; color:#d1c7b3; font-size:12px; line-height:1.65; }
        .back-merchant { display:flex; align-items:center; gap:9px; margin-top:24px; color:#efe3ca; font-size:11px; font-weight:650; }
        .back-merchant-mark { display:grid; width:32px; height:32px; overflow:hidden; place-items:center; border:1px solid rgba(221,184,111,.6); border-radius:9px; color:#f3d99f; font-family:Georgia,serif; }
        .back-merchant-mark img { width:100%; height:100%; object-fit:contain; padding:2px; background:white; }
        .qr-trigger { position:relative; z-index:2; display:grid; width:clamp(120px,16vw,176px); aspect-ratio:1; place-items:center; padding:11px; border:1px solid rgba(232,207,159,.69); border-radius:19px; background:#fffefa; box-shadow:0 12px 32px rgba(0,0,0,.24),inset 0 0 0 5px rgba(239,232,217,.77); cursor:zoom-in; transform:translateZ(32px); transition:transform .25s var(--loyalty-motion), box-shadow .25s ease; }
        .qr-trigger:hover { transform:translateZ(36px) scale(1.035); box-shadow:0 18px 37px rgba(0,0,0,.28),inset 0 0 0 5px rgba(239,232,217,.77); }
        .qr-trigger canvas { display:block; width:100%; height:100%; image-rendering:pixelated; }
        .qr-trigger span { position:absolute; right:0; bottom:-27px; left:0; color:#d3c6ad; font-size:9px; letter-spacing:.1em; text-align:center; }
        .flip-back { position:absolute; z-index:2; right:clamp(19px,3.4vw,42px); bottom:clamp(17px,2.8vw,30px); display:inline-flex; align-items:center; gap:7px; padding:8px 10px; border:1px solid rgba(221,184,111,.5); border-radius:999px; color:#f0dcad; background:rgba(9,9,8,.42); font-size:9px; font-weight:700; letter-spacing:.08em; cursor:pointer; transform:translateZ(44px); }
        .flip-back svg { width:14px;height:14px; }
        .member-panel { display:flex; width:100%; min-width:0; min-height:0; height:min(100%,690px); flex-direction:column; padding:clamp(22px,3vw,38px); border:1px solid rgba(255,255,255,.78); border-radius:clamp(22px,2.7vw,34px); background:linear-gradient(150deg,rgba(255,253,249,.86),rgba(255,252,247,.53)); box-shadow:0 22px 60px rgba(85,69,43,.075),inset 0 1px 0 #fff; backdrop-filter:blur(12px); }
        .panel-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; padding-bottom:17px; border-bottom:1px solid rgba(119,101,70,.15); }
        .panel-heading .eyebrow { color:#88754f; }
        .panel-heading h2 { margin:7px 0 0; font-family:Georgia,"Times New Roman",serif; font-size:clamp(21px,2.4vw,27px); font-weight:500; letter-spacing:-.025em; }
        .member-mini-code { flex:0 0 auto; padding:7px 9px; border:1px solid rgba(157,125,70,.32); border-radius:8px; color:#775e32; background:rgba(255,255,255,.56); font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:9px; font-weight:700; letter-spacing:.06em; }
        .overview { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; gap:14px; padding:18px 0 15px; border-bottom:1px solid rgba(119,101,70,.15); }
        .overview-label { color:var(--loyalty-muted); font-size:10px; letter-spacing:.04em; }
        .overview-reward { margin-top:5px; color:#29241d; font-size:12px; font-weight:700; }
        .overview-reward span { display:block; margin-top:3px; color:#796b53; font-size:10px; font-weight:500; }
        .reward-count { color:#93713a; font-family:Georgia,serif; font-size:25px; font-variant-numeric:tabular-nums; text-align:right; }
        .reward-count small { display:block; margin-top:1px; color:#827b6e; font-family:inherit; font-size:9px; text-align:right; }
        .section-heading { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:18px 0 7px; }
        .section-heading h3 { margin:0; font-family:Georgia,"Times New Roman",serif; font-size:17px; font-weight:500; }
        .section-heading span { color:#82796a; font-size:9px; }
        .activity-list { min-height:0; flex:1; margin:0; padding:0 3px 0 0; overflow-y:auto; overscroll-behavior:contain; scrollbar-color:#c4ad7d transparent; scrollbar-width:thin; }
        .activity-list:focus-visible { outline:2px solid #a9874e; outline-offset:-2px; border-radius:7px; }
        .activity-item { display:flex; min-height:58px; align-items:center; gap:11px; padding:9px 2px; border-bottom:1px solid rgba(119,101,70,.12); list-style:none; }
        .activity-marker { display:grid; width:28px; height:28px; flex:0 0 auto; place-items:center; border:1px solid rgba(182,146,81,.25); border-radius:50%; color:#9c793c; background:rgba(201,171,116,.1); font-size:10px; }
        .activity-copy { min-width:0; flex:1; }
        .activity-copy strong { display:block; overflow:hidden; color:#322d25; font-size:11px; font-weight:660; text-overflow:ellipsis; white-space:nowrap; }
        .activity-copy time { display:block; margin-top:4px; color:#847d71; font-size:9px; }
        .activity-points { flex:0 0 auto; color:#88682f; font-family:Georgia,serif; font-size:15px; font-weight:650; font-variant-numeric:tabular-nums; }
        .activity-points.is-earned { color:#4b725d; }
        .activity-empty { display:flex !important; min-height:130px !important; flex-direction:column; align-items:center; justify-content:center; color:#655e52; font-size:11px; text-align:center; }
        .activity-empty span { margin-top:5px; color:#898174; font-size:10px; }
        .panel-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:13px; color:#7e7566; font-size:9px; }
        .utility-button { display:inline-flex; min-height:37px; align-items:center; justify-content:center; gap:7px; padding:8px 12px; border:1px solid rgba(162,129,72,.32); border-radius:999px; color:#6a522b; background:rgba(255,255,255,.55); font-size:10px; font-weight:700; cursor:pointer; transition:background .2s ease,transform .2s ease; }
        .utility-button:hover { transform:translateY(-1px); background:#fffdf8; }
        .utility-button svg { width:14px;height:14px; }
        .loyalty-footer { display:flex; align-items:center; justify-content:center; gap:13px; color:#847a69; font-size:9px; letter-spacing:.07em; }
        .footer-rule { width:clamp(35px,7vw,95px); height:1px; background:rgba(127,107,75,.28); }
        .loyalty-footer strong { color:#30291e; font-weight:750; letter-spacing:.02em; }
        .qr-dialog { width:min(92vw,390px); padding:0; overflow:visible; border:0; border-radius:25px; color:var(--loyalty-ink); background:transparent; box-shadow:0 24px 90px rgba(24,19,12,.28); }
        .qr-dialog::backdrop { background:rgba(22,20,17,.56); backdrop-filter:blur(7px); }
        .dialog-card { padding:27px; border:1px solid rgba(255,255,255,.85); border-radius:25px; background:#f8f4ec; text-align:center; }
        .dialog-card .eyebrow { color:#88754f; }
        .dialog-card h2 { margin:8px 0 19px; font-family:Georgia,serif; font-size:24px; font-weight:500; }
        .dialog-qr { display:block; width:min(62vw,240px); aspect-ratio:1; margin:0 auto 14px; padding:12px; border:1px solid #e0d6c3; border-radius:17px; background:#fff; image-rendering:pixelated; }
        .dialog-card p { margin:0; color:#655e52; font-size:11px; }
        .dialog-close { display:flex; min-height:41px; align-items:center; justify-content:center; width:100%; margin-top:20px; border:1px solid #cbb78f; border-radius:12px; color:#554326; background:linear-gradient(130deg,#f2e5c8,#e5d1a7); font-size:11px; font-weight:750; cursor:pointer; }
        .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
        @keyframes progress-in { from { width:0; } to { width:var(--progress); } }
        @media (hover:hover) and (pointer:fine) { .card-shell:not(.is-flipped):hover .card-front { box-shadow:0 34px 26px rgba(39,31,19,.28), inset 0 1px 0 rgba(255,255,255,.17), inset 0 -1px 0 rgba(255,255,255,.08); } }
        @media (max-width:900px) {
            .loyalty-page { padding-inline:clamp(16px,3vw,28px); gap:14px; }
            .loyalty-content { grid-template-columns:minmax(0,1fr) minmax(300px,.83fr); gap:22px; }
            .member-panel { padding:22px; }
            .card-front { padding:clamp(17px,3vw,27px); }
            .card-status { padding:6px 9px; font-size:8px; }
            .card-brand-mark { width:36px; height:36px; }
            .card-member { margin-top:17px; }
        }
        @media (max-width:680px) {
            .loyalty-page { padding: max(12px,env(safe-area-inset-top)) 14px max(10px,env(safe-area-inset-bottom)); grid-template-rows:auto minmax(0,1fr); gap:12px; }
            .merchant { gap:10px; }
            .merchant-mark { width:38px; height:38px; border-radius:12px; font-size:17px; }
            .merchant-copy { padding-left:10px; }
            .merchant-copy h1 { max-width:56vw; font-size:18px; }
            .merchant-copy p { font-size:9px; }
            .membership-state { gap:7px; padding:8px 10px; font-size:8px; letter-spacing:.11em; }
            .state-dot { width:6px;height:6px; }
            .loyalty-content { grid-template-columns:minmax(0,1fr); grid-template-rows:auto minmax(0,1fr); align-content:stretch; align-items:stretch; gap:11px; }
            .card-column { gap:7px; }
            .card-shell { width:min(100%,520px); }
            .card-front { padding:clamp(13px,4vw,20px); }
            .card-back { gap:10px; padding:clamp(13px,4vw,20px); }
            .card-brand { gap:8px; }
            .card-brand-mark { width:31px;height:31px;border-radius:9px;font-size:16px; }
            .card-brand strong { font-size:clamp(11px,3.5vw,15px); }
            .card-brand small { margin-top:2px;font-size:7px;letter-spacing:.18em; }
            .card-status { padding:5px 7px; font-size:7px; letter-spacing:.1em; }
            .card-member { margin-top:clamp(8px,2vh,17px); }
            .eyebrow { font-size:8px; letter-spacing:.18em; }
            .member-name { max-width:88%; margin-top:4px; font-size:clamp(21px,6.5vw,31px); }
            .member-code { margin-top:5px; padding:4px 7px; font-size:8px; letter-spacing:.1em; }
            .card-lower { gap:9px; padding-top:7px; }
            .balance-label { font-size:7px; letter-spacing:.13em; }
            .balance-value { margin-top:2px; font-size:clamp(25px,8vw,37px); }
            .balance-value span { font-size:10px; }
            .progress-copy { min-width:78px; }
            .progress-copy strong { font-size:10px; }
            .progress-copy span { font-size:8px; }
            .card-progress { height:5px; margin-top:-4px; }
            .reward-line { margin-top:-5px; font-size:8px; }
            .flip-hint { min-height:30px; padding:5px 11px; font-size:9px; }
            .flip-hint svg { width:12px;height:12px; }
            .back-copy h2 { margin:7px 0 4px; font-size:clamp(15px,4.2vw,21px); }
            .back-copy p { max-width:170px; font-size:9px; line-height:1.45; }
            .back-merchant { gap:6px; margin-top:9px; font-size:8px; }
            .back-merchant-mark { width:23px;height:23px;border-radius:6px; }
            .qr-trigger { width:clamp(86px,26vw,128px); padding:7px; border-radius:13px; }
            .qr-trigger span { bottom:-19px; font-size:7px; }
            .flip-back { right:11px;bottom:10px;padding:5px 7px;font-size:7px; }
            .flip-back svg { width:11px;height:11px; }
            .member-panel { padding:13px 15px 11px; border-radius:20px; }
            .panel-heading { padding-bottom:9px; }
            .panel-heading h2 { margin-top:4px; font-size:19px; }
            .panel-heading .eyebrow { font-size:7px; }
            .member-mini-code { padding:5px 7px; font-size:7px; }
            .overview { gap:8px; padding:9px 0; }
            .overview-label { font-size:8px; }
            .overview-reward { margin-top:3px; font-size:10px; }
            .overview-reward span { margin-top:2px;font-size:8px; }
            .reward-count { font-size:20px; }
            .reward-count small { font-size:7px; }
            .section-heading { margin:9px 0 3px; }
            .section-heading h3 { font-size:15px; }
            .section-heading span { font-size:8px; }
            .activity-item { min-height:43px; gap:8px; padding:5px 1px; }
            .activity-marker { width:23px;height:23px;font-size:8px; }
            .activity-copy strong { font-size:9px; }
            .activity-copy time { margin-top:2px;font-size:7px; }
            .activity-points { font-size:13px; }
            .activity-empty { min-height:75px !important; font-size:9px; }
            .activity-empty span { font-size:8px; }
            .panel-foot { padding-top:7px;font-size:8px; }
            .utility-button { min-height:32px; padding:6px 9px; font-size:9px; }
            .loyalty-footer { display:none; }
        }
        @media (max-width:360px) {
            .loyalty-page { padding-inline:10px; }
            .membership-state { padding:7px 8px; font-size:7px; }
            .card-front { padding:11px; }
            .card-back { padding:11px; }
            .card-brand small { letter-spacing:.12em; }
            .member-name { font-size:20px; }
            .member-code { font-size:7px; }
            .card-status { font-size:6px; }
            .qr-trigger { width:80px; }
            .back-copy p { max-width:135px; }
        }
        @media (max-width:680px) and (max-height:520px) {
            .loyalty-page { padding-block:7px; gap:7px; }
            .loyalty-content { grid-template-columns:minmax(0,.94fr) minmax(0,1.06fr); grid-template-rows:minmax(0,1fr); gap:10px; }
            .card-column { justify-content:center; gap:5px; }
            .card-shell { width:100%; }
            .card-front { padding:10px; }
            .card-back { padding:10px; }
            .member-panel { padding:9px 11px; }
            .panel-heading { padding-bottom:6px; }
            .panel-heading h2 { font-size:15px; }
            .overview { padding:6px 0; }
            .section-heading { margin:6px 0 1px; }
            .activity-item { min-height:34px; padding:3px 1px; }
            .panel-foot { padding-top:4px; }
            .loyalty-footer { display:none; }
        }
        @media (max-height:670px) and (min-width:681px) {
            .loyalty-page { padding-block:10px;gap:10px; }
            .member-panel { padding-block:17px; }
            .card-column { gap:10px; }
            .panel-heading { padding-bottom:10px; }
            .overview { padding-block:11px; }
            .section-heading { margin-top:12px; }
            .activity-item { min-height:46px; }
            .loyalty-footer { display:none; }
        }
        @media (prefers-reduced-motion:reduce) {
            *,*::before,*::after { scroll-behavior:auto !important; animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; }
            .card-shell { transform:none !important; }
            .card-face { transform:none !important; }
            .card-shell.is-flipped .card-front { display:none; }
            .card-shell:not(.is-flipped) .card-back { display:none; }
        }
        /* GrowPOS identity: emerald leads, with champagne kept as a restrained premium detail. */
        :root {
            --loyalty-paper: #eaf3ee;
            --loyalty-paper-light: #f7fbf8;
            --loyalty-ink: #163b2e;
            --loyalty-muted: #61776d;
            --loyalty-green: #087451;
            --loyalty-green-light: #20a579;
            --loyalty-card: #075a42;
        }
        body { background:radial-gradient(ellipse at 48% -34%,#fff 2%,var(--loyalty-paper-light) 42%,var(--loyalty-paper) 100%); }
        .merchant-mark { border-color:rgba(8,116,81,.42); color:#e8fff5; background:linear-gradient(145deg,#15966c,#075a42); box-shadow:0 7px 16px rgba(8,74,53,.18),inset 0 1px rgba(255,255,255,.23); }
        .merchant-copy { border-left-color:rgba(8,116,81,.24); }
        .membership-state { border-color:rgba(8,116,81,.28); color:#086344; background:rgba(255,255,255,.72); box-shadow:0 5px 14px rgba(8,74,53,.06); }
        .state-dot { background:var(--loyalty-green-light); box-shadow:0 0 0 3px rgba(32,165,121,.16); }
        .card-face { border-color:rgba(118,211,171,.56); background:radial-gradient(ellipse at var(--light-x,72%) var(--light-y,8%),rgba(173,255,216,.27),transparent 34%),radial-gradient(ellipse at 8% 105%,rgba(4,66,47,.32),transparent 43%),linear-gradient(137deg,#117b59 0%,#075a42 48%,#064332 100%); box-shadow:0 28px 24px rgba(20,74,54,.24),inset 0 1px 0 rgba(255,255,255,.2),inset 0 -1px 0 rgba(255,255,255,.08); }
        .card-front { background:radial-gradient(ellipse at 82% 20%,rgba(113,241,183,.2),transparent 26%),radial-gradient(ellipse at 8% 100%,rgba(0,45,31,.33),transparent 38%),linear-gradient(137deg,#12845f 0%,#087451 44%,#064b38 100%); }
        .card-front-inner { z-index:1; }
        .card-ornament { color:rgba(205,255,225,.5); }
        .card-ornament .ornament-core { fill:rgba(206,255,226,.075); stroke:rgba(206,255,226,.3); }
        .card-ornament .ornament-leaf { fill:url(#leafFoil); stroke:rgba(235,255,241,.82); }
        .card-ornament .ornament-vein { stroke:rgba(235,255,241,.8); }
        .card-brand-mark { box-shadow:0 0 18px rgba(133,247,192,.1),inset 0 1px rgba(255,255,255,.22); }
        .card-status { box-shadow:0 0 0 3px rgba(185,255,218,.045),inset 0 1px rgba(255,255,255,.09); }
        .member-code { box-shadow:inset 0 1px rgba(255,255,255,.09); }
        .card-progress-fill { box-shadow:0 0 16px rgba(82,220,153,.54); }
        .card-face::after { border-color:rgba(240,211,151,.48); box-shadow:0 0 0 1px rgba(240,211,151,.12),0 0 0 25px rgba(240,211,151,.045),0 0 0 52px rgba(240,211,151,.025); }
        .card-back { background:radial-gradient(ellipse at 20% 12%,rgba(91,217,162,.2),transparent 37%),linear-gradient(145deg,#117b59,#064332 76%); }
        .card-brand-mark { border-color:rgba(151,237,197,.75); color:#d8ffeb; background:linear-gradient(145deg,rgba(255,255,255,.18),rgba(255,255,255,.045)); }
        .card-brand small { color:#b9ead1; }
        .card-status { border-color:rgba(163,238,200,.68); color:#dcffed; background:rgba(3,67,47,.22); }
        .eyebrow,.balance-label { color:#bce9d1; }
        .member-name { color:#fff; text-shadow:0 2px 18px rgba(0,35,24,.2); }
        .member-code { border-color:rgba(177,236,203,.36); color:#e2fff0; background:rgba(0,42,29,.2); }
        .card-lower { border-top-color:rgba(208,246,225,.25); }
        .balance-value { color:#d9f7a5; text-shadow:0 4px 20px rgba(172,242,137,.16); }
        .balance-value span { color:#e4f5e9; }
        .progress-copy strong { color:#f4fff8; }
        .progress-copy span,.reward-line { color:#c4e5d2; }
        .card-progress { border-color:rgba(219,255,234,.2); background:rgba(0,37,26,.4); }
        .card-progress-fill { background:linear-gradient(90deg,#34c98a,#9be8a6 68%,#e3f7ad); box-shadow:0 0 13px rgba(82,220,153,.45); }
        .stamp-token { border-color:rgba(199,255,220,.35); }
        .stamp-token.is-earned { border-color:#d9f7a5; color:#075a42; background:radial-gradient(circle at 30% 20%,#f3ffcf,#a3efa9 48%,#25b77d); box-shadow:0 0 13px rgba(82,220,153,.48),inset 0 1px #fff; }
        .flip-hint { border-color:rgba(8,116,81,.22); color:#17664c; background:rgba(255,255,255,.58); }
        .flip-hint:hover { background:#fff; box-shadow:0 8px 18px rgba(19,86,61,.1); }
        .flip-hint:focus-visible,.utility-button:focus-visible,.qr-trigger:focus-visible,.dialog-close:focus-visible { outline-color:#15966c; }
        .back-copy .eyebrow,.back-merchant { color:#d1f2df; }
        .back-copy h2 { color:#fff; }
        .back-copy p,.qr-trigger span { color:#d5eee0; }
        .back-merchant-mark { border-color:rgba(157,237,194,.58); color:#d8ffeb; }
        .qr-trigger { border-color:rgba(191,238,211,.7); }
        .flip-back { border-color:rgba(173,237,201,.48); color:#e6fff0; background:rgba(0,55,38,.55); }
        .member-panel { border-color:rgba(255,255,255,.86); background:linear-gradient(150deg,rgba(255,255,255,.9),rgba(248,253,249,.62)); box-shadow:0 22px 60px rgba(35,88,65,.09),inset 0 1px 0 #fff; }
        .panel-heading,.overview,.activity-item { border-color:rgba(38,104,75,.13); }
        .panel-heading .eyebrow,.dialog-card .eyebrow { color:#087451; }
        .member-mini-code { border-color:rgba(8,116,81,.24); color:#086344; }
        .overview-reward { color:#183b2e; }
        .overview-reward span,.reward-count small,.section-heading span,.panel-foot { color:#6c8176; }
        .reward-count,.activity-points { color:#087451; }
        .activity-list { scrollbar-color:#8bbca3 transparent; }
        .activity-list:focus-visible { outline-color:#15966c; }
        .activity-marker { border-color:rgba(8,116,81,.2); color:#087451; background:rgba(32,165,121,.09); }
        .activity-copy strong { color:#213b30; }
        .activity-copy time { color:#75887e; }
        .activity-points.is-earned { color:#13885f; }
        .utility-button { border-color:rgba(8,116,81,.24); color:#086344; }
        .utility-button:hover { background:#f3fbf6; }
        .loyalty-footer { color:#6d8378; }
        .footer-rule { background:rgba(8,116,81,.22); }
        .loyalty-footer strong { color:#087451; }
        .dialog-card { background:#f7fbf8; }
        .dialog-close { border-color:#9ac9ae; color:#fff; background:linear-gradient(130deg,#15966c,#087451); }
        @media (hover:hover) and (pointer:fine) { .card-shell:not(.is-flipped):hover .card-front { box-shadow:0 34px 26px rgba(20,74,54,.29),inset 0 1px 0 rgba(255,255,255,.2),inset 0 -1px 0 rgba(255,255,255,.08); } }
    </style>
</head>

<body>
    @php
        $availableRewards = (int) floor($customer->points / $step);
        $pointsIntoStep = (int) $customer->points % $step;
        $pointsToNextReward = $pointsIntoStep === 0 ? 0 : $step - $pointsIntoStep;
        $progressPercent = min(100, (int) round(($pointsIntoStep / $step) * 100));
        $visiblePointBalance = max(0, (int) $customer->points);
        $stampProgressLabel = min($stampGoal, max(0, $stampProgress));
        $logo = $tenant->img_logo ? \Illuminate\Support\Facades\Storage::url($tenant->img_logo) : null;
        $memberCode = (string) ($customer->loyalty_member_code ?? '');
    @endphp

    <main class="loyalty-page">
        <header class="loyalty-header">
            <div class="merchant">
                <span class="merchant-mark" aria-hidden="true">
                    @if ($logo)<img src="{{ $logo }}" alt="">@else{{ mb_substr($tenant->name, 0, 1) }}@endif
                </span>
                <div class="merchant-copy"><h1>{{ $tenant->name }}</h1><p>Kartu loyalitas digital</p></div>
            </div>
            <span class="membership-state"><span class="state-dot" aria-hidden="true"></span>Member aktif</span>
        </header>

        <section class="loyalty-content" aria-label="Kartu loyalitas {{ $customer->name }}">
            <div class="card-column">
                <div class="card-shell" id="loyalty-card">
                    <article class="card-face card-front" aria-label="Sisi depan kartu member">
                        <svg class="card-ornament" viewBox="0 0 240 240" fill="none" aria-hidden="true">
                            <defs><linearGradient id="leafFoil" x1="38" y1="196" x2="192" y2="36" gradientUnits="userSpaceOnUse"><stop stop-color="#8de8ad" stop-opacity=".22"/><stop offset=".55" stop-color="#e7f8ad" stop-opacity=".7"/><stop offset="1" stop-color="#9affd1" stop-opacity=".36"/></linearGradient></defs>
                            <circle class="ornament-core" cx="132" cy="108" r="74" stroke-width=".8"/>
                            <circle cx="132" cy="108" r="89" stroke="currentColor" stroke-opacity=".26" stroke-width=".7" stroke-dasharray="1 5"/>
                            <path class="ornament-leaf" d="M127 155c-2-31 8-57 31-77 14-12 31-18 47-20-1 22-8 40-23 54-16 16-34 22-55 21Z"/>
                            <path class="ornament-leaf" d="M124 143c-22-3-39-13-50-31-7-12-10-25-9-38 18 3 32 10 42 23 12 15 17 30 17 46Z"/>
                            <path class="ornament-vein" d="M119 172c3-35 20-65 53-91M119 150c-13-22-27-37-44-47M120 172v-52"/>
                            <path d="M43 188c28-18 54-21 79-9 24 11 51 11 81-2" stroke="currentColor" stroke-opacity=".38" stroke-width=".8"/>
                            <circle cx="185" cy="43" r="2" fill="#dfffc2" fill-opacity=".9"/><path d="M185 36v14M178 43h14" stroke="#e8ffd4" stroke-opacity=".75" stroke-width=".7"/>
                        </svg>
                        <div class="card-front-inner">
                            <div>
                                <div class="card-topline">
                                    <div class="card-brand">
                                        <span class="card-brand-mark" aria-hidden="true">@if ($logo)<img src="{{ $logo }}" alt="">@else G.@endif</span>
                                        <span><strong>{{ $tenant->name }}</strong><small>LOYALTY MEMBER</small></span>
                                    </div>
                                    <span class="card-status">MEMBER AKTIF</span>
                                </div>
                                <div class="card-member">
                                    <span class="eyebrow">Kartu milik</span>
                                    <strong class="member-name">{{ $customer->name }}</strong>
                                    <span class="member-code">{{ $memberCode ?: 'Member GrowPOS' }}</span>
                                </div>
                            </div>

                            <div class="card-lower">
                                <div>
                                    <span class="balance-label">{{ $isStampProgram ? 'Stamp terkumpul' : 'Saldo poin Anda' }}</span>
                                    <p class="balance-value">{{ number_format($isStampProgram ? $visibleStampBalance : $visiblePointBalance, 0, ',', '.') }} <span>{{ $isStampProgram ? 'stamp' : 'poin' }}</span></p>
                                </div>
                                @if ($programActive)
                                    @if ($isStampProgram)
                                        <div class="progress-copy"><strong>{{ $stampProgressLabel }} / {{ $stampGoal }}</strong><span>stamp ke reward</span></div>
                                        <div class="stamp-row" role="img" aria-label="{{ $stampProgressLabel }} dari {{ $stampGoal }} stamp menuju reward">
                                            @for ($stamp = 1; $stamp <= $stampGoal; $stamp++)
                                                <span class="stamp-token {{ $stamp <= $stampProgressLabel ? 'is-earned' : '' }}" style="animation-delay:{{ min(350, $stamp * 35) }}ms">
                                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.8 2.77 5.62 6.2.9-4.49 4.38 1.06 6.18L12 16.96l-5.54 2.92 1.06-6.18-4.49-4.38 6.2-.9L12 2.8Z"/></svg>
                                                </span>
                                            @endfor
                                        </div>
                                        <div class="card-progress" role="progressbar" aria-label="Progres menuju reward berikutnya" aria-valuemin="0" aria-valuemax="{{ $stampGoal }}" aria-valuenow="{{ $stampProgressLabel }}"><div class="card-progress-fill" style="--progress:{{ $stampProgressPercent }}%"></div></div>
                                        <p class="reward-line">
                                            @if ($stampRewardsAvailable > 0) {{ number_format($stampRewardsAvailable, 0, ',', '.') }} reward siap ditukar.
                                            @elseif ($stampBalance < 0) Stamp sedang disesuaikan karena transaksi retur penuh.
                                            @else {{ number_format($stampGoal - $stampProgressLabel, 0, ',', '.') }} stamp lagi untuk potongan Rp {{ number_format($stampRewardValue, 0, ',', '.') }}.
                                            @endif
                                        </p>
                                    @else
                                        <div class="progress-copy"><strong>{{ number_format($pointsIntoStep, 0, ',', '.') }} / {{ number_format($step, 0, ',', '.') }}</strong><span>poin ke reward</span></div>
                                        <div class="card-progress" role="progressbar" aria-label="Progres menuju reward berikutnya" aria-valuemin="0" aria-valuemax="{{ $step }}" aria-valuenow="{{ $pointsIntoStep }}"><div class="card-progress-fill" style="--progress:{{ $progressPercent }}%"></div></div>
                                        <p class="reward-line">
                                            @if ($pointsToNextReward === 0) Reward berikutnya sudah bisa ditukar.
                                            @else {{ number_format($pointsToNextReward, 0, ',', '.') }} poin lagi untuk potongan Rp {{ number_format($rewardValue, 0, ',', '.') }}.
                                            @endif
                                        </p>
                                    @endif
                                @else
                                    <div class="progress-copy"><strong>—</strong><span>program loyalty</span></div>
                                    <p class="reward-line">Program loyalty sedang tidak aktif.</p>
                                @endif
                            </div>
                        </div>
                    </article>

                    <article class="card-face card-back" aria-label="Sisi belakang kartu member">
                        <div class="back-copy">
                            <span class="eyebrow">Akses member</span>
                            <h2>Kartu digital<br>Anda, selalu siap.</h2>
                            <p>Tunjukkan kode QR ini kepada kasir agar member Anda mudah ditemukan saat transaksi.</p>
                            <div class="back-merchant">
                                <span class="back-merchant-mark" aria-hidden="true">@if ($logo)<img src="{{ $logo }}" alt="">@else{{ mb_substr($tenant->name, 0, 1) }}@endif</span>
                                <span>{{ $tenant->name }}<br><span style="color:#c8eed7;font-weight:400">{{ $memberCode }}</span></span>
                            </div>
                        </div>
                        <button class="qr-trigger" type="button" data-qr-open aria-label="Perbesar kode QR member {{ $memberCode }}">
                            <canvas class="member-qr" data-qr-value="{{ $memberCode }}" aria-label="Kode QR untuk nomor member {{ $memberCode }}"></canvas>
                            <span>Ketuk untuk memperbesar</span>
                        </button>
                        <button class="flip-back" type="button" data-card-flip aria-label="Balik ke sisi depan kartu">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0 1 11.8-2L20 9M4 15l2.6 2a7 7 0 0 0 11.8-2"/></svg>Balik kartu
                        </button>
                    </article>
                </div>
                <button class="flip-hint" type="button" data-card-flip aria-pressed="false" aria-controls="loyalty-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0 1 11.8-2L20 9M4 15l2.6 2a7 7 0 0 0 11.8-2"/></svg>
                    <span data-flip-label>Lihat sisi belakang</span>
                </button>
            </div>

            <aside class="member-panel" aria-labelledby="activity-heading">
                <div class="panel-heading">
                    <div><span class="eyebrow">Keanggotaan Anda</span><h2 id="activity-heading">Ringkasan member</h2></div>
                    <span class="member-mini-code">{{ $memberCode }}</span>
                </div>

                <div class="overview">
                    <div>
                        <div class="overview-label">Reward berikutnya</div>
                        <div class="overview-reward">
                            @if ($programActive)
                                Potongan Rp {{ number_format($isStampProgram ? $stampRewardValue : $rewardValue, 0, ',', '.') }}
                                <span>{{ $isStampProgram ? ($stampRewardsAvailable > 0 ? number_format($stampRewardsAvailable, 0, ',', '.') . ' reward siap ditukar' : number_format($stampGoal - $stampProgressLabel, 0, ',', '.') . ' stamp lagi untuk reward') : ($availableRewards > 0 ? number_format($availableRewards, 0, ',', '.') . ' reward siap ditukar' : number_format($pointsToNextReward, 0, ',', '.') . ' poin lagi untuk reward') }}</span>
                            @else
                                Program loyalty tidak aktif
                                <span>Riwayat loyalty tetap tersimpan.</span>
                            @endif
                        </div>
                    </div>
                    <div class="reward-count">{{ number_format($isStampProgram ? $visibleStampBalance : $visiblePointBalance, 0, ',', '.') }}<small>{{ $isStampProgram ? 'total stamp' : 'total poin' }}</small></div>
                </div>

                <div class="section-heading"><h3>{{ $isStampProgram ? 'Aktivitas stamp' : 'Aktivitas poin' }}</h3><span>{{ $activity->count() }} terbaru</span></div>
                <ul class="activity-list" tabindex="0" aria-label="Daftar aktivitas loyalty, dapat digulir">
                    @forelse ($activity as $entry)
                        @php($delta = (int) ($isStampProgram ? $entry->stamps_delta : $entry->points_delta))
                        <li class="activity-item">
                            <span class="activity-marker" aria-hidden="true">{{ $delta > 0 ? '＋' : ($delta < 0 ? '−' : '•') }}</span>
                            <span class="activity-copy">
                                <strong>{{ $isStampProgram ? match ($entry->type) { 'earned' => 'Stamp dari pembelian', 'redeemed' => 'Stamp ditukar', 'redemption_reversed' => 'Stamp penukaran dikembalikan', 'redemption_restored' => 'Stamp dikembalikan karena retur', 'return_reversal' => 'Stamp disesuaikan karena retur', default => 'Penyesuaian stamp' } : match ($entry->type) { 'earned' => 'Poin dari pembelian', 'redeemed' => 'Poin digunakan', 'redemption_reversed' => 'Poin penukaran dikembalikan', 'redemption_restored' => 'Poin dikembalikan karena retur', 'return_reversal' => 'Poin disesuaikan karena retur', default => 'Penyesuaian poin' } }}</strong>
                                <time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</time>
                            </span>
                            <strong class="activity-points {{ $delta > 0 ? 'is-earned' : '' }}">{{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 0, ',', '.') }} {{ $isStampProgram ? 'stamp' : 'poin' }}</strong>
                        </li>
                    @empty
                        <li class="activity-empty">Belum ada aktivitas {{ $isStampProgram ? 'stamp' : 'poin' }}.<span>Transaksi berikutnya akan muncul di sini.</span></li>
                    @endforelse
                </ul>
                <div class="panel-foot">
                    <span>Tunjukkan kartu ke kasir</span>
                    <button class="utility-button" type="button" data-copy-member="{{ $memberCode }}" aria-label="Salin nomor member {{ $memberCode }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/></svg>
                        <span data-copy-label>Salin ID</span>
                    </button>
                </div>
            </aside>
        </section>

        <footer class="loyalty-footer"><span class="footer-rule" aria-hidden="true"></span><span>Digital membership experience</span><strong>GrowPOS</strong><span class="footer-rule" aria-hidden="true"></span></footer>
    </main>

    <dialog class="qr-dialog" data-qr-dialog aria-labelledby="qr-dialog-title">
        <div class="dialog-card">
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2 id="qr-dialog-title">Kode QR member</h2>
            <canvas class="dialog-qr" data-qr-large data-qr-value="{{ $memberCode }}" aria-label="Kode QR untuk nomor member {{ $memberCode }}"></canvas>
            <p>{{ $memberCode }} · Tunjukkan kepada kasir saat transaksi</p>
            <button class="dialog-close" type="button" data-qr-close>Tutup</button>
        </div>
    </dialog>
</body>

</html>

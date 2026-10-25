<x-guest-layout>
    @section('title', 'Verifikasi Email')
    <x-auth.shell eyebrow="VERIFY YOUR EMAIL" heading="Satu langkah lagi untuk memulai." description="Verifikasi email untuk mengaktifkan akses ke workspace GrowPOS.">
        <div x-data="{
            remaining:60, sending:false, verified:false, interval:null, poller:null,
            init(){
                this.interval = setInterval(()=>{if(this.remaining>0)this.remaining--},1000);
                this.poller = setInterval(()=>this.check(),5000);
                document.addEventListener('visibilitychange',()=>{if(!document.hidden)this.check()},{signal:this.$data.abortController?.signal});
            },
            abortController: new AbortController(),
            async check(){
                if(document.hidden || this.verified)return;
                try{const r=await fetch(@js(route('api.check-verification')),{headers:{'Accept':'application/json'},credentials:'same-origin'});
                if(!r.ok)return;const data=await r.json();
                if(data.verified && data.redirect_url){this.verified=true;window.location.assign(data.redirect_url)}
                }catch(e){}
            },
            destroy(){clearInterval(this.interval);clearInterval(this.poller);this.abortController.abort()}
        }">
            <div class="mb-7 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary-600">VERIFIKASI EMAIL</p>
            <h1 class="text-4xl font-semibold tracking-[-0.055em]">Periksa email Anda.</h1>
            <p class="mt-3 text-sm leading-7 text-ink-700">Kami telah mengirim tautan verifikasi ke email yang terdaftar. Klik tautan tersebut untuk melanjutkan.</p>
            @auth<p class="mt-3 break-all rounded-lg bg-surface-100 px-3 py-2 text-sm font-medium text-ink-900">{{ auth()->user()->email }}</p>@endauth
            <div role="status" class="my-7 flex items-start gap-3 rounded-xl border border-border-200 bg-surface-100 p-4 text-sm text-ink-700"><span class="mt-1 h-2.5 w-2.5 shrink-0 animate-pulse rounded-full bg-primary-600"></span><span>Menunggu verifikasi. Halaman akan otomatis beralih setelah email dikonfirmasi.</span></div>
            @if(session('status') === 'verification-link-sent')<div role="status" class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">Tautan verifikasi baru telah dikirim.</div>@endif
            <form method="POST" action="{{ route('verification.send') }}" @submit="sending=true"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit" :disabled="remaining>0 || sending" class="w-full rounded-xl bg-primary-600 px-5 py-3.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"><span x-text="sending ? 'Mengirim...' : remaining>0 ? 'Kirim ulang dalam '+remaining+' detik' : 'Kirim ulang email verifikasi →'"></span></button></form>
            <p class="mt-4 text-center text-xs leading-6 text-ink-400">Tidak menemukan email? Periksa folder spam atau promosi.</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-8 text-center">@csrf<button type="submit" class="text-sm font-semibold text-ink-700 hover:text-primary-600">Keluar dari akun</button></form>
        </div>
    </x-auth.shell>
</x-guest-layout>

@php
    $notifications = [];
    foreach (['success', 'error', 'warning', 'info'] as $type) {
        $message = session($type);
        if (is_string($message) && trim($message) !== '') {
            $notifications[] = ['type' => $type, 'message' => $message];
        }
    }

    $status = session('status');
    $statusMessages = [
        'verification-link-sent' => 'Tautan verifikasi baru telah dikirim.',
        'profile-updated' => 'Perubahan profil berhasil disimpan.',
        'password-updated' => 'Kata sandi berhasil diperbarui.',
    ];
    if (is_string($status) && trim($status) !== '') {
        $notifications[] = [
            'type' => 'success',
            'message' => $statusMessages[$status] ?? $status,
        ];
    }
@endphp

<div x-data="toastStack(@js($notifications))"
    class="fixed top-20 right-4 z-[120] flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-2 pointer-events-none"
    aria-label="Notifikasi">
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.visible" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-2 opacity-0"
            @mouseenter="pause(notification.id)" @mouseleave="resume(notification.id)"
            @focusin="pause(notification.id)" @focusout="if (!$el.contains($event.relatedTarget)) resume(notification.id)"
            :role="notification.type === 'error' ? 'alert' : 'status'"
            :aria-label="notification.type === 'success' ? 'Berhasil' : notification.type === 'error' ? 'Terjadi kesalahan' : notification.type === 'warning' ? 'Perhatian' : 'Informasi'"
            class="pointer-events-auto flex items-start gap-3 rounded-lg border border-border-200 border-l-4 bg-surface-0 px-4 py-3 shadow-lg"
            :class="notification.type === 'success' ? 'border-l-primary-600' : notification.type === 'error' ? 'border-l-semantic-danger' : notification.type === 'warning' ? 'border-l-amber-500' : 'border-l-sky-600'">
            <span class="mt-0.5 shrink-0"
                :class="notification.type === 'success' ? 'text-primary-600' : notification.type === 'error' ? 'text-semantic-danger' : notification.type === 'warning' ? 'text-amber-600' : 'text-sky-600'"
                aria-hidden="true">
                <x-icon x-show="notification.type === 'success'" class="text-base fa-solid fa-circle-check" />
                <x-icon x-show="notification.type === 'error'" class="text-base fa-solid fa-circle-exclamation" />
                <x-icon x-show="notification.type === 'warning'" class="text-base fa-solid fa-triangle-exclamation" />
                <x-icon x-show="notification.type === 'info'" class="text-base fa-solid fa-circle-info" />
            </span>
            <p class="flex-1 pt-0.5 text-sm leading-5 text-ink-900" x-text="notification.message"></p>
            <button type="button" @click="dismiss(notification.id)" aria-label="Tutup pemberitahuan"
                class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-ink-400 transition-colors hover:bg-surface-100 hover:text-ink-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                <x-icon class="text-xs fa-solid fa-xmark" />
            </button>
        </div>
    </template>
</div>

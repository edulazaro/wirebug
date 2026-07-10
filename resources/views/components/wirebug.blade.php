@props([
    'position' => null,
    'types' => null,

    // Textos: null = se resuelven desde las traducciones (wirebug::wirebug.*).
    // Pasar cualquiera de estas props sobrescribe la traducción para ese string.
    'title' => null,
    'modalTitle' => null,
    'typeLabel' => null,
    'messageLabel' => null,
    'messagePlaceholder' => null,
    'emailLabel' => null,
    'emailPlaceholder' => null,
    'send' => null,
    'sending' => null,
    'cancel' => null,
    'successTitle' => null,
    'successDescription' => null,
    'close' => null,
    'errorMessage' => null,
])

@php
    $position = $position ?? config('wirebug.position', 'left');
    $types    = $types    ?? config('wirebug.types', []);

    $askEmail = config('wirebug.ask_guest_email', true) && ! auth()->check();

    // Textos: prop explícita > traducción del paquete.
    $title              = $title              ?? __('wirebug::wirebug.title');
    $modalTitle         = $modalTitle         ?? __('wirebug::wirebug.modal_title');
    $typeLabel          = $typeLabel          ?? __('wirebug::wirebug.type_label');
    $messageLabel       = $messageLabel       ?? __('wirebug::wirebug.message_label');
    $messagePlaceholder = $messagePlaceholder ?? __('wirebug::wirebug.message_placeholder');
    $emailLabel         = $emailLabel         ?? __('wirebug::wirebug.email_label');
    $emailPlaceholder   = $emailPlaceholder   ?? __('wirebug::wirebug.email_placeholder');
    $send               = $send               ?? __('wirebug::wirebug.send');
    $sending            = $sending            ?? __('wirebug::wirebug.sending');
    $cancel             = $cancel             ?? __('wirebug::wirebug.cancel');
    $successTitle       = $successTitle       ?? __('wirebug::wirebug.success_title');
    $successDescription = $successDescription ?? __('wirebug::wirebug.success_description');
    $close              = $close              ?? __('wirebug::wirebug.close');
    $errorMessage       = $errorMessage       ?? __('wirebug::wirebug.error');

    // Label de cada tipo: 'label' en config > traducción del paquete.
    $typeOptions = collect($types)
        ->map(fn ($t, $key) => $t['label'] ?? __("wirebug::wirebug.types.{$key}"))
        ->all();

    $defaultType = array_key_first($typeOptions) ?? 'bug';
@endphp

<div
    class="wb-root"
    x-data="{
        type: @js($defaultType),
        message: '',
        email: '',
        sending: false,
        sent: false,
        error: null,
        open() {
            if (window.Wiremodal) Wiremodal.open('wirebug-report');
        },
        closeModal() {
            if (window.Wiremodal) Wiremodal.close('wirebug-report');
            // Deja el form limpio para el siguiente reporte, pero espera al
            // fade-out del modal para que no se vea el reset.
            setTimeout(() => this.reset(), 300);
        },
        reset() {
            this.type = @js($defaultType);
            this.message = '';
            this.email = '';
            this.sending = false;
            this.sent = false;
            this.error = null;
        },
        async submit() {
            if (this.sending || !this.message.trim()) return;
            this.sending = true;
            this.error = null;
            try {
                const response = await fetch(@js(route('wirebug.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @js(csrf_token()),
                    },
                    body: JSON.stringify({
                        type: this.type,
                        message: this.message,
                        email: this.email || null,
                        url: window.location.href,
                        viewport: window.innerWidth + 'x' + window.innerHeight,
                    }),
                });
                if (!response.ok) throw new Error('wirebug: ' + response.status);
                this.sent = true;
                this.$dispatch('wirebug-sent');
            } catch (e) {
                this.error = @js($errorMessage);
            } finally {
                this.sending = false;
            }
        },
    }"
    x-cloak
>
    {{-- Botón flotante --}}
    <button
        @click="open()"
        type="button"
        aria-label="{{ $title }}"
        title="{{ $title }}"
        class="wb-floating {{ $position === 'right' ? 'wb-floating-right' : 'wb-floating-left' }}"
    >
        <svg class="wb-floating-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20a5 5 0 0 0 5-5v-3a5 5 0 0 0-10 0v3a5 5 0 0 0 5 5z"/>
            <path stroke-linecap="round" d="M9.5 8.5a2.5 2.5 0 0 1 5 0M12 20v-8M7 13H4.5M19.5 13H17M7.5 9.5 5.5 7.5M16.5 9.5l2-2M7.5 17l-2 2M16.5 17l2 2"/>
        </svg>
    </button>

    {{-- Modal de reporte (wiremodal — hereda el data-wire-theme global) --}}
    <x-wiremodal name="wirebug-report" size="lg" :title="$modalTitle">
        <x-slot:body>
            {{-- Formulario --}}
            <div x-show="!sent" class="wb-form">
                <div class="wb-field">
                    <span class="wb-label">{{ $typeLabel }}</span>
                    <div class="wb-pills" role="radiogroup" aria-label="{{ $typeLabel }}">
                        @foreach($typeOptions as $key => $label)
                            <button
                                type="button"
                                role="radio"
                                :aria-checked="type === @js($key)"
                                @click="type = @js($key)"
                                class="wb-pill"
                                :class="{ 'wb-pill-active': type === @js($key) }"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="wb-field">
                    <label class="wb-label" for="wb-message">{{ $messageLabel }}</label>
                    <textarea
                        id="wb-message"
                        x-model="message"
                        rows="4"
                        maxlength="5000"
                        class="wb-textarea"
                        placeholder="{{ $messagePlaceholder }}"
                        @keydown.enter.meta.prevent="submit()"
                        @keydown.enter.ctrl.prevent="submit()"
                    ></textarea>
                </div>

                @if($askEmail)
                    <div class="wb-field">
                        <label class="wb-label" for="wb-email">{{ $emailLabel }}</label>
                        <input
                            id="wb-email"
                            x-model="email"
                            type="email"
                            class="wb-input"
                            placeholder="{{ $emailPlaceholder }}"
                        >
                    </div>
                @endif

                <p x-show="error" x-text="error" class="wb-error"></p>
            </div>

            {{-- Confirmación --}}
            <div x-show="sent" class="wb-success">
                <svg class="wb-success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12.5 2.5 2.5 4.5-5.5"/>
                </svg>
                <h3 class="wb-success-title">{{ $successTitle }}</h3>
                <p class="wb-success-description">{{ $successDescription }}</p>
            </div>
        </x-slot:body>

        <x-slot:footer>
            <div class="wb-modal-actions">
                <template x-if="!sent">
                    <div class="wb-modal-actions-inner">
                        <button @click="closeModal()" type="button" class="wb-btn wb-btn-muted">
                            {{ $cancel }}
                        </button>
                        <button
                            @click="submit()"
                            type="button"
                            class="wb-btn wb-btn-primary"
                            :disabled="sending || !message.trim()"
                            x-text="sending ? @js($sending) : @js($send)"
                        ></button>
                    </div>
                </template>
                <template x-if="sent">
                    <div class="wb-modal-actions-inner">
                        <button @click="closeModal()" type="button" class="wb-btn wb-btn-primary">
                            {{ $close }}
                        </button>
                    </div>
                </template>
            </div>
        </x-slot:footer>
    </x-wiremodal>
</div>

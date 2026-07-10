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
    'stepsLabel' => null,
    'stepsPlaceholder' => null,
    'screenshotLabel' => null,
    'screenshotButton' => null,
    'screenshotRemove' => null,
    'recordingLabel' => null,
    'recordButton' => null,
    'recordingStop' => null,
    'recordingDiscard' => null,
    'recordingName' => null,
    'recordingRemove' => null,
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
    $stepsLabel         = $stepsLabel         ?? __('wirebug::wirebug.steps_label');
    $stepsPlaceholder   = $stepsPlaceholder   ?? __('wirebug::wirebug.steps_placeholder');
    $screenshotLabel    = $screenshotLabel    ?? __('wirebug::wirebug.screenshot_label');
    $screenshotButton   = $screenshotButton   ?? __('wirebug::wirebug.screenshot_button');
    $screenshotRemove   = $screenshotRemove   ?? __('wirebug::wirebug.screenshot_remove');
    $recordingLabel     = $recordingLabel     ?? __('wirebug::wirebug.recording_label');
    $recordButton       = $recordButton       ?? __('wirebug::wirebug.record_button');
    $recordingStop      = $recordingStop      ?? __('wirebug::wirebug.recording_stop');
    $recordingDiscard   = $recordingDiscard   ?? __('wirebug::wirebug.recording_discard');
    $recordingName      = $recordingName      ?? __('wirebug::wirebug.recording_name');
    $recordingRemove    = $recordingRemove    ?? __('wirebug::wirebug.recording_remove');

    $recordingEnabled = config('wirebug.recording.enabled', true);
    $maxSeconds       = (int) config('wirebug.recording.max_seconds', 90);
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
        steps: '',
        email: '',
        screenshot: null,
        screenshotName: '',
        sending: false,
        sent: false,
        error: null,
        pickScreenshot(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.screenshot = file;
            this.screenshotName = file.name;
        },
        removeScreenshot() {
            this.screenshot = null;
            this.screenshotName = '';
            if (this.$refs.screenshotInput) this.$refs.screenshotInput.value = '';
        },
        // --- Grabación de pantalla (getDisplayMedia + MediaRecorder) ---
        canRecord: !!(navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia && window.MediaRecorder),
        recording: false,
        recordingSeconds: 0,
        recordingTimer: null,
        recorder: null,
        recorderStream: null,
        recorderChunks: [],
        discarded: false,
        video: null,
        videoName: '',
        maxSeconds: {{ $maxSeconds }},
        formatTime(s) {
            return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
        },
        async startRecording() {
            if (this.recording) return;
            let stream;
            try {
                stream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: false });
            } catch (e) {
                return; // el usuario canceló el selector del navegador
            }
            const mime = ['video/webm;codecs=vp9', 'video/webm', 'video/mp4']
                .find(t => MediaRecorder.isTypeSupported(t)) || '';
            this.recorderStream = stream;
            this.recorderChunks = [];
            this.discarded = false;
            this.recorder = new MediaRecorder(stream, mime ? { mimeType: mime } : undefined);
            this.recorder.ondataavailable = e => { if (e.data.size) this.recorderChunks.push(e.data); };
            this.recorder.onstop = () => this.finishRecording();
            // El usuario puede cortar desde la barra nativa del navegador
            // ('Dejar de compartir'): lo tratamos como un stop normal.
            stream.getVideoTracks()[0].addEventListener('ended', () => this.stopRecording());
            this.recorder.start(1000);
            this.recording = true;
            this.recordingSeconds = 0;
            this.recordingTimer = setInterval(() => {
                this.recordingSeconds++;
                if (this.recordingSeconds >= this.maxSeconds) this.stopRecording();
            }, 1000);
            // El modal estorba para reproducir el bug: se cierra y queda el
            // overlay; al detener se reabre con el vídeo adjunto.
            if (window.Wiremodal) Wiremodal.close('wirebug-report');
        },
        stopRecording(discard = false) {
            if (!this.recording) return;
            this.recording = false;
            this.discarded = discard;
            clearInterval(this.recordingTimer);
            if (this.recorder && this.recorder.state !== 'inactive') {
                this.recorder.stop(); // dispara onstop → finishRecording()
            } else {
                this.finishRecording();
            }
        },
        finishRecording() {
            if (this.recorderStream) this.recorderStream.getTracks().forEach(t => t.stop());
            if (!this.discarded && this.recorderChunks.length) {
                const type = this.recorderChunks[0].type || 'video/webm';
                this.video = new Blob(this.recorderChunks, { type });
                this.videoName = @js($recordingName) + ' (' + this.formatTime(this.recordingSeconds) + ')';
            }
            this.recorderChunks = [];
            this.recorder = null;
            this.recorderStream = null;
            if (window.Wiremodal) Wiremodal.open('wirebug-report');
        },
        removeRecording() {
            this.video = null;
            this.videoName = '';
        },
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
            this.steps = '';
            this.email = '';
            this.removeScreenshot();
            this.removeRecording();
            this.sending = false;
            this.sent = false;
            this.error = null;
        },
        async submit() {
            if (this.sending || !this.message.trim()) return;
            this.sending = true;
            this.error = null;
            try {
                // FormData (multipart) por la captura adjunta; el navegador
                // fija solo el Content-Type con su boundary.
                const data = new FormData();
                data.append('type', this.type);
                data.append('message', this.message);
                if (this.steps.trim()) data.append('steps', this.steps);
                if (this.email) data.append('email', this.email);
                if (this.screenshot) data.append('screenshot', this.screenshot);
                if (this.video) {
                    data.append('recording', this.video, this.video.type.includes('mp4') ? 'recording.mp4' : 'recording.webm');
                }
                data.append('url', window.location.href);
                data.append('viewport', window.innerWidth + 'x' + window.innerHeight);

                const response = await fetch(@js(route('wirebug.store')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @js(csrf_token()),
                    },
                    body: data,
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
    {{-- Botón flotante (oculto mientras se graba: el overlay toma su lugar) --}}
    <button
        x-show="!recording"
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

    {{-- Overlay de grabación: elemento del DOM de la propia app (page-level),
         visible mientras se graba con el modal cerrado. --}}
    <div x-show="recording" x-transition.opacity class="wb-recorder" role="status">
        <span class="wb-recorder-dot" aria-hidden="true"></span>
        <span class="wb-recorder-time" x-text="formatTime(recordingSeconds)"></span>
        <button @click="stopRecording()" type="button" class="wb-btn wb-btn-primary wb-recorder-stop">
            {{ $recordingStop }}
        </button>
        <button
            @click="stopRecording(true)"
            type="button"
            class="wb-recorder-discard"
            aria-label="{{ $recordingDiscard }}"
            title="{{ $recordingDiscard }}"
        >&times;</button>
    </div>

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

                <div class="wb-field">
                    <label class="wb-label" for="wb-steps">{{ $stepsLabel }}</label>
                    <textarea
                        id="wb-steps"
                        x-model="steps"
                        rows="3"
                        maxlength="5000"
                        class="wb-textarea"
                        placeholder="{{ $stepsPlaceholder }}"
                    ></textarea>
                </div>

                <div class="wb-field">
                    <span class="wb-label">{{ $screenshotLabel }}</span>
                    <div class="wb-file">
                        <input
                            type="file"
                            id="wb-screenshot"
                            x-ref="screenshotInput"
                            @change="pickScreenshot($event)"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            class="wb-file-input"
                        >
                        <label for="wb-screenshot" class="wb-btn wb-btn-muted wb-file-button">
                            {{ $screenshotButton }}
                        </label>
                        <span x-show="screenshotName" x-text="screenshotName" class="wb-file-name"></span>
                        <button
                            type="button"
                            x-show="screenshot"
                            @click="removeScreenshot()"
                            class="wb-file-remove"
                            aria-label="{{ $screenshotRemove }}"
                            title="{{ $screenshotRemove }}"
                        >&times;</button>
                    </div>
                </div>

                @if($recordingEnabled)
                    {{-- Solo navegadores con getDisplayMedia (desktop). --}}
                    <div class="wb-field" x-show="canRecord">
                        <span class="wb-label">{{ $recordingLabel }}</span>
                        <div class="wb-file">
                            <button
                                x-show="!video"
                                @click="startRecording()"
                                type="button"
                                class="wb-btn wb-btn-muted wb-file-button"
                            >{{ $recordButton }}</button>
                            <span x-show="video" x-text="videoName" class="wb-file-name"></span>
                            <button
                                x-show="video"
                                @click="removeRecording()"
                                type="button"
                                class="wb-file-remove"
                                aria-label="{{ $recordingRemove }}"
                                title="{{ $recordingRemove }}"
                            >&times;</button>
                        </div>
                    </div>
                @endif

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

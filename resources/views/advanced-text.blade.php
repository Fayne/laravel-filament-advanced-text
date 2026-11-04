<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        state: $wire.$entangle(@js($getStatePath())),
        config: @js($getAdvancedTextConfig()),
        mapping: {},
        loading: false,
        error: '',
        copied: false,
        query: '',
        composing: false,
        revision: 0,
        controller: null,
        init() { this.mapping = {...this.config.mapping}; },
        core(value = this.state) {
            let valueText = String(value ?? '');
            if (this.config.prefixConcatenate && this.config.prefix && valueText.startsWith(this.config.prefix)) valueText = valueText.slice(this.config.prefix.length);
            if (this.config.suffixConcatenate && this.config.suffix && valueText.endsWith(this.config.suffix)) valueText = valueText.slice(0, -this.config.suffix.length);
            return valueText;
        },
        compose(core) { return core === '' ? '' : (this.config.prefixConcatenate ? this.config.prefix : '') + core + (this.config.suffixConcatenate ? this.config.suffix : ''); },
        labels() { return [...new Set([...Object.keys(this.mapping), ...this.config.suggestions.map(String)])]; },
        handleInput(event) {
            if (this.composing || event.isComposing) return;
            this.input(event.target.value);
            if (event.target.value !== this.core()) event.target.value = this.core();
        },
        input(label) {
            if (this.config.readonly) return;
            this.query = label;
            const mapped = Object.hasOwn(this.mapping, label);
            let value = String(mapped ? this.mapping[label] : label);
            if (!mapped && this.config.maxLength != null) value = Array.from(value).slice(0, this.config.maxLength).join('');
            this.state = this.compose(value);
            this.revision++;
            this.controller?.abort();
            this.loading = false;
            this.error = '';
            this.copied = false;
            if (label === '') this.mapping = {...this.config.mapping};
        },
        async search(label) {
            if (!label || !this.config.remoteUrl || this.config.readonly) return;
            const revision = this.revision;
            let url;
            try {
                url = new URL(this.config.remoteUrl, window.location.origin);
                if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password || url.hash || (url.origin !== window.location.origin && !this.config.allowedUrls.includes(url.href))) throw new Error();
            } catch { this.error = @js(__('filament-advanced-text::messages.unlicensed_source')); return; }
            const sameOrigin = url.origin === window.location.origin;
            url.searchParams.set('query', label);
            const controller = new AbortController();
            this.controller?.abort();
            this.controller = controller;
            this.loading = true;
            try {
                const response = await fetch(url.href, {headers: {Accept: 'application/json'}, credentials: sameOrigin ? 'same-origin' : 'omit', redirect: 'error', signal: controller.signal});
                if (!response.ok) throw new Error();
                const data = await response.json();
                if (!data || Array.isArray(data) || typeof data !== 'object' || Object.values(data).some(value => !['string', 'number', 'boolean'].includes(typeof value))) throw new Error();
                if (revision !== this.revision || controller.signal.aborted) return;
                this.mapping = {...this.config.mapping, ...data};
            } catch (error) { if (revision === this.revision && error.name !== 'AbortError') this.error = @js(__('filament-advanced-text::messages.load_failed')); }
            finally { if (this.controller === controller) this.loading = false; }
        },
        async copy() { try { await navigator.clipboard.writeText(this.compose(this.core())); this.copied = true; this.error = ''; } catch { this.error = @js(__('filament-advanced-text::messages.copy_failed')); } },
        destroy() { this.controller?.abort(); }
    }" class="cms-advanced-text">
        <div class="cms-advanced-text-input">
            <span x-show="config.prefix" x-text="config.prefix" class="cms-advanced-text-affix"></span>
            <input type="text" id="{{ $getId() }}" aria-label="{{ $getLabel() }}" placeholder="{{ $getPlaceholder() }}"
                :value="core()" list="{{ $getId() }}-suggestions" @input="handleInput($event)" @input.debounce.300ms="if (!composing) search(query)"
                @compositionstart="composing = true" @compositionend="composing = false; handleInput($event)"
                @readonly($isReadOnly()) @disabled($isDisabled()) @required($isRequired() && !$isReadOnly())>
            @if ($isCopyable())
                <button type="button" @click="copy()" :aria-label="copied ? @js(__('filament-advanced-text::messages.copied')) : @js(__('filament-advanced-text::messages.copy_content'))" :title="copied ? @js(__('filament-advanced-text::messages.copied')) : @js(__('filament-advanced-text::messages.copy_content'))">
                    <x-filament::icon icon="heroicon-m-clipboard-document" class="size-4" />
                    <span x-text="copied ? @js(__('filament-advanced-text::messages.copied')) : @js(__('filament-advanced-text::messages.copy'))"></span>
                </button>
            @endif
            <span x-show="config.suffix" x-text="config.suffix" class="cms-advanced-text-affix"></span>
        </div>
        <datalist id="{{ $getId() }}-suggestions"><template x-for="label in labels()" :key="label"><option :value="label"></option></template></datalist>
        <div class="cms-advanced-text-status" aria-live="polite">
            <span x-show="loading" x-cloak>{{ __('filament-advanced-text::messages.loading') }}</span>
            <span x-show="error" x-text="error" role="status"></span>
            @if ($getMaxLength() !== null)<span x-text="Array.from(core()).length + ' / {{ $getMaxLength() }}'" class="cms-advanced-text-count"></span>@endif
        </div>
        <style>
            .cms-advanced-text{width:100%}.cms-advanced-text-input{display:flex;align-items:center;gap:8px;border:1px solid #9ca3af;border-radius:8px;padding:0 10px;min-height:38px}.cms-advanced-text-input:focus-within{outline:2px solid #6366f1;outline-offset:1px}.cms-advanced-text-input input{flex:1;min-width:0;border:0;outline:none;background:transparent;padding:8px 0;box-shadow:none}.cms-advanced-text-affix{color:#6b7280;white-space:pre-wrap}.cms-advanced-text-input button{display:inline-flex;align-items:center;gap:4px;font-size:.75rem;white-space:nowrap}.cms-advanced-text-status{display:flex;gap:8px;font-size:.75rem;color:#6b7280;margin-top:4px}.cms-advanced-text-count{margin-left:auto}.dark .cms-advanced-text-affix,.dark .cms-advanced-text-status{color:#9ca3af}
        </style>
    </div>
</x-dynamic-component>

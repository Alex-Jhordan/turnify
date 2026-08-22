<div class="min-h-screen overflow-hidden bg-[#f4f1ea] text-[#17211d] selection:bg-[#f2c14e] selection:text-[#17211d]">
    <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-5 py-5 sm:px-8 lg:px-12 lg:py-8">
        <header class="flex items-center justify-between border-b border-[#17211d]/15 pb-5">
            <div class="flex items-center gap-3">
                <div class="flex size-11 items-center justify-center rounded-full bg-[#17211d] text-lg font-black text-[#f2c14e]">T</div>
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.22em]">Turnify</p>
                    <p class="text-xs font-medium text-[#17211d]/60">In-person assistance</p>
                </div>
            </div>
            <div class="hidden items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-[#17211d]/55 sm:flex">
                <span class="size-2 rounded-full bg-[#4c956c]"></span>
                Service available
            </div>
        </header>

        <main class="grid flex-1 items-center gap-10 py-8 lg:grid-cols-[0.85fr_1.15fr] lg:gap-20 lg:py-14">
            <section class="max-w-md">
                <p class="mb-5 text-xs font-black uppercase tracking-[0.28em] text-[#b45f3c]">Get your ticket</p>
                <h1 class="font-serif text-5xl font-black leading-[0.95] tracking-tight sm:text-7xl">Your service starts here.</h1>
                <p class="mt-6 max-w-sm text-base leading-7 text-[#17211d]/65">Complete your details in a few simple steps. An agent will assist you shortly.</p>

                <div class="mt-10 flex items-center gap-3" aria-label="Progress">
                    @foreach ([1 => 'Details', 2 => 'Service', 3 => 'Confirm'] as $number => $label)
                        <div class="flex items-center gap-2 {{ $number < 3 ? 'flex-1' : '' }}">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-black transition {{ $step >= $number ? 'border-[#17211d] bg-[#17211d] text-[#f2c14e]' : 'border-[#17211d]/25 text-[#17211d]/40' }}">{{ $number }}</div>
                            <span class="hidden text-xs font-black uppercase tracking-wider text-[#17211d]/55 sm:block">{{ $label }}</span>
                            @if ($number < 3)
                                <div class="mx-1 h-px flex-1 bg-[#17211d]/15"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg bg-[#fffdf8] p-5 shadow-[0_20px_60px_rgba(23,33,29,0.12)] sm:p-8">
                @if ($step === 1)
                    <div>
                        <div class="mb-7">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b45f3c]">Step 1 of 3</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight">Identify yourself</h2>
                            <p class="mt-2 text-sm text-[#17211d]/60">Select your ID type and enter the number.</p>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            @foreach (\App\Enums\DocumentType::cases() as $type)
                                <button type="button" wire:click="$set('document_type', '{{ $type->value }}')" class="min-h-14 rounded-lg border-2 px-2 text-sm font-black transition {{ $document_type === $type->value ? 'border-[#17211d] bg-[#f2c14e]' : 'border-[#17211d]/10 bg-[#f4f1ea] hover:border-[#17211d]/35' }}">
                                    {{ $type->getLabel() }}
                                </button>
                            @endforeach
                        </div>

                        <label for="document-number" class="mt-7 block text-xs font-black uppercase tracking-[0.18em] text-[#17211d]/55">ID Number</label>
                        <input id="document-number" type="text" inputmode="numeric" wire:model.live="document_number" maxlength="12" class="mt-2 h-16 w-full rounded-lg border-2 border-[#17211d]/15 bg-[#f4f1ea] px-5 text-center text-3xl font-black tracking-[0.18em] outline-none transition focus:border-[#b45f3c]" placeholder="00000000" aria-describedby="document-number-error">
                        @error('document_number') <p id="document-number-error" class="mt-2 text-sm font-bold text-[#b45f3c]">{{ $message }}</p> @enderror

                        <div class="mt-5 grid grid-cols-3 gap-2 sm:gap-3">
                            @foreach (range(1, 9) as $digit)
                                <button type="button" wire:click="appendDigit('{{ $digit }}')" class="h-14 rounded-lg bg-[#e9e4d8] text-2xl font-black transition hover:bg-[#ded7c8] active:scale-95">{{ $digit }}</button>
                            @endforeach
                            <span></span>
                            <button type="button" wire:click="appendDigit('0')" class="h-14 rounded-lg bg-[#e9e4d8] text-2xl font-black transition hover:bg-[#ded7c8] active:scale-95">0</button>
                            <button type="button" wire:click="removeDigit" class="h-14 rounded-lg bg-[#e9e4d8] text-sm font-black uppercase transition hover:bg-[#ded7c8] active:scale-95">Clear</button>
                        </div>

                        <button type="button" wire:click="nextStep" wire:loading.attr="disabled" class="mt-7 flex h-16 w-full items-center justify-center rounded-lg bg-[#17211d] text-base font-black text-[#fffdf8] transition hover:bg-[#293a32] active:scale-[0.99] disabled:opacity-50">
                            <span wire:loading.remove wire:target="nextStep">Continue <span class="ml-3 text-xl" aria-hidden="true">→</span></span>
                            <span wire:loading wire:target="nextStep">Verifying...</span>
                        </button>
                    </div>
                @elseif ($step === 2)
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b45f3c]">Step 2 of 3</p>
                        
                        @if ($name_found)
                            <div class="mt-2 rounded-lg bg-[#f4f1ea] p-4 border-2 border-[#17211d]/10">
                                <p class="text-xs font-bold uppercase tracking-wider text-[#17211d]/60">Welcome</p>
                                <h2 class="text-2xl font-black tracking-tight text-[#17211d]">{{ $name }}</h2>
                            </div>
                        @else
                            <div class="mt-2">
                                <label for="user-name" class="block text-xs font-black uppercase tracking-[0.18em] text-[#17211d]/55">Enter your full name</label>
                                <input id="user-name" type="text" wire:model="name" class="mt-2 h-14 w-full rounded-lg border-2 border-[#17211d]/15 bg-[#f4f1ea] px-4 text-lg font-black outline-none transition focus:border-[#b45f3c]" placeholder="John Doe">
                                @error('name') <p class="mt-1 text-sm font-bold text-[#b45f3c]">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <h3 class="mt-6 text-xl font-black tracking-tight">What service do you need?</h3>
                        <p class="mt-1 text-sm text-[#17211d]/60">Select an option to route you to the correct desk.</p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @forelse ($categories as $category)
                                <button type="button" wire:click="$set('category_id', {{ $category->id }})" class="min-h-24 rounded-lg border-2 p-4 text-left transition {{ $category_id === $category->id ? 'border-[#17211d] bg-[#f2c14e]' : 'border-[#17211d]/10 bg-[#f4f1ea] hover:border-[#17211d]/35' }}">
                                    <span class="text-xs font-black uppercase tracking-[0.18em] text-[#b45f3c]">{{ $category->prefix }}</span>
                                    <span class="mt-2 block text-lg font-black">{{ $category->name }}</span>
                                </button>
                            @empty
                                <p class="col-span-full rounded-lg bg-[#f4f1ea] p-5 text-sm font-bold text-[#17211d]/60">No services available.</p>
                            @endforelse
                        </div>
                        @error('category_id') <p class="mt-3 text-sm font-bold text-[#b45f3c]">{{ $message }}</p> @enderror

                        <div class="mt-7 flex gap-3">
                            <button type="button" wire:click="previousStep" class="h-16 w-1/3 rounded-lg border-2 border-[#17211d]/15 text-sm font-black transition hover:border-[#17211d]/40">Back</button>
                            <button type="button" wire:click="nextStep" class="h-16 flex-1 rounded-lg bg-[#17211d] text-base font-black text-[#fffdf8] transition hover:bg-[#293a32]">Continue <span class="ml-3 text-xl" aria-hidden="true">→</span></button>
                        </div>
                    </div>
                @else
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b45f3c]">Step 3 of 3</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight">One final preference</h2>
                        <p class="mt-2 text-sm text-[#17211d]/60">Let us know if you require priority assistance.</p>
                        <button type="button" wire:click="$toggle('is_priority')" class="mt-8 flex w-full items-center justify-between rounded-lg border-2 p-5 text-left transition {{ $is_priority ? 'border-[#17211d] bg-[#f2c14e]' : 'border-[#17211d]/10 bg-[#f4f1ea]' }}">
                            <span><span class="block text-lg font-black">Priority service</span><span class="mt-1 block text-sm text-[#17211d]/60">For individuals requiring priority attention.</span></span>
                            <span class="flex size-8 items-center justify-center rounded-full border-2 border-[#17211d] text-lg font-black">{{ $is_priority ? '✓' : '' }}</span>
                        </button>
                        <div class="mt-8 flex gap-3">
                            <button type="button" wire:click="previousStep" class="h-16 w-1/3 rounded-lg border-2 border-[#17211d]/15 text-sm font-black transition hover:border-[#17211d]/40">Back</button>
                            <button type="button" wire:click="$dispatch('kiosk-ready')" class="h-16 flex-1 rounded-lg bg-[#17211d] text-base font-black text-[#fffdf8] transition hover:bg-[#293a32]">Get ticket</button>
                        </div>
                    </div>
                @endif
            </section>
        </main>

        <footer class="flex justify-between border-t border-[#17211d]/15 pt-4 text-xs font-bold text-[#17211d]/45">
            <span>Customer Service Center</span>
            <span>Assistance Available</span>
        </footer>
    </div>
</div>
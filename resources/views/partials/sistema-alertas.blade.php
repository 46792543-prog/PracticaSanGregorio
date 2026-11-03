{{-- Mensajes de éxito y errores de validación, comunes a todos los paneles. $sinErrores omite el bloque de errores. --}}
@if (session('status'))
    <div class="sg-aparecer mb-6 flex items-start gap-3 rounded-2xl bg-white border border-emerald-200 border-l-4 border-l-emerald-500 text-emerald-800 px-5 py-4 text-sm shadow-sm">
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </span>
        <span class="pt-1 font-medium">{{ session('status') }}</span>
    </div>
@endif
@if (empty($sinErrores) && isset($errors) && $errors->any())
    <div data-sg-errores class="sg-aparecer mb-6 flex items-start gap-3 rounded-2xl bg-white border border-red-200 border-l-4 border-l-red-500 text-red-700 px-5 py-4 text-sm shadow-sm">
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </span>
        <div class="pt-1">
            <p class="font-semibold mb-1">Revisá los siguientes datos:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

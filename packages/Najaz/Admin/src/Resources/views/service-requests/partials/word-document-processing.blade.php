@if ($request->service && $request->service->documentTemplate && $request->service->documentTemplate->is_active)
    @php
        $documentService = new \Najaz\Service\Services\DocumentTemplateService();
        $hasFileFields = $documentService->hasFileOrImageFields($request);
    @endphp
    @if ($hasFileFields)
        <div class="space-y-4">
            <!-- Instructions -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">
                    @lang('Admin::app.service-requests.word-document.instructions')
                </h4>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    @lang('Admin::app.service-requests.word-document.instruction-text')
                </p>
            </div>

            <!-- Status -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    @lang('Admin::app.service-requests.word-document.status')
                </h4>
                @if ($request->final_pdf_path && \Storage::exists($request->final_pdf_path))
                    <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                        <span class="icon-checkmark text-xl"></span>
                        <span>@lang('Admin::app.service-requests.word-document.status-ready')</span>
                    </div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <p><strong>@lang('Admin::app.service-requests.word-document.filled-by'):</strong> {{ $request->filledByAdmin->name ?? 'N/A' }}</p>
                        <p><strong>@lang('Admin::app.service-requests.word-document.filled-at'):</strong> {{ $request->filled_at ? $request->filled_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-orange-600 dark:text-orange-400">
                        <span class="icon-information text-xl"></span>
                        <span>@lang('Admin::app.service-requests.word-document.status-pending')</span>
                    </div>
                @endif
            </div>

            <!-- Download Word Button -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <a
                    href="{{ route('admin.service-requests.download-word', $request->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                >
                    <span class="icon-download text-xl"></span>
                    @lang('Admin::app.service-requests.word-document.download-word')
                </a>
            </div>

            <!-- Upload PDF Form -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">
                    @lang('Admin::app.service-requests.word-document.upload-pdf')
                </h4>
                <form
                    id="uploadPdfForm"
                    action="{{ route('admin.service-requests.upload-pdf', $request->id) }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf
                    <div class="flex items-center gap-3">
                        <input
                            type="file"
                            name="filled_pdf"
                            id="filled_pdf"
                            accept=".pdf"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                            required
                        />
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors whitespace-nowrap"
                            id="uploadButton"
                        >
                            <span class="icon-upload text-xl"></span>
                            <span id="uploadButtonText">@lang('Admin::app.service-requests.word-document.upload')</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('uploadPdfForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const button = document.getElementById('uploadButton');
                const buttonText = document.getElementById('uploadButtonText');
                const originalText = buttonText.textContent;
                
                button.disabled = true;
                buttonText.textContent = '@lang('Admin::app.service-requests.word-document.uploading')';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('@lang('Admin::app.service-requests.word-document.upload-failed')');
                    console.error('Error:', error);
                })
                .finally(() => {
                    button.disabled = false;
                    buttonText.textContent = originalText;
                });
            });
        </script>
    @endif
@endif


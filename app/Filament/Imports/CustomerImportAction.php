<?php

namespace App\Filament\Imports;

use Closure;
use Filament\Actions\ImportAction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\ValidationException;
use League\Csv\Reader as CsvReader;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Override;

final class CustomerImportAction extends ImportAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->options(fn (CustomerImportAction $action): array => [
            'authGuard' => $action->getAuthGuard(),
        ]);

        $this->schema(fn (CustomerImportAction $action): array => array_merge([
            FileUpload::make('file')
                ->label(__('customers.import.file_label'))
                ->placeholder(__('customers.import.file_placeholder'))
                ->helperText(__('customers.import.file_help'))
                ->acceptedFileTypes([
                    'text/csv',
                    'text/x-csv',
                    'application/csv',
                    'application/x-csv',
                    'text/comma-separated-values',
                    'text/x-comma-separated-values',
                    'text/plain',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->rules($action->getFileValidationRules())
                ->afterStateUpdated(function (
                    FileUpload $component,
                    Component $livewire,
                    Set $set,
                    ?TemporaryUploadedFile $state,
                ) use ($action): void {
                    if (! $state instanceof TemporaryUploadedFile) {
                        return;
                    }

                    try {
                        $livewire->validateOnly($component->getStatePath());
                    } catch (ValidationException $exception) {
                        $component->state([]);

                        throw $exception;
                    }

                    $worksheet = null;

                    if (CustomerImportFileReader::isXlsx($state->getClientOriginalName())) {
                        $filePath = $state->getRealPath();

                        if (is_string($filePath)) {
                            $worksheet = array_key_first(CustomerImportFileReader::getSheetOptions($filePath));
                            $set('worksheet', $worksheet);
                        }
                    }

                    $headers = $action->getCsvHeaders($state, $worksheet);
                    $set('columnMap', CustomerImporter::guessColumnMap($headers));
                })
                ->storeFiles(false)
                ->visibility('private')
                ->required()
                ->hiddenLabel(),
            Fieldset::make(__('filament-actions::import.modal.form.columns.label'))
                ->columns(1)
                ->inlineLabel()
                ->schema(function (Get $get) use ($action): array {
                    $file = $get('file');

                    if (! $file instanceof TemporaryUploadedFile) {
                        return [];
                    }

                    $worksheet = $get('worksheet');
                    $headers = $action->getCsvHeaders($file, is_string($worksheet) ? $worksheet : null);
                    $columnOptions = array_combine($headers, $headers) ?: [];

                    return array_map(
                        fn (ImportColumn $column): Select => $column->getSelect()->options($columnOptions),
                        $action->getImporter()::getColumns(),
                    );
                })
                ->statePath('columnMap')
                ->visible(fn (Get $get): bool => $get('file') instanceof TemporaryUploadedFile),
        ], $action->getImporter()::getOptionsFormComponents()));
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    public function getFileValidationRules(): array
    {
        $parentRules = parent::getFileValidationRules();

        return [
            'extensions:csv,txt,xlsx',
            fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                if (! $value instanceof TemporaryUploadedFile) {
                    return;
                }

                $headers = $this->getCsvHeaders($value, $this->getSelectedWorksheet());
                $duplicateHeaders = array_filter(
                    array_keys(array_filter(array_count_values($headers), fn (int $count): bool => $count > 1)),
                    fn (string $header): bool => filled($header),
                );

                if (blank($duplicateHeaders)) {
                    return;
                }

                $fail(trans_choice('filament-actions::import.modal.form.file.rules.duplicate_columns', count($duplicateHeaders), [
                    'columns' => implode(', ', $duplicateHeaders),
                ]));
            },
            ...array_slice($parentRules, 2),
        ];
    }

    /**
     * @return resource | false
     */
    #[Override]
    public function getUploadedFileStream(TemporaryUploadedFile $file)
    {
        if (! CustomerImportFileReader::isXlsx($file->getClientOriginalName())) {
            return parent::getUploadedFileStream($file);
        }

        $worksheet = $this->getSelectedWorksheet();
        $filePath = $file->getRealPath();

        if (! is_string($filePath)) {
            return false;
        }

        return CustomerImportFileReader::getCsvStream($filePath, is_string($worksheet) ? $worksheet : null);
    }

    /**
     * @return array<int, string>
     */
    private function getCsvHeaders(TemporaryUploadedFile $file, ?string $worksheet = null): array
    {
        if (CustomerImportFileReader::isXlsx($file->getClientOriginalName())) {
            $filePath = $file->getRealPath();

            return is_string($filePath) ? CustomerImportFileReader::getHeaders($filePath, $worksheet) : [];
        }

        $stream = $this->getUploadedFileStream($file);

        if (! $stream) {
            return [];
        }

        try {
            rewind($stream);
            $reader = CsvReader::from($stream);

            if (filled($delimiter = $this->getCsvDelimiter($reader))) {
                $reader->setDelimiter($delimiter);
            }

            $reader->setHeaderOffset($this->getHeaderOffset() ?? 0);

            return $reader->getHeader();
        } finally {
            fclose($stream);
        }
    }

    private function getSelectedWorksheet(): ?string
    {
        $worksheet = $this->getRawData()['worksheet'] ?? null;

        return is_string($worksheet) ? $worksheet : null;
    }
}

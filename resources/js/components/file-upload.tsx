import { Upload, X, FileImage } from 'lucide-react';
import { useCallback, useId, useRef, useState } from 'react';

import { cn } from '@/lib/utils';

/** Props untuk komponen FileUpload */
interface FileUploadProps {
    /** Tipe file yang diterima, contoh: "image/jpeg,image/png" */
    accept?: string;
    /** Ukuran maksimal file dalam bytes (default: 2MB) */
    maxSize?: number;
    /** Callback saat file berubah (file baru dipilih atau dihapus) */
    onChange: (file: File | null) => void;
    /** Pesan error dari luar (misal error validasi server) */
    error?: string;
    /** Label untuk input file */
    label?: string;
    /** ID custom untuk input (opsional) */
    id?: string;
}

/** Informasi file yang sudah dipilih */
interface SelectedFile {
    file: File;
    previewUrl: string;
}

/**
 * Format ukuran file menjadi string yang mudah dibaca.
 * Contoh: 1048576 → "1.00 MB"
 */
function formatFileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

/**
 * Komponen upload file dengan preview thumbnail.
 * Mendukung validasi tipe dan ukuran file secara client-side.
 * Digunakan untuk upload KTP pada form permohonan.
 */
export default function FileUpload({
    accept = 'image/jpeg,image/png',
    maxSize = 2 * 1024 * 1024,
    onChange,
    error,
    label = 'Upload File',
    id: customId,
}: FileUploadProps) {
    const generatedId = useId();
    const inputId = customId ?? `file-upload-${generatedId}`;
    const errorId = `${inputId}-error`;
    const inputRef = useRef<HTMLInputElement>(null);

    const [selectedFile, setSelectedFile] = useState<SelectedFile | null>(null);
    const [validationError, setValidationError] = useState<string>('');
    const [isDragOver, setIsDragOver] = useState(false);

    // Pesan error yang ditampilkan (prioritas: validasi internal > error eksternal)
    const displayError = validationError || error;

    /** Validasi file berdasarkan tipe dan ukuran */
    const validateFile = useCallback(
        (file: File): string => {
            // Validasi tipe file
            const acceptedTypes = accept.split(',').map((t) => t.trim());
            if (!acceptedTypes.includes(file.type)) {
                return `Format file tidak didukung. Hanya ${acceptedTypes.map((t) => t.split('/')[1]?.toUpperCase()).join('/')} yang diizinkan.`;
            }

            // Validasi ukuran file
            if (file.size > maxSize) {
                return `File terlalu besar (${formatFileSize(file.size)}). Maksimal ${formatFileSize(maxSize)}.`;
            }

            return '';
        },
        [accept, maxSize],
    );

    /** Proses file yang dipilih */
    const handleFileSelect = useCallback(
        (file: File) => {
            // Validasi file
            const errorMessage = validateFile(file);
            if (errorMessage) {
                setValidationError(errorMessage);
                setSelectedFile(null);
                onChange(null);
                return;
            }

            // Buat preview URL dari file
            const previewUrl = URL.createObjectURL(file);

            // Bersihkan preview URL sebelumnya jika ada
            if (selectedFile?.previewUrl) {
                URL.revokeObjectURL(selectedFile.previewUrl);
            }

            setValidationError('');
            setSelectedFile({ file, previewUrl });
            onChange(file);
        },
        [validateFile, onChange, selectedFile?.previewUrl],
    );

    /** Handler perubahan input file */
    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            handleFileSelect(file);
        }
    };

    /** Handler drag & drop */
    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(false);

        const file = e.dataTransfer.files?.[0];
        if (file) {
            handleFileSelect(file);
        }
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(false);
    };

    /** Hapus file yang sudah dipilih */
    const handleRemoveFile = () => {
        if (selectedFile?.previewUrl) {
            URL.revokeObjectURL(selectedFile.previewUrl);
        }
        setSelectedFile(null);
        setValidationError('');
        onChange(null);

        // Reset input agar file yang sama bisa dipilih kembali
        if (inputRef.current) {
            inputRef.current.value = '';
        }
    };

    /** Buka dialog file picker */
    const handleClickArea = () => {
        inputRef.current?.click();
    };

    return (
        <div className="w-full">
            {/* Label */}
            <label htmlFor={inputId} className="mb-1.5 block text-sm font-medium">
                {label}
            </label>

            {/* Area upload atau preview */}
            {selectedFile ? (
                // Preview file yang sudah dipilih
                <div
                    className={cn(
                        'relative rounded-lg border-2 border-dashed p-4',
                        displayError ? 'border-red-400 bg-red-50/50' : 'border-hijau/30 bg-hijau/5',
                    )}
                >
                    <div className="flex items-center gap-4">
                        {/* Thumbnail preview */}
                        <div className="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 bg-white">
                            <img
                                src={selectedFile.previewUrl}
                                alt={`Preview ${selectedFile.file.name}`}
                                className="h-full w-full object-cover"
                            />
                        </div>

                        {/* Info file */}
                        <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-medium text-gray-800">
                                {selectedFile.file.name}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                {formatFileSize(selectedFile.file.size)} &bull;{' '}
                                {selectedFile.file.type.split('/')[1]?.toUpperCase()}
                            </p>
                        </div>

                        {/* Tombol hapus */}
                        <button
                            type="button"
                            onClick={handleRemoveFile}
                            className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-red-100 hover:text-red-600 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            aria-label="Hapus file"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            ) : (
                // Area drop zone / klik untuk upload
                <div
                    role="button"
                    tabIndex={0}
                    onClick={handleClickArea}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            handleClickArea();
                        }
                    }}
                    onDrop={handleDrop}
                    onDragOver={handleDragOver}
                    onDragLeave={handleDragLeave}
                    aria-describedby={displayError ? errorId : undefined}
                    className={cn(
                        'flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-6 py-8 transition-colors',
                        'focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                        isDragOver && 'border-hijau bg-hijau/5',
                        displayError && !isDragOver && 'border-red-400 bg-red-50/50',
                        !isDragOver && !displayError && 'border-gray-300 hover:border-hijau/50 hover:bg-gray-50',
                    )}
                >
                    <div
                        className={cn(
                            'mb-3 flex h-12 w-12 items-center justify-center rounded-full',
                            isDragOver ? 'bg-hijau/10 text-hijau' : 'bg-gray-100 text-gray-400',
                        )}
                    >
                        {isDragOver ? (
                            <FileImage className="h-6 w-6" />
                        ) : (
                            <Upload className="h-6 w-6" />
                        )}
                    </div>
                    <p className="text-sm font-medium text-gray-700">
                        {isDragOver ? 'Lepaskan file di sini' : 'Klik atau seret file ke sini'}
                    </p>
                    <p className="mt-1 text-xs text-gray-500">
                        {accept
                            .split(',')
                            .map((t) => t.trim().split('/')[1]?.toUpperCase())
                            .join(', ')}{' '}
                        &bull; Maks {formatFileSize(maxSize)}
                    </p>
                </div>
            )}

            {/* Input file tersembunyi */}
            <input
                ref={inputRef}
                id={inputId}
                type="file"
                accept={accept}
                onChange={handleInputChange}
                className="sr-only"
                aria-describedby={displayError ? errorId : undefined}
                aria-invalid={displayError ? true : undefined}
            />

            {/* Pesan error */}
            {displayError && (
                <p id={errorId} role="alert" className="mt-2 text-sm text-red-600 dark:text-red-400">
                    {displayError}
                </p>
            )}
        </div>
    );
}

Yth. {{ $nama }},

Status permohonan Anda dengan nomor tiket {{ $tiketNo }} telah diperbarui.

Status Terbaru: {{ ucfirst($status) }}

@if($catatanAdmin)
Catatan Admin: {{ $catatanAdmin }}
@endif

@if($downloadUrl)
Dokumen balasan Anda telah tersedia. Silakan cek detail permohonan untuk mengunduh dokumen:
{{ $downloadUrl }}
@endif

Terima kasih telah menggunakan layanan PPID Pengadilan Agama Penajam.

Hormat kami,
PPID Pengadilan Agama Penajam

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-background text-foreground font-sans antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center gap-4 p-6 text-center">
        <p class="text-6xl font-bold text-muted-foreground">404</p>
        <h1 class="text-2xl font-semibold">Halaman Tidak Ditemukan</h1>
        <p class="text-muted-foreground text-sm max-w-sm">
            Halaman yang Anda cari tidak ada atau sudah dipindahkan.
        </p>

        <a
            href="/dashboard"
            class="mt-2 inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
        >
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>

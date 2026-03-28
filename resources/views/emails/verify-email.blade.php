<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi EMart11</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #2d89ef;">Halo, {{ $name }}!</h2>
        <p>Terima kasih telah mendaftar di <strong>eMart11</strong>.</p>
        <p>Silakan klik tombol di bawah ini untuk memverifikasi email Anda dan mengaktifkan akun:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}"
               style="background-color: #2d89ef; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
               Verifikasi Akun Saya
            </a>
        </div>

        <p>Link ini akan kadaluarsa dalam 60 menit.</p>
        <p>Jika Anda tidak merasa mendaftar di EMart11, abaikan saja email ini.</p>
        <hr style="border: 0; border-top: 1px solid #eee;">
        <p style="font-size: 0.8em; color: #888;">&copy; 2026 EMart11 Official - Belanja Cepat & Aman.</p>
    </div>
</body>
</html>

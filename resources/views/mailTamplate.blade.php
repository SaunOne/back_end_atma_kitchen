<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verifikasi Akun</title>
</head>
<body>
    <p>
        Anda telah melakukan registrasi akun dengan menggunakan email ini.
    </p>
    <p>
        Halo <b>{{ $details['username'] }}</b>!
    </p>
    <table>
        <tr>
            <td>Username</td>
            <td>:</td>
            <td>{{ $details['username'] }}</td>
        </tr>

        <tr>
            <td>Website</td>
            <td>:</td>
            <td>{{ $details['website'] }}</td>
        </tr>

        <tr>
            <td>Tanggal Register</td>
            <td>:</td>
            <td>{{ $details['datetime'] }}</td>
        </tr>
    </table>
    
    <center>
        <h3>
            <p><b style="color: blue">{{ $details['url'] }}</b></p>
            Buka link di bawah untuk melakukan verifikasi akun.
        </h3>
    </center>

    <p>
        Terima kasih telah melakukan registrasi.
    </p>
</body>
</html>
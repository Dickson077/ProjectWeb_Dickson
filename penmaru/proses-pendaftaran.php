<?php

include("config.php");

// cek apakah tombol daftar sudah diklik atau belum?
if (isset($_POST['daftar'])) {

    // ambil data dari formulir 
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $jk = $_POST['jenis_kelamin'];
    $agama = $_POST['agama'];
    $sekolah = $_POST['sekolah_asal'];

    // buat query
    $sql = "INSERT INTO calon_siswa (nama, alamat, jenis_kelamin, agama, sekolah_asal)
    VALUE ('$nama', '$alamat', '$jk', '$agama', '$sekolah')";
    $query = mysqli_query($koneksi, $sql);

    // apakah query simpan berhasil?
    if($query) {
        // kalau berhasil alihkan ke halaman index.php dengan status=sukses
        header('Location: index.php?status=sukses');
    } else {
        // kalau gagal alihkan ke halaman index.php dengan status=gagal
        header('Location: index.php?status=gagal');
    }

} else {
    die("Akses dilarang...");
}

?>
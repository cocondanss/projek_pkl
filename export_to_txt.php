<?php
  // Dapatkan data form yang dikirim
  $formData = $_POST;

  // Simpan data ke database Anda atau lakukan tindakan lain yang diperlukan
  // ...

  // Ekspor data ke file teks
  $fileName = 'voucher_data_' . date('YmdHis') . '.txt';
  $fileContent = '';
  foreach ($formData as $key => $value) {
    $fileContent .= $key . ': ' . $value . "\n";
  }
  file_put_contents($fileName, $fileContent);

  echo 'Data disimpan dan diekspor ke file teks berhasil!';

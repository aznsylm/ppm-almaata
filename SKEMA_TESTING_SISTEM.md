# SKEMA TESTING PPM-ALMAATA SYSTEM

**Format:** Test Case | Expected Result | Status (✓/✗)

---

## **MODULE 1: AUTHENTICATION**

### LOGIN - SANTRI

| No  | Scenario              | Action                                | Expected Result                        |
| --- | --------------------- | ------------------------------------- | -------------------------------------- |
| 1.1 | Login normal          | NIM: 223200231, Pass: correct         | ✓ Redirect ke `/user/dashboard`        |
| 1.2 | Login salah password  | NIM: 223200231, Pass: wrong           | ✗ Error message, tetap di login        |
| 1.3 | Login NIM tidak ada   | NIM: 999999999, Pass: any             | ✗ Error message                        |
| 1.4 | Login santri inactive | NIM: (inactive status), Pass: correct | ✗ Rejected                             |
| 1.5 | Session persist       | Login sukses → reload halaman         | ✓ Tetap login, tidak redirect ke login |

### LOGIN - ADMIN

| No  | Scenario                   | Action                                    | Expected Result                           |
| --- | -------------------------- | ----------------------------------------- | ----------------------------------------- |
| 2.1 | Login admin normal         | NIM: (admin), Pass: (dari config)         | ✓ Redirect ke `/admin/dashboard`          |
| 2.2 | Login admin salah password | NIM: (admin), Pass: wrong                 | ✗ Error message                           |
| 2.3 | Admin akses santri area    | URL: `/user/presensi` setelah login admin | ✗ Redirect ke `/admin/dashboard` atau 403 |

### PASSWORD RESET

| No  | Scenario                                        | Action                             | Expected Result                            |
| --- | ----------------------------------------------- | ---------------------------------- | ------------------------------------------ |
| 3.1 | First-time santri login (must_reset_password=1) | Login normal                       | ✓ Force redirect ke `/auth/reset-password` |
| 3.2 | Reset password berhasil                         | Old pass: correct, New pass: valid | ✓ Password updated, redirect login         |
| 3.3 | Reset password salah lama                       | Old pass: wrong, New pass: valid   | ✗ Error message                            |
| 3.4 | Reset password kosong                           | New pass: empty                    | ✗ Validation error                         |

### LOGOUT

| No  | Scenario                 | Action                               | Expected Result                     |
| --- | ------------------------ | ------------------------------------ | ----------------------------------- |
| 4.1 | Logout santri            | Click logout button                  | ✓ Session destroyed, redirect login |
| 4.2 | Logout admin             | Click logout button                  | ✓ Session destroyed, redirect login |
| 4.3 | Akses URL setelah logout | URL: `/user/presensi` setelah logout | ✗ Redirect ke login                 |

---

## **MODULE 2: ADMIN DASHBOARD & SANTRI MANAGEMENT**

### ADMIN DASHBOARD

| No  | Scenario                  | Action                           | Expected Result                            |
| --- | ------------------------- | -------------------------------- | ------------------------------------------ |
| 1.1 | Akses dashboard           | Login admin → `/admin/dashboard` | ✓ Dashboard tampil dengan menu             |
| 1.2 | Santri list filter prodi  | Filter: prodi="Informatika"      | ✓ Hanya santri Informatika muncul          |
| 1.3 | Santri list filter lantai | Filter: lantai="2"               | ✓ Hanya santri kamar lantai 2 muncul       |
| 1.4 | Santri list search NIM    | Search: "2232"                   | ✓ Santri dengan NIM mengandung 2232 muncul |
| 1.5 | Santri list search nama   | Search: "Ahmad"                  | ✓ Santri dengan nama Ahmad muncul          |
| 1.6 | Santri list pagination    | Page 2, per_page=10              | ✓ Data halaman 2 tampil (11-20)            |

### SANTRI MANAGEMENT

| No  | Scenario                           | Action                            | Expected Result                    |
| --- | ---------------------------------- | --------------------------------- | ---------------------------------- |
| 2.1 | Tambah santri (NIM baru)           | NIM dari msmhs, assign kamar      | ✓ User created, akun siap pakai    |
| 2.2 | Tambah santri (NIM sudah ada)      | NIM sudah terdaftar               | ✗ Error: "NIM sudah terdaftar"     |
| 2.3 | Tambah santri (NIM tidak di msmhs) | NIM tidak di sim_akademik.msmhs   | ✗ Error: "NIM tidak ditemukan"     |
| 2.4 | Update kamar santri                | Edit kamar santri existing        | ✓ Kamar updated                    |
| 2.5 | Delete santri                      | Hapus santri + semua data terkait | ✓ Data presensi, ijin, kartu hapus |
| 2.6 | Lihat detail santri                | Click nama santri                 | ✓ Profile + riwayat muncul         |

---

## **MODULE 3: PERIZINAN (SANTRI POV)**

### SUBMIT PERIZINAN - TIPE 1 (< 2 minggu)

| No  | Scenario                       | Action                                            | Expected Result                                          |
| --- | ------------------------------ | ------------------------------------------------- | -------------------------------------------------------- |
| 1.1 | Submit normal                  | Tipe 1, tgl 1-7 hari, alasan dipilih              | ✓ Izin tersimpan, status "Siap Cetak", PDF auto-generate |
| 1.2 | Submit tanpa alasan            | Tipe 1, tanpa pilih alasan checkbox & alasan lain | ✗ Error: "Alasan wajib diisi"                            |
| 1.3 | Submit tanggal selesai < mulai | Tgl mulai: 15, Tgl selesai: 10                    | ✗ Error validasi durasi                                  |
| 1.4 | Submit durasi > 14 hari        | Tgl mulai: 1, Tgl selesai: 20                     | ✗ Error: "Durasi maks 14 hari untuk tipe 1"              |
| 1.5 | Submit bentrok presensi        | Tgl sudah ada presensi hadir tercatat             | ✗ Error: "Ada bentrok dengan presensi" + tampil bentrok  |
| 1.6 | PDF auto-generated             | Submit sukses                                     | ✓ File PDF dibuat di `uploads/perizinan/`                |

### SUBMIT PERIZINAN - TIPE 2 (≥ 2 minggu)

| No  | Scenario                  | Action                              | Expected Result                         |
| --- | ------------------------- | ----------------------------------- | --------------------------------------- |
| 2.1 | Submit normal             | Tipe 2, tgl ≥ 15 hari, alasan       | ✓ Izin tersimpan, status "Siap Cetak"   |
| 2.2 | Submit durasi < 14 hari   | Tipe 2, durasi 10 hari              | ✗ Error: "Minimal 14 hari untuk tipe 2" |
| 2.3 | Submit dengan alasan lain | Tipe 2, custom alasan di field text | ✓ Alasan tersimpan & tampil di surat    |

### SUBMIT PERIZINAN - TIPE 3 (Event, Jamaah/Ngaji)

| No  | Scenario                                | Action                                                       | Expected Result                                           |
| --- | --------------------------------------- | ------------------------------------------------------------ | --------------------------------------------------------- |
| 3.1 | Jamaah maghrib - tanpa dokumen          | Tipe 3, kategori: jamaah_maghrib, alasan: Sakit              | ✓ Izin tersimpan, status "Siap Cetak"                     |
| 3.2 | Ngaji maghrib - alasan Kerkom + dokumen | Tipe 3, kategori: ngaji_maghrib, alasan: Kerkom, upload file | ✓ Izin + dokumen tersimpan, status "Menunggu Dokumentasi" |
| 3.3 | Tipe 3 multi-kategori                   | Pilih 2 kategori (jamaah_maghrib + ngaji_subuh)              | ✓ Tersimpan dengan sub_kategori gabung                    |
| 3.4 | Tipe 3 tanpa kategori                   | Tipe 3, tidak pilih kategori                                 | ✗ Error: "Kategori wajib dipilih"                         |
| 3.5 | Tipe 3 tanpa alasan                     | Tipe 3, kategori dipilih, alasan kosong                      | ✗ Error: "Alasan wajib dipilih"                           |
| 3.6 | Upload dokumen size > 2MB               | File > 2MB                                                   | ✗ Rejected, hanya max 2MB                                 |
| 3.7 | Upload dokumen format valid             | Format: pdf, jpg, jpeg, png                                  | ✓ File tersimpan                                          |
| 3.8 | Upload dokumen format invalid           | Format: exe, doc                                             | ✗ Rejected                                                |

### RIWAYAT PERIZINAN

| No  | Scenario                        | Action                                     | Expected Result                                                    |
| --- | ------------------------------- | ------------------------------------------ | ------------------------------------------------------------------ |
| 4.1 | Lihat riwayat                   | Click "Perizinan" → Riwayat tab            | ✓ List riwayat izin (newest first), paginated 5/page               |
| 4.2 | Filter status                   | Filter: status="Disetujui"                 | ✓ Hanya izin approved tampil                                       |
| 4.3 | Download surat                  | Status "Siap Cetak" → Click download       | ✓ PDF download, status jadi "Menunggu Upload"                      |
| 4.4 | Upload surat hasil cetak        | Status "Menunggu Upload", upload file      | ✓ File tersimpan, status "Menunggu Validasi"                       |
| 4.5 | Upload dokumentasi (Kerkom)     | Status "Menunggu Dokumentasi", upload file | ✓ Dokumen tersimpan, jika surat sudah → status "Menunggu Validasi" |
| 4.6 | Download surat selesai validasi | Status "Disetujui" → Download              | ✓ PDF bisa diunduh                                                 |
| 4.7 | Download surat ditolak          | Status "Ditolak" → Download                | ✗ Error: "Surat tidak bisa diunduh pada status saat ini"           |

---

## **MODULE 4: PERIZINAN (ADMIN POV)**

### VALIDASI PERIZINAN

| No  | Scenario                          | Action                                   | Expected Result                   |
| --- | --------------------------------- | ---------------------------------------- | --------------------------------- |
| 1.1 | List perizinan                    | Admin → Perizinan                        | ✓ Semua izin tampil, newest first |
| 1.2 | Filter status "Menunggu Validasi" | Filter status=2                          | ✓ Hanya izin status 2 tampil      |
| 1.3 | Filter tipe izin                  | Filter: tipe=1                           | ✓ Hanya tipe 1 tampil             |
| 1.4 | Filter by room                    | Filter: kamar="2-01"                     | ✓ Hanya izin kamar 2-01 tampil    |
| 1.5 | Filter by semester                | Filter: smt=1                            | ✓ Hanya semester 1 tampil         |
| 1.6 | Filter by date range              | Tgl dari: 2026-05-01, Sampai: 2026-05-10 | ✓ Hanya izin dalam range tampil   |
| 1.7 | Filter kombinasi                  | Status=2 + Tipe=1 + Kamar=2-01           | ✓ Union filter work               |
| 1.8 | Search NIM/nama                   | Search: "2232"                           | ✓ Izin by NIM/nama santri muncul  |

### APPROVE PERIZINAN

| No  | Scenario                   | Action                                             | Expected Result                                        |
| --- | -------------------------- | -------------------------------------------------- | ------------------------------------------------------ |
| 2.1 | Approve izin               | Click tombol "Setujui", status="Menunggu Validasi" | ✓ Status jadi "Disetujui" (3), presensi auto-mark izin |
| 2.2 | Reject izin                | Click "Tolak", beri catatan                        | ✓ Status jadi "Ditolak" (4), catatan tersimpan         |
| 2.3 | Approve izin tipe 3 Kerkom | Izin kerkom (butuh dokumentasi), approve           | ✓ Status jadi "Disetujui" jika dokumentasi sudah ada   |
| 2.4 | Selesaikan izin            | Click "Selesaikan Izin" saat "Disetujui"           | ✓ Status jadi "Selesai" / mark finalized               |
| 2.5 | Lanjutkan izin             | Izin yg pernah selesai → "Lanjutkan"               | ✓ Status kembali edit-able                             |

### PRESENSI AUTO-MARK (linked dengan perizinan)

| No  | Scenario                  | Action                   | Expected Result                                                                    |
| --- | ------------------------- | ------------------------ | ---------------------------------------------------------------------------------- |
| 3.1 | Admin approve izin tipe 1 | Izin tgl 5-10 May        | ✓ presensi tabel: row untuk 5-10 May di-create dengan status "izin" + id_izin link |
| 3.2 | Admin reject izin         | Reject tgl 5-10 May izin | ✓ presensi row di-delete atau di-mark "tidak izin"                                 |
| 3.3 | Izin type 3 (event)       | Approve jamaah maghrib   | ✓ presensi hanya untuk kegiatan itu (jamaah_maghrib) di-mark izin                  |

### BACKUP & EXPORT

| No  | Scenario             | Action                                        | Expected Result                                        |
| --- | -------------------- | --------------------------------------------- | ------------------------------------------------------ |
| 4.1 | Backup filtered izin | Set date range, filter status, click "Backup" | ✓ CSV download dengan data filtered                    |
| 4.2 | Backup size check    | Range > 3 months                              | ✗ Error: "Max 3 bulan per backup"                      |
| 4.3 | Delete filtered izin | Filter + click "HAPUS" + konfirmasi           | ✓ Data terpilih dihapus, presensi terkait juga dihapus |

---

## **MODULE 5: KEHADIRAN (SANTRI POV)**

### DASHBOARD PRESENSI

| No  | Scenario              | Action            | Expected Result                                                                                           |
| --- | --------------------- | ----------------- | --------------------------------------------------------------------------------------------------------- |
| 1.1 | Akses presensi        | Santri → Presensi | ✓ Dashboard + checkin buttons tampil                                                                      |
| 1.2 | Lihat jadwal kegiatan | Dashboard load    | ✓ 5 kegiatan (jamaah_maghrib, jamaah_isya, jamaah_subuh, ngaji_maghrib, ngaji_subuh) dengan jadwal tampil |
| 1.3 | Lihat status cards    | Dashboard         | ✓ Jamaah card + Ngaji card color tampil                                                                   |
| 1.4 | Lihat alpha count     | Dashboard         | ✓ Total alpha minggu ini tampil                                                                           |

### SELF CHECK-IN

| No  | Scenario                       | Action                                  | Expected Result                                           |
| --- | ------------------------------ | --------------------------------------- | --------------------------------------------------------- |
| 2.1 | Check-in on time               | Jamaah maghrib 17:45, checkin 17:50     | ✓ Presensi recorded status "hadir", presensi_at timestamp |
| 2.2 | Check-in late                  | Kegiatan jam 17:45-19:00, checkin 18:45 | ✓ Recorded (asumsi late masih accept, verify logic)       |
| 2.3 | Check-in after kegiatan end    | Kegiatan 17:45-19:00, checkin 20:00     | ✗ Rejected: "Waktu kegiatan sudah selesai"                |
| 2.4 | Check-in 2x hari sama          | Jamaah maghrib checkin 2x hari sama     | ✗ Rejected: "Sudah ada presensi hari ini"                 |
| 2.5 | Check-in saat izin approved    | Hari dlm range izin approved            | ✗ Rejected: "Sudah ada izin tercetat" / atau auto-skip    |
| 2.6 | Check-in saat tidak ada jadwal | Jadwal kegiatan diset inactive          | ✗ Rejected / button disabled                              |

### ATTENDANCE HISTORY

| No  | Scenario            | Action                   | Expected Result                             |
| --- | ------------------- | ------------------------ | ------------------------------------------- |
| 3.1 | Lihat history       | Click "Riwayat Presensi" | ✓ History 5 minggu terakhir tampil          |
| 3.2 | Lihat detail minggu | Click minggu tertentu    | ✓ Detail per hari (hadir/alpha/izin) tampil |
| 3.3 | Export history      | Click "Export"           | ✓ File Excel download                       |

### CARD SYSTEM (automatic calculation)

| No  | Scenario                 | Action                  | Expected Result        |
| --- | ------------------------ | ----------------------- | ---------------------- |
| 4.1 | Jamaah putih (0-6 alpha) | Alpha jamaah minggu: 3  | ✓ Kartu jamaah: putih  |
| 4.2 | Jamaah kuning (7 alpha)  | Alpha jamaah minggu: 7  | ✓ Kartu jamaah: kuning |
| 4.3 | Jamaah orange (8 alpha)  | Alpha jamaah minggu: 8  | ✓ Kartu jamaah: orange |
| 4.4 | Jamaah merah (9 alpha)   | Alpha jamaah minggu: 9  | ✓ Kartu jamaah: merah  |
| 4.5 | Jamaah hitam (10+ alpha) | Alpha jamaah minggu: 12 | ✓ Kartu jamaah: hitam  |
| 4.6 | Ngaji putih (0-1 alpha)  | Alpha ngaji minggu: 1   | ✓ Kartu ngaji: putih   |
| 4.7 | Ngaji kuning (2 alpha)   | Alpha ngaji minggu: 2   | ✓ Kartu ngaji: kuning  |
| 4.8 | Ngaji merah (3+ alpha)   | Alpha ngaji minggu: 3   | ✓ Kartu ngaji: merah   |

---

## **MODULE 6: KEHADIRAN (ADMIN POV)**

### ATTENDANCE RECAP (Weekly)

| No  | Scenario           | Action                            | Expected Result                                            |
| --- | ------------------ | --------------------------------- | ---------------------------------------------------------- |
| 1.1 | Lihat recap minggu | Admin → Kehadiran                 | ✓ Semua santri, minggu terkini tampil dengan alpha & cards |
| 1.2 | Filter kegiatan    | Filter: kegiatan="jamaah_maghrib" | ✓ Hanya data jamaah maghrib tampil                         |
| 1.3 | Filter kamar       | Filter: kamar="2-01"              | ✓ Hanya santri kamar 2-01 tampil                           |
| 1.4 | Filter nama/NIM    | Search: "Ahmad"                   | ✓ Data santri Ahmad muncul                                 |
| 1.5 | Export recap       | Click "Export Recap"              | ✓ Excel file download                                      |

### DETAIL SANTRI PER MINGGU

| No  | Scenario                 | Action                        | Expected Result                               |
| --- | ------------------------ | ----------------------------- | --------------------------------------------- |
| 2.1 | Lihat detail santri      | Click nama santri di recap    | ✓ Week detail (per kegiatan, per hari) tampil |
| 2.2 | Export individual detail | Click "Export" di detail page | ✓ Excel download                              |

### JADWAL MANAGEMENT

| No  | Scenario            | Action                         | Expected Result                         |
| --- | ------------------- | ------------------------------ | --------------------------------------- |
| 3.1 | Akses jadwal config | Admin → Kehadiran → Jadwal     | ✓ Jadwal kegiatan + jam tampil          |
| 3.2 | Edit jam kegiatan   | Update jam_mulai / jam_selesai | ✓ Jadwal updated                        |
| 3.3 | Disable kegiatan    | Set is_active=0                | ✓ Kegiatan tidak tersedia untuk checkin |
| 3.4 | Enable kegiatan     | Set is_active=1                | ✓ Kegiatan tersedia kembali             |

### MANUAL ENTRY PRESENSI

| No  | Scenario                          | Action                                 | Expected Result                           |
| --- | --------------------------------- | -------------------------------------- | ----------------------------------------- |
| 4.1 | Add manual entry                  | NIM, kegiatan, tanggal, status         | ✓ Presensi recorded, created_by=admin NIM |
| 4.2 | Add manual entry (duplicate date) | Same NIM, kegiatan, tanggal            | ✗ Error / warning                         |
| 4.3 | Add multiple (batch)              | Upload CSV: NIM, kegiatan, tgl, status | ✓ Batch processed, duplikat skipped       |
| 4.4 | Edit manual entry                 | Ubah status hadir→izin                 | ✓ Presensi updated                        |
| 4.5 | Edit manual entry (link izin)     | Change status to "izin" + link id_izin | ✓ id_izin updated                         |
| 4.6 | Delete manual entry               | Delete presensi manual                 | ✓ Record deleted, alpha recalc            |

### FINALISASI KARTU

| No  | Scenario               | Action                                  | Expected Result                           |
| --- | ---------------------- | --------------------------------------- | ----------------------------------------- |
| 5.1 | Finalize weekly cards  | Week minggu 1 May → Click "Finalisasi"  | ✓ presensi_kartu is_final=1, cards locked |
| 5.2 | View finalized cards   | Lihat recap minggu yang sudah finalized | ✓ Data read-only                          |
| 5.3 | Reopen finalized cards | Admin buka kembali                      | ✓ is_final=0, allow edit kembali          |

---

## **MODULE 7: USER DASHBOARD (PROFILE)**

### PROFILE VIEW - SANTRI

| No  | Scenario                                  | Action                         | Expected Result                                              |
| --- | ----------------------------------------- | ------------------------------ | ------------------------------------------------------------ |
| 1.1 | Akses dashboard                           | Santri login → /user/dashboard | ✓ Dashboard + 6 cards tampil                                 |
| 1.2 | Card 1 - 4 icon (NIM, Nama, Prodi, Kamar) | Page load                      | ✓ Data dari profile (atau '-' jika kosong)                   |
| 1.3 | Card "Ringkasan Utama"                    | Click expand                   | ✓ Status Izin Saat Ini + Periode Izin Aktif tampil           |
| 1.4 | Ringkasan - No active izin                | Tidak ada izin aktif           | ✓ Status: "Tidak ada izin aktif", Periode: "- s/d -"         |
| 1.5 | Card "Riwayat Izin Singkat"               | Click expand                   | ✓ Total Pengajuan, Pengajuan Terakhir, Status Terakhir       |
| 1.6 | Card "Profil Akademik"                    | Click expand                   | ✓ Angkatan, Semester, Status Mahasiswa, Jenjang/Prodi tampil |
| 1.7 | Card "Profil Pribadi"                     | Click expand                   | ✓ TTL, JK, Agama, Darah, Tinggi, Berat tampil                |
| 1.8 | Card "Kontak & Domisili"                  | Click expand                   | ✓ Email, No HP, Alamat tampil                                |
| 1.9 | Card "Ortu/Wali & Sekolah"                | Click expand                   | ✓ Data ortu, sekolah asal tampil                             |

### DATA COMPLETENESS

| No  | Scenario              | Action                                    | Expected Result                    |
| --- | --------------------- | ----------------------------------------- | ---------------------------------- |
| 2.1 | Field kosong          | Field tanpa data di database              | ✓ Show '-' (fallback)              |
| 2.2 | Data dari join tables | Profil dari sim_akademik.msmhs + mssantri | ✓ All join fields pulled correctly |

---

## **MODULE 8: SECURITY & ACCESS CONTROL**

### ROLE-BASED ACCESS

| No  | Scenario                  | Action                      | Expected Result                   |
| --- | ------------------------- | --------------------------- | --------------------------------- |
| 1.1 | Santri akses /admin/\*    | Direct URL                  | ✗ 403 / redirect /user/dashboard  |
| 1.2 | Admin akses /user/\*      | Direct URL                  | ✗ 403 / redirect /admin/dashboard |
| 1.3 | Guest akses any protected | Without login               | ✗ Redirect /auth/login            |
| 1.4 | Santri edit orang lain    | Direct edit API santri lain | ✗ Blocked                         |
| 1.5 | Admin delete santri       | Delete santri + cascade     | ✓ OK (admin privilege)            |

### DATA INTEGRITY

| No  | Scenario                     | Action                                        | Expected Result       |
| --- | ---------------------------- | --------------------------------------------- | --------------------- |
| 2.1 | Delete santri cascade        | Delete santri → presensi, ijin, kartu deleted | ✓ All cascade OK      |
| 2.2 | Reject izin cascade          | Reject izin → presensi izin dihapus           | ✓ Cascade handled     |
| 2.3 | Delete presensi recalc alpha | Delete presensi manual → alpha recalc         | ✓ Cardinality updated |

---

## **MODULE 9: EDGE CASES & ERROR HANDLING**

### PERIZINAN EDGE CASES

| No  | Scenario                                 | Action                                          | Expected Result                  |
| --- | ---------------------------------------- | ----------------------------------------------- | -------------------------------- |
| 1.1 | Submit izin overlap dengan izin approved | Izin A (1-10 May) approved, submit B (5-15 May) | ⚠ Check if system allow / reject |
| 1.2 | Submit izin across month boundary        | Izin 28 May - 3 June                            | ✓ Cross-boundary OK              |
| 1.3 | Submit izin past date                    | Mulai: yesterday                                | ✗ Reject / warning               |
| 1.4 | Submit izin future far                   | Mulai: 6 bulan depan                            | ⚠ Verify limit                   |
| 1.5 | Tipe 3 same kategori 2x                  | Submit 2x jamaah_maghrib in range               | ⚠ Check if allowed               |
| 1.6 | Admin validate deleted document          | Upload file but then delete from system         | ⚠ Verify file integrity check    |

### PRESENSI EDGE CASES

| No  | Scenario                            | Action                               | Expected Result                    |
| --- | ----------------------------------- | ------------------------------------ | ---------------------------------- |
| 2.1 | Check-in clock sync issue           | System clock off, user checkin       | ⚠ Verify timestamp logic           |
| 2.2 | Multiple checkin same second        | 2 santri checkin identical time      | ✓ Both recorded (no conflict)      |
| 2.3 | Alpha calc with multiple izin types | Jamaah approved + manual ngaji entry | ✓ Alpha counted correctly for each |
| 2.4 | Card transition end-of-week         | Minggu A: 6 alpha, Minggu B: 7 alpha | ✓ Card change visible              |

### DATA CONSISTENCY

| No  | Scenario                    | Action                             | Expected Result                 |
| --- | --------------------------- | ---------------------------------- | ------------------------------- |
| 3.1 | DB constraint violation     | Attempt insert invalid foreign key | ✗ DB error / handled gracefully |
| 3.2 | Concurrent izin approval    | Admin1 + Admin2 approve same izin  | ⚠ First wins / last wins?       |
| 3.3 | File upload partial failure | Upload 3 files, 1 fail             | ⚠ Rollback or partial?          |

### UI/UX ISSUES

| No  | Scenario                  | Action                   | Expected Result                      |
| --- | ------------------------- | ------------------------ | ------------------------------------ |
| 4.1 | Pagination boundary       | Page=999 (doesn't exist) | ✓ Return empty / redirect page 1     |
| 4.2 | Export empty result       | Filter result=0, export  | ✓ Empty file or "no data" message    |
| 4.3 | Form validation real-time | Submit invalid data      | ✓ Client-side validation before POST |
| 4.4 | Long names/data           | Santri nama very long    | ✓ Truncate/wrap gracefully           |

---

## **MODULE 10: SYSTEM INTEGRATION**

### EXTERNAL DB DEPENDENCY (sim_akademik)

| No  | Scenario                     | Action                           | Expected Result             |
| --- | ---------------------------- | -------------------------------- | --------------------------- |
| 1.1 | sim_akademik unreachable     | DB down / wrong credentials      | ✗ Error handling / fallback |
| 1.2 | Santri data mismatch         | NIM di users tapi tidak di msmhs | ⚠ Profile partially empty   |
| 1.3 | Program (prodi) data missing | mspst tidak ada data program     | ✓ Show '-' or cached value  |

### PDF GENERATION

| No  | Scenario                     | Action                            | Expected Result                  |
| --- | ---------------------------- | --------------------------------- | -------------------------------- |
| 2.1 | Generate PDF tipe 1          | Submit izin tipe 1                | ✓ PDF template 1 applied         |
| 2.2 | Generate PDF tipe 2          | Submit izin tipe 2                | ✓ PDF template 2 applied         |
| 2.3 | Generate PDF tipe 3 (single) | Submit izin tipe 3 jamaah_maghrib | ✓ PDF template 3_jamaah_maghrib  |
| 2.4 | Generate PDF tipe 3 (multi)  | Submit izin tipe 3 multi-kategori | ✓ PDF template 3_multi generated |
| 2.5 | PDF signature correctness    | Tipe 1/2 vs Tipe 3                | ✓ Signature line sesuai tipe     |
| 2.6 | PDF file not writable        | Upload folder permission 000      | ✗ Error handling + log           |

### FILE SYSTEM

| No  | Scenario                 | Action                  | Expected Result           |
| --- | ------------------------ | ----------------------- | ------------------------- |
| 3.1 | Upload folder exists     | /uploads/perizinan/     | ✓ Auto-created if missing |
| 3.2 | Upload folder permission | Permission 755          | ✓ Files writable          |
| 3.3 | File storage quota       | Upload many large files | ⚠ Monitor disk usage      |

---

## **MODULE 11: PERFORMANCE & LOAD**

| No  | Scenario                   | Action                              | Expected Result                  |
| --- | -------------------------- | ----------------------------------- | -------------------------------- |
| 1.1 | List 1000 santri           | Load admin/santri dengan 1000+ data | ✓ Pagination prevent slow load   |
| 1.2 | Export 1000 records        | Backup 1000 perizinan rows          | ✓ File generated (check timeout) |
| 1.3 | Multiple concurrent users  | 10+ simultaneous checkins           | ✓ DB lock/queue handling         |
| 1.4 | Presensi_kartu calculation | Recalc 500 santri cards             | ⚠ Performance? check time        |

---

## **MODULE 12: BUSINESS LOGIC VALIDATION**

| No  | Scenario                              | Action                                          | Expected Result                                  |
| --- | ------------------------------------- | ----------------------------------------------- | ------------------------------------------------ |
| 1.1 | Izin approved → presensi auto-created | Approve izin 5-10 May                           | ✓ ijindetail rows created + presensi marked izin |
| 1.2 | Last approved izin status check       | Query latest izin status                        | ✓ Correct status reflected                       |
| 1.3 | Card persistence across weeks         | Week 1: putih → Week 2: kuning                  | ✓ History maintained, new card assigned          |
| 1.4 | Semester consistency                  | Izin smt=1 vs presensi smt mismatch             | ⚠ Verify logic                                   |
| 1.5 | Reason normalization                  | Submit multi-alasan → stored as comma-separated | ✓ Parse on retrieve correct                      |
| 1.6 | Sub-kategori multi-handling           | Tipe 3 multi-kategori → sub_kategori field      | ✓ Stored & parsed correctly                      |

---

## **QUICK TEST CHECKLIST (PRODUCTION READINESS)**  

### CRITICAL (Must pass)

- [ ] Login: Santri & Admin work
- [ ] Submit Perizinan: Tipe 1, 2, 3 all workflow complete
- [ ] Admin Approve: Izin status update + presensi auto-create
- [ ] Self Check-in: Santri checkin recorded within jadwal window
- [ ] Card Auto-calc: Alpha counted, card color assigned correctly
- [ ] Download PDF: Letter generated with correct template & signature

### HIGH (Should pass)

- [ ] Filter & Search: All filters work on admin pages
- [ ] Export: Data export (CSV, Excel) working
- [ ] Manual Entry: Admin can manual add/edit presensi
- [ ] Role-based Access: Santri cannot access admin area
- [ ] Profile: All 6 dashboard cards display data

### MEDIUM (Nice to have)

- [ ] Concurrent checkins: Multiple simultaneous requests handled
- [ ] File upload: Dokumen storage & retrieval OK
- [ ] Data validation: Edge cases caught gracefully
- [ ] Performance: 1000+ data list & export acceptable time

### LOW (Enhancement)

- [ ] UI/UX: Mobile responsive layout
- [ ] Error messages: Localization (Indonesian/English)
- [ ] Audit logging: Admin actions tracked

---

**End of Testing Schema**

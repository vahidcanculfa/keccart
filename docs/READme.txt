PROJE ADI: KECCART E-TICARET SISTEMI
MIMARI: Klasör Tabanlı Modüler Yapı (Separation of Concerns)

ACIKLAMALAR:
1. Root (Ana Dizin): Sadece index.php ve sistem yapılandırma dosyalarını içerir.
2. Modüller: Her fonksiyonel özellik (Alışveriş, Hesap, Yetkilendirme) kendi klasöründedir.
3. Güvenlik: Veritabanı bilgileri /config klasörü altında izole edilmiştir.
4. Tasarım: Tüm stil dosyaları /assets klasöründen merkezi olarak yönetilir.


========================================================================
KECCART E-TICARET SISTEMI - DOSYA VE KLASOR YAPISI (README)
========================================================================

KLASOR/DOSYA      GOREVI
--------------    ------------------------------------------------------
account/          Kullanici profili, siparislerim ve hesap yonetimi.
admin/            Site sahibi icin yonetim paneli ve kontrol merkezi.
assets/           CSS, JS, Fontlar ve Logo gibi gorsel kaynaklar.
auth/             Giris yap, Kayit ol ve Cikis yap sayfalari.
config/           Veritabani baglanti ayarlari (db.php).
core/             Sepet yardimcisi gibi sistemin cekirdek fonksiyonlari.
includes/         Header ve Footer gibi ortak kullanılan parcalar.
orders/           Siparis detaylari ve onay sayfalari.
shop/             Urun detay, sepet ve arama sonuclari sayfalari.
uploads/          Sisteme yuklenen urun gorsellerinin deposu.
.htaccess         URL yonlendirmeleri ve sunucu guvenlik kurallari.
index.php         Sitenin ana giris kapisi ve vitrin sayfası.
README.txt        Su an okudugunuz sistem bilgilendirme dosyasi.

------------------------------------------------------------------------
NOTLAR:
- index.php her zaman ana dizinde (root) kalmalidir.
- Klasor icindeki dosyalarda ust dizine erismek icin '../' kullanilir.
- Tum tasarim degisiklikleri assets/css/style.css uzerinden yapilir.
========================================================================
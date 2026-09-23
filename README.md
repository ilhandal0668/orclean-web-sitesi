# ORCLEAN — WordPress / WooCommerce Sitesi

Bu depo, mevcut WordPress + WooCommerce sitesinin **ORKA → ORCLEAN marka
dönüşümü** uygulanmış tam kopyasını içerir. Marka değişikliği dışında sitenin
yapısı, temaları, eklentileri ve içerik kurgusu değiştirilmemiştir.

## Depo içeriği

| Yol | Açıklama |
| --- | --- |
| `site/` | WordPress kök dizini (tema, eklentiler, yüklenen dosyalar) |
| `db/u2319598_tlcq1.sql` | Marka dönüşümü uygulanmış veritabanı dökümü (61 tablo) |
| `dotfiles/` | Kök `.htaccess` dosyaları (arşivlerde kaybolduğu için ayrı tutulur) |
| `local-disabled/` | Lokal çalışma için devre dışı bırakılan sunucu dosyaları |
| `orclean logo.pdf` | Marka logosunun kaynak dosyası (4 varyant) |
| `LOKAL-DEGISIKLIKLER.md` | Lokal ortama özel, sunucuya gitmemesi gereken değişiklikler |

## Depoda **olmayan** dosyalar

Aşağıdakiler bilinçli olarak dışarıda bırakıldı (`.gitignore`):

- `site/wp-config.php`, `wp-config.production.php` — veritabanı parolası ve
  WordPress güvenlik anahtarları içerir.
- `ftp.conf` — FTP kimlik bilgisi içerir.
- `orkamak-yedek.zip` (599 MB) — GitHub'ın 100 MB dosya sınırını aşıyor.
- Loglar, önbellek ve geçici yükseltme dizinleri.

Kurulum için `site/wp-config-sample.php` dosyasını `wp-config.php` olarak
kopyalayıp veritabanı bilgilerini ve güvenlik anahtarlarını kendiniz girin.

## Kişisel verilerin anonimleştirilmesi

Bu depo herkese açık olduğu için veritabanı dökümündeki kişisel veriler
temizlendi:

- Yönetici hesabı `admin` / `admin@orclean.com` olarak değiştirildi; **parola
  özeti kaldırıldı**, bu hesapla giriş yapılamaz. Kurulumda yeni bir yönetici
  parolası oluşturmanız gerekir.
- Yönetici e-posta ayarları (`admin_email`, WooCommerce bildirim adresleri) ve
  bekleyen e-posta değişikliği kaydı temizlendi.
- 42 yorumun yazar e-postası, IP adresi ve site bağlantısı anonimleştirildi.
- Açık oturum anahtarları (`session_tokens`) ve WooCommerce oturum kayıtları
  silindi.

Sitede görünen kurumsal iletişim adresleri (`info@orclean.com` vb.) zaten
herkese açık olduğu için korundu.

## Marka dönüşümü kapsamı

Dönüşüm büyük/küçük harf düzeni korunarak uygulandı
(`ORKA→ORCLEAN`, `Orka→Orclean`, `orka→orclean`; `ORKAMAK→ORCLEAN`):

- **Veritabanı** — 1.800 değişiklik. Ürün başlıkları, ürün açıklamaları, sayfa
  ve blog metinleri, menüler, Elementor içerikleri, kategori/etiket adları,
  URL'ler (`orkamak.com → orclean.com`) ve e-posta adresleri.
  Değişiklik WP-CLI `search-replace` ile yapıldığı için PHP serileştirilmiş
  veriler (782 alan) ve Elementor JSON kayıtları (259 kayıt) bozulmadan kaldı.
- **Dosya adları** — 160 dosya yeniden adlandırıldı
  (`ORKA-30-7.webp → ORCLEAN-30-7.webp` vb.); veritabanındaki karşılıkları
  birebir güncellendi.
- **Metin veri dosyaları** — `llms.txt`, WooCommerce ürün besleme XML'i ve
  WebToffee dışa aktarım CSV'leri.
- **Logo** — `site/wp-content/uploads/2025/06/Orclean-Logo.svg`,
  `orclean logo.pdf` içindeki yatay varyanttan vektör olarak üretildi.

## Bilinen eksik: görsellere gömülü eski logo

Aşağıdaki görsellerde ORKA logosu ve marka metni **resmin içine gömülüdür**;
bunlar metin olmadığı için otomatik dönüşüm kapsamında değildir ve grafik
düzenleme gerektirir. Hepsi sitede aktif olarak kullanılmaktadır:

| Dosya (`site/wp-content/uploads/2025/06/`) | İçerik | Kullanım |
| --- | --- | --- |
| `Banner-05-01.jpg` | Bordo zemin üzerinde ORKA logosu | 32 |
| `asasa.jpg` | "30. YIL ORKA" görseli | 86 |
| `Basliksiz-2-01.jpg` | "30. YIL ORKA" görseli | 22 |
| `Basliksiz-1-01.jpg` | "30. YIL ORKA" görseli | 2 |
| `CM43-.jpg` | ORKA logosu + ürün tanıtım metni | 29 |
| `picolo-02.jpg` | ORKA logosu + ürün tanıtım metni | 2 |
| `2.jpg` | "ORKA M30B…" teknik tanıtım kartı | 109 |
| `3.jpg` | "ORKA K70T…" teknik tanıtım kartı | 133 |
| `diger-01.jpg` | Ürün gövdesinde ORKA yazısı (fotoğraf) | 40 |

## Kurulum

```bash
# 1. Veritabanını oluşturup dökümü yükleyin
mysql -u KULLANICI -p -e "CREATE DATABASE orclean DEFAULT CHARACTER SET utf8mb4;"
mysql -u KULLANICI -p --default-character-set=utf8mb4 orclean < db/u2319598_tlcq1.sql

# 2. wp-config.php dosyasını oluşturun
cp site/wp-config-sample.php site/wp-config.php
#    DB bilgilerini ve https://api.wordpress.org/secret-key/1.1/salt/
#    adresinden alacağınız güvenlik anahtarlarını girin.

# 3. Site adresini ortamınıza göre ayarlayın
wp --path=site option update home    'https://ALAN-ADINIZ'
wp --path=site option update siteurl 'https://ALAN-ADINIZ'
```

Veritabanındaki içerik adresleri `orclean.com` alan adına işaret eder. Farklı
bir alan adı kullanacaksanız görsellerin açılması için dönüşümü tekrarlayın:

```bash
wp --path=site search-replace 'orclean.com' 'ALAN-ADINIZ' --all-tables --precise
```

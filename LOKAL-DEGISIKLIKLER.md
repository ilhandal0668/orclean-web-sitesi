# Lokal-Ozel Degisiklikler — SUNUCUYA YUKLENMEYECEKLER

Bu dosya, lokal calisma icin yapilan ve **Natro'ya geri yuklenmemesi gereken**
degisikliklerin kaydidir. Deploy oncesi mutlaka kontrol et.

## 1. wp-config.php — ASLA YUKLEME
`site/wp-config.php` lokal veritabanina isaret ediyor:
- `DB_NAME` = orkamak_local, `DB_USER` = root, sifre bos, `DB_HOST` = 127.0.0.1
- `WP_HOME` / `WP_SITEURL` = http://localhost:8080
- `WP_DEBUG` = true, `DISABLE_WP_CRON` = true

Sunucudaki orijinal hali: `D:\orkamak-site\wp-config.production.php`
**Bu dosya yuklenirse canli site lokal veritabanini arar ve tamamen coker.**

## 2. Tasinan dosyalar — `D:\orkamak-site\local-disabled\`
Silinmediler, sadece lokalde devre disi:
- `mu-plugins/hostinger-auto-updates.php`
- `mu-plugins/hostinger-preview-domain.php`
- `object-cache.php` (LiteSpeed obje onbellegi; lokalde Memcached yok)

Sunucuda bunlar duruyor. Geri yuklerken bu yollara DOKUNMA
(yoksa sunucudaki dosyalar silinmis olur).

## 3. Lokal veritabanindaki degisiklikler — sunucu DB'sine gitmez
- URL donusumu: orkamak.com -> localhost:8080 (489 degisiklik)
- Hostinger eklentileri pasife alindi (hostinger, hostinger-ai-assistant,
  hostinger-easy-onboarding) — Hostinger altyapisi olmadigi icin olumcul hata veriyorlardi
- `lokal` adinda yonetici kullanici eklendi

## 4. Degistirilmeyenler (dogru davranis)
- `info@orkamak.com`, `wordpress@orkamak.com` e-posta adresleri
- `http://www.b2b.orkamak.com/` bayi giris baglantilari (ayri site)

## Deploy dislama listesi
```
wp-config.php
wp-content/debug.log
wp-content/mu-plugins/hostinger-auto-updates.php
wp-content/mu-plugins/hostinger-preview-domain.php
wp-content/object-cache.php
wp-content/ai1wm-backups/
wp-content/upgrade/
wp-content/upgrade-temp-backup/
wp-content/litespeed/
error_log
```

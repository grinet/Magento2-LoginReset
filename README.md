# Grinet Login Reset

This Magento 2 module provides a **Login Reset** feature for customers who experience login/session problems. It preserves the current cart before clearing the session, then restores the cart (including the coupon) after the customer logs in again.

---

## Features

- Adds a **"Reset Session"** link on the customer login page.
- Saves the current cart to a database table before the session is cleared.
- Stores only a short **token** in the browser cookie (avoids the 4 KB cookie size limit).
- Restores cart items and reapplies the coupon after the customer logs back in.
- Keeps the saved cart record in the database for logging/history.
- Includes admin configuration for enabling/disabling the module and optional file logging.
- Supports English (default), Turkish, German and French translations.

---

## Flow

1. Customer clicks **"Reset Session"** on the login page.
2. The module saves the current quote data (`items`, `coupon`, `grand_total`, `remote_ip`) to the `grinet_login_reset` table with a unique token.
3. Only the token is written to the `cart_before_reset` cookie.
4. The customer and checkout sessions are destroyed.
5. The customer is redirected to `/login_reset/restore_cart`.
6. The restore controller reads the token, loads the saved cart from the database, adds the products back and reapplies the coupon.
7. The cookie is deleted; the database record is retained.

---

## Configuration

Navigate to **Stores > Configuration > Grinet Modülleri > Login Reset**.

### General Settings

| Field | Description | Default |
|-------|-------------|---------|
| Is Active | Enables/disables the module on the storefront. | Yes |

### Log Settings

| Field | Description | Default |
|-------|-------------|---------|
| Log To File | Writes an info entry to `var/log/system.log` when a cart is saved. | Yes |

> **Note:** Database logging is always enabled because the table is used as temporary cart storage.

---

## Requirements

- Magento 2.4.x
- PHP 8.1 or higher (compatible with Magento 2.4.x)

## Installation

### Option 1: Composer

```bash
composer require grinet/module-login-reset
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Option 2: Manual file copy

Copy the module files into `app/code/Grinet/LoginReset`, then run the standard Magento setup commands:

```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

---

## Notes

- If the module is disabled, the **"Reset Session"** link is not rendered and the reset controller redirects directly to the login page.
- The database record is intentionally kept after restore for logging purposes.
- Large carts are fully supported because the cart payload is stored in the database, not in the cookie.

---

---

# Grinet Login Reset (Türkçe)

Bu Magento 2 modülü, giriş/oturum sorunu yaşayan müşteriler için **Oturum Sıfırlama** özelliği sağlar. Oturum temizlenmeden önce mevcut sepeti korur, müşteri tekrar giriş yaptıktan sonra sepeti (kupon dahil) geri yükler.

---

## Özellikler

- Müşteri giriş sayfasına **"Oturum Sıfırla"** bağlantısı ekler.
- Oturum temizlenmeden önce mevcut sepeti veritabanı tablosuna kaydeder.
- Tarayıcı cookie'sine yalnızca kısa bir **token** yazar (4 KB cookie limitini aşmaz).
- Müşteri tekrar giriş yaptığında sepet öğelerini geri yükler ve kuponu yeniden uygular.
- Kaydedilen sepet kaydını loglama/geçmiş için veritabanında tutar.
- Modülü açıp/kapatma ve isteğe bağlı dosya loglama için yönetici ayarları içerir.
- İngilizce (varsayılan), Türkçe, Almanca ve Fransızca çevirileri destekler.

---

## Akış

1. Müşteri giriş sayfasındaki **"Oturum Sıfırla"** bağlantısına tıklar.
2. Modül, mevcut sepet verisini (`ürünler`, `kupon`, `toplam`, `IP`) benzersiz bir token ile `grinet_login_reset` tablosuna kaydeder.
3. Yalnızca token, `cart_before_reset` cookie'sine yazılır.
4. Müşteri ve ödeme oturumları sonlandırılır.
5. Müşteri `/login_reset/restore_cart` adresine yönlendirilir.
6. Geri yükleme controller'ı token'ı okur, veritabanından kaydedilen sepeti yükler, ürünleri sepete ekler ve kuponu uygular.
7. Cookie silinir; veritabanı kaydı saklanır.

---

## Yapılandırma

**Mağazalar > Yapılandırma > Grinet Modülleri > Login Reset** menüsünden erişilir.

### Genel Ayarlar

| Alan | Açıklama | Varsayılan |
|------|----------|------------|
| Is Active | Modülü vitrinde açar/kapatır. | Yes |

### Log Ayarları

| Alan | Açıklama | Varsayılan |
|------|----------|------------|
| Log To File | Sepet kaydedildiğinde `var/log/system.log` dosyasına info kaydı yazar. | Yes |

> **Not:** Veritabanı loglaması her zaman açıktır çünkü tablo geçici sepet deposu olarak kullanılır.

---

## Gereksinimler

- Magento 2.4.x
- PHP 8.1 veya üzeri (Magento 2.4.x ile uyumlu)

## Kurulum

### Seçenek 1: Composer

```bash
composer require grinet/module-login-reset
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Seçenek 2: Manuel dosya kopyalama

Modül dosyalarını `app/code/Grinet/LoginReset` dizinine kopyalayın, ardından standart Magento kurulum komutlarını çalıştırın:

```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

---

## Notlar

- Modül kapalıysa **"Oturum Sıfırla"** bağlantısı gösterilmez ve reset controller doğrudan giriş sayfasına yönlendirir.
- Geri yükleme sonrası veritabanı kaydı loglama amacıyla bilerek saklanır.
- Sepet verisi veritabanında tutulduğu için büyük sepetler de tam olarak desteklenir.

## PHP compatibility

PHP 8.5 syntax and PHPCompatibility static checks were run on this release. Existing minimum PHP requirements are retained. Use a Magento release that supports your PHP runtime; syntax/static checks alone do not verify installation, DI compilation or storefront/admin behavior.

The Composer `mage-os` and `mageos` keywords indicate expected compatibility based on Mage-OS retaining Magento namespaces and replacing the corresponding `magento/*` packages. The existing Magento requirements are preserved. No full Mage-OS integration test was performed. See https://mage-os.org/faq/ and https://repo.mage-os.org/packages.json.

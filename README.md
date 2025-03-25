
# 🖼️ Image Zip Converter (WordPress Plugin)

Convert uploaded images to optimized **JPG or PNG** format and download them as a single ZIP file — directly inside WordPress.  
Temporary files are deleted instantly after download. Nothing is stored on the server.


---

## 🚀 Features

- ✅ Supports multiple image formats: JPG, JPEG, PNG, WEBP, BMP, TIFF
- ✅ Converts images with transparency to **PNG**
- ✅ Converts all other images to **JPG** with a white background
- ✅ 100% image quality (no compression)
- ✅ Simple upload form with classic file input
- ✅ Converts and returns a ZIP file instantly
- ✅ **No images are stored on the server**
- ✅ Lightweight and fast — no external libraries or bloated UI
- ✅ Easy integration via shortcode

---

## 🔧 Installation

1. Upload the plugin folder to your `/wp-content/plugins/` directory  
2. Activate the plugin from your WordPress admin panel  
3. Use the shortcode `[image_zip_converter]` on any page or post

---

## 🛠️ Usage

- Go to any page or post  
- Add the shortcode:

```text
[image_zip_converter]
```

- Upload your images  
- Click “Dönüştür ve ZIP Olarak İndir”  
- Your converted images will be downloaded as a ZIP instantly  
- Nothing is stored on your server — everything is cleared immediately ✅

---

## 📁 Plugin Structure

```
image-zip-converter/
├── includes/
│   └── converter.php      → Image processing and ZIP logic
└── image-zip-converter.php → Main plugin file and shortcode handler
```

---

## 🧼 Clean & Safe

This plugin ensures **zero storage** on your server.  
All uploaded and converted files are immediately deleted after the ZIP is served to the user.

---

## 💼 Use Cases

- WooCommerce product image prep
- Unifying image formats for SEO or theme compatibility
- Client-side image cleaning before upload
- Quick conversions of mixed image types to web-friendly formats

---

## 📄 License

MIT — Free for personal and commercial use  
Built with 💙 by [SeoMEW](https://seomew.com.tr)

# PosterWall — Apna Design. Apna Brand. Apni Pehchaan.

PosterWall is a digital identity platform built for small businesses, shops, dhabas, clinics, and professionals. It allows users to capture a photo of their physical storefront or business card and automatically generates a stunning, personalized, mobile-friendly digital web page complete with a QR code and business details.

## Features

- **Photo to Page Conversion (AI-Powered)**: Uses Vision AI to extract information from storefronts/business cards and constructs optimized HTML pages.
- **Digital Storefront Pages**: Clean, responsive page layouts for mobile and web representation.
- **Menu/Catalog Management**: Enable merchants to showcase items, services, or dishes.
- **Order Processing**: Let customers place orders directly from the merchant's digital page with optional payment integration.
- **Razorpay Wallet Integration**: Pre-pay wallet balance model (e.g. ₹9 per page generation) for merchants.
- **Google OAuth Login**: Swift login and registration.
- **Dark/Light Themes**: Dynamic responsiveness to theme choices.

## Tech Stack

- **Backend**: PHP (v8.2+) & MySQL
- **Frontend**: Pure HTML, Vanilla CSS, JavaScript
- **API Integrations**: Google OAuth, OpenRouter (for Gemini / Llama models), Groq, Razorpay

## Quick Start

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/Anmol-Bhatnagar/PosterWall.git
   cd PosterWall
   ```

2. **Database Setup**:
   Import `db/setup.sql` to your MySQL instance:
   ```bash
   mysql -u root -p < db/setup.sql
   ```

3. **Configure Environment Variables**:
   Copy `.env.example` to `.env` and adjust the variables (database, API keys, credentials):
   ```bash
   cp .env.example .env
   ```

4. **Web Server Setup**:
   Host the workspace root via Apache or Nginx. Enable `mod_rewrite` to respect `.htaccess` rules.

## Security Controls

- **Hardened Sessions**: Session cookies are configured with `httponly`, `secure` (on HTTPS connections), and `samesite = Lax` options to protect user sessions.
- **Anti-CSRF Tokens**: Form actions validation using cryptographic anti-CSRF token verification.
- **CLI/Local Protected Scripts**: Critical maintenance scripts (like `reset-admin.php`) are locked to localhost and CLI access.

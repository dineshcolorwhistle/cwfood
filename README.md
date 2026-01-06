# CW Food

**CW Food** is a comprehensive food manufacturing management platform designed for food producers, manufacturers, and CPG (Consumer Packaged Goods) companies. It streamlines product development, costing, compliance, and operations management with powerful AI-driven features.

---

## 🚀 Features

### Product Management
- Complete product lifecycle management with SKU, barcodes (GS1, GTIN14), and categorization
- Multi-step product creation workflow (details, recipe, specifications, labelling, costing)
- Product duplication and archiving
- Grid and list views with advanced search and filtering
- Image library management

### Recipe & Formulation
- Recipe builder with ingredient management
- Oven/baking instructions with temperature and time tracking
- Batch size calculations with loss percentages (baking loss, waste)
- Recipe method and notes documentation

### Raw Materials / Ingredients
- Comprehensive ingredient database with nutritional information
- Supplier management and pricing
- Allergen tracking and declarations
- Country of origin tracking
- AI-predicted allergen detection

### Nutritional Information
- Automatic nutrition calculation per 100g and per serving
- Energy (kJ), protein, fats, carbohydrates, sugars, sodium tracking
- **FSANZ (Food Standards Australia New Zealand)** database integration
- Nutritional panel generation for labelling

### Product Costing
- Multi-component cost tracking:
  - Raw material costs
  - Labour costs
  - Machinery/equipment costs
  - Packaging costs
  - Freight costs
- Cost per kg calculations
- Contingency percentage management
- Price analysis with wholesale, distributor, and RRP margins
- Unit economics analytics

### Labelling & Compliance
- Automated ingredient list generation
- Allergen statement management
- "May contain" declarations
- Country of Origin Labelling (COOL) compliance
- Australian regulatory compliance support

### AI-Powered Specifications (CW Food Agent)
- Upload supplier specifications (PDF/documents)
- AI extraction of nutritional data, ingredients, allergens
- Specification auditing and comparison
- FSANZ data integration for specification creation

### Business Operations
- **Companies & Contacts CRM**: Manage suppliers, customers, and key personnel
- **Workspaces**: Multi-workspace organization for different business units
- **Support Tickets**: Internal ticketing system with comments and status tracking

### Integrations
- **Xero Accounting**: Sales performance analytics, invoice sync, credit notes
- **Stripe**: Subscription billing and payment management
- **AWS Cognito**: Secure authentication with OAuth 2.0
- **AWS S3**: File and image storage

### Analytics & Reporting
- Dashboard with key metrics
- Unit economic analysis
- Sales performance (via Xero integration)
- Customer analytics
- Export to CSV/Excel/PDF

### Multi-Tenancy & Access Control
- Client-based multi-tenancy
- Role-based permissions (Platform Admin, Client Admin, Members)
- Workspace-level access control
- Member invitation and management

---

## 🛠️ Tech Stack

| Category | Technology |
|----------|------------|
| **Backend** | PHP 8.2+, Laravel 11 |
| **Database** | MySQL |
| **Frontend** | Blade Templates, Vite, JavaScript |
| **Authentication** | AWS Cognito, Laravel Socialite |
| **Payments** | Stripe |
| **Accounting** | Xero API |
| **AI/ML** | OpenAI API |
| **File Storage** | AWS S3 |
| **PDF Generation** | DomPDF |
| **Excel/CSV** | Maatwebsite Excel, League CSV |
| **Testing** | PHPUnit |

---

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js & npm
- MySQL 8.0+
- AWS Account (for Cognito, S3)
- Stripe Account (for billing)
- Xero Developer Account (optional, for accounting integration)
- OpenAI API Key (for AI features)

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/CW Food.git
cd CW Food
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Configure the following in your `.env` file:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=CW Food
DB_USERNAME=your_username
DB_PASSWORD=your_password

# AWS Cognito
AWS_COGNITO_CLIENT_ID=your_cognito_client_id
AWS_COGNITO_CLIENT_SECRET=your_cognito_client_secret
AWS_COGNITO_REGION=your_region
AWS_COGNITO_USER_POOL_ID=your_user_pool_id

# AWS S3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=your_region
AWS_BUCKET=your_bucket_name

# Stripe
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key

# Xero (Optional)
XERO_CLIENT_ID=your_xero_client_id
XERO_CLIENT_SECRET=your_xero_client_secret

# OpenAI
OPENAI_API_KEY=your_openai_api_key
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Seed Database (Optional)

```bash
php artisan db:seed
```

### 7. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 8. Start the Application

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## 🚀 Production Deployment

For production deployment with PM2:

```bash
# Build assets
npm run build

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart PM2 process
pm2 restart CW Food
```

---

## 📁 Project Structure

```
CW Food/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Exports/              # Excel/CSV export classes
│   ├── Http/
│   │   ├── Controllers/      # Application controllers
│   │   └── Middleware/       # Custom middleware
│   ├── Imports/              # Data import classes
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   └── View/Components/      # Blade components
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── views/                # Blade templates
├── routes/
│   ├── web.php               # Web routes
│   └── console.php           # Console routes
├── storage/                  # Application storage
└── tests/                    # Test files
```

---

## 🔑 Key Modules

| Module | Description |
|--------|-------------|
| Products | Full product management with recipes, costing, and labelling |
| Raw Materials | Ingredient database with nutritional and allergen data |
| FSANZ | Australian food standards database integration |
| Specifications | AI-powered supplier spec extraction and management |
| Labour | Labour cost tracking and management |
| Machinery | Equipment cost tracking |
| Packaging | Packaging cost management with SKU generation |
| Freight | Freight and logistics cost tracking |
| Companies | Supplier and customer company management |
| Contacts | Contact management with categories and tags |
| Analytics | Dashboard, unit economics, and sales performance |
| Support | Internal ticketing and support system |

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 🤝 Support

For support inquiries, please use the in-app support ticket system or contact your account manager.

---

**Built with ❤️ for the food manufacturing industry**


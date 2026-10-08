# Esprit_5twin5_ErrorList

## Project Overview
This is a Laravel-based platform integrating several functional modules developed collaboratively by the team. It is structured as a university group project that aims to provide a centralized service hub handling user authentication, equipment management, support tickets, and external technical service providers.

## Technologies
- **Laravel 12**
- **PHP**
- **Blade** (Template Engine)
- **Tailwind CSS**
- **SQLite** (Database)
- **Eloquent ORM**
- **Git/GitHub**

## Main Modules
The platform is composed of several seamlessly integrated functional modules:
- **Authentication**: Secure login, registration, and user session management via Laravel Breeze.
- **Reclamation / Support**: A client-facing ticketing system for inquiries and helpdesk support.
- **Equipment Management**: Module to manage and track user or business equipment.
- **Payment / Transaction**: Handling user payments, transaction history, and invoicing.
- **Reservation / Rentals**: Module for reserving or renting platform resources.
- **Technical Services**: A comprehensive module to manage independent service providers and handle client service requests.

---

## Technical Services Module
The Technical Services module connects customers with skilled independent providers. 

### Service Providers
- **Public Directory**: Browse available service providers.
- **Profile Management**: Users can create and edit their service provider profiles.
- **Details**: Configure specific specialties, location, and toggle availability statuses.
- **Moderation**: Admin panel allows for the approval or rejection of new service provider profiles.

### Service Requests
**Customer Capabilities:**
- Create a service request targeting a specific approved provider.
- Optionally link the request to one of their own existing equipment items.
- Provide a clear title, description, requested date, and address.
- View request history and track request details/status.
- Cancel a service request (allowed only while the status is pending).

**Provider Capabilities:**
- View a dedicated dashboard listing all received requests.
- Accept a pending request and specify an estimated price in TND.
- Reject a pending request.
- Start an accepted request to indicate work has begun.
- Complete an in-progress request once the intervention is finished.

**Request Workflow:**
1. `pending` &rarr; `accepted` &rarr; `in_progress` &rarr; `completed`
2. Alternative path: `pending` &rarr; `rejected`
3. Customer cancellation is allowed while the request is in the `pending` state.

### Admin
The administration panel provides tools to oversee the Technical Services ecosystem:
- List all service providers.
- Filter providers by status or criteria.
- View in-depth provider details.
- Approve or reject pending service provider profiles to maintain platform quality.

---

## Main Models and Relationships
- **User** `hasOne` **ServiceProvider**
- **User** `hasMany` **ServiceRequests**
- **ServiceProvider** `belongsTo` **User**
- **ServiceProvider** `hasMany` **ServiceRequests**
- **ServiceRequest** `belongsTo` **User**
- **ServiceRequest** `belongsTo` **ServiceProvider**
- **ServiceRequest** `belongsTo` **Equipment**
- **Equipment** `hasMany` **ServiceRequests**

## Database
The Technical Services module includes structured data management logic. Migrations, Factories, and Seeders are provided for:
- `ServiceProvider`
- `ServiceRequest`

*(Running the seeders will automatically generate realistic dummy data for testing the Technical Services workflow).*

---

## Installation

Follow these safe project setup steps to run the application locally:

```bash
# 1. Clone the repository
git clone <repository-url>
cd Esprit_5twin5_ErrorList

# 2. Install PHP dependencies
composer install

# 3. Setup environment variables
cp .env.example .env
php artisan key:generate

# 4. Configure Database (Ensure .env DB_CONNECTION is set to sqlite)
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# 5. Install and build Frontend dependencies
npm install
npm run build

# 6. Start the local development server
php artisan serve
```
*Note: Make sure your `.env` file uses SQLite when configuring the database connection for this project.*

## Useful URLs
Once the server is running, you can access the following main areas of the application:
- `/` - Home page
- `/services` - Service Provider directory
- `/my-service-requests` - Customer's service requests dashboard
- `/provider/service-requests` - Service Provider's received requests dashboard
- `/support` - Support and reclamation module
- `/admin` - Administration panel

## Roles
- **Client**: Standard authenticated user capable of creating service requests, managing equipment, and opening support tickets.
- **Service Provider**: A profile linked to a standard user account granting access to receive and manage service interventions.
- **Admin**: Platform administrator capable of managing users, moderating providers, and overseeing platform transactions.

## Git Workflow
The project leverages a feature-branching git workflow. 
Feature branches are developed independently and merged into the primary integration branch: `authentification-breeze-and-reclamation`.
The Technical Services functionality was developed on the feature branch: `feature/technical-services`.

## Réalisé par

- Salma Briki
- Ouaiss Aeiechi Chaouch
- Marwen Jemai
- Med Khalil Wendy
- Med Amine Graja

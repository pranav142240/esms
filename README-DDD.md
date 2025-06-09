# Domain-Driven Design with Multi-tenancy in Laravel
## ESMS - Educational School Management System

**Updated: June 2025** - Reflecting latest multi-tenant architecture improvements

This document outlines how we implement Domain-Driven Design (DDD) principles in the ESMS multi-tenant Laravel application, with enhanced tenant isolation and subscription management.

## 🏗️ Latest Architecture Enhancements
- ✅ **Enhanced Multi-Tenancy**: Improved tenant database isolation
- ✅ **Domain Separation**: Clear bounded contexts for different business areas
- ✅ **Subscription Management**: Robust billing and plan management domain
- ✅ **XAMPP Compatibility**: Production-ready deployment configurations
- ✅ **Comprehensive Seeding**: Domain-specific test data generation

## Directory Structure

Following DDD principles, here's our implemented directory structure for the ESMS application:

```
app/
├── Domain/                       # Core domain logic organized by bounded contexts
│   ├── Superadmin/              # Superadmin bounded context (implemented)
│   │   ├── Models/              # Domain models for superadmin
│   │   ├── Services/            # Business logic services
│   │   ├── Repositories/        # Data access contracts
│   │   └── Events/              # Domain events
│   ├── User/                    # User management bounded context (implemented)
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Repositories/
│   ├── Billing/                 # Subscription and billing context (implemented)
│   │   ├── Models/              # SubscriptionPlan, School billing
│   │   ├── Services/            # Payment processing, plan management
│   │   └── Repositories/
│   ├── School/                  # School management context (implemented)
│   │   ├── Models/              # School, SchoolInquiry
│   │   ├── Services/            # School creation, tenant setup
│   │   └── Repositories/
│   └── Shared/                  # Shared domain logic between contexts
│       ├── ValueObjects/        # Common value objects
│       └── Events/              # Shared domain events
├── Application/                 # Application services (implemented)
│   └── Services/                # Application-level orchestration
├── Infrastructure/              # Infrastructure concerns (implemented)
│   └── Persistence/             # Database-related implementations
│       └── Eloquent/            # Eloquent models and repositories
├── Interfaces/                  # User interfaces (partially implemented)
│   └── Api/                     # API controllers and resources
│       ├── Controllers/         # RESTful API controllers
│       └── Resources/           # API response transformers
```

## Current Implementation Status

### ✅ Implemented Bounded Contexts

Identify the core subdomains and bounded contexts of your application and organize your code accordingly. Each bounded context should have its own set of models, services, repositories, and events.

### 2. Implement Domain Models

Create rich domain models that encapsulate business rules and behavior:

```php
namespace App\Domain\Billing\Models;

class Invoice
{
    private $id;
    private $customerId;
    private $items = [];
    private $status;
    private $total;

    public function addItem(InvoiceItem $item)
    {
        $this->items[] = $item;
        $this->recalculateTotal();
    }

    public function finalize()
    {
        if (empty($this->items)) {
            throw new DomainException("Cannot finalize an empty invoice");
        }
        
        $this->status = 'finalized';
    }

    private function recalculateTotal()
    {
        $this->total = array_reduce($this->items, function($carry, $item) {
            return $carry + $item->getTotal();
        }, 0);
    }
}
```

### 3. Set up Value Objects for Immutable Concepts

```php
namespace App\Domain\Shared\ValueObjects;

class Money
{
    private $amount;
    private $currency;
    
    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }
    
    public function add(Money $money): Money
    {
        if ($this->currency !== $money->currency) {
            throw new DomainException("Cannot add money with different currencies");
        }
        
        return new Money($this->amount + $money->amount, $this->currency);
    }
    
    public function getAmount(): float
    {
        return $this->amount;
    }
    
    public function getCurrency(): string
    {
        return $this->currency;
    }
}
```

### 4. Implement Domain Events

```php
namespace App\Domain\Billing\Events;

class InvoicePaid
{
    public $invoiceId;
    public $customerId;
    public $amount;
    public $paidAt;
    
    public function __construct(string $invoiceId, string $customerId, float $amount)
    {
        $this->invoiceId = $invoiceId;
        $this->customerId = $customerId;
        $this->amount = $amount;
        $this->paidAt = now();
    }
}
```

### 5. Create Application Services

```php
namespace App\Application\Services;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Repositories\InvoiceRepository;
use App\Domain\Billing\Events\InvoicePaid;
use App\Infrastructure\EventDispatcher;

class PaymentService
{
    private $invoiceRepository;
    private $eventDispatcher;
    
    public function __construct(
        InvoiceRepository $invoiceRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    public function processPayment(string $invoiceId, float $amount)
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        
        if ($invoice->getStatus() !== 'pending') {
            throw new ApplicationException("Cannot pay an invoice that is not pending");
        }
        
        if ($amount < $invoice->getTotal()) {
            throw new ApplicationException("Payment amount is less than the invoice total");
        }
        
        $invoice->markAsPaid();
        $this->invoiceRepository->save($invoice);
        
        $this->eventDispatcher->dispatch(
            new InvoicePaid($invoice->getId(), $invoice->getCustomerId(), $amount)
        );
        
        return $invoice;
    }
}
```

### 6. Set up Multi-tenancy

Multi-tenancy integrates with DDD by ensuring that domain logic is executed in the correct tenant context:

1. Use middleware to determine the current tenant
2. Use tenant-aware repositories to scope data access
3. Ensure domain services have access to tenant context when needed

Example of a tenant-aware repository:

```php
namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Repositories\InvoiceRepository;
use App\Infrastructure\Tenancy\TenantManager;

class EloquentInvoiceRepository implements InvoiceRepository
{
    private $tenantManager;
    
    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }
    
    public function findById(string $id): Invoice
    {
        $model = InvoiceModel::where('id', $id)
            ->where('tenant_id', $this->tenantManager->getCurrentTenantId())
            ->firstOrFail();
            
        return $this->mapToEntity($model);
    }
    
    // Other repository methods...
}
```

## Tips for Successful DDD with Multi-tenancy

1. **Keep Domain Logic Tenant-Agnostic**: Your domain models should not need to know about tenancy - this is an infrastructure concern.

2. **Use Application Services for Orchestration**: Application services coordinate between the domain layer and infrastructure (including tenancy).

3. **Implement Tenant Middleware**: Use middleware to establish the tenant context early in the request lifecycle.

4. **Use Repository Pattern**: Repositories abstract data access and can enforce tenant isolation.

5. **Consider Aggregate Roots**: Define clear aggregate boundaries to maintain consistency within each tenant.

6. **Use Domain Events for Cross-Context Communication**: Domain events allow different bounded contexts to react to changes without tight coupling.

7. **Implement CQRS for Complex Domains**: Consider Command Query Responsibility Segregation for more complex domains, separating read and write operations.

8. **Test at the Domain Level**: Write tests that verify domain logic works correctly independent of tenancy.

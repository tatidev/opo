# Restock System Documentation

## Overview

The Restock system manages the ordering, tracking, and fulfillment of fabric samples and materials. It provides a complete workflow from initial sample requests through shipment tracking and automatic completion detection.

**Status:** ✅ **FULLY FUNCTIONAL** - All major issues resolved as of 2025-08-07

## 📋 Current Documentation (Active)

### **🎯 Start Here**
- **[CURRENT STATUS](CURRENT-STATUS.md)** - **System status, resolved issues, and current functionality**

### **🚀 Deployment & Maintenance**  
- **[Deployment Plan](deployment-plan-status-display-fix.md)** - Current deployment procedures
- **[Admin Tools](order-placement-fix-endpoint.md)** - Data integrity maintenance endpoints

### **📊 Production Compatibility**
- **[Production Fixes](production-compatibility-fixes.md)** - Environment differences and solutions

## 📁 Historical Documentation (Archived)

The following files contain historical analysis and planning information from when issues were being diagnosed. They are kept for reference but reflect **past problems that have been resolved:**

- `completion-tab-issues-analysis.md` - Original issue analysis (~~problems now fixed~~)
- `troubleshooting.md` - Historical troubleshooting (~~issues resolved~~)  
- `php73-*.md` files - PHP upgrade issues (~~compatibility achieved~~)
- `system-overview.md`, `api-endpoints.md`, etc. - Planning docs (~~implementation complete~~)

## 🏗️ Key Components

### Backend Files
| File | Purpose | Key Methods |
|------|---------|-------------|
| [`application/controllers/Restock.php`](../../application/controllers/Restock.php) | Main controller | `index()`, `add()`, `save()`, `get()` |
| `application/models/Restock_model.php` | Database operations | `get_restocks()`, `add_batch_on_order()` |
| [`application/views/restock/list.php`](../../application/views/restock/list.php) | Main interface | DataTables configuration, filters |

### Frontend Files
| File | Purpose | Key Functions |
|------|---------|---------------|
| [`assets/js/init_datatables.js`](../../assets/js/init_datatables.js#L651) | Restock button config | Add restock button action |
| [`assets/js/commons.js`](../../assets/js/commons.js) | Modal handling | `open_item_modal()`, table updates |

## 🚀 Quick Start for Developers

### Understanding the Flow
1. **Start with** [System Overview](system-overview.md) to understand the big picture
2. **Review** [User Workflows](user-workflows.md) to see how users interact with the system  
3. **Examine** [API Endpoints](api-endpoints.md) to understand the controller methods
4. **Check** [Database Schema](database-schema.md) for data relationships

### Key Code Locations
- **Order Creation Logic**: [`Restock.php:100-200`](../../application/controllers/Restock.php#L100)
- **Duplicate Detection**: [`Restock.php:107-156`](../../application/controllers/Restock.php#L107)
- **Status Management**: [`Restock.php:202-302`](../../application/controllers/Restock.php#L202)
- **Email Notifications**: [`Restock.php:304-376`](../../application/controllers/Restock.php#L304)
- **Main Interface**: [`list.php:138-189`](../../application/views/restock/list.php#L138)

## 🔍 Common Developer Tasks

### Adding New Features
- **New Status Types**: Modify status constants in [`Restock.php:6-10`](../../application/controllers/Restock.php#L6)
- **Email Recipients**: Update email logic in [`send_backorders_email()`](../../application/controllers/Restock.php#L304)
- **UI Filters**: Add to filter section in [`list.php:47-81`](../../application/views/restock/list.php#L47)

### Debugging Issues
- **After PHP Upgrade**: See [PHP 7.3 Compatibility Issues](php73-compatibility-issues.md) - Critical fixes needed
- **Completion/Tab Problems**: See [Completion Tab Issues Analysis](completion-tab-issues-analysis.md) - Orders not moving between tabs correctly
- **Order Not Creating**: Check [Troubleshooting Guide - Order Creation](troubleshooting.md#order-creation-issues)
- **Email Not Sending**: See [Email Notifications - Debugging](email-notifications.md#troubleshooting)
- **Status Not Updating**: Review [Status Management - Common Issues](status-management.md#troubleshooting)

## 📊 System Statistics

- **Controller Methods**: 8 public methods handling different aspects of restock management
- **Status Types**: 4 main status categories (pending, backorder, completed, cancelled)
- **Sample Sizes**: 3 standard sizes (6x6, 12x12, 18x18 inches)
- **Quantity Types**: 3 quantity types tracked (total, priority, ringsets)

## 🔗 Related Systems

The Restock system integrates with:
- **Item Management** - Links to fabric items and products
- **User System** - Tracks who created/modified orders
- **Specs System** - Uses destination and status specifications
- **Email System** - Automated notifications for backorders

## 📞 Support

For questions about this documentation or the Restock system:
- Review the [Troubleshooting Guide](troubleshooting.md)
- Check specific documentation sections listed above
- Examine the referenced code files for implementation details

---

*Last Updated: December 2024*  
*Documentation covers Restock system as implemented in `application/controllers/Restock.php`*
# Restock Order Placement Fix Endpoint

## Production Documentation

### Overview
Admin endpoint to fix misplaced orders between Pendings and Completed tabs based on actual completion status and pending quantities.

### Endpoint URLs
```
# Default (100 orders max)
https://your-production-domain.com/restock/fix_order_placement

# Custom batch sizes
https://your-production-domain.com/restock/fix_order_placement/50   (50 orders)
https://your-production-domain.com/restock/fix_order_placement/500  (500 orders)
https://your-production-domain.com/restock/fix_order_placement/1000 (1000 orders max)
```

### What It Fixes
**Moves to Completed Tab:**
- Orders with `COMPLETED` status (restock_status_id = 5)
- Orders with pending_total ≤ 0 (quantity fulfilled)

**Moves to Pendings Tab:** 
- Orders with pending_total > 0 and status ≠ COMPLETED/CANCELLED
- Orders that shouldn't be in Completed tab

### Batch Processing
- **Memory Protection**: Limits processed orders to prevent memory exhaustion
- **Safe Batches**: Start with 50-100 orders, scale up as needed
- **Multiple Runs**: Run repeatedly until no more orders need fixing
- **Transaction Safety**: All-or-nothing database operations

### Usage Instructions

#### Step 1: Start Small (Recommended)
```bash
curl -k https://your-domain.com/restock/fix_order_placement/50
```

#### Step 2: Scale Up If Needed
```bash
# If Step 1 found issues, run larger batches
curl -k https://your-domain.com/restock/fix_order_placement/500
```

#### Step 3: Repeat Until Clean
```bash
# Keep running until "total_fixed": 0
curl -k https://your-domain.com/restock/fix_order_placement/500
```

### Sample Response (No Issues Found)
```json
{
    "success": true,
    "message": "Fixed placement for 0 orders (batch size: 500)",
    "details": {
        "moved_to_completed": [],
        "moved_to_pending": [],
        "summary": {
            "to_completed_count": 0,
            "to_pending_count": 0,
            "total_fixed": 0,
            "limit_used": 500,
            "note": "Processing limited to 500 orders to prevent memory issues"
        }
    },
    "instructions": "Refresh the restock page to see the corrected order placement. Run again if more orders need fixing.",
    "timestamp": "2025-08-07 13:17:29"
}
```

### Sample Response (Issues Fixed)
```json
{
    "success": true,
    "message": "Fixed placement for 12 orders (batch size: 500)",
    "details": {
        "moved_to_completed": [
            {
                "order_id": "12345",
                "reason": "quantity_completed",
                "pending_samples": 0,
                "pending_ringsets": 0,
                "status": "4"
            }
        ],
        "moved_to_pending": [
            {
                "order_id": "67890", 
                "reason": "quantity_incomplete",
                "pending_samples": 5,
                "pending_ringsets": 2,
                "status_was": "5"
            }
        ],
        "summary": {
            "to_completed_count": 8,
            "to_pending_count": 4,
            "total_fixed": 12
        }
    }
}
```

### When to Use
- **After production data imports** - Clean up any placement issues
- **Monthly maintenance** - Ensure data integrity  
- **After bulk order processing** - Fix any edge cases
- **When users report missing orders** - Orders might be in wrong tab

### Safety Features
- **Admin permissions required** (when enabled)
- **Database transactions** - All changes are atomic
- **Detailed logging** - Shows exactly what was moved and why
- **Batch processing** - Prevents memory exhaustion
- **Idempotent** - Safe to run multiple times

### Production Checklist
- [ ] Test on staging environment first
- [ ] Start with small batches (50-100 orders)  
- [ ] Monitor memory usage and response times
- [ ] Run during low-traffic periods
- [ ] Keep logs of all runs
- [ ] Refresh restock page after running
- [ ] Verify orders are in correct tabs

### Troubleshooting
- **Memory errors**: Use smaller batch sizes (50 instead of 500)
- **Timeout errors**: Use smaller batches or increase server timeout
- **Permission errors**: Ensure admin permissions are enabled
- **No changes**: Data is already correctly placed (success!)

---
**Last Updated**: August 2025  
**Tested With**: Production data (thousands of orders)  
**Status**: Production Ready ✅
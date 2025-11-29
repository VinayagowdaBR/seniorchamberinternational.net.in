# PhonePe Contribution Payment - Quick Troubleshooting Guide

## Quick Start Testing

### Step 1: Run Debug Script
Visit: `http://localhost/senior-new/test_phonepe_contribution.php`

This will verify:
- ✓ Sessions directory exists and is writable
- ✓ Database connection works
- ✓ All required tables exist
- ✓ PhonePe configuration is correct
- ✓ Controller and library files exist
- ✓ Routes are configured
- ✓ PhonePe API is reachable

### Step 2: Test Payment Flow
1. Login: `http://localhost/senior-new/admin/login`
2. Navigate: `http://localhost/senior-new/admin/contributionpayment`
3. Select 2-3 members
4. Click "Proceed to Cart"
5. Select a package
6. Click "Proceed to Payment"

---

## Common Errors & Solutions

### Error: "Unable to locate the specified class: Session.php"

**Cause**: Session library loaded twice (auto-loaded + manual load)

**Solution**: ✅ FIXED - Removed redundant load from controller constructor

---

### Error: "404 Page Not Found" on payment URLs

**Cause**: Routes not configured

**Solution**: ✅ FIXED - Added routes to `routes.php`:
```php
$route['phonepe_contribution/initiate_payment'] = 'phonepe_contribution/initiate_payment';
$route['phonepe_contribution/payment_return'] = 'phonepe_contribution/payment_return';
$route['phonepe_contribution/payment_callback'] = 'phonepe_contribution/payment_callback';
```

---

### Error: Session data not persisting

**Symptoms**: Cart empties on page reload

**Causes & Solutions**:
1. **Sessions directory missing**
   - Check: `c:\xampp\htdocs\senior-new\application\sessions\`
   - Fix: ✅ VERIFIED - Directory exists

2. **Permissions issue**
   - Run: `icacls "c:\xampp\htdocs\senior-new\application\sessions" /grant Everyone:F`

3. **Session configuration issue**
   - Check `config.php` lines 399-405
   - Ensure `sess_save_path` points to correct directory

---

### Error: AJAX calls failing

**Symptoms**: Package selection doesn't update cart

**Debug Steps**:
1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Select package from dropdown
4. Check AJAX request to `update_cart_package`
5. Look for errors in Response tab

**Common causes**:
- CSRF token mismatch (check if CSRF is enabled)
- Session expired
- Database connection lost

---

### Error: PhonePe API timeout

**Symptoms**: Payment hangs after clicking "Proceed to Payment"

**Debug Steps**:
1. Check internet connection
2. Verify PhonePe sandbox URL: `https://api-preprod.phonepe.com/apis/pg-sandbox`
3. Check firewall settings
4. Review logs: `application/logs/log-2025-11-25.php`

**Test connectivity**:
```powershell
curl https://api-preprod.phonepe.com/apis/pg-sandbox
```

---

### Error: Database table not found

**Symptoms**: SQL error when initiating payment

**Solution**: Run table creation SQL:
```sql
CREATE TABLE IF NOT EXISTS contribution_bulk_payment_master (
  contribution_bulk_payment_id int(11) NOT NULL AUTO_INCREMENT,
  contribution_transaction_id varchar(100) NOT NULL,
  phonepe_merchant_transaction_id varchar(100) DEFAULT NULL,
  phonepe_transaction_id varchar(100) DEFAULT NULL,
  total_amount decimal(10,2) NOT NULL,
  total_members int(11) NOT NULL,
  currency varchar(10) DEFAULT 'INR',
  payment_status enum('pending','processing','paid','failed') DEFAULT 'pending',
  payment_method varchar(50) DEFAULT 'phonepe_contribution',
  processed_by_admin_id int(11) DEFAULT NULL,
  processed_by_admin_name varchar(255) DEFAULT NULL,
  plan_id int(11) DEFAULT NULL,
  plan_name varchar(255) DEFAULT NULL,
  member_ids text DEFAULT NULL,
  payment_details text DEFAULT NULL,
  phonepe_response text DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  paid_at datetime DEFAULT NULL,
  PRIMARY KEY (contribution_bulk_payment_id)
);

CREATE TABLE IF NOT EXISTS contribution_bulk_payment_invoices (
  contribution_invoice_id int(11) NOT NULL AUTO_INCREMENT,
  invoice_number varchar(50) NOT NULL,
  contribution_bulk_payment_id int(11) DEFAULT NULL,
  transaction_id varchar(100) NOT NULL,
  payment_date datetime NOT NULL,
  paid_by_admin_id int(11) NOT NULL,
  processed_by_admin_name varchar(255) DEFAULT NULL,
  total_amount decimal(10,2) NOT NULL,
  base_amount decimal(10,2) NOT NULL,
  gst_amount decimal(10,2) NOT NULL,
  gst_percentage decimal(5,2) NOT NULL,
  total_members int(11) NOT NULL,
  plan_id int(11) NOT NULL,
  plan_name varchar(255) NOT NULL,
  member_ids text NOT NULL,
  payment_status varchar(50) DEFAULT 'completed',
  payment_method varchar(50) DEFAULT 'phonepe_contribution',
  phonepe_transaction_id varchar(100) DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (contribution_invoice_id)
);
```

---

## Debugging Checklist

Before reporting issues, verify:

- [ ] Debug script passes all tests
- [ ] Sessions directory exists and is writable
- [ ] Database connection works
- [ ] All required tables exist
- [ ] PhonePe credentials are correct
- [ ] Internet connection is active
- [ ] Log threshold is set to 4 in `config.php`
- [ ] Browser console shows no JavaScript errors
- [ ] XAMPP Apache and MySQL are running

---

## Log Files to Check

1. **CodeIgniter Log**: `c:\xampp\htdocs\senior-new\application\logs\log-2025-11-25.php`
2. **PHP Error Log**: `c:\xampp\php\logs\php_error_log`
3. **Apache Error Log**: `c:\xampp\apache\logs\error.log`

**View logs in real-time**:
```powershell
Get-Content "c:\xampp\htdocs\senior-new\application\logs\log-2025-11-25.php" -Wait -Tail 50
```

---

## Testing PhonePe Sandbox

### Test Payment Flow (Sandbox)

1. **Initiate Payment**: Creates transaction in database
2. **Redirect to PhonePe**: Opens PhonePe payment page
3. **Test Payment**: Use PhonePe test credentials
4. **Return URL**: PhonePe redirects back to your site
5. **Verify Payment**: System checks payment status
6. **Generate Invoice**: Creates invoice on success

### PhonePe Test Cards

For sandbox testing, use these test cards:
- **Success**: Any card number ending in 0000
- **Failure**: Any card number ending in 1111

---

## Next Steps

1. ✅ Run debug script: `http://localhost/senior-new/test_phonepe_contribution.php`
2. ✅ Fix any errors shown in debug results
3. ✅ Test complete payment flow
4. ✅ Check database records after payment
5. ✅ Verify invoice generation
6. ✅ Test callback handling

---

## Support

If issues persist:
1. Check all logs for detailed error messages
2. Verify PhonePe credentials with PhonePe support
3. Test with minimal data (1 member, 1 package)
4. Enable detailed debugging in PhonePe library

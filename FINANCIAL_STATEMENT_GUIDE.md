# Financial Statement System Guide

## Overview
The Financial Statement system allows students to view their financial obligations, track payments, and submit payment proofs for verification by the accountant.

## Features

### For Students

#### 1. Financial Dashboard
- **Total Amount Due**: Calculated based on:
  - Tuition Fee: UGX 51,000 per credit enrolled
  - Computer Lab Fee: UGX 140,000 per semester
  - Functional Fee: 
    - UGX 650,000 (Year 1, Semester 1 - New Students)
    - UGX 530,000 (Other students)

#### 2. Payment Tracking
- **Amount Paid**: Total verified payments for current semester
- **Balance**: Remaining amount to be paid
- **Payment Progress**: Visual percentage indicator

#### 3. Fee Breakdown
- Detailed breakdown of all fees
- Shows calculation for tuition based on enrolled credits
- Clear display of all fee components

#### 4. Payment History
- Complete history of all payments
- Status indicators (Verified, Pending, Rejected)
- Transaction references and dates
- Download proof of payment

#### 5. Submit Payment
Students can submit payment details including:
- Payment date
- Amount paid
- Payment method (Bank Transfer, Mobile Money, Cash, Cheque)
- Transaction reference
- Proof of payment (upload receipt/screenshot)
- Additional notes

### For Accountants

#### Payment Verification Portal
Access: `accountant_verify_payments.php`

**Features:**
1. **Pending Payments Tab**
   - View all pending payment submissions
   - See student details and payment information
   - View uploaded proof of payment
   - Verify or reject payments

2. **Verified Payments Tab**
   - History of all verified payments
   - Shows who verified each payment and when

3. **Rejected Payments Tab**
   - List of rejected payments with reasons
   - Helps track payment issues

**Actions:**
- **Verify Payment**: Approve the payment (reflects in student account)
- **Reject Payment**: Reject with reason (student can resubmit)

## Database Structure

### student_payments Table
```sql
- id: Primary key
- student_id: Foreign key to users table
- academic_year: Year of payment
- semester: Semester number
- payment_date: Date of payment
- amount: Payment amount
- payment_method: Method used
- transaction_reference: Transaction ID
- payment_proof: File path to proof
- notes: Additional information
- status: pending/verified/rejected
- verified_by: Accountant who verified
- verified_at: Verification timestamp
- rejection_reason: Reason if rejected
- submitted_at: Submission timestamp
```

## Installation

1. **Run the database migration:**
```bash
mysql -u root -p student_record_management_system < database/migration_add_finance.sql
```

2. **Ensure upload directory exists:**
```bash
mkdir -p uploads/payments
chmod 777 uploads/payments
```

3. **Access Points:**
   - Students: Navigate to "Financial Statement" in sidebar
   - Accountants: Access via accountant dashboard

## Fee Calculation Logic

```php
// Tuition Fee
$tuition_fee = $total_credits * 51000;

// Computer Lab Fee (fixed)
$computer_lab_fee = 140000;

// Functional Fee (depends on year and semester)
if ($current_year == 1 && $current_semester == 1) {
    $functional_fee = 650000; // New students
} else {
    $functional_fee = 530000; // Continuing students
}

// Total
$total_amount_due = $tuition_fee + $computer_lab_fee + $functional_fee;
```

## Payment Workflow

1. **Student submits payment details**
   - Fills form with payment information
   - Uploads proof of payment
   - Status: Pending

2. **Accountant reviews payment**
   - Views payment details and proof
   - Verifies against bank/mobile money records
   - Either verifies or rejects

3. **Payment verified**
   - Status changes to "Verified"
   - Amount reflects in student's financial statement
   - Balance updates automatically

4. **Payment rejected**
   - Status changes to "Rejected"
   - Rejection reason provided
   - Student can resubmit with correct information

## Print/Download Feature

Students can download their financial statement by clicking the "Download Statement" button. This uses the browser's print function to generate a PDF.

**Print-friendly features:**
- Hides navigation buttons
- Optimized layout for printing
- Includes all financial details
- Shows payment history

## Security Features

- Role-based access control
- File upload validation
- SQL injection prevention
- Session management
- Secure file storage

## Support

For issues or questions:
- Students: Contact accountant or registrar
- Technical issues: Contact system administrator

## Future Enhancements

Potential improvements:
- Email notifications for payment verification
- SMS alerts for payment status
- Online payment integration
- Automatic bank reconciliation
- Payment plans/installments
- Receipt generation
- Financial reports for administration

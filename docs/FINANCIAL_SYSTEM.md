# Financial Management System

## Overview
The Student Record Management System includes a comprehensive financial management module for handling tuition payments, scholarships, invoices, and financial clearance.

## Features

### 1. Invoice Management
- **Automatic Invoice Generation**: Generate invoices for students per semester
- **Fee Components**:
  - Tuition fees (based on program)
  - Registration fees
  - Library fees
  - Laboratory fees
  - Other miscellaneous fees
- **Invoice Tracking**: Track invoice status (pending, partial, paid, overdue)
- **Bulk Invoice Generation**: Generate invoices for multiple students at once

### 2. Payment Processing
- **Multiple Payment Methods**:
  - Cash
  - Bank Transfer
  - Mobile Money (M-Pesa, Tigo Pesa, Airtel Money)
  - Cheque
  - Card Payment
- **Receipt Generation**: Automatic receipt generation for all payments
- **Payment History**: Complete payment history for each student
- **Partial Payments**: Support for installment payments

### 3. Scholarship Management
- **Scholarship Types**:
  - Full Scholarship (100% tuition waiver)
  - Partial Scholarship (percentage-based discount)
  - Merit-based Scholarships
  - Need-based Grants
  - Sports Excellence Awards
  - Other special scholarships
- **Scholarship Features**:
  - Award scholarships to students
  - Set duration (number of semesters)
  - Track scholarship status
  - Automatic fee calculation with scholarship applied
  - Scholarship eligibility criteria

### 4. Financial Clearance
- **Semester Clearance**: Approve students for:
  - Course registration
  - Exam participation
  - Graduation eligibility
- **Clearance Criteria**:
  - Minimum payment threshold
  - Outstanding balance limits
  - Payment plan compliance
- **Clearance Status**: Cleared, Not Cleared, Pending

### 5. Financial Reports
- **Revenue Reports**:
  - Total revenue collected
  - Revenue by semester
  - Revenue by program
  - Payment method breakdown
- **Outstanding Balance Reports**:
  - Total outstanding
  - Overdue invoices
  - Students with balances
- **Scholarship Reports**:
  - Active scholarships
  - Scholarship expenditure
  - Scholarship by type

## Database Tables

### invoices
Stores student invoices with fee breakdown and payment status.

**Key Fields:**
- `invoice_number`: Unique invoice identifier
- `student_id`: Student reference
- `academic_year`: e.g., "2025/2026"
- `semester`: 1 or 2
- `tuition_fee`, `registration_fee`, `library_fee`, `lab_fee`, `other_fees`
- `total_amount`: Sum of all fees
- `amount_paid`: Total paid so far
- `balance`: Remaining balance
- `status`: pending, partial, paid, overdue
- `due_date`: Payment deadline

### payments
Records all payment transactions.

**Key Fields:**
- `payment_reference`: Unique payment ID
- `invoice_id`: Related invoice
- `student_id`: Student who paid
- `amount`: Payment amount
- `payment_method`: cash, bank_transfer, mobile_money, etc.
- `payment_date`: Date of payment
- `transaction_id`: External transaction reference
- `receipt_number`: Receipt identifier
- `received_by`: Accountant who processed

### scholarships
Defines available scholarship programs.

**Key Fields:**
- `name`: Scholarship name
- `scholarship_type`: full, partial, merit, need_based, sports, other
- `amount`: Fixed amount (if applicable)
- `percentage`: Percentage discount (if applicable)
- `duration_semesters`: How long scholarship lasts
- `eligibility_criteria`: Requirements
- `is_active`: Whether scholarship is currently available

### student_scholarships
Links students to their awarded scholarships.

**Key Fields:**
- `student_id`: Student reference
- `scholarship_id`: Scholarship reference
- `academic_year`: Year awarded
- `start_semester`: When scholarship begins
- `end_semester`: When scholarship ends
- `status`: active, expired, revoked, completed
- `awarded_by`: Who approved the scholarship

### financial_clearance
Tracks financial clearance status per semester.

**Key Fields:**
- `student_id`: Student reference
- `academic_year`: Academic year
- `semester`: Semester number
- `total_fees`: Total fees for semester
- `total_paid`: Amount paid
- `balance`: Outstanding balance
- `clearance_status`: cleared, not_cleared, pending
- `can_register_courses`: Boolean
- `can_take_exams`: Boolean
- `can_graduate`: Boolean

## User Roles & Permissions

### Accountant
**Can:**
- Generate invoices
- Process payments
- Issue receipts
- Award scholarships
- Approve financial clearance
- View all financial data
- Generate financial reports
- Manage refunds and adjustments

**Cannot:**
- Modify academic records
- Approve student registrations
- Manage courses

### Admin
**Can:**
- All accountant permissions
- Create/delete accountant accounts
- Override financial decisions
- Access all system data

### Student
**Can:**
- View their invoices
- View payment history
- View scholarship status
- View clearance status
- Make payment inquiries

**Cannot:**
- Process payments
- Generate invoices
- Award scholarships

## Workflows

### Invoice Generation Workflow
1. Accountant selects students (by program, year, semester)
2. System calculates fees based on program tuition
3. System checks for active scholarships
4. System applies scholarship discounts
5. System generates invoices with unique numbers
6. Students can view invoices in their portal

### Payment Processing Workflow
1. Student makes payment (bank, mobile money, cash, etc.)
2. Accountant receives payment confirmation
3. Accountant records payment in system
4. System updates invoice (amount_paid, balance, status)
5. System generates receipt
6. System updates financial clearance status
7. Student receives receipt and confirmation

### Scholarship Award Workflow
1. Accountant reviews scholarship applications
2. Accountant selects scholarship type and amount
3. Accountant assigns scholarship to student
4. System records scholarship details
5. System automatically applies discount to future invoices
6. Student notified of scholarship award

### Financial Clearance Workflow
1. System calculates total fees and payments per semester
2. Accountant reviews payment status
3. If payment threshold met:
   - Accountant approves clearance
   - System enables course registration
   - System enables exam participation
4. If payment insufficient:
   - Clearance denied
   - Student cannot register/take exams
5. Student can view clearance status in portal

## Sample Data

### Demo Invoices
- 7 sample invoices for different students
- Mix of paid, partial, pending, and overdue statuses
- Various fee amounts based on programs

### Demo Payments
- 5 sample payments
- Different payment methods demonstrated
- Linked to invoices

### Demo Scholarships
- 5 scholarship types:
  - Presidential Scholarship (100% full)
  - Merit Scholarship (50% partial)
  - Sports Excellence Award (fixed amount)
  - Need-Based Grant (fixed amount)
  - Academic Excellence (75% partial)

### Demo Student Scholarships
- 3 students with active scholarships
- Different scholarship types applied

### Demo Financial Clearance
- 7 clearance records
- 3 cleared students
- 2 pending (partial payment)
- 2 not cleared (no payment)

## Integration Points

### With Student Registration
- New students automatically get invoices generated
- Program tuition fees pulled from programs table

### With Course Registration
- Financial clearance checked before allowing course registration
- Students with outstanding balances may be blocked

### With Academic Records
- Clearance status affects exam eligibility
- Graduation requires full financial clearance

## Future Enhancements

### Planned Features
- Email invoice notifications
- SMS payment reminders
- Online payment gateway integration
- Automatic late fee calculation
- Payment plan management
- Financial aid application system
- Refund processing
- Budget forecasting
- Advanced analytics dashboard

### API Endpoints (Future)
- `/api/invoices` - Invoice management
- `/api/payments` - Payment processing
- `/api/scholarships` - Scholarship management
- `/api/clearance` - Clearance status
- `/api/reports` - Financial reports

## Security Considerations

- All financial transactions logged
- Payment data encrypted
- Role-based access control
- Audit trail for all changes
- Receipt numbers are unique and sequential
- Invoice numbers follow standard format
- Payment references validated

## Reporting

### Available Reports
1. **Revenue Report**: Total revenue by period
2. **Outstanding Balance Report**: Students with unpaid fees
3. **Payment Method Report**: Breakdown by payment type
4. **Scholarship Report**: Active scholarships and costs
5. **Clearance Report**: Clearance status by semester
6. **Program Revenue Report**: Revenue by academic program
7. **Overdue Invoice Report**: Invoices past due date

## Best Practices

1. **Generate invoices early**: At least 30 days before semester start
2. **Set realistic due dates**: Allow time for payment processing
3. **Track scholarships carefully**: Ensure proper application to invoices
4. **Regular clearance reviews**: Check clearance status weekly
5. **Maintain payment records**: Keep all receipts and transaction IDs
6. **Communicate with students**: Send reminders for outstanding balances
7. **Reconcile regularly**: Match payments to invoices frequently

---

**Last Updated:** November 10, 2025  
**Version:** 2.0.0  
**Module:** Financial Management

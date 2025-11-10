<?php
include 'includes/conn.php';

$message = '';
$message_type = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Get schools, departments, programs for dropdowns
try {
    $schools = $pdo->query("SELECT * FROM schools ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    // If schools table doesn't exist, redirect to installation
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        header("Location: install_v2.php");
        exit();
    }
    $schools = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
    // Validation and processing
    $errors = [];
    
    // Personal Information
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $nationality = $_POST['nationality'];
    $national_id = trim($_POST['national_id']);
    
    // Contact Information
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $alternative_phone = trim($_POST['alternative_phone']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $region = $_POST['region'];
    $postal_code = trim($_POST['postal_code']);
    $country = $_POST['country'];
    
    // Emergency Contact
    $emergency_contact_name = trim($_POST['emergency_contact_name']);
    $emergency_contact_relationship = $_POST['emergency_contact_relationship'];
    $emergency_contact_phone = trim($_POST['emergency_contact_phone']);
    $emergency_contact_email = trim($_POST['emergency_contact_email']);
    
    // Previous Education
    $secondary_school = trim($_POST['secondary_school']);
    $completion_year = $_POST['completion_year'];
    $certificate_type = $_POST['certificate_type'];
    $division_grade = $_POST['division_grade'];
    $index_number = trim($_POST['index_number']);
    
    // Program Selection
    $school = $_POST['school'];
    $department = $_POST['department'];
    $program = $_POST['program'];
    $entry_year = $_POST['entry_year'];
    $entry_semester = $_POST['entry_semester'];
    
    // Account Creation
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Terms
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;
    $privacy_accepted = isset($_POST['privacy_accepted']) ? 1 : 0;
    $info_accurate = isset($_POST['info_accurate']) ? 1 : 0;
    
    // Validation
    if (!preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
        $errors[] = "First name should contain only letters";
    }
    
    // Calculate age
    $dob = new DateTime($date_of_birth);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
    
    if ($age < 16 || $age > 65) {
        $errors[] = "Age must be between 16 and 65 years";
    }
    
    // Check email uniqueness
    $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->execute([$email]);
    if ($check_email->rowCount() > 0) {
        $errors[] = "Email already registered";
    }
    
    // Check username uniqueness
    $check_username = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check_username->execute([$username]);
    if ($check_username->rowCount() > 0) {
        $errors[] = "Username already taken";
    }
    
    // Password validation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    // Terms validation
    if (!$terms_accepted || !$privacy_accepted || !$info_accurate) {
        $errors[] = "You must accept all terms and conditions";
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Generate student number
            $program_data = $pdo->prepare("SELECT p.code, d.code as dept_code, s.code as school_code 
                                          FROM programs p 
                                          JOIN departments d ON p.department_id = d.id 
                                          JOIN schools s ON d.school_id = s.id 
                                          WHERE p.id = ?");
            $program_data->execute([$program]);
            $prog = $program_data->fetch();
            
            // Count existing students for this program
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM registration_requests WHERE program = ? AND entry_year = ?");
            $count_stmt->execute([$program, $entry_year]);
            $count = $count_stmt->fetchColumn() + 1;
            
            $student_number = $prog['school_code'] . '/' . $prog['dept_code'] . '/' . $entry_year . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
            
            // Handle file uploads (simplified for now - you'll need proper upload handling)
            $birth_certificate = null;
            $national_id_copy = null;
            $certificate_copy = null;
            $passport_photo = null;
            
            // Insert registration request
            $stmt = $pdo->prepare("INSERT INTO registration_requests 
                (username, password, email, first_name, middle_name, last_name, date_of_birth, gender, 
                 nationality, national_id, phone, alternative_phone, street, city, region, postal_code, country,
                 address, emergency_contact_name, emergency_contact_relationship, emergency_contact_phone, 
                 emergency_contact_email, guardian_name, guardian_phone, secondary_school, completion_year, 
                 certificate_type, division_grade, index_number, previous_school, birth_certificate, 
                 national_id_copy, certificate_copy, passport_photo, school, department, program, 
                 entry_year, entry_semester, student_number, terms_accepted, privacy_accepted, 
                 info_accurate, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $full_address = $street . ', ' . $city;
            
            $stmt->execute([
                $username, $hashed_password, $email, $first_name, $middle_name, $last_name, 
                $date_of_birth, $gender, $nationality, $national_id, $phone, $alternative_phone,
                $street, $city, $region, $postal_code, $country, $full_address,
                $emergency_contact_name, $emergency_contact_relationship, $emergency_contact_phone,
                $emergency_contact_email, $emergency_contact_name, $emergency_contact_phone,
                $secondary_school, $completion_year, $certificate_type, $division_grade, 
                $index_number, $secondary_school, $birth_certificate, $national_id_copy,
                $certificate_copy, $passport_photo, $school, $department, $program,
                $entry_year, $entry_semester, $student_number, $terms_accepted, 
                $privacy_accepted, $info_accurate
            ]);
            
            $pdo->commit();
            
            // Redirect to success page
            header("Location: registration_success.php?student_number=" . urlencode($student_number) . "&username=" . urlencode($username));
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error submitting registration: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Apply Now</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .registration-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .required::after {
            content: " *";
            color: red;
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
        }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 1rem;
            background: #e9ecef;
            position: relative;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <div class="text-center mb-4">
                <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                <h2>Student Registration</h2>
                <p class="text-muted">Apply Now for Academic Year 2025/2026</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" id="registrationForm" enctype="multipart/form-data">
                <!-- Section 1: Personal Information -->
                <div class="section-header">
                    <h4><i class="fas fa-user me-2"></i>Section 1: Personal Information</h4>
                </div>
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">First Name</label>
                            <input type="text" class="form-control" name="first_name" required 
                                   pattern="[A-Za-z\s]+" title="Only letters allowed">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" 
                                   pattern="[A-Za-z\s]+" title="Only letters allowed">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required 
                                   pattern="[A-Za-z\s]+" title="Only letters allowed">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" required 
                                   max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>"
                                   min="<?php echo date('Y-m-d', strtotime('-65 years')); ?>">
                            <small class="text-muted" id="ageDisplay"></small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Nationality</label>
                            <select class="form-select" name="nationality" required>
                                <option value="">Select Nationality</option>
                                <option value="Tanzanian" selected>Tanzanian</option>
                                <option value="Kenyan">Kenyan</option>
                                <option value="Ugandan">Ugandan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">National ID</label>
                            <input type="text" class="form-control" name="national_id" required 
                                   placeholder="e.g., 20050115-12345-67890-12">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contact Information -->
                <div class="section-header">
                    <h4><i class="fas fa-address-book me-2"></i>Section 2: Contact Information</h4>
                </div>
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Email Address</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" required 
                                   placeholder="+255 712 345 678">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alternative Phone</label>
                            <input type="tel" class="form-control" name="alternative_phone" 
                                   placeholder="+255 754 987 654">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Street Address</label>
                            <input type="text" class="form-control" name="street" required 
                                   placeholder="123 Main Street">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">City</label>
                            <input type="text" class="form-control" name="city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Region</label>
                            <select class="form-select" name="region" required>
                                <option value="">Select Region</option>
                                <option value="Dar es Salaam">Dar es Salaam</option>
                                <option value="Arusha">Arusha</option>
                                <option value="Dodoma">Dodoma</option>
                                <option value="Mwanza">Mwanza</option>
                                <option value="Kilimanjaro">Kilimanjaro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-control" name="postal_code">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Country</label>
                            <select class="form-select" name="country" required>
                                <option value="Tanzania" selected>Tanzania</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Uganda">Uganda</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Emergency Contact -->
                <div class="section-header">
                    <h4><i class="fas fa-phone-alt me-2"></i>Section 3: Emergency Contact</h4>
                </div>
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Contact Person Name</label>
                            <input type="text" class="form-control" name="emergency_contact_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Relationship</label>
                            <select class="form-select" name="emergency_contact_relationship" required>
                                <option value="">Select Relationship</option>
                                <option value="Mother">Mother</option>
                                <option value="Father">Father</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Phone Number</label>
                            <input type="tel" class="form-control" name="emergency_contact_phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="emergency_contact_email">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Previous Education -->
                <div class="section-header">
                    <h4><i class="fas fa-school me-2"></i>Section 4: Previous Education</h4>
                </div>
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Secondary School Name</label>
                            <input type="text" class="form-control" name="secondary_school" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Completion Year</label>
                            <input type="number" class="form-control" name="completion_year" required 
                                   min="2000" max="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Certificate Type</label>
                            <select class="form-select" name="certificate_type" required>
                                <option value="">Select Certificate</option>
                                <option value="Form IV Certificate">Form IV Certificate</option>
                                <option value="Form VI Certificate">Form VI Certificate</option>
                                <option value="Diploma">Diploma</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Division/Grade</label>
                            <select class="form-select" name="division_grade" required>
                                <option value="">Select Division</option>
                                <option value="Division I">Division I</option>
                                <option value="Division II">Division II</option>
                                <option value="Division III">Division III</option>
                                <option value="Division IV">Division IV</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Index Number</label>
                            <input type="text" class="form-control" name="index_number" 
                                   placeholder="S1234/0567/2023">
                        </div>
                    </div>
                </div>

                <!-- Section 5: Program Selection -->
                <div class="section-header">
                    <h4><i class="fas fa-graduation-cap me-2"></i>Section 5: Program Selection</h4>
                </div>
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Choose School</label>
                            <select class="form-select" name="school" id="schoolSelect" required>
                                <option value="">Select School</option>
                                <?php foreach($schools as $school): ?>
                                <option value="<?php echo $school['id']; ?>"><?php echo $school['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Choose Department</label>
                            <select class="form-select" name="department" id="departmentSelect" required disabled>
                                <option value="">Select Department</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Choose Program</label>
                            <select class="form-select" name="program" id="programSelect" required disabled>
                                <option value="">Select Program</option>
                            </select>
                            <div id="programDetails" class="mt-2" style="display:none;">
                                <div class="alert alert-info">
                                    <strong>Program Details:</strong>
                                    <ul class="mb-0">
                                        <li>Duration: <span id="duration"></span> years</li>
                                        <li>Total Credits: <span id="credits"></span></li>
                                        <li>Tuition per Semester: $<span id="tuition"></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Entry Year</label>
                            <select class="form-select" name="entry_year" required>
                                <option value="">Select Year</option>
                                <option value="2025" selected>2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Semester</label>
                            <select class="form-select" name="entry_semester" required>
                                <option value="1" selected>Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Account Creation -->
                <div class="section-header">
                    <h4><i class="fas fa-user-lock me-2"></i>Section 6: Account Creation</h4>
                </div>
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Username</label>
                            <input type="text" class="form-control" name="username" required 
                                   pattern="[a-zA-Z0-9]{4,20}" 
                                   title="4-20 characters, alphanumeric only"
                                   placeholder="Choose a unique username">
                            <small class="text-muted">4-20 characters, letters and numbers only</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Password</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                            <div class="password-strength" id="passwordStrength"></div>
                            <small class="text-muted">Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                </div>

                <!-- Section 7: Terms & Conditions -->
                <div class="section-header">
                    <h4><i class="fas fa-file-contract me-2"></i>Section 7: Terms & Conditions</h4>
                </div>
                <div class="form-section">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="terms_accepted" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I have read and agree to the <a href="#" target="_blank">Terms and Conditions</a>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="privacy_accepted" id="privacy" required>
                        <label class="form-check-label" for="privacy">
                            I consent to data processing as per <a href="#" target="_blank">Privacy Policy</a>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="info_accurate" id="accurate" required>
                        <label class="form-check-label" for="accurate">
                            I confirm all information provided is accurate and complete
                        </label>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="submit_registration" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Submit Registration
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Age calculator
        document.querySelector('[name="date_of_birth"]').addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            document.getElementById('ageDisplay').textContent = 'Age: ' + age + ' years';
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            
            const strengthBar = document.getElementById('passwordStrength');
            strengthBar.className = 'password-strength';
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });

        // Dynamic department loading
        document.getElementById('schoolSelect').addEventListener('change', function() {
            const schoolId = this.value;
            const deptSelect = document.getElementById('departmentSelect');
            const progSelect = document.getElementById('programSelect');
            
            deptSelect.disabled = true;
            progSelect.disabled = true;
            deptSelect.innerHTML = '<option value="">Loading...</option>';
            
            if (schoolId) {
                fetch('api/get_departments.php?school_id=' + schoolId)
                    .then(response => response.json())
                    .then(data => {
                        deptSelect.innerHTML = '<option value="">Select Department</option>';
                        data.forEach(dept => {
                            deptSelect.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
                        });
                        deptSelect.disabled = false;
                    });
            }
        });

        // Dynamic program loading
        document.getElementById('departmentSelect').addEventListener('change', function() {
            const deptId = this.value;
            const progSelect = document.getElementById('programSelect');
            
            progSelect.disabled = true;
            progSelect.innerHTML = '<option value="">Loading...</option>';
            
            if (deptId) {
                fetch('api/get_programs.php?department_id=' + deptId)
                    .then(response => response.json())
                    .then(data => {
                        progSelect.innerHTML = '<option value="">Select Program</option>';
                        data.forEach(prog => {
                            progSelect.innerHTML += `<option value="${prog.id}" 
                                data-duration="${prog.duration_years}" 
                                data-credits="${prog.total_credits}" 
                                data-tuition="${prog.tuition_per_semester}">
                                ${prog.name}
                            </option>`;
                        });
                        progSelect.disabled = false;
                    });
            }
        });

        // Show program details
        document.getElementById('programSelect').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (this.value) {
                document.getElementById('duration').textContent = selected.dataset.duration;
                document.getElementById('credits').textContent = selected.dataset.credits;
                document.getElementById('tuition').textContent = selected.dataset.tuition;
                document.getElementById('programDetails').style.display = 'block';
            } else {
                document.getElementById('programDetails').style.display = 'none';
            }
        });
    </script>
</body>
</html>

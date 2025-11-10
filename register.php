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
        header("Location: setup/install_complete_system.php");
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
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="registration-page">
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
                    <!-- Profile Photo Upload -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label required">Profile Photo</label>
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <img id="photoPreview" src="https://via.placeholder.com/150" 
                                             alt="Profile Preview" class="img-thumbnail" 
                                             style="max-width: 150px; max-height: 150px;">
                                    </div>
                                    <input type="file" class="form-control" name="profile_photo" 
                                           id="profilePhoto" accept="image/*" required 
                                           onchange="previewPhoto(this)">
                                    <small class="text-muted">Upload a passport-size photo (JPG, PNG, max 2MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Name Fields -->
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

                    <!-- Date of Birth, Gender, Marital Status -->
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
                            <label class="form-label required">Marital Status</label>
                            <select class="form-select" name="marital_status" required>
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Nationality and Religion -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Nationality</label>
                            <select class="form-select" name="nationality" id="nationalitySelect" required>
                                <option value="">Select Nationality</option>
                                <option value="Ugandan" selected>Ugandan</option>
                                <option value="Afghan">Afghan</option>
                                <option value="Albanian">Albanian</option>
                                <option value="Algerian">Algerian</option>
                                <option value="American">American</option>
                                <option value="Andorran">Andorran</option>
                                <option value="Angolan">Angolan</option>
                                <option value="Argentine">Argentine</option>
                                <option value="Armenian">Armenian</option>
                                <option value="Australian">Australian</option>
                                <option value="Austrian">Austrian</option>
                                <option value="Azerbaijani">Azerbaijani</option>
                                <option value="Bahamian">Bahamian</option>
                                <option value="Bahraini">Bahraini</option>
                                <option value="Bangladeshi">Bangladeshi</option>
                                <option value="Barbadian">Barbadian</option>
                                <option value="Belarusian">Belarusian</option>
                                <option value="Belgian">Belgian</option>
                                <option value="Belizean">Belizean</option>
                                <option value="Beninese">Beninese</option>
                                <option value="Bhutanese">Bhutanese</option>
                                <option value="Bolivian">Bolivian</option>
                                <option value="Bosnian">Bosnian</option>
                                <option value="Botswanan">Botswanan</option>
                                <option value="Brazilian">Brazilian</option>
                                <option value="British">British</option>
                                <option value="Bruneian">Bruneian</option>
                                <option value="Bulgarian">Bulgarian</option>
                                <option value="Burkinabe">Burkinabe</option>
                                <option value="Burundian">Burundian</option>
                                <option value="Cambodian">Cambodian</option>
                                <option value="Cameroonian">Cameroonian</option>
                                <option value="Canadian">Canadian</option>
                                <option value="Cape Verdean">Cape Verdean</option>
                                <option value="Central African">Central African</option>
                                <option value="Chadian">Chadian</option>
                                <option value="Chilean">Chilean</option>
                                <option value="Chinese">Chinese</option>
                                <option value="Colombian">Colombian</option>
                                <option value="Congolese">Congolese</option>
                                <option value="Costa Rican">Costa Rican</option>
                                <option value="Croatian">Croatian</option>
                                <option value="Cuban">Cuban</option>
                                <option value="Cypriot">Cypriot</option>
                                <option value="Czech">Czech</option>
                                <option value="Danish">Danish</option>
                                <option value="Djiboutian">Djiboutian</option>
                                <option value="Dominican">Dominican</option>
                                <option value="Dutch">Dutch</option>
                                <option value="Ecuadorian">Ecuadorian</option>
                                <option value="Egyptian">Egyptian</option>
                                <option value="Emirati">Emirati</option>
                                <option value="Equatorial Guinean">Equatorial Guinean</option>
                                <option value="Eritrean">Eritrean</option>
                                <option value="Estonian">Estonian</option>
                                <option value="Ethiopian">Ethiopian</option>
                                <option value="Fijian">Fijian</option>
                                <option value="Filipino">Filipino</option>
                                <option value="Finnish">Finnish</option>
                                <option value="French">French</option>
                                <option value="Gabonese">Gabonese</option>
                                <option value="Gambian">Gambian</option>
                                <option value="Georgian">Georgian</option>
                                <option value="German">German</option>
                                <option value="Ghanaian">Ghanaian</option>
                                <option value="Greek">Greek</option>
                                <option value="Grenadian">Grenadian</option>
                                <option value="Guatemalan">Guatemalan</option>
                                <option value="Guinean">Guinean</option>
                                <option value="Guyanese">Guyanese</option>
                                <option value="Haitian">Haitian</option>
                                <option value="Honduran">Honduran</option>
                                <option value="Hungarian">Hungarian</option>
                                <option value="Icelandic">Icelandic</option>
                                <option value="Indian">Indian</option>
                                <option value="Indonesian">Indonesian</option>
                                <option value="Iranian">Iranian</option>
                                <option value="Iraqi">Iraqi</option>
                                <option value="Irish">Irish</option>
                                <option value="Israeli">Israeli</option>
                                <option value="Italian">Italian</option>
                                <option value="Ivorian">Ivorian</option>
                                <option value="Jamaican">Jamaican</option>
                                <option value="Japanese">Japanese</option>
                                <option value="Jordanian">Jordanian</option>
                                <option value="Kazakh">Kazakh</option>
                                <option value="Kenyan">Kenyan</option>
                                <option value="Korean">Korean</option>
                                <option value="Kuwaiti">Kuwaiti</option>
                                <option value="Kyrgyz">Kyrgyz</option>
                                <option value="Laotian">Laotian</option>
                                <option value="Latvian">Latvian</option>
                                <option value="Lebanese">Lebanese</option>
                                <option value="Liberian">Liberian</option>
                                <option value="Libyan">Libyan</option>
                                <option value="Lithuanian">Lithuanian</option>
                                <option value="Luxembourgish">Luxembourgish</option>
                                <option value="Macedonian">Macedonian</option>
                                <option value="Malagasy">Malagasy</option>
                                <option value="Malawian">Malawian</option>
                                <option value="Malaysian">Malaysian</option>
                                <option value="Maldivian">Maldivian</option>
                                <option value="Malian">Malian</option>
                                <option value="Maltese">Maltese</option>
                                <option value="Mauritanian">Mauritanian</option>
                                <option value="Mauritian">Mauritian</option>
                                <option value="Mexican">Mexican</option>
                                <option value="Moldovan">Moldovan</option>
                                <option value="Mongolian">Mongolian</option>
                                <option value="Montenegrin">Montenegrin</option>
                                <option value="Moroccan">Moroccan</option>
                                <option value="Mozambican">Mozambican</option>
                                <option value="Namibian">Namibian</option>
                                <option value="Nepalese">Nepalese</option>
                                <option value="New Zealander">New Zealander</option>
                                <option value="Nicaraguan">Nicaraguan</option>
                                <option value="Nigerian">Nigerian</option>
                                <option value="Nigerien">Nigerien</option>
                                <option value="Norwegian">Norwegian</option>
                                <option value="Omani">Omani</option>
                                <option value="Pakistani">Pakistani</option>
                                <option value="Panamanian">Panamanian</option>
                                <option value="Papua New Guinean">Papua New Guinean</option>
                                <option value="Paraguayan">Paraguayan</option>
                                <option value="Peruvian">Peruvian</option>
                                <option value="Polish">Polish</option>
                                <option value="Portuguese">Portuguese</option>
                                <option value="Qatari">Qatari</option>
                                <option value="Romanian">Romanian</option>
                                <option value="Russian">Russian</option>
                                <option value="Rwandan">Rwandan</option>
                                <option value="Saudi">Saudi</option>
                                <option value="Senegalese">Senegalese</option>
                                <option value="Serbian">Serbian</option>
                                <option value="Sierra Leonean">Sierra Leonean</option>
                                <option value="Singaporean">Singaporean</option>
                                <option value="Slovak">Slovak</option>
                                <option value="Slovenian">Slovenian</option>
                                <option value="Somali">Somali</option>
                                <option value="South African">South African</option>
                                <option value="South Sudanese">South Sudanese</option>
                                <option value="Spanish">Spanish</option>
                                <option value="Sri Lankan">Sri Lankan</option>
                                <option value="Sudanese">Sudanese</option>
                                <option value="Surinamese">Surinamese</option>
                                <option value="Swedish">Swedish</option>
                                <option value="Swiss">Swiss</option>
                                <option value="Syrian">Syrian</option>
                                <option value="Taiwanese">Taiwanese</option>
                                <option value="Tajik">Tajik</option>
                                <option value="Tanzanian">Tanzanian</option>
                                <option value="Thai">Thai</option>
                                <option value="Togolese">Togolese</option>
                                <option value="Trinidadian">Trinidadian</option>
                                <option value="Tunisian">Tunisian</option>
                                <option value="Turkish">Turkish</option>
                                <option value="Turkmen">Turkmen</option>
                                <option value="Ukrainian">Ukrainian</option>
                                <option value="Uruguayan">Uruguayan</option>
                                <option value="Uzbek">Uzbek</option>
                                <option value="Venezuelan">Venezuelan</option>
                                <option value="Vietnamese">Vietnamese</option>
                                <option value="Yemeni">Yemeni</option>
                                <option value="Zambian">Zambian</option>
                                <option value="Zimbabwean">Zimbabwean</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Religion</label>
                            <select class="form-select" name="religion" required>
                                <option value="">Select Religion</option>
                                <option value="Christianity">Christianity</option>
                                <option value="Islam">Islam</option>
                                <option value="Hinduism">Hinduism</option>
                                <option value="Buddhism">Buddhism</option>
                                <option value="Judaism">Judaism</option>
                                <option value="Traditional African Religion">Traditional African Religion</option>
                                <option value="Other">Other</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    <!-- ID Type and Number -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">ID Type</label>
                            <select class="form-select" name="id_type" id="idType" required onchange="updateIdPlaceholder()">
                                <option value="">Select ID Type</option>
                                <option value="national_id" selected>National ID</option>
                                <option value="passport">Passport</option>
                                <option value="refugee_id">Refugee ID</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required" id="idLabel">ID Number</label>
                            <input type="text" class="form-control" name="id_number" id="idNumber" required 
                                   placeholder="Enter your ID number">
                            <small class="text-muted" id="idHelp">Enter your identification number</small>
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
                                   placeholder="+256 712 345 678">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alternative Phone</label>
                            <input type="tel" class="form-control" name="alternative_phone" 
                                   placeholder="+256 754 987 654">
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
                            <label class="form-label required">Region/District</label>
                            <select class="form-select" name="region" required>
                                <option value="">Select Region/District</option>
                                <!-- Central Region -->
                                <optgroup label="Central Region">
                                    <option value="Kampala">Kampala</option>
                                    <option value="Wakiso">Wakiso</option>
                                    <option value="Mpigi">Mpigi</option>
                                    <option value="Mukono">Mukono</option>
                                    <option value="Luwero">Luwero</option>
                                    <option value="Nakasongola">Nakasongola</option>
                                    <option value="Nakaseke">Nakaseke</option>
                                    <option value="Kiboga">Kiboga</option>
                                    <option value="Mubende">Mubende</option>
                                    <option value="Mityana">Mityana</option>
                                    <option value="Kyankwanzi">Kyankwanzi</option>
                                    <option value="Kassanda">Kassanda</option>
                                    <option value="Gomba">Gomba</option>
                                    <option value="Butambala">Butambala</option>
                                    <option value="Buikwe">Buikwe</option>
                                    <option value="Buvuma">Buvuma</option>
                                    <option value="Kalangala">Kalangala</option>
                                    <option value="Kalungu">Kalungu</option>
                                    <option value="Lwengo">Lwengo</option>
                                    <option value="Lyantonde">Lyantonde</option>
                                    <option value="Masaka">Masaka</option>
                                    <option value="Rakai">Rakai</option>
                                    <option value="Sembabule">Sembabule</option>
                                </optgroup>
                                <!-- Eastern Region -->
                                <optgroup label="Eastern Region">
                                    <option value="Jinja">Jinja</option>
                                    <option value="Iganga">Iganga</option>
                                    <option value="Bugiri">Bugiri</option>
                                    <option value="Mayuge">Mayuge</option>
                                    <option value="Kamuli">Kamuli</option>
                                    <option value="Kaliro">Kaliro</option>
                                    <option value="Buyende">Buyende</option>
                                    <option value="Luuka">Luuka</option>
                                    <option value="Namutumba">Namutumba</option>
                                    <option value="Mbale">Mbale</option>
                                    <option value="Bududa">Bududa</option>
                                    <option value="Manafwa">Manafwa</option>
                                    <option value="Sironko">Sironko</option>
                                    <option value="Bulambuli">Bulambuli</option>
                                    <option value="Tororo">Tororo</option>
                                    <option value="Busia">Busia</option>
                                    <option value="Butaleja">Butaleja</option>
                                    <option value="Pallisa">Pallisa</option>
                                    <option value="Budaka">Budaka</option>
                                    <option value="Kibuku">Kibuku</option>
                                    <option value="Soroti">Soroti</option>
                                    <option value="Serere">Serere</option>
                                    <option value="Ngora">Ngora</option>
                                    <option value="Kumi">Kumi</option>
                                    <option value="Bukedea">Bukedea</option>
                                    <option value="Kapchorwa">Kapchorwa</option>
                                    <option value="Bukwo">Bukwo</option>
                                    <option value="Kween">Kween</option>
                                </optgroup>
                                <!-- Northern Region -->
                                <optgroup label="Northern Region">
                                    <option value="Gulu">Gulu</option>
                                    <option value="Kitgum">Kitgum</option>
                                    <option value="Pader">Pader</option>
                                    <option value="Agago">Agago</option>
                                    <option value="Lamwo">Lamwo</option>
                                    <option value="Amuru">Amuru</option>
                                    <option value="Nwoya">Nwoya</option>
                                    <option value="Lira">Lira</option>
                                    <option value="Alebtong">Alebtong</option>
                                    <option value="Dokolo">Dokolo</option>
                                    <option value="Amolatar">Amolatar</option>
                                    <option value="Oyam">Oyam</option>
                                    <option value="Kole">Kole</option>
                                    <option value="Apac">Apac</option>
                                    <option value="Kotido">Kotido</option>
                                    <option value="Moroto">Moroto</option>
                                    <option value="Nakapiripirit">Nakapiripirit</option>
                                    <option value="Amudat">Amudat</option>
                                    <option value="Napak">Napak</option>
                                    <option value="Abim">Abim</option>
                                </optgroup>
                                <!-- Western Region -->
                                <optgroup label="Western Region">
                                    <option value="Mbarara">Mbarara</option>
                                    <option value="Bushenyi">Bushenyi</option>
                                    <option value="Sheema">Sheema</option>
                                    <option value="Buhweju">Buhweju</option>
                                    <option value="Mitooma">Mitooma</option>
                                    <option value="Rubirizi">Rubirizi</option>
                                    <option value="Ntungamo">Ntungamo</option>
                                    <option value="Isingiro">Isingiro</option>
                                    <option value="Kiruhura">Kiruhura</option>
                                    <option value="Ibanda">Ibanda</option>
                                    <option value="Kabale">Kabale</option>
                                    <option value="Rukungiri">Rukungiri</option>
                                    <option value="Kanungu">Kanungu</option>
                                    <option value="Kisoro">Kisoro</option>
                                    <option value="Kasese">Kasese</option>
                                    <option value="Bundibugyo">Bundibugyo</option>
                                    <option value="Ntoroko">Ntoroko</option>
                                    <option value="Kabarole">Kabarole</option>
                                    <option value="Kyenjojo">Kyenjojo</option>
                                    <option value="Kamwenge">Kamwenge</option>
                                    <option value="Kyegegwa">Kyegegwa</option>
                                    <option value="Hoima">Hoima</option>
                                    <option value="Masindi">Masindi</option>
                                    <option value="Buliisa">Buliisa</option>
                                    <option value="Kiryandongo">Kiryandongo</option>
                                </optgroup>
                                <!-- Other (for international students) -->
                                <optgroup label="Other">
                                    <option value="Other">Other (Outside Uganda)</option>
                                </optgroup>
                            </select>
                            <small class="text-muted">Select your region or district in Uganda</small>
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
                                <option value="">Select Country</option>
                                <option value="Uganda" selected>Uganda</option>
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Brazil">Brazil</option>
                                <option value="Brunei">Brunei</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Cape Verde">Cape Verde</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Chile">Chile</option>
                                <option value="China">China</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo">Congo</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Croatia">Croatia</option>
                                <option value="Cuba">Cuba</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czech Republic">Czech Republic</option>
                                <option value="Democratic Republic of the Congo">Democratic Republic of the Congo</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="East Timor">East Timor</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Greece">Greece</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guinea-Bissau">Guinea-Bissau</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran">Iran</option>
                                <option value="Iraq">Iraq</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Israel">Israel</option>
                                <option value="Italy">Italy</option>
                                <option value="Ivory Coast">Ivory Coast</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Laos">Laos</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libya">Libya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Macedonia">Macedonia</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia">Micronesia</option>
                                <option value="Moldova">Moldova</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montenegro">Montenegro</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="North Korea">North Korea</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Palestine">Palestine</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Romania">Romania</option>
                                <option value="Russia">Russia</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Serbia">Serbia</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia">Slovakia</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Korea">South Korea</option>
                                <option value="South Sudan">South Sudan</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Swaziland">Swaziland</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syria">Syria</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania">Tanzania</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Togo">Togo</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Vatican City">Vatican City</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
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

                <!-- Section 3B: Sponsor Information (Optional) -->
                <div class="section-header">
                    <h4><i class="fas fa-hand-holding-usd me-2"></i>Section 3B: Sponsor Information <span class="badge bg-secondary">Optional</span></h4>
                </div>
                <div class="form-section">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_self_sponsored" 
                                       id="selfSponsored" value="1" onchange="toggleSponsorFields()">
                                <label class="form-check-label" for="selfSponsored">
                                    <strong>I am self-sponsored</strong> (Check this if you're paying for yourself)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="sponsorFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sponsor Name</label>
                                <input type="text" class="form-control" name="sponsor_name" id="sponsorName">
                                <small class="text-muted">Person or organization paying your fees</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Relationship to Sponsor</label>
                                <select class="form-select" name="sponsor_relationship" id="sponsorRelationship">
                                    <option value="">Select Relationship</option>
                                    <option value="Parent">Parent</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Relative">Relative</option>
                                    <option value="Organization">Organization/Company</option>
                                    <option value="Government">Government Scholarship</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sponsor Phone Number</label>
                                <input type="tel" class="form-control" name="sponsor_phone" id="sponsorPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sponsor Email</label>
                                <input type="email" class="form-control" name="sponsor_email" id="sponsorEmail">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Previous Education -->
                <div class="section-header">
                    <h4><i class="fas fa-school me-2"></i>Section 4: Previous Education</h4>
                </div>
                <div class="form-section">
                    <!-- Student Type Selection -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label required">Student Type</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <input type="radio" class="form-check-input" name="student_type" 
                                                   id="localStudent" value="local" checked 
                                                   onchange="toggleEducationFields()">
                                            <label class="form-check-label ms-2" for="localStudent">
                                                <strong>Local Student (Ugandan)</strong>
                                            </label>
                                            <p class="text-muted small mt-2">Completed O'Level and A'Level in Uganda</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <input type="radio" class="form-check-input" name="student_type" 
                                                   id="internationalStudent" value="international" 
                                                   onchange="toggleEducationFields()">
                                            <label class="form-check-label ms-2" for="internationalStudent">
                                                <strong>International Student</strong>
                                            </label>
                                            <p class="text-muted small mt-2">Completed education outside Uganda</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Local Student Fields -->
                    <div id="localStudentFields">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>For Local Students:</strong> Please provide your O'Level and A'Level details
                        </div>
                        
                        <!-- O'Level Information -->
                        <h5 class="text-primary mb-3"><i class="fas fa-certificate me-2"></i>O'Level Information</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">O'Level School</label>
                                <input type="text" class="form-control" name="olevel_school" id="olevelSchool" 
                                       placeholder="Name of your O'Level school">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Completion Year</label>
                                <select class="form-select" name="olevel_completion_year" id="olevelYear">
                                    <option value="">Select Year</option>
                                    <?php for($year = date('Y'); $year >= 2010; $year--): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Index Number</label>
                                <input type="text" class="form-control" name="olevel_index_number" id="olevelIndex" 
                                       placeholder="O'Level index number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">O'Level Certificate</label>
                                <input type="file" class="form-control" name="olevel_certificate" id="olevelCert" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload your O'Level certificate (PDF, JPG, PNG)</small>
                            </div>
                        </div>

                        <!-- A'Level Information -->
                        <h5 class="text-primary mb-3 mt-4"><i class="fas fa-graduation-cap me-2"></i>A'Level Information</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">A'Level School</label>
                                <input type="text" class="form-control" name="alevel_school" id="alevelSchool" 
                                       placeholder="Name of your A'Level school">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Completion Year</label>
                                <select class="form-select" name="alevel_completion_year" id="alevelYear">
                                    <option value="">Select Year</option>
                                    <?php for($year = date('Y'); $year >= 2012; $year--): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Index Number</label>
                                <input type="text" class="form-control" name="alevel_index_number" id="alevelIndex" 
                                       placeholder="A'Level index number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">A'Level Certificate</label>
                                <input type="file" class="form-control" name="alevel_certificate" id="alevelCert" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload your A'Level certificate (PDF, JPG, PNG)</small>
                            </div>
                        </div>
                    </div>

                    <!-- International Student Fields -->
                    <div id="internationalStudentFields" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fas fa-globe"></i> 
                            <strong>For International Students:</strong> Please upload proof of A'Level equivalent completion
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Original Certificate/Transcript</label>
                                <input type="file" class="form-control" name="international_certificate" 
                                       id="internationalCert" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload your original A'Level equivalent certificate</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Translated Certificate (if not in English)</label>
                                <input type="file" class="form-control" name="international_certificate_translated" 
                                       id="translatedCert" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload officially translated version if original is not in English</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Important:</strong> If your certificate is not in English, you must provide an official translation 
                            from a certified translator or embassy. Both original and translated documents are required.
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
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Entry Year</label>
                            <select class="form-select" name="entry_year" required>
                                <option value="">Select Year</option>
                                <option value="2025" selected>2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Intake Month</label>
                            <select class="form-select" name="intake_month" required>
                                <option value="">Select Intake</option>
                                <option value="january">January Intake</option>
                                <option value="august" selected>August Intake</option>
                            </select>
                            <small class="text-muted">When you plan to start your studies</small>
                        </div>
                        <div class="col-md-4 mb-3">
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

                <!-- Section 7: Application Fee Payment -->
                <div class="section-header">
                    <h4><i class="fas fa-money-bill-wave me-2"></i>Section 7: Application Fee Payment</h4>
                </div>
                <div class="form-section">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Application Fee Required</h5>
                        <p class="mb-0">An application fee of <strong>UGX 50,000</strong> is required to process your application.</p>
                    </div>

                    <!-- Payment Status Selection -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label required">Payment Status</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <input type="radio" class="form-check-input" name="payment_status" 
                                                   id="paymentCompleted" value="completed" 
                                                   onchange="togglePaymentFields()" required>
                                            <label class="form-check-label ms-2" for="paymentCompleted">
                                                <strong>I have already paid</strong>
                                            </label>
                                            <p class="text-muted small mt-2">Upload your proof of payment</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <input type="radio" class="form-check-input" name="payment_status" 
                                                   id="paymentPending" value="pending" 
                                                   onchange="togglePaymentFields()" required>
                                            <label class="form-check-label ms-2" for="paymentPending">
                                                <strong>I will pay now</strong>
                                            </label>
                                            <p class="text-muted small mt-2">View payment instructions</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Proof of Payment (if already paid) -->
                    <div id="proofOfPaymentSection" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Great!</strong> Please upload your proof of payment below.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Proof of Payment</label>
                                <input type="file" class="form-control" name="payment_proof" 
                                       id="paymentProof" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload receipt/screenshot (PDF, JPG, PNG - Max 5MB)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transaction Reference Number</label>
                                <input type="text" class="form-control" name="transaction_reference" 
                                       id="transactionRef" placeholder="e.g., TXN123456789">
                                <small class="text-muted">Optional: Enter the transaction reference if available</small>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions (if not yet paid) -->
                    <div id="paymentInstructionsSection" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Payment Instructions:</strong> Follow the steps below to complete your payment.
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-university me-2"></i>Bank Payment Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Bank Name:</strong> Stanbic Bank Uganda</p>
                                        <p><strong>Account Name:</strong> University Admissions Office</p>
                                        <p><strong>Account Number:</strong> 9030012345678</p>
                                        <p><strong>Branch:</strong> Kampala Main Branch</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Swift Code:</strong> SBICUGKX</p>
                                        <p><strong>Currency:</strong> UGX (Uganda Shillings)</p>
                                        <p><strong>Amount:</strong> <span class="text-danger fw-bold">UGX 50,000</span></p>
                                        <p><strong>Reference:</strong> Your Full Name + "Application Fee"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Mobile Money Payment</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary"><i class="fas fa-phone me-2"></i>MTN Mobile Money</h6>
                                        <ol class="small">
                                            <li>Dial <strong>*165#</strong></li>
                                            <li>Select <strong>4. Payments</strong></li>
                                            <li>Select <strong>3. Pay Bill</strong></li>
                                            <li>Enter Business Number: <strong>123456</strong></li>
                                            <li>Enter Amount: <strong>50000</strong></li>
                                            <li>Enter Reference: <strong>Your Full Name</strong></li>
                                            <li>Enter PIN to confirm</li>
                                        </ol>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-danger"><i class="fas fa-phone me-2"></i>Airtel Money</h6>
                                        <ol class="small">
                                            <li>Dial <strong>*185#</strong></li>
                                            <li>Select <strong>5. Make Payments</strong></li>
                                            <li>Select <strong>1. Pay Bill</strong></li>
                                            <li>Enter Business Number: <strong>654321</strong></li>
                                            <li>Enter Amount: <strong>50000</strong></li>
                                            <li>Enter Reference: <strong>Your Full Name</strong></li>
                                            <li>Enter PIN to confirm</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-circle me-2"></i>Important Notes:</h6>
                            <ul class="mb-0">
                                <li>Keep your payment receipt/confirmation message safe</li>
                                <li>After payment, you can upload the proof of payment by editing your application</li>
                                <li>Your application will be processed once payment is verified</li>
                                <li>Payment verification may take 1-2 business days</li>
                                <li>For payment issues, contact: <strong>admissions@university.ac.ug</strong> or call <strong>+256-XXX-XXXXXX</strong></li>
                            </ul>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_later_acknowledged" 
                                   id="paymentLaterAck" required>
                            <label class="form-check-label" for="paymentLaterAck">
                                <strong>I acknowledge that I need to complete the payment and upload proof before my application can be processed.</strong>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section 8: Terms & Conditions -->
                <div class="section-header">
                    <h4><i class="fas fa-file-contract me-2"></i>Section 8: Terms & Conditions</h4>
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
    <script src="assets/script.js"></script>
    <script>
        // Photo preview function
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Update ID placeholder based on ID type
        function updateIdPlaceholder() {
            const idType = document.getElementById('idType').value;
            const idNumber = document.getElementById('idNumber');
            const idLabel = document.getElementById('idLabel');
            const idHelp = document.getElementById('idHelp');
            
            switch(idType) {
                case 'national_id':
                    idLabel.textContent = 'National ID Number';
                    idNumber.placeholder = 'e.g., CM12345678901234';
                    idHelp.textContent = 'Enter your National ID number';
                    break;
                case 'passport':
                    idLabel.textContent = 'Passport Number';
                    idNumber.placeholder = 'e.g., A12345678';
                    idHelp.textContent = 'Enter your Passport number';
                    break;
                case 'refugee_id':
                    idLabel.textContent = 'Refugee ID Number';
                    idNumber.placeholder = 'e.g., REF123456789';
                    idHelp.textContent = 'Enter your Refugee ID number';
                    break;
            }
        }

        // Toggle sponsor fields
        function toggleSponsorFields() {
            const isSelfSponsored = document.getElementById('selfSponsored').checked;
            const sponsorFields = document.getElementById('sponsorFields');
            const sponsorInputs = sponsorFields.querySelectorAll('input, select');
            
            if (isSelfSponsored) {
                sponsorFields.style.display = 'none';
                sponsorInputs.forEach(input => {
                    input.removeAttribute('required');
                    input.value = '';
                });
            } else {
                sponsorFields.style.display = 'block';
            }
        }

        // Toggle payment fields based on payment status
        function togglePaymentFields() {
            const isPaid = document.getElementById('paymentCompleted').checked;
            const proofSection = document.getElementById('proofOfPaymentSection');
            const instructionsSection = document.getElementById('paymentInstructionsSection');
            const paymentProof = document.getElementById('paymentProof');
            const paymentLaterAck = document.getElementById('paymentLaterAck');
            
            if (isPaid) {
                // Show proof upload section
                proofSection.style.display = 'block';
                instructionsSection.style.display = 'none';
                
                // Make proof of payment required
                if (paymentProof) {
                    paymentProof.setAttribute('required', 'required');
                }
                
                // Remove required from acknowledgment checkbox
                if (paymentLaterAck) {
                    paymentLaterAck.removeAttribute('required');
                }
            } else {
                // Show payment instructions
                proofSection.style.display = 'none';
                instructionsSection.style.display = 'block';
                
                // Remove required from proof of payment
                if (paymentProof) {
                    paymentProof.removeAttribute('required');
                    paymentProof.value = '';
                }
                
                // Make acknowledgment checkbox required
                if (paymentLaterAck) {
                    paymentLaterAck.setAttribute('required', 'required');
                }
            }
        }

        // Toggle education fields based on student type
        function toggleEducationFields() {
            const isLocal = document.getElementById('localStudent').checked;
            const localFields = document.getElementById('localStudentFields');
            const internationalFields = document.getElementById('internationalStudentFields');
            const localInputs = localFields.querySelectorAll('input, select');
            const internationalInputs = internationalFields.querySelectorAll('input, select');
            
            if (isLocal) {
                // Show local fields, hide international
                localFields.style.display = 'block';
                internationalFields.style.display = 'none';
                
                // Make local fields required
                localInputs.forEach(input => {
                    if (input.id && (input.id.includes('olevel') || input.id.includes('alevel'))) {
                        input.setAttribute('required', 'required');
                    }
                });
                
                // Remove required from international fields
                internationalInputs.forEach(input => {
                    input.removeAttribute('required');
                    input.value = '';
                });
            } else {
                // Show international fields, hide local
                localFields.style.display = 'none';
                internationalFields.style.display = 'block';
                
                // Make international certificate required
                const intCert = document.getElementById('internationalCert');
                if (intCert) {
                    intCert.setAttribute('required', 'required');
                }
                
                // Remove required from local fields
                localInputs.forEach(input => {
                    input.removeAttribute('required');
                    input.value = '';
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateIdPlaceholder();
            toggleEducationFields(); // Initialize education fields
        });
    </script>
</body>
</html>

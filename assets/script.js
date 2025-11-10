/* ============================================
   STUDENT MANAGEMENT SYSTEM - GENERAL SCRIPTS
   ============================================ */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tabs
    initializeTabs();
    
    // Initialize modals if they exist
    initializeModals();
    
    // Initialize password strength indicators
    initializePasswordStrength();
    
    // Initialize age calculator
    initializeAgeCalculator();
    
    // Initialize dynamic dropdowns
    initializeDynamicDropdowns();
    
    // Initialize course selection counter
    initializeCourseSelection();
});

/* ============================================
   TAB INITIALIZATION
   ============================================ */
function initializeTabs() {
    var triggerTabList = [].slice.call(document.querySelectorAll('a[data-bs-toggle="tab"]'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
}

/* ============================================
   MODAL INITIALIZATION
   ============================================ */

// Edit User Modal
function initializeEditUserModal() {
    var editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            var firstName = button.getAttribute('data-firstname');
            var lastName = button.getAttribute('data-lastname');
            var email = button.getAttribute('data-email');
            var role = button.getAttribute('data-role');
            
            var modal = this;
            if (modal.querySelector('#edit_user_id')) {
                modal.querySelector('#edit_user_id').value = userId;
                modal.querySelector('#edit_username').value = username;
                modal.querySelector('#edit_first_name').value = firstName;
                modal.querySelector('#edit_last_name').value = lastName;
                modal.querySelector('#edit_email').value = email;
                modal.querySelector('#edit_role').value = role;
            }
        });
    }
}

// Change Password Modal
function initializeChangePasswordModal() {
    var changePasswordModal = document.getElementById('changePasswordModal');
    if (changePasswordModal) {
        changePasswordModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            
            var modal = this;
            if (modal.querySelector('#password_user_id')) {
                modal.querySelector('#password_user_id').value = userId;
                modal.querySelector('#password_username').value = username;
            }
        });
    }
}

// Edit Subject Modal
function initializeEditSubjectModal() {
    var editSubjectModal = document.getElementById('editSubjectModal');
    if (editSubjectModal) {
        editSubjectModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var subjectId = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var code = button.getAttribute('data-code');
            var description = button.getAttribute('data-description');
            
            var modal = this;
            if (modal.querySelector('#edit_subject_id')) {
                modal.querySelector('#edit_subject_id').value = subjectId;
                modal.querySelector('#edit_subject_name').value = name;
                modal.querySelector('#edit_subject_code').value = code;
                modal.querySelector('#edit_subject_description').value = description;
            }
        });
    }
}

function initializeModals() {
    initializeEditUserModal();
    initializeChangePasswordModal();
    initializeEditSubjectModal();
}

/* ============================================
   PASSWORD TOGGLE FUNCTIONALITY
   ============================================ */
function togglePassword(passwordFieldId, iconId) {
    var passwordField = document.getElementById(passwordFieldId);
    var icon = document.getElementById(iconId);
    
    if (passwordField && icon) {
        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
}

// Make togglePassword available globally
window.togglePassword = togglePassword;

/* ============================================
   PASSWORD STRENGTH INDICATOR
   ============================================ */
function initializePasswordStrength() {
    var passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            
            const strengthBar = document.getElementById('passwordStrength');
            if (strengthBar) {
                strengthBar.className = 'password-strength';
                
                if (strength <= 2) {
                    strengthBar.classList.add('strength-weak');
                } else if (strength <= 4) {
                    strengthBar.classList.add('strength-medium');
                } else {
                    strengthBar.classList.add('strength-strong');
                }
            }
        });
    }
}

function checkPasswordStrength(password) {
    var strength = 0;
    if (password.length >= 6) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    return strength;
}

/* ============================================
   AGE CALCULATOR
   ============================================ */
function initializeAgeCalculator() {
    var dobInput = document.querySelector('[name="date_of_birth"]');
    if (dobInput) {
        dobInput.addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            // Adjust age if birthday hasn't occurred this year
            const actualAge = monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate()) 
                ? age - 1 
                : age;
            
            var ageDisplay = document.getElementById('ageDisplay');
            if (ageDisplay) {
                ageDisplay.textContent = 'Age: ' + actualAge + ' years';
            }
        });
    }
}

/* ============================================
   DYNAMIC DROPDOWNS (School -> Department -> Program)
   ============================================ */
function initializeDynamicDropdowns() {
    var schoolSelect = document.getElementById('schoolSelect');
    var departmentSelect = document.getElementById('departmentSelect');
    var programSelect = document.getElementById('programSelect');
    
    if (schoolSelect) {
        schoolSelect.addEventListener('change', function() {
            const schoolId = this.value;
            
            if (departmentSelect) {
                departmentSelect.disabled = true;
                departmentSelect.innerHTML = '<option value="">Loading...</option>';
                
                if (schoolId) {
                    fetch('api/get_departments.php?school_id=' + schoolId)
                        .then(response => response.json())
                        .then(data => {
                            departmentSelect.innerHTML = '<option value="">Select Department</option>';
                            data.forEach(dept => {
                                departmentSelect.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
                            });
                            departmentSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error loading departments:', error);
                            departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
                        });
                } else {
                    departmentSelect.innerHTML = '<option value="">Select Department</option>';
                    if (programSelect) {
                        programSelect.disabled = true;
                        programSelect.innerHTML = '<option value="">Select Program</option>';
                    }
                }
            }
        });
    }
    
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            const deptId = this.value;
            
            if (programSelect) {
                programSelect.disabled = true;
                programSelect.innerHTML = '<option value="">Loading...</option>';
                
                if (deptId) {
                    fetch('api/get_programs.php?department_id=' + deptId)
                        .then(response => response.json())
                        .then(data => {
                            programSelect.innerHTML = '<option value="">Select Program</option>';
                            data.forEach(prog => {
                                programSelect.innerHTML += `<option value="${prog.id}" 
                                    data-duration="${prog.duration_years}" 
                                    data-credits="${prog.total_credits}" 
                                    data-tuition="${prog.tuition_per_semester}">
                                    ${prog.name}
                                </option>`;
                            });
                            programSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error loading programs:', error);
                            programSelect.innerHTML = '<option value="">Error loading programs</option>';
                        });
                } else {
                    programSelect.innerHTML = '<option value="">Select Program</option>';
                }
            }
        });
    }
    
    // Show program details when program is selected
    if (programSelect) {
        programSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            var programDetails = document.getElementById('programDetails');
            
            if (programDetails) {
                if (this.value) {
                    var duration = document.getElementById('duration');
                    var credits = document.getElementById('credits');
                    var tuition = document.getElementById('tuition');
                    
                    if (duration) duration.textContent = selected.dataset.duration;
                    if (credits) credits.textContent = selected.dataset.credits;
                    if (tuition) tuition.textContent = selected.dataset.tuition;
                    
                    programDetails.style.display = 'block';
                } else {
                    programDetails.style.display = 'none';
                }
            }
        });
    }
}

/* ============================================
   COURSE SELECTION COUNTER
   ============================================ */
function initializeCourseSelection() {
    var courseCheckboxes = document.querySelectorAll('input[name="courses[]"]');
    if (courseCheckboxes.length > 0) {
        courseCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selected = document.querySelectorAll('input[name="courses[]"]:checked').length;
                console.log('Selected courses:', selected);
                // You can add more functionality here, like showing total credits
            });
        });
    }
}

/* ============================================
   CHART.JS INITIALIZATION (for grade charts)
   ============================================ */
function initializeGradeChart() {
    var gradeChartCanvas = document.getElementById('gradeChart');
    if (gradeChartCanvas && typeof Chart !== 'undefined') {
        var ctx = gradeChartCanvas.getContext('2d');
        var gradeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Quiz 1', 'Assignment 1', 'Exam 1', 'Quiz 2', 'Project'],
                datasets: [{
                    label: 'Grades',
                    data: [85, 92, 78, 88, 95],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 50,
                        max: 100
                    }
                }
            }
        });
    }
}

// Initialize chart if Chart.js is loaded
if (typeof Chart !== 'undefined') {
    document.addEventListener('DOMContentLoaded', initializeGradeChart);
}


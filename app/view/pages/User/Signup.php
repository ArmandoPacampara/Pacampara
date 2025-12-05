<?php
session_start();
// Adjust paths to match your project structure (app/view/pages/User/)
require_once '../../../../app/core/db.php';
require_once '../../../../app/core/Logger.php'; 
require_once '../../../../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure database connection is established
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $phone = $_POST['phone-number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $confirm = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Passwords do not match.");
    }

    $check = $con->prepare("SELECT * FROM users WHERE user_email = ?"); 
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        Logger::log($con, 0, "SIGNUP_FAILED", "Attempted signup with existing email: $email");
        die("Email already exists.");
    }

    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['signup_data'] = [
        'name' => $name,
        'province' => $province,
        'address' => $address,
        'city' => $city,
        'phone' => $phone,
        'email' => $email,
        'password' => $password
    ];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'austrianeon@gmail.com'; 
        $mail->Password   = 'nghr kpmt blck nkwg'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('austrianeon@gmail.com', 'MoviEase');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'MoviEase Email Verification OTP';
        $mail->Body    = "<h2>Hi $name!</h2><p>Your OTP is: <b>$otp</b></p><p>Enter this code to complete your signup.</p>";

        $mail->send();
        
        Logger::log($con, 0, "SIGNUP_INITIATED", "OTP sent to potential new user: $email");

        header("Location: VerifyOTP.php");
        exit;
    } catch (Exception $e) {
        echo "Error sending OTP: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>MoviEase Signup</title>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../../../../public/styles/css/Signup.css"> 
    
    <style>
        /* Extra styles for the checkbox link */
        .privacy-row {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 92%;
            margin-bottom: 20px;
            padding-left: 5px;
            font-size: 14px;
            color: #333;
        }

        .privacy-row input[type="checkbox"] {
            accent-color: #a31212;
            width: 16px;
            height: 16px;
            margin-right: 10px;
            cursor: pointer;
        }

        .privacy-link {
            color: #a31212;
            text-decoration: underline;
            cursor: pointer;
            font-weight: bold;
        }

        .privacy-link:hover {
            color: #870e0e;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="poster-container">
            <img src="https://cdn.myanimelist.net/images/anime/1806/126216.jpg" alt="Chainsaw Man Poster">
        </div>

        <div class="container">
            <div class="logo">
                <img src="../../../../public/assets/images/movies_icon.png" alt="logo">
                <h1>MoviEase</h1>
            </div>

            <h2>Sign Up your Account</h2>
            <div class="divider"></div>

            <form id="signupForm" method="POST" action="" enctype="multipart/form-data">
                <center>
                    <div class="container2">
                        <div class="profile-upload">
                            <label for="profileImageInput">
                                <img id="profilePreview" src="../../../../public/assets/images/default_user.png" alt="Profile Preview">
                                <div class="upload-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </label>
                            <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                        </div>

                        <div class="input-group">
                            <img src="../../../../public/assets/images/profile_icon.png" alt="profile_icon" class="input-icon">
                            <input id="nameInput" type="text" placeholder="Name" required name="name">
                        </div>
                        
                        <div class="input-group">
                            <img src="../../../../public/assets/images/location_icon.png" alt="location_icon" class="input-icon">
                            <button type="button" class="genre-select-display" id="locationSelectButton" onclick="openLocationPopup()">
                                <span id="locationDisplayField">Select Location...</span>
                                <i class="fas fa-chevron-down"></i> 
                            </button>
                        </div>
                        <div class="input-group">
                            <img src="../../../../public/assets/images/phone_icon.png" alt="phone_icon" class="input-icon">
                            <input id="phoneInput" type="text" placeholder="Phone Number" required name="phone-number">
                        </div>
                    </div>
                    
                    <div id="locationPopup" class="genre-popup">
                        <div class="genre-popup-content">
                            <h3>Select Your Location</h3>
                            <div class="location-selectors">
                                <label for="islandSelect" class="location-label">Island Group:</label>
                                <select id="islandSelect" onchange="populateProvincesFromIsland()" required>
                                    <option value="" disabled selected>Select Island Group</option>
                                    <option value="Luzon">Luzon</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                </select>
                                <label for="provinceSelect" class="location-label">Province:</label>
                                <select id="provinceSelect" onchange="populateCities()" required>
                                    <option value="" disabled selected>Select Province</option>
                                </select>
                                
                                <label for="citySelect" class="location-label">City/Municipality:</label>
                                <select id="citySelect" onchange="populateBarangays()" disabled required>
                                    <option value="" disabled selected>Select City/Municipality</option>
                                </select>
                                
                                <label for="barangaySelect" class="location-label">Barangay:</label>
                                <select id="barangaySelect" disabled required>
                                    <option value="" disabled selected>Select Barangay</option>
                                </select>
                                
                                <input type="hidden" id="provinceInput" name="province">
                            </div>
                            <div class="popup-buttons">
                                <button type="button" class="close-popup-btn" onclick="saveLocationAndClose()">Set Location</button>
                            </div>
                        </div>
                    </div>

                    <div class="dots" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <div class="button-container">
                        <button type="button" class="next-btn" onclick="goToStep2()">NEXT</button>
                    </div>
                </center>
            </form>
        </div>
    </div>

    <div id="errorModal" class="error-modal">
        <div class="error-modal-content">
            <h3>Error</h3>
            <ul id="errorList"></ul>
            <button onclick="closeErrorModal()" class="error-modal-btn">Close</button>
        </div>
    </div>

    <div id="privacyModal" class="error-modal">
        <div class="error-modal-content">
            <h3>Data Privacy Consent</h3>
            <div style="margin-bottom: 20px; font-size: 13px; color: #555; line-height: 1.6; max-height: 250px; overflow-y: auto; text-align: justify; padding-right: 5px;">
                <p>By proceeding with this registration, you explicitly consent to the collection, processing, and storage of your personal data by <strong>MoviEase</strong> in accordance with the <strong>Data Privacy Act of 2012 (R.A. 10173)</strong>.</p>
                <br>
                <p><strong>We collect the following:</strong></p>
                <ul style="list-style-type: disc; margin-left: 20px; margin-bottom: 10px;">
                    <li>Personal details (Name, Age, Contact Number)</li>
                    <li>Account credentials (Email, Password)</li>
                    <li>Location and Movie Preferences</li>
                </ul>
                <p><strong>Purpose:</strong> Your data will be used solely for account verification, processing ticket bookings, and providing personalized movie recommendations.</p>
                <br>
                <p>Your information is secure and will never be shared with third parties without your permission.</p>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="closePrivacyModal()" style="padding: 10px 20px; border-radius: 5px; border: 1px solid #ccc; background: #fff; cursor: pointer; color: #555; font-weight: bold;">Cancel</button>
                <button onclick="acceptPrivacy()" style="padding: 10px 20px; border-radius: 5px; border: none; background: #a31212; color: white; font-weight: bold; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#870e0e'" onmouseout="this.style.background='#a31212'">I Agree</button>
            </div>
        </div>
    </div>

<script>
    let currentStep = 1;
    let formData = {};

    // --- LOCATION DATA (Truncated for brevity, same as before) ---
    const locationData = {
        "Cebu": { "Cebu City": ["Lahug", "Banilad"], "Lapu-Lapu City": ["Pajo", "Pusok"] },
        "Metro Manila": { "Quezon City": ["Cubao", "Project 4"], "Manila": ["Ermita", "Intramuros"] },
        // ... (Keep your full list here)
    };

    // --- HELPER FUNCTIONS ---
    function saveStepData() {
        if (currentStep === 1) {
            formData.name = document.getElementById('nameInput') ? document.getElementById('nameInput').value : '';
            formData.province = document.getElementById('provinceInput') ? document.getElementById('provinceInput').value : ''; 
            formData.phone = document.getElementById('phoneInput') ? document.getElementById('phoneInput').value : '';
        } else if (currentStep === 2) {
            formData.birthdate = document.getElementById('birthdate') ? document.getElementById('birthdate').value : '';
            formData.age = document.getElementById('age') ? document.getElementById('age').value : '';
            formData.genres = Array.from(document.querySelectorAll('input[name="popupGenre"]:checked')).map(i => i.value);
        } else if (currentStep === 3) {
            formData.email = document.getElementById('emailInput') ? document.getElementById('emailInput').value : '';
            formData.password = document.getElementById('passwordInput') ? document.getElementById('passwordInput').value : '';
            formData.confirmPassword = document.getElementById('confirmPasswordInput') ? document.getElementById('confirmPasswordInput').value : '';
            // Don't need to save checkbox state as it's the final action
        }
    }

    function loadStepData() {
        if (currentStep === 1) {
            if (document.getElementById('nameInput') && formData.name) document.getElementById('nameInput').value = formData.name;
            if (document.getElementById('phoneInput') && formData.phone) document.getElementById('phoneInput').value = formData.phone;
            const locationDisplayField = document.getElementById('locationDisplayField');
            if (locationDisplayField && formData.province) locationDisplayField.textContent = formData.province;

        } else if (currentStep === 2) {
            if (document.getElementById('birthdate') && formData.birthdate) document.getElementById('birthdate').value = formData.birthdate;
            if (document.getElementById('age') && formData.age) document.getElementById('age').value = formData.age;
            if (formData.genres) {
                const checkboxes = document.querySelectorAll('input[name="popupGenre"]');
                checkboxes.forEach(cb => { cb.checked = formData.genres.includes(cb.value); });
                updateGenreField(); 
            }

        } else if (currentStep === 3) {
            if (document.getElementById('emailInput') && formData.email) document.getElementById('emailInput').value = formData.email;
            if (document.getElementById('passwordInput') && formData.password) document.getElementById('passwordInput').value = formData.password;
            if (document.getElementById('confirmPasswordInput') && formData.confirmPassword) document.getElementById('confirmPasswordInput').value = formData.confirmPassword;
        }
    }

    // --- STEP CONTENT STRINGS ---
    
    // Step 1 is static HTML above
    const step1Content = `
            <div class="profile-upload">
                <label for="profileImageInput">
                    <img id="profilePreview" src="../../../../public/assets/images/default_user.png" alt="Profile Preview">
                    <div class="upload-overlay"><i class="fas fa-camera"></i></div>
                </label>
                <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
            </div>
            <div class="input-group">
                <img src="../../../../public/assets/images/profile_icon.png" class="input-icon">
                <input id="nameInput" type="text" placeholder="Name" required name="name">
            </div>
            <div class="input-group">
                <img src="../../../../public/assets/images/location_icon.png" class="input-icon">
                <button type="button" class="genre-select-display" onclick="openLocationPopup()">
                    <span id="locationDisplayField">Select Location...</span><i class="fas fa-chevron-down"></i> 
                </button>
            </div>
            <div class="input-group">
                <img src="../../../../public/assets/images/phone_icon.png" class="input-icon">
                <input id="phoneInput" type="tel" placeholder="Phone Number" required name="phone-number" pattern="[0-9]{10,12}">
            </div>
    `;

    const step2Content = `
    <div class="genre-group">
        <label class="genre-label">Your Favorite Genres:</label>
        <div class="input-group genre-input-group">
            <img src="../../../../public/assets/images/movies_icon.png" class="input-icon">
            <button type="button" class="genre-select-display" id="genreSelectButton" onclick="openGenrePopup()">
                <span id="genreTextField">Select genres...</span><i class="fas fa-chevron-down"></i> 
            </button>
        </div>
    </div>
    <div id="genrePopup" class="genre-popup">
        <div class="genre-popup-content">
        <h3>Select Genres</h3>
        <div class="genres-container-popup">
            ${["Action","Adventure","Comedy","Drama","Horror","Romance","Sci-Fi","Fantasy","Thriller","Animation"].map(g => `
            <label class="popup-item"><input type="checkbox" name="popupGenre" value="${g}" onchange="updateGenreField()"> ${g}</label>
            `).join("")}
        </div>
        <div class="popup-buttons"><button type="button" class="close-popup-btn" onclick="closeGenrePopup()">Done</button></div>
        </div>
    </div>
    <div class="input-group">
        <img src="../../../../public/assets/images/profile_icon.png" class="input-icon">
        <input id="birthdate" type="date" required>
    </div>
    <div class="input-group">
        <img src="../../../../public/assets/images/profile_icon.png" class="input-icon">
        <input id="age" type="number" readonly>
    </div>
            <div class="input-group">
            <img src="../../../../public/assets/images/mail_icon.png" class="input-icon">
            <input id="emailInput" type="email" placeholder="Email" required name="email">
        </div>
    `;

    // UPDATED STEP 3: Now includes the Checkbox and Link
    const step3Content = `
    <div class="step3-wrapper">
        <div class="input-group password-group"> 
            <img src="../../../../public/assets/images/lock_icon.png" class="input-icon">
            <input id="passwordInput" type="password" placeholder="Password" required name="password">
            <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('passwordInput')"></i>
        </div>
        <div class="input-group password-group"> 
            <img src="../../../../public/assets/images/lock_icon.png" class="input-icon">
            <input id="confirmPasswordInput" type="password" placeholder="Confirm Password" required name="confirm_password">
            <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('confirmPasswordInput')"></i>
        </div>
        
        <!-- Password Strength Indicator -->
        <div class="password-strength-container">
            <div class="strength-bar-bg">
                <div id="strengthBar" class="strength-bar"></div>
            </div>
            <p id="strengthText" class="strength-text"></p>
            <div class="requirements-list">
                <div id="req-length" class="requirement unchecked">
                    <i class="fas fa-times"></i> At least 8 characters
                </div>
                <div id="req-number" class="requirement unchecked">
                    <i class="fas fa-times"></i> At least 1 number
                </div>
                <div id="req-lowercase" class="requirement unchecked">
                    <i class="fas fa-times"></i> At least 1 lowercase letter
                </div>
                <div id="req-uppercase" class="requirement unchecked">
                    <i class="fas fa-times"></i> At least 1 uppercase letter
                </div>
                <div id="req-special" class="requirement unchecked">
                    <i class="fas fa-times"></i> At least 1 special character
                </div>
            </div>
        </div>

        <div class="privacy-row">
            <input type="checkbox" id="privacyCheck">
            <span>I have read and agree to the <span class="privacy-link" onclick="showPrivacyModal()">Data Privacy Consent</span></span>
        </div>
    </div>
    `;

    // --- NAVIGATION FUNCTIONS ---
    function updateDots() {
        document.querySelectorAll('.dots span').forEach((dot, i) => dot.style.opacity = (i + 1 === currentStep) ? '1' : '0.6');
    }

    function updateButton() {
        const btn = document.querySelector('.button-container');
        if (currentStep === 1) btn.innerHTML = '<button type="button" class="next-btn" onclick="goToStep2()">NEXT</button>';
        else if (currentStep === 2) btn.innerHTML = '<button type="button" class="back-btn" onclick="goToStep1()">BACK</button><button type="button" class="next-btn" onclick="goToStep3()">NEXT</button>';
        else if (currentStep === 3) btn.innerHTML = '<button type="button" class="back-btn" onclick="goToStep2()">BACK</button><button type="button" class="signup-btn" onclick="validateAndSubmit()">SIGN UP</button>';
    }

    function goToStep2() {
        saveStepData();
        transitionStep(step2Content, 2, () => { attachAgeCalculator(); });
    }
    function goToStep1() {
        saveStepData();
        transitionStep(step1Content, 1, () => { attachProfileImageHandlerIfNeeded(); });
    }
    function goToStep3() {
        saveStepData();
        transitionStep(step3Content, 3, attachPasswordStrengthChecker); // Change null to attachPasswordStrengthChecker
    }

    function transitionStep(content, step, callback) {
        const c2 = document.querySelector('.container2');
        c2.style.opacity = '0';
        c2.style.transform = (step > currentStep) ? 'translateX(-20px)' : 'translateX(20px)';
        setTimeout(() => {
            c2.innerHTML = content;
            currentStep = step;
            updateDots();
            updateButton();
            loadStepData();
            c2.style.opacity = '1';
            c2.style.transform = 'translateX(0)';
            if(callback) callback();
        }, 300);
    }

    // --- VALIDATION AND SUBMIT ---
    function validateAndSubmit() {
        const errors = [];
        
        // Re-validate Step 1 & 2 data from memory
        const name = formData.name || document.getElementById("nameInput")?.value;
        const location = formData.province || document.getElementById("provinceInput")?.value;
        const phone = formData.phone || document.getElementById("phoneInput")?.value;
        if (!name || name.trim().length < 2) errors.push("Please enter a valid name.");
        if (!location) errors.push("Please select your complete location.");
        if (!phone || !/^[0-9]{10,12}$/.test(phone)) errors.push("Phone number must be 10-12 digits.");

        const birthdate = formData.birthdate || document.getElementById("birthdate")?.value;
        const age = formData.age || document.getElementById("age")?.value;
        if (!birthdate) errors.push("Please select your birthdate.");
        if (!age || age < 5) errors.push("Your age is invalid.");
        if (!formData.genres || formData.genres.length === 0) errors.push("Please choose at least one genre.");

        // Validate Step 3 (Current)
        const email = document.getElementById("emailInput").value;
        const password = document.getElementById("passwordInput").value;
        const confirmPassword = document.getElementById("confirmPasswordInput").value;
        const privacyChecked = document.getElementById("privacyCheck").checked;

        if (!email || !email.includes("@")) errors.push("Please enter a valid email address.");
        if (!password) errors.push("Password cannot be empty.");
        else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password)) errors.push("Password must be 8+ chars, with uppercase, lowercase, and numbers.");
        if (password !== confirmPassword) errors.push("Passwords do not match.");

        // NEW CHECK: Checkbox validation
        if (!privacyChecked) {
            errors.push("You must agree to the Data Privacy Consent.");
        }

        if (errors.length > 0) {
            showErrorModal(errors);
            return;
        }

        confirmSignup();
    }

    function confirmSignup() {
        const form = document.getElementById("signupForm");
        // Inject hidden inputs for data from previous steps
        const hiddenFields = { 'name': formData.name, 'province': formData.province, 'phone-number': formData.phone };
        for (const [key, value] of Object.entries(hiddenFields)) {
            if (value && !form.querySelector(input[name="${key}"])) {
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
        }
        form.submit();
    }

    // --- MODAL LOGIC ---
    function showPrivacyModal() { document.getElementById("privacyModal").style.display = "flex"; }
    function closePrivacyModal() { document.getElementById("privacyModal").style.display = "none"; }
    
    function acceptPrivacy() {
        // When clicking "I Agree" in the modal:
        const checkbox = document.getElementById("privacyCheck");
        if (checkbox) checkbox.checked = true; // Check the box automatically
        closePrivacyModal();
    }

    function showErrorModal(errors) {
        const list = document.getElementById("errorList");
        list.innerHTML = "";
        errors.forEach(err => {
            const li = document.createElement("li");
            li.textContent = err;
            list.appendChild(li);
        });
        document.getElementById("errorModal").style.display = "flex";
    }
    function closeErrorModal() { document.getElementById("errorModal").style.display = "none"; }

    // --- UTILITIES (Password toggle, location, genre, etc.) ---
    function togglePasswordVisibility(id) {
        const input = document.getElementById(id);
        const icon = document.querySelector(`#${id} + .toggle-password`);
        if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye', 'fa-eye-slash'); } 
        else { input.type = 'password'; icon.classList.replace('fa-eye-slash', 'fa-eye'); }
    }

    function openLocationPopup() { document.getElementById("locationPopup").style.display = "flex"; populateProvinces(); }
    function populateProvinces() {
        const sel = document.getElementById('provinceSelect');
        sel.innerHTML = '<option value="" disabled selected>Select Province</option>';
        for (const p in locationData) { const opt = document.createElement('option'); opt.value = p; opt.textContent = p; sel.appendChild(opt); }
        document.getElementById('citySelect').disabled = true; document.getElementById('barangaySelect').disabled = true;
    }
    function populateCities() {
        const prov = document.getElementById('provinceSelect').value;
        const sel = document.getElementById('citySelect');
        sel.innerHTML = '<option value="" disabled selected>Select City</option>';
        if (prov && locationData[prov]) {
            for (const c in locationData[prov]) { const opt = document.createElement('option'); opt.value = c; opt.textContent = c; sel.appendChild(opt); }
            sel.disabled = false;
        }
        document.getElementById('barangaySelect').disabled = true;
    }
    function populateBarangays() {
        const prov = document.getElementById('provinceSelect').value;
        const city = document.getElementById('citySelect').value;
        const sel = document.getElementById('barangaySelect');
        sel.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
        if (prov && city && locationData[prov][city]) {
            locationData[prov][city].forEach(b => { const opt = document.createElement('option'); opt.value = b; opt.textContent = b; sel.appendChild(opt); });
            sel.disabled = false;
        }
    }
    function saveLocationAndClose() {
        const o = document.getElementById('provinceSelect').value;
        const c = document.getElementById('citySelect').value;
        const b = document.getElementById('barangaySelect').value;
        if (!o || !c || !b) { alert("Incomplete location."); return; }
        const loc = `${o} / ${c} / ${b}`;
        document.getElementById('provinceInput').value = loc;
        document.getElementById('locationDisplayField').textContent = loc;
        formData.province = loc;
        document.getElementById("locationPopup").style.display = "none";
    }

    function openGenrePopup() { document.getElementById("genrePopup").style.display = "flex"; }
    function closeGenrePopup() { document.getElementById("genrePopup").style.display = "none"; }
    function updateGenreField() {
        const checked = Array.from(document.querySelectorAll('input[name="popupGenre"]:checked')).map(i => i.value);
        document.getElementById("genreTextField").textContent = checked.length ? checked.join(" / ") : "Select genres...";
        formData.genres = checked;
    }

    function attachAgeCalculator() {
        const bInput = document.getElementById('birthdate');
        const aInput = document.getElementById('age');
        if (!bInput) return;
        bInput.addEventListener('change', () => {
            formData.birthdate = bInput.value;
            const b = new Date(bInput.value), t = new Date();
            let age = t.getFullYear() - b.getFullYear();
            if (t.getMonth() < b.getMonth() || (t.getMonth()===b.getMonth() && t.getDate()<b.getDate())) age--;
            aInput.value = age; formData.age = age;
        });
        if(formData.birthdate) bInput.dispatchEvent(new Event('change'));
    }
    function attachProfileImageHandlerIfNeeded() {
        const fileInput = document.getElementById('profileImageInput');
        if (fileInput && !fileInput._hasHandler) {
            fileInput.addEventListener('change', (e) => {
                const f = e.target.files[0];
                if(f) document.getElementById('profilePreview').src = URL.createObjectURL(f);
            });
            fileInput._hasHandler = true;
        }
    }

     function attachPasswordStrengthChecker() {
    const passwordInput = document.getElementById('passwordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    
    if (!passwordInput) return;

    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        // Check each requirement
        const hasLength = password.length >= 8;
        const hasNumber = /\d/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasUppercase = /[A-Z]/.test(password);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        // Update requirement indicators
        updateRequirement('req-length', hasLength);
        updateRequirement('req-number', hasNumber);
        updateRequirement('req-lowercase', hasLowercase);
        updateRequirement('req-uppercase', hasUppercase);
        updateRequirement('req-special', hasSpecial);
        
        // Calculate strength
        let strength = 0;
        if (hasLength) strength++;
        if (hasNumber) strength++;
        if (hasLowercase) strength++;
        if (hasUppercase) strength++;
        if (hasSpecial) strength++;
        
        // Update strength bar
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.style.backgroundColor = '#e0e0e0';
            strengthText.textContent = '';
            strengthText.style.color = '#999';
        } else if (strength <= 2) {
            strengthBar.style.width = '33%';
            strengthBar.style.backgroundColor = '#d32f2f';
            strengthText.textContent = 'Weak Password';
            strengthText.style.color = '#d32f2f';
        } else if (strength === 3 || strength === 4) {
            strengthBar.style.width = '66%';
            strengthBar.style.backgroundColor = '#ff9800';
            strengthText.textContent = 'Medium Password';
            strengthText.style.color = '#ff9800';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.style.backgroundColor = '#388e3c';
            strengthText.textContent = 'Strong Password';
            strengthText.style.color = '#388e3c';
        }
    });
    
    // Also check confirm password match
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    this.style.borderColor = '#388e3c';
                } else {
                    this.style.borderColor = '#d32f2f';
                }
            } else {
                this.style.borderColor = '#a31212';
            }
        });
    }
}

function updateRequirement(id, satisfied) {
    const element = document.getElementById(id);
    if (!element) return;
    
    if (satisfied) {
        element.classList.remove('unchecked');
        element.classList.add('checked');
    } else {
        element.classList.remove('checked');
        element.classList.add('unchecked');
    }
}   

    document.addEventListener('DOMContentLoaded', () => {
        updateButton(); updateDots(); attachProfileImageHandlerIfNeeded(); loadStepData();
        const p = document.getElementById('phoneInput');
        if(p) p.addEventListener('input', function() { this.value = this.value.replace(/\D/g, ''); });
    });
</script>
</body>
</html>
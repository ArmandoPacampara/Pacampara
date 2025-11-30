<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>MoviEase Signup</title>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="../../../../public/styles/css/Signup.css"> 
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

            <form id="signupForm" 
                method="POST" 
                action="../../../Controller/SignupController.php"
                enctype="multipart/form-data">

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

        <!-- Error Modal -->
    <div id="errorModal" class="error-modal">
        <div class="error-modal-content">
            <h3>Error</h3>
            <ul id="errorList"></ul>
            <button onclick="closeErrorModal()" class="error-modal-btn">Close</button>
        </div>
    </div>


<script>
    // Multi-step form functionality for MoviEase signup
    let currentStep = 1;

    // DATA PERSISTENCE object to store input values
    let formData = {};

    // ==============================================================
    // NEW LOCATION DATA STRUCTURE FOR CASCADING DROPDOWNS (SAMPLE)
    // ==============================================================
const locationData = {
    "Cebu": {
        "Cebu City": ["Lahug", "Banilad", "Mabolo"],
        "Lapu-Lapu City": ["Pajo", "Pusok", "Maribago"],
        "Danao City": ["Taytay", "Looc"]
    },
    "Rizal": {
        "Antipolo City": ["Dela Paz", "Bagong Nayon", "Cupang"],
        "Taytay": ["San Juan", "Dolores"],
        "Cainta": ["Sto. Domingo", "San Andres"]
    },
    "Davao del Sur": {
        "Davao City": ["Bago Aplaya", "Matina", "Agdao"],
        "Digos City": ["Aplaya", "San Jose"]
    },
    "Metro Manila": {
        "Quezon City": ["Cubao", "Project 4", "Novaliches"],
        "Manila": ["Ermita", "Intramuros", "Malate"],
        "Makati": ["Poblacion", "Bel-Air", "San Lorenzo"]
    },
    "Batangas": {
        "Batangas City": ["Alangilan", "Poblacion", "Sta. Clara"],
        "Lipa City": ["Ayala", "Balintawak", "Poblacion"],
        "Tanauan": ["Talisay", "Mabini", "Poblacion"]
    },
    "Cagayan": {
        "Tuguegarao City": ["Centro 1", "Centro 2", "Bagumbayan"],
        "Ilagan": ["San Vicente", "San Pedro", "Centro"],
        "Aparri": ["Centro Norte", "Centro Sur", "Bagumbayan"]
    },
    "Iloilo": {
        "Iloilo City": ["Mandurriao", "Jaro", "La Paz"],
        "Passi City": ["Zone 1", "Zone 2", "Zone 3"],
        "Dumangas": ["Poblacion", "Malingin", "San Isidro"]
    },
    "Negros Occidental": {
        "Bacolod City": ["Pahanocoy", "Singcang", "Vista Alegre"],
        "Talisay City": ["Zone 1", "Zone 2", "Zone 3"],
        "Silay City": ["E. Lopez", "Poblacion", "Cagay"]
    },
    "Pampanga": {
        "San Fernando": ["San Jose", "San Juan", "Del Pilar"],
        "Angeles City": ["Balibago", "Clark", "Malabanias"],
        "Mabalacat": ["Capas", "Poblacion", "Dela Paz"]
    },
    "Bulacan": {
        "Malolos": ["Poblacion", "Guinhawa", "Tabang"],
        "Meycauayan": ["Banga", "Burgos", "Bagong Buhay"],
        "San Jose del Monte": ["Tungkong Mangga", "Poblacion", "Carmen"]
    },
    "Pangasinan": {
        "Dagupan": ["Bonuan", "Poblacion", "Lucao"],
        "San Carlos": ["Poblacion Norte", "Poblacion Sur", "San Juan"],
        "Alaminos": ["Poblacion", "Lawa", "Lohong"]
    },
    "Zambales": {
        "Olongapo City": ["Burgos", "Mabayuan", "West Bajac-Bajac"],
        "Subic": ["Poblacion", "Alava", "Barretto"],
        "Castillejos": ["Poblacion", "San Agustin", "San Juan"]
    },
    "Leyte": {
        "Tacloban City": ["P. Burgos", "San Jose", "Larga"],
        "Ormoc": ["San Pablo", "Kananga", "Brgy 1"],
        "Baybay": ["Poblacion", "San Roque", "Hernani"]
    },
    "Bohol": {
        "Tagbilaran City": ["Cogon", "Tawala", "Poblacion"],
        "Bilar": ["Poblacion", "Sikatuna", "Luna"],
        "Loboc": ["Poblacion", "Badiang", "Tindog"]
    },
    "Samar": {
        "Catbalogan": ["Barangay 1", "Barangay 2", "Barangay 3"],
        "Calbayog": ["Poblacion", "San Roque", "San Jose"],
        "Borongan": ["Maypangdan", "Canlanipa", "Poblacion"]
    },
    "Negros Oriental": {
        "Dumaguete": ["Bacong", "Banilad", "Poblacion"],
        "Bayawan": ["Barangay 1", "Barangay 2", "Barangay 3"],
        "Bais": ["Poblacion", "Anoling", "Cangmating"]
    },
    "Misamis Oriental": {
        "Cagayan de Oro": ["Kauswagan", "Lapasan", "Poblacion"],
        "El Salvador": ["Poblacion", "Barangay 1", "Barangay 2"],
        "Gingoog": ["Barangay 1", "Barangay 2", "Barangay 3"]
    },
    "La Union": {
        "San Fernando": ["Poblacion", "Bacnotan", "Agoo"],
        "Agoo": ["Poblacion", "Barangay 1", "Barangay 2"],
        "Bauang": ["Poblacion", "Barangay 1", "Barangay 2"]
    },
    "Bukidnon": {
        "Malaybalay": ["Sumpong", "Poblacion", "Casisang"],
        "Valencia": ["Poblacion", "Barangay 1", "Barangay 2"],
        "Manolo Fortich": ["Poblacion", "Barangay 1", "Barangay 2"]
    },
    "Leyte": {
        "Tacloban": ["Poblacion 1", "Poblacion 2", "Barangay 3"],
        "Ormoc": ["Poblacion 1", "Poblacion 2", "Barangay 3"],
        "Baybay": ["Poblacion 1", "Poblacion 2", "Barangay 3"]
    }
};

    // ==============================================================

    // Helper to save current step data before switching
    function saveStepData() {
        if (currentStep === 1) {
            formData.name = document.getElementById('nameInput') ? document.getElementById('nameInput').value : '';
            // Save the final calculated location string
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
        }
    }

    // Helper to load data after switching
    function loadStepData() {
        if (currentStep === 1) {
            if (document.getElementById('nameInput') && formData.name) document.getElementById('nameInput').value = formData.name;
            if (document.getElementById('phoneInput') && formData.phone) document.getElementById('phoneInput').value = formData.phone;
            
            // Update location display field if data is present
            const locationDisplayField = document.getElementById('locationDisplayField');
            if (locationDisplayField && formData.province) {
                locationDisplayField.textContent = formData.province;
            }

        } else if (currentStep === 2) {
            if (document.getElementById('birthdate') && formData.birthdate) document.getElementById('birthdate').value = formData.birthdate;
            if (document.getElementById('age') && formData.age) document.getElementById('age').value = formData.age;
            
            if (formData.genres) {
                const checkboxes = document.querySelectorAll('input[name="popupGenre"]');
                checkboxes.forEach(cb => {
                    cb.checked = formData.genres.includes(cb.value);
                });
                updateGenreField(); 
            }

        } else if (currentStep === 3) {
            if (document.getElementById('emailInput') && formData.email) document.getElementById('emailInput').value = formData.email;
            if (document.getElementById('passwordInput') && formData.password) document.getElementById('passwordInput').value = formData.password;
            if (document.getElementById('confirmPasswordInput') && formData.confirmPassword) document.getElementById('confirmPasswordInput').value = formData.confirmPassword;
        }
    }

    // ==============================================================
    // NEW LOCATION POPUP FUNCTIONS
    // ==============================================================

    function openLocationPopup() {
        document.getElementById("locationPopup").style.display = "flex";
        populateProvinces();
    }

    function populateProvinces() {
        const provinceSelect = document.getElementById('provinceSelect');
        provinceSelect.innerHTML = '<option value="" disabled selected>Select Province</option>';
        
        for (const province in locationData) {
            const option = document.createElement('option');
            option.value = province;
            option.textContent = province;
            provinceSelect.appendChild(option);
        }
        
        // Reset subsequent dropdowns
        document.getElementById('citySelect').innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';
        document.getElementById('citySelect').disabled = true;
        document.getElementById('barangaySelect').innerHTML = '<option value="" disabled selected>Select Barangay</option>';
        document.getElementById('barangaySelect').disabled = true;
    }

    function populateCities() {
        const provinceSelect = document.getElementById('provinceSelect');
        const citySelect = document.getElementById('citySelect');
        const selectedProvince = provinceSelect.value;

        citySelect.innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';
        document.getElementById('barangaySelect').innerHTML = '<option value="" disabled selected>Select Barangay</option>';
        
        if (selectedProvince && locationData[selectedProvince]) {
            for (const city in locationData[selectedProvince]) {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            }
            citySelect.disabled = false;
        } else {
            citySelect.disabled = true;
        }
        document.getElementById('barangaySelect').disabled = true;
    }

    function populateBarangays() {
        const provinceSelect = document.getElementById('provinceSelect');
        const citySelect = document.getElementById('citySelect');
        const barangaySelect = document.getElementById('barangaySelect');
        
        const selectedProvince = provinceSelect.value;
        const selectedCity = citySelect.value;

        barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
        
        if (selectedProvince && selectedCity && locationData[selectedProvince][selectedCity]) {
            const barangays = locationData[selectedProvince][selectedCity];
            for (const barangay of barangays) {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            }
            barangaySelect.disabled = false;
        } else {
            barangaySelect.disabled = true;
        }
    }

    function saveLocationAndClose() {
        const province = document.getElementById('provinceSelect').value;
        const city = document.getElementById('citySelect').value;
        const barangay = document.getElementById('barangaySelect').value;

        if (!province || !city || !barangay) {
             alert("Please select a Province, City, and Barangay.");
             return;
        }

        const fullLocation = `${province} / ${city} / ${barangay}`;
        
        // 1. Update the hidden input field in the form
        document.getElementById('provinceInput').value = fullLocation;
        
        // 2. Update the display button text
        document.getElementById('locationDisplayField').textContent = fullLocation;
        
        // 3. Save to formData (for persistence)
        formData.province = fullLocation;

        // 4. Close the popup
        document.getElementById("locationPopup").style.display = "none";
    }

    // ==============================================================
    // EXISTING FUNCTIONS (modified for the location input change)
    // ==============================================================

    // Step 1 content (kept for dynamic replacement when going back)
    // NOTE: This variable is now OUTDATED. The actual Step 1 content is rendered directly above 
    // the script block. We keep it as a backup for the goToStep1 function, but ensure 
    // it reflects the updated structure (without the old location input).
const step1Content = `
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
                <input 
                    id="phoneInput" 
                    type="tel" 
                    placeholder="Phone Number" 
                    required 
                    name="phone-number"
                    pattern="[0-9]{10,12}" 
                    title="Please enter a valid 10-12 digit phone number">

            </div>
    `;


    // Step 2: preferences + account credentials
    const step2Content = `
    <div class="genre-group">
        <label class="genre-label">Your Favorite Genres:</label>

        <div class="input-group genre-input-group">
            <img src="../../../../public/assets/images/movies_icon.png" alt="genre_icon" class="input-icon">
            <button type="button" class="genre-select-display" id="genreSelectButton" onclick="openGenrePopup()">
                <span id="genreTextField">Select genres...</span>
                <i class="fas fa-chevron-down"></i> 
            </button>
        </div>
    </div>

    <div id="genrePopup" class="genre-popup">
        <div class="genre-popup-content">
        <h3>Select Genres</h3>

        <div class="genres-container-popup">
            ${[
            "Action","Adventure","Comedy","Drama","Horror","Romance",
            "Sci-Fi","Fantasy","Thriller","Animation","Documentary","Musical"
            ].map(g => `
            <label class="popup-item">
                <input type="checkbox" name="popupGenre" value="${g}" onchange="updateGenreField()">
                ${g}
            </label>
            `).join("")}
        </div>

        <div class="popup-buttons">
            <button type="button" class="close-popup-btn" onclick="closeGenrePopup()">Done</button>
        </div>
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
    `;


    // Step 3: account credentials (updated with eye icon and error display)
    const step3Content = `
    <div class="step3-wrapper">
        
        <div class="input-group">
            <img src="../../../../public/assets/images/mail_icon.png" alt="profile_icon" class="input-icon">
            <input id="emailInput" type="email" placeholder="Email" required name="email">
        </div>

        <div class="input-group password-group"> 
            <img src="../../../../public/assets/images/lock_icon.png" alt="lock_icon" class="input-icon">
            <input id="passwordInput" type="password" placeholder="Password" required name="password">
            <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('passwordInput')"></i>
        </div>

        <div class="input-group password-group"> 
            <img src="../../../../public/assets/images/lock_icon.png" alt="lock_icon" class="input-icon">
            <input id="confirmPasswordInput" type="password" placeholder="Confirm Password" required name="confirm_password">
            <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('confirmPasswordInput')"></i>
        </div>

        <div id="passwordError" style="color: #a31212; margin-top: -10px; margin-bottom: 20px; font-size: 14px; text-align: left; padding-left: 55px; min-height: 20px;"></div>
        
    </div>
    `;


    function updateDots() {
        const dots = document.querySelectorAll('.dots span');
        dots.forEach((dot, i) => dot.style.opacity = (i + 1 === currentStep) ? '1' : '0.6');
    }

    function updateButton() {
        const buttonContainer = document.querySelector('.button-container');
        if (currentStep === 1) {
            buttonContainer.innerHTML = '<button type="button" class="next-btn" onclick="goToStep2()">NEXT</button>';
        } else if (currentStep === 2) {
            buttonContainer.innerHTML = `
                <button type="button" class="back-btn" onclick="goToStep1()">BACK</button>
                <button type="button" class="next-btn" onclick="goToStep3()">NEXT</button>
            `;
        } else if (currentStep === 3) {
            buttonContainer.innerHTML = `
                <button type="button" class="back-btn" onclick="goToStep2()">BACK</button>
                <button type="button" class="signup-btn" onclick="validateAndSubmit()">SIGN UP</button>
            `;
        }
    }

    function goToStep2() {
        saveStepData(); // Save Step 1 data
        const container2 = document.querySelector('.container2');
        container2.style.opacity = '0';
        container2.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            container2.innerHTML = step2Content;
            currentStep = 2;
            updateDots();
            updateButton();
            loadStepData(); // Load Step 2 data
            container2.style.opacity = '1';
            container2.style.transform = 'translateX(0)';

            attachProfileImageHandlerIfNeeded();
            attachAgeCalculator(); // <— AGE CALCULATOR ACTIVATED HERE
        }, 300);
    }


    function goToStep1() {
        saveStepData(); // Save current step data (e.g., Step 2 data if coming from there)
        const container2 = document.querySelector('.container2');
        container2.style.opacity = '0';
        container2.style.transform = 'translateX(20px)';
        setTimeout(() => {
            container2.innerHTML = step1Content;
            currentStep = 1;
            updateDots();
            updateButton();
            loadStepData(); // Load Step 1 data
            container2.style.opacity = '1';
            container2.style.transform = 'translateX(0)';

            attachProfileImageHandlerIfNeeded();
        }, 300);
    }

    function goToStep3() {
        saveStepData(); // Save Step 2 data
        const container2 = document.querySelector('.container2');
        container2.style.opacity = '0';
        container2.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            container2.innerHTML = step3Content; // Use the updated content string
            currentStep = 3;
            updateDots();
            updateButton();
            loadStepData(); // Load Step 3 data
            container2.style.opacity = '1';
            container2.style.transform = 'translateX(0)';

            // fill review info from inputs (currently disabled in populateSummary)
            populateSummary();
        }, 300);
    }
    
    // Function to check password complexity
    function checkPasswordComplexity(password) {
        const minLength = 8;
        // Regex: at least one lowercase, one uppercase, one digit
        const complexityRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;

        if (password.length < minLength) {
            return "Password must be at least 8 characters long.";
        }
        if (!complexityRegex.test(password)) {
            return "Password must include at least one uppercase letter, one lowercase letter, and one number.";
        }
        if (password !== confirmPassword){
            return "Passwords do not match."
        }
        return ; // Password passed complexity check
    }
    
    // Validation function for Step 3 (Passwords)
    function validateAndSubmit() {
        const errors = [];

        // STEP 1 VALIDATION
        const name = formData.name || document.getElementById("nameInput")?.value;
        const phone = formData.phone || document.getElementById("phoneInput")?.value;
        const location = formData.province || document.getElementById("provinceInput")?.value;

        if (!name || name.trim().length < 2) {
            errors.push("Please enter a valid name.");
        }
        if (!location) {
            errors.push("Please select your complete location.");
        }
        if (!phone || !/^[0-9]{10,12}$/.test(phone)) {
            errors.push("Phone number must be 10-12 digits.");
        }


        // STEP 2 VALIDATION
        const birthdate = formData.birthdate || document.getElementById("birthdate")?.value;
        const age = formData.age || document.getElementById("age")?.value;
        const genres = formData.genres || [];

        if (!birthdate) {
            errors.push("Please select your birthdate.");
        }
        if (!age || age < 5) {
            errors.push("Your age is invalid. Please check your birthdate.");
        }
        if (genres.length === 0) {
            errors.push("Please choose at least one genre.");
        }


        // STEP 3 VALIDATION
        const email = document.getElementById("emailInput").value;
        const password = document.getElementById("passwordInput").value;
        const confirmPassword = document.getElementById("confirmPasswordInput").value;

        if (!email || !email.includes("@")) {
            errors.push("Please enter a valid email address.");
        }

        const complexityRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

        if (!password) {
            errors.push("Password cannot be empty.");
        } else if (!complexityRegex.test(password)) {
            errors.push("Password must be 8+ characters, with uppercase, lowercase, and numbers.");
        }

        if (password !== confirmPassword) {
            errors.push("Passwords do not match.");
        }


        // IF ERRORS FOUND → SHOW MODAL
        if (errors.length > 0) {
            showErrorModal(errors);
            return;
        }

        // NO ERRORS → SUBMIT 🎉
        document.getElementById("signupForm").submit();
    }

    
    /**
     * Toggles the visibility of a password field.
     * @param {string} inputId - The ID of the password input element.
     */
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.querySelector(`#${inputId} + .toggle-password`); 
        
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }


    function populateSummary() {
        // Summary population logic removed as step 3 is now for credentials, not review.
        // If you want a review screen, let me know, and I'll add a Step 4!
    }

    // attach file preview handler to profile input if it exists in DOM
    function attachProfileImageHandlerIfNeeded() {
        const fileInput = document.getElementById('profileImageInput');
        const preview = document.getElementById('profilePreview');

        if (fileInput && !fileInput._hasHandler) {
            fileInput.addEventListener('change', (e) => {
                const f = e.target.files[0];
                if (!f) return;
                const url = URL.createObjectURL(f);
                if (preview) preview.src = url;
            });
            fileInput._hasHandler = true;
        }

        // treat label clicks to trigger input
        const label = document.querySelector('.profile-upload label');
        if (label && fileInput) {
            label.addEventListener('click', () => fileInput.click());
        }
    }

    // initial attach
    document.addEventListener('DOMContentLoaded', () => {
        updateButton();
        updateDots();
        attachProfileImageHandlerIfNeeded();
        // Load initial data if any (though usually empty on first load)
        loadStepData();
    });

    // The form 'submit' listener is redundant now that we use validateAndSubmit() on the button.
    // It's left here but modified to prevent browser default behavior if somehow triggered outside the button click.
    document.getElementById('signupForm').addEventListener('submit', (e) => {
        e.preventDefault();
    });


    function attachAgeCalculator() {
        const birthdateInput = document.getElementById('birthdate');
        const ageInput = document.getElementById('age');

        if (!birthdateInput || !ageInput) return;

        birthdateInput.addEventListener('change', () => {
            // Save current value immediately
            formData.birthdate = birthdateInput.value;

            const birthdate = new Date(birthdateInput.value);
            const today = new Date();

            let age = today.getFullYear() - birthdate.getFullYear();
            const monthDiff = today.getMonth() - birthdate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }

            ageInput.value = age;
            formData.age = age;
        });
        // Re-run the change event if there is a saved birthdate value on load
        if(birthdateInput.value) {
            birthdateInput.dispatchEvent(new Event('change'));
        }
    }

    function openGenrePopup() {
        document.getElementById("genrePopup").style.display = "flex";
    }

    function closeGenrePopup() {
        // The genres are already saved/updated by updateGenreField()
        document.getElementById("genrePopup").style.display = "none";
    }

    // UPDATED: Now uses ' / ' as the separator for selected genres
    function updateGenreField() {
        const checked = Array.from(document.querySelectorAll('input[name="popupGenre"]:checked'))
            .map(i => i.value);

        const textField = document.getElementById("genreTextField");
        
        if (textField) {
            if (checked.length > 0) {
                textField.textContent = checked.join(" / ");
            } else {
                textField.textContent = "Select genres...";
            }
        }
        formData.genres = checked; // Save to data persistence object
    }
    
    // NOTE: openLocationPopup, populateProvinces, populateCities, populateBarangays,
    // and saveLocationAndClose are defined above the existing Step 1 content 
    // to ensure they are available when the page loads.

    const phoneInput = document.getElementById('phoneInput');

        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, ''); // Remove any non-digit characters
            });
        }

    function showErrorModal(errors) {
    const modal = document.getElementById("errorModal");
    const list = document.getElementById("errorList");

    list.innerHTML = ""; // clear old errors
    errors.forEach(err => {
        const li = document.createElement("li");
        li.textContent = err;
        list.appendChild(li);
    });

    modal.style.display = "flex";
}

function closeErrorModal() {
    document.getElementById("errorModal").style.display = "none";
}


</script>
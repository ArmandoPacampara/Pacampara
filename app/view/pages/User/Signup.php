<?php
session_start();
// Adjust paths to match your project structure (app/view/pages/User/)
require_once '../../../../app/core/db.php';
require_once '../../../../app/controller/SignupController.php'; // Include the Controller

// Ensure database connection is established (from db.php, provides $con)
if ($con === null || $con->connect_error) {
    die("Database connection failed.");
}

// 1. Instantiate the Controller
$signupController = new SignupController($con);

// 2. Delegate the request handling
$signupController->handleSignupRequest();
// If successful, the controller redirects to VerifyOTP.php. 
// If unsuccessful, the controller redirects back to Signup.php after setting $_SESSION['error'].
?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>MoviEase Signup</title>
    <link rel="stylesheet" href="../../../../public/styles/css/Signup.css">
   
</head>

<style>
    /* ===== Global Styles (unchanged) ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}


body {
    background-color: #a31212;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}


h2 {
    text-align: center;
    color: #333;
    margin-top: 12px;
    margin-bottom: 10px;
    margin-right: 50px;
    font-size: 16px;
}


.divider {
    display: flex;
    align-items: center;
    color: #999;
    margin-bottom: -20px;
}


.divider::before,
.divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: #ccc;
}


.divider span {
    font-size: 14px;
}


/* ===== Layout (MODIFIED: .container position changed to relative for back button) ===== */
.wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 40px;
}


.poster-container {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    width: 380px;
    height: 520px;
    margin-right: -260px;
    position: relative;
    transition: transform 0.3s ease;
}


.poster-container:hover {
    transform: translateY(-8px);
}


.poster-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 20px;
}


.container {
    position: relative; /* MODIFIED: Set to relative for positioning the back button */
    background-color: #fff;
    border-radius: 25px;
    height: 640px;
    width: 920px;
    padding: 50px;
    padding-left: 280px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}


/* ===== Logo Section (unchanged) ===== */
.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}


.logo img {
    width: 42px;
    margin-top: -10px;
    margin-right: 10px;
}


.logo h1 {
    color: #a31212;
    font-size: 38px;
    font-weight: 700;
    margin-right: 50px;
}

/* ===== Back Button ADDED Styles (unchanged from last request) ===== */
.back-to-login-link {
    display: inline-flex;
    align-items: center;
    position: absolute;
    top: 35px;
    left: 45px; 
    text-decoration: none;
    color: #a31212;
    font-size: 14px;
    font-weight: 600;
    transition: color 0.2s;
    z-index: 10;
}

.back-to-login-link i {
    margin-right: 8px;
    font-size: 16px;
}

.back-to-login-link:hover {
    color: #870e0e;
    text-decoration: underline;
}
/* =================================== */

/* ===== Input Fields (icon outside input) ===== */
/* NOTE: We modify the input-group margin here to reduce the gap for Step 2/3 buttons */
.input-group {
    display: flex;
    align-items: center;
    margin-bottom: 12px; /* ADJUSTED: Reduced from 20px to 12px for better spacing */
    width: 92%;
}


.input-icon {
    width: 28px;
    height: 28px;
    margin-right: 15px;
    flex-shrink: 0;
}


form .input-group input[type="text"] {
    width: 80%;
    margin-top: 3px;
    padding: 12px 16px;
    border: 1px solid #a31212;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    color: #333 !important;
    background-color: #fff6f6;
    transition: all 0.3s ease;
}


.input-group input,
.input-group select {
    flex: 1;
    padding: 12px;
    border: 1px solid #a31212;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
    background-color: #fff;
    color: #333;
    transition: border-color 0.3s;
}


.input-group input:focus,
.input-group select:focus {
    border-color: #870e0e;
}


/* ===== Buttons (unchanged, but relies on new .input-group margin) ===== */
.next-btn {
    width: 120px;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    color: #fff;
    margin-right: 0;
}


.back-btn,
.signup-btn {
    width: 120px;
    padding: 12px;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin-right: 10px;
}


.signup-btn {
    margin-right: 0;
}


.next-btn,
.signup-btn {
    background-color: #a31212;
}


.next-btn:hover,
.signup-btn:hover {
    background-color: #870e0e;
}


.back-btn {
    background-color: #666;
}


.back-btn:hover {
    background-color: #555;
}


/* ===== Step Buttons Container - Vertical Alignment FIX ===== */
.button-container {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px; /* ADJUSTED: Reduced from 20px to 10px for vertical alignment */
    width: 92%;
    margin-left: 40px;
    padding: 0;
}


.step-2-buttons {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    padding-left: 130px;
}


/* ===== Step Transition (unchanged) ===== */
.container2 {
    display: flex;
    flex-direction: column;
    padding-top: 30px;
    padding-left: 40px;
    padding-right: 60px;
    transition: opacity 0.3s ease, transform 0.3s ease;
    min-height: 320px;
}


/* ===== Dots Indicator (unchanged) ===== */
.dots {
    display: flex;
    justify-content: center;
    margin-top: 15px;
    margin-right: 55px;
    color: #a31212;
}


.dots span {
    height: 8px;
    width: 8px;
    background-color: #a31212;
    border-radius: 50%;
    display: inline-block;
    margin: 0 6px;
    transition: opacity 0.3s;
}


/* default lower opacity handled in script */


/* ================================================= */
/* ===== GENRE SELECTOR (Step 2) STYLES - FIXED WIDTH (unchanged) ===== */
/* ================================================= */


/* 1. Genre Group container width FIX: Match the standard 92% width */
.genre-group {
    width: 92%;
    margin-left: 0;
}


/* Ensures the inner input-group for the new button is aligned */
.genre-input-group {
    width: 100%;
    margin-left: 0;
}


.genre-label {
    display: block;
    color: #a31212;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 12px;
    text-align: left;
}


/* New full-width selector styled like a form input */
.genre-select-display {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
   
    /* Matches standard input styling */
    padding: 12px;
    border: 1px solid #a31212;
    border-radius: 8px;
    outline: none;
    background-color: #fff;
    color: #333;
    cursor: pointer;
    text-align: left;
    transition: border-color 0.3s;
}


.genre-select-display:hover,
.genre-select-display:focus {
    border-color: #870e0e;
}


/* Style the text span inside the button */
.genre-select-display span {
    color: #333;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 90%;
}


/* Style the chevron icon (or any other icon used) */
.genre-select-display .fas {
    font-size: 12px;
    color: #a31212;
}


/* REMOVED/MODIFIED OLD GENRE STYLES (unchanged) */
.genre-input-wrapper {
    display: flex;
    gap: 10px;
    width: 100%;
}
/* The following blocks were removed/modified:
#genreTextField
.genre-btn
*/




/* Existing genre list styles (kept for completeness, though some are likely unused now) */
.genres-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px 20px;
    width: 80%;
    margin: 0 auto;
    margin-left: 30px;
}


.genres-container label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
}


.genres-container input[type="checkbox"] {
    accent-color: #a31212;
    width: 16px;
    height: 50px;
    cursor: pointer;
}


.checkbox-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 3px;
    margin-top: 5px;
}


.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    color: #333;
    padding: 8px;
    border-radius: 6px;
    transition: background-color 0.2s;
}


.checkbox-label:hover {
    background-color: #f5f5f5;
}


.checkbox-label input[type="checkbox"] {
    margin-right: 8px;
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #a31212;
}


/* ===== Responsive (unchanged) ===== */
@media (max-width: 900px) {
    .wrapper {
        flex-direction: column;
        gap: 20px;
    }


    .poster-container {
        width: 70%;
        height: 300px;
    }


    .container {
        width: 80%;
        padding-left: 40px;
    }
}


/* ===== Profile Upload Section (unchanged) ===== */
.profile-upload {
    position: relative;
    width: 70px;
    height: 70px;
    margin: 0 auto 20px auto;
    cursor: pointer;
    margin-top: 10px;
    margin-bottom: 25px;
    margin-right: 230px;
}


.profile-upload label {
    display: block;
    width: 100%;
    height: 100%;
    position: relative;
    border-radius: 50%;
    border: 2px solid #a31212;
    border-color: #870e0e;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}


.profile-upload label:hover {
    transform: scale(1.05);
}


.profile-upload img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    background-color: #fff6f6;
}


.upload-overlay {
    position: absolute;
    bottom: 0;
    width: 100%;
    height: 35%;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
}


.profile-upload label:hover .upload-overlay {
    opacity: 1;
}


/* ===== Small Utilities (unchanged) ===== */
.summary-box {
    background: #fff6f6;
    border: 1px solid #eee;
    padding: 12px;
    border-radius: 8px;
    color: #333;
}


.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 14px;
}


/* final small responsive tweaks for mobile (unchanged) */
@media (max-width: 520px) {
    .container {
        padding-left: 20px;
        padding-right: 20px;
        width: 100%;
        height: auto;
    }


    .poster-container {
        display: none;
    }


    .profile-upload {
        margin-right: 0;
    }
}




.step3-wrapper {
    /* margin-top: -px; */
}




/* ================================================= */
/* ===== GENRE POPUP STYLES (MODAL) (unchanged) ===== */
/* ================================================= */


/* Popup modal */
.genre-popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75); /* Darker overlay */
    justify-content: center;
    align-items: center;
    z-index: 999;
}


@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}


.genre-popup-content {
    width: 90%;
    max-width: 350px; /* Refined max width */
    background: #fff;
    padding: 25px; /* Increased padding */
    border-radius: 15px; /* More rounded corners */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); /* Stronger shadow */
    animation: fadeIn 0.3s ease-out;
}


/* Popup Header */
.genre-popup-content h3 {
    color: #a31212; /* Match theme color */
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}


/* Scrollable Genre List */
.genres-container-popup {
    max-height: 250px;
    overflow-y: auto;
    margin-top: 15px;
    padding-right: 15px; /* Space for scrollbar */
}


/* Style for each genre label/item */
.popup-item {
    display: flex;
    align-items: center;
    padding: 10px 5px;
    cursor: pointer;
    font-size: 15px;
    color: #333;
    transition: background-color 0.2s;
}


.popup-item:hover {
    background-color: #fff6f6; /* Light red background on hover */
}


.popup-item input[type="checkbox"] {
    accent-color: #a31212;
    margin-right: 10px;
    width: 18px;
    height: 18px;
}


/* Done Button */
.popup-buttons {
    margin-top: 25px;
    text-align: right;
}


.close-popup-btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    background: #a31212; /* Primary red theme color */
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}


.close-popup-btn:hover {
    background: #870e0e; /* Darker red on hover */
}


/* --- New Styles for Password Toggle (unchanged) --- */
/* This section fixes the positioning of the eye icon inside the password inputs. */
.password-group {
    position: relative;
    width: 92%;
}


.toggle-password {
    /* Absolute positioning relative to .password-group */
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);


    cursor: pointer;
    color: #666;
    font-size: 16px;
    padding: 5px;
    z-index: 10;
    /* Ensures it sits on top of the input field */
}


.toggle-password:hover {
    color: #a31212;
}


.location-selectors {
    display: flex;
    flex-direction: column; /* Stacks the selectors vertically */
    gap: 15px; /* Space between the dropdowns */
    margin-top: 20px;
}


/* Apply input field styles to the select elements */
.location-selectors select {
    width: 100%; /* Make them fill the popup width */
    padding: 12px;
    border: 1px solid #a31212; /* Match input border */
    border-radius: 8px; /* Match input radius */
    outline: none;
    font-size: 14px;
    background-color: #fff;
    color: #333;
    /* Use custom styling for consistency if possible, otherwise use browser defaults */
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="%23a31212" d="M8 11.5L2.5 6h11L8 11.5z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 10px;
    cursor: pointer;
}


.location-selectors select:focus {
    border-color: #870e0e;
}


.error-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}


/* Modal box */
.error-modal-content {
    background: #ffffff;
    padding: 25px 30px;
    width: 320px;
    border-radius: 10px;
    text-align: left;
    animation: popupFade .25s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,.2);
}


/* Title */
.error-modal-content h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #c62828;
    font-size: 22px;
}


/* Error list */
#errorList {
    padding-left: 20px;
    color: #333;
    margin-bottom: 20px;
    max-height: 200px;
    overflow-y: auto;
}


/* Button */
.error-modal-btn {
    width: 100%;
    padding: 10px;
    background: #c62828;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}


.error-modal-btn:hover {
    background: #a61e1e;
}


/* Animation */
@keyframes popupFade {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}


/* Password Strength Indicator - ALWAYS VISIBLE */
.password-strength-container {
    width: 92%;
    margin-left: 43px;
    margin-top: 8px;
    margin-bottom: 13px;
}


.strength-bar-bg {
    width: 100%;
    height: 6px;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}


.strength-bar {
    height: 100%;
    width: 0%;
    transition: width 0.4s ease, background-color 0.4s ease;
    border-radius: 4px;
}


.strength-text {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
    min-height: 18px;
    text-align: left;
}


.requirements-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: #f9f9f9;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}


.password-strength-container:hover .requirements-list,
.requirements-list:hover {
    display: flex;
}
.requirement {
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    padding: 4px;
    border-radius: 4px;
}


.requirement i {
    font-size: 14px;
    width: 16px;
    flex-shrink: 0;
}


.requirement.unchecked {
    color: #666;
}


.requirement.unchecked i {
    color: #d32f2f;
}


.requirement.checked {
    color: #388e3c;
    font-weight: 500;
}


.requirement.checked i::before {
    content: "\f00c"; /* fa-check */
}


.requirement.checked i {
    color: #388e3c;
}


/* Password input with icon adjustment */
.password-group input {
    padding-right: 45px !important; /* Make room for the eye icon */
}


/* Privacy checkbox row styling */
.privacy-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 92%;
    margin-left: 43px;
    margin-bottom: -10px;
    margin-top: 8px;
    padding: 0px 12px;
    font-size: 14px;
    color: #333;
}


.privacy-row input[type="checkbox"] {
    accent-color: #a31212;
    width: 18px;
    height: 18px;
    margin-right: 10px;
    cursor: pointer;
    flex-shrink: 0;
}


.privacy-link {
    color: #a31212;
    text-decoration: underline;
    cursor: pointer;
    font-weight: 600;
}


.privacy-link:hover {
    color: #870e0e;
    text-decoration: none;
}


/* Step 3 wrapper spacing adjustment */
.step3-wrapper {
    margin-top: 20px;
    padding-bottom: 10px;
}


/* Adjust input group spacing in step 3 */
.step3-wrapper .input-group {
    margin-bottom: 15px;
}


/* Error message for password mismatch */
#passwordError {
    color: #d32f2f;
    margin-top: -10px;
    margin-left: 43px;
    margin-bottom: 15px;
    font-size: 13px;
    text-align: left;
    min-height: 20px;
    font-weight: 500;
}


</style>

<body>


    <div class="wrapper">
        <div class="poster-container">
            <img src="https://cdn.myanimelist.net/images/anime/1806/126216.jpg" alt="Chainsaw Man Poster">
        </div>


        <div class="container">
            <a href="../../../../public/index.php" class="back-to-login-link">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
            <div class="logo">
                <img src="../../../../public/assets/images/movies_icon.png" alt="logo">
                <h1>MoviEase</h1>
            </div>


            <h2>Sign Up your Account</h2>
            <div class="divider"></div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message" style="color: red; text-align: center; margin-bottom: 15px; font-weight: 500;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
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
            // NEW: Save email input from step 2
            formData.email = document.getElementById('emailInput') ? document.getElementById('emailInput').value : '';
        } else if (currentStep === 3) {
            // NEW: Use email from step 2 data
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
            // NEW: Load email input into step 2
            if (document.getElementById('emailInput') && formData.email) document.getElementById('emailInput').value = formData.email;


        } else if (currentStep === 3) {
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
        transitionStep(step2Content, 2, () => { 
            attachAgeCalculator();
            // Ensure email field is loaded for validation
            if (document.getElementById('emailInput') && formData.email) document.getElementById('emailInput').value = formData.email;
        });
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
        // MUST call saveStepData() one last time to pull data from fields currently in view (Step 3)
        // AND data from Step 2 fields that are displayed in Step 2.
        saveStepData(); 

        const errors = [];
       
        // Re-validate Step 1 data from memory
        const name = formData.name;
        const location = formData.province;
        const phone = formData.phone;
        if (!name || name.trim().length < 2) errors.push("Step 1: Please enter a valid name.");
        if (!location) errors.push("Step 1: Please select your complete location.");
        if (!phone || !/^[0-9]{10,12}$/.test(phone)) errors.push("Step 1: Phone number must be 10-12 digits.");

        // Re-validate Step 2 data from memory
        const birthdate = formData.birthdate;
        const age = formData.age;
        const email = formData.email;
        if (!birthdate) errors.push("Step 2: Please select your birthdate.");
        if (!age || age < 5) errors.push("Step 2: Your age is invalid.");
        if (!formData.genres || formData.genres.length === 0) errors.push("Step 2: Please choose at least one genre.");
        if (!email || !email.includes("@")) errors.push("Step 2: Please enter a valid email address.");


        // Validate Step 3 (Current)
        const password = formData.password;
        const confirmPassword = formData.confirmPassword;
        const privacyChecked = document.getElementById("privacyCheck")?.checked;


        if (!password) errors.push("Step 3: Password cannot be empty.");
        // Use the stricter regex check
        else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/.test(password)) errors.push("Step 3: Password must be 8+ chars, with uppercase, lowercase, numbers, and a special character.");
        
        if (password !== confirmPassword) errors.push("Step 3: Passwords do not match.");


        // Checkbox validation
        if (!privacyChecked) {
            errors.push("Step 3: You must agree to the Data Privacy Consent.");
        }


        if (errors.length > 0) {
            showErrorModal(errors);
            return;
        }


        confirmSignup();
    }


    function confirmSignup() {
        const form = document.getElementById("signupForm");
        // Inject hidden inputs for ALL data from previous steps (1 & 2) AND current step (3) that need to be POSTed to PHP
        
        // Data from Step 1
        const hiddenFields = { 
            'name': formData.name, 
            'province': formData.province, 
            'phone-number': formData.phone, 
            'email': formData.email, // From step 2 (now added to formData in saveStepData)
            'password': formData.password, // From step 3 (now added to formData in saveStepData)
            'confirm_password': formData.confirmPassword // From step 3 (now added to formData in saveStepData)
            // Note: birthdate, age, genres are currently not captured by PHP but can be added here if the PHP uses them later
        };
        
        // Clear any existing hidden fields before adding new ones
        form.querySelectorAll('input[type="hidden"]').forEach(input => input.remove());
        
        for (const [key, value] of Object.entries(hiddenFields)) {
            if (value !== undefined && value !== null) { // Only add if present
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
        }
        
        // **This line triggers the POST request to the Controller via PHP**
        form.submit();
    }


    // --- MODAL LOGIC (UNCHANGED) ---
    function showPrivacyModal() { document.getElementById("privacyModal").style.display = "flex"; }
    function closePrivacyModal() { document.getElementById("privacyModal").style.display = "none"; }
   
    function acceptPrivacy() {
        const checkbox = document.getElementById("privacyCheck");
        if (checkbox) checkbox.checked = true;
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


    // --- UTILITIES (Password toggle, location, genre, etc. - UNCHANGED) ---
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
        // Note: The original JS had a separate populateProvincesFromIsland, maintaining the original one here.
        // Assuming the JS helper 'populateProvincesFromIsland()' correctly filters by island group.
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
        const p = document.getElementById('provinceSelect').value;
        const c = document.getElementById('citySelect').value;
        const b = document.getElementById('barangaySelect').value;
        if (!p || !c || !b) { alert("Incomplete location."); return; }
        const loc = `${p} / ${c} / ${b}`;
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
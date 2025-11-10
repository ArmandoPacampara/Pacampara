<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase Signup</title>

  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
/* ===== Global Styles ===== */
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
  margin-bottom: 20px;
  margin-right: 50px;
  font-size: 16px;
}

.divider {
  display: flex;
  align-items: center;
  color: #999;
  margin-bottom: -15px;
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

/* ===== Layout ===== */
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
  position: unset;
  background-color: #fff;
  border-radius: 25px;
  height: 609px;
  width: 920px;
  padding: 50px;
  padding-left: 280px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

/* ===== Logo Section ===== */
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

/* ===== Input Fields (icon outside input) ===== */
.input-group {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
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

/* ===== Buttons ===== */
.next-btn {
  width: 120px;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
  color: #fff;
  margin-right: 100px;
}

.back-btn,.signup-btn {
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
  margin-right: 100px;
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

/* ===== Step Buttons Container ===== */
.button-container {
  display: flex;
  justify-content: right;
  margin-top: 10px;
  padding-left: 260px;
}

.step-2-buttons {
  display: flex;
  justify-content: center;
  margin-top: 30px;
  padding-left: 130px;
}

/* ===== Step Transition ===== */
.container2 {
  display: flex;
  flex-direction: column;
  padding-top: 30px;
  padding-left: 40px;
  padding-right: 60px;
  transition: opacity 0.3s ease, transform 0.3s ease;
}

/* ===== Dots Indicator ===== */
.dots {
  display: flex;
  justify-content: center;
  margin-top: 10px;
  margin-right: 55px;
  color: #a31212;
}

.dots span {
  height: 8px;
  width: 8px;
  background-color: #a31212;
  border-radius: 50%;
  display: inline-block;
  margin: 0 4px;
  transition: opacity 0.3s;
}

.dots span:nth-child(2) {
  opacity: 0.6;
}

/* ===== Genre Checkbox Section ===== */
.genre-group {
  margin-bottom: 20px;
  width: 80%;
}

.genre-label {
  display: block;
  color: #a31212;
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 12px;
  text-align: left;
}

.genres-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* 4 columns */
  gap: 20px 20px; /* row and column spacing */
  width: 80%;
  margin: 0 auto;
  margin-left: 10px;
}

.genres-container label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  cursor: pointer;
}

.genres-container input[type="checkbox"] {
  accent-color: #a31212; /* red checkbox theme */
  width: 16px;
  height: 16px;
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

/* ===== Responsive ===== */
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

/* ===== Profile Upload Section ===== */
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
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
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

  </style>
</head>
<body>

  <div class="wrapper">
    <div class="poster-container">
      <img src="https://cdn.myanimelist.net/images/anime/1806/126216.jpg" alt="Chainsaw Man Poster">
    </div>

    <div class="container">
      <div class="logo">
        <img src="../../../public/assets/images/movies_icon.png" alt="logo">
        <h1>MoviEase</h1>
      </div>

      <h2>Sign Up your Account</h2>
      <div class="divider"></div>

      <form>
        <center>
          <div class="container2">
            <div class="profile-upload">
              <label for="profileImageInput">
                <img id="profilePreview" src="../../../public/assets/images/default_user.png" alt="Profile Preview">
                <div class="upload-overlay">
                  <i class="fas fa-camera"></i>
                </div>
              </label>
              <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
            </div>

            <div class="input-group">
              <img src="../../../public/assets/images/profile_icon.png" alt="profile_icon" class="input-icon">
              <input type="text" placeholder="Name" required name="name">
            </div>

            <div class="input-group">
              <img src="../../../public/assets/images/location_icon.png" alt="location_icon" class="input-icon">
              <input type="text" placeholder="Country / Province / City / #Barangay" required name="province">
            </div>

            <div class="input-group">
              <img src="../../../public/assets/images/phone_icon.png" alt="phone_icon" class="input-icon">
              <input type="text" placeholder="Phone Number" required name="phone-number">
            </div>
          </div>

          <div class="dots">
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

  <script>
    // Multi-step form functionality for MoviEase signup
    let currentStep = 1;

    const step1Content = `
            <div class="profile-upload">
              <label for="profileImageInput">
                <img id="profilePreview" src="../../../public/assets/images/default_user.png" alt="Profile Preview">
                <div class="upload-overlay">
                  <i class="fas fa-camera"></i>
                </div>
              </label>
              <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
            </div>

            <div class="input-group">
              <img src="../../../public/assets/images/profile_icon.png" alt="profile_icon" class="input-icon">
              <input type="text" placeholder="Name" required name="name">
            </div>

            <div class="input-group">
              <img src="../../../public/assets/images/location_icon.png" alt="location_icon" class="input-icon">
              <input type="text" placeholder="Country / Province / City / #Barangay" required name="province">
            </div>

            <div class="input-group">
              <img src="../../../public/assets/images/phone_icon.png" alt="phone_icon" class="input-icon">
              <input type="text" placeholder="Phone Number" required name="phone-number">
            </div>
    `;

    const step2Content = `
      <div class="genre-group">
        <label class="genre-label">Choose Your Favorite Genres:</label>
          <div class="genres-container">
            ${['Action','Adventure','Comedy','Drama','Horror','Romance','Sci-Fi','Fantasy','Thriller','Animation','Documentary','Musical']
              .map(genre => `
                <label>
                  <input type="checkbox" name="genre" value="${genre}"> ${genre}
                </label>`).join('')}
          </div>
      </div>

      <div class="input-group">
        <img src="../../../public/assets/images/profile_icon.png" alt="profile_icon" class="input-icon">
        <input type="email" placeholder="Email" required name="email">
      </div>

      <div class="input-group">
        <img src="../../../public/assets/images/lock_icon.png" alt="lock_icon" class="input-icon">
        <input type="password" placeholder="Password" required name="password">
      </div>

      <div class="input-group">
        <img src="../../../public/assets/images/lock_icon.png" alt="lock_icon" class="input-icon">
        <input type="password" placeholder="Confirm Password" required name="confirm_password">
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
      } else {
        buttonContainer.innerHTML = `
          <button type="button" class="back-btn" onclick="goToStep1()">BACK</button>
          <button type="submit" class="signup-btn">SIGN UP</button>
        `;
      }
    }

    function goToStep2() {
      const container2 = document.querySelector('.container2');
      container2.style.opacity = '0';
      container2.style.transform = 'translateX(-20px)';
      setTimeout(() => {
        container2.innerHTML = step2Content;
        currentStep = 2;
        updateDots();
        updateButton();
        container2.style.opacity = '1';
        container2.style.transform = 'translateX(0)';
      }, 300);
    }

    function goToStep1() {
      const container2 = document.querySelector('.container2');
      container2.style.opacity = '0';
      container2.style.transform = 'translateX(20px)';
      setTimeout(() => {
        container2.innerHTML = step1Content;
        currentStep = 1;
        updateDots();
        updateButton();
        container2.style.opacity = '1';
        container2.style.transform = 'translateX(0)';
      }, 300);
    }

    document.addEventListener('DOMContentLoaded', () => {
      const container2 = document.querySelector('.container2');
      container2.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      updateButton();
      updateDots();
    });
  </script>

</body>
</html>

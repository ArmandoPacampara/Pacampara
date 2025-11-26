<!DOCTYPE html>
<html lang="en">


<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Account - MoviEase</title>
    <link rel="stylesheet" href="/../../../public/styles/css/AccountPage.css" />
</head>


<body>
  <div class="page-wrapper">
    <!-- ACCOUNT CONTAINER -->
    <div class="account-container">
      <div class="profile-section">
        <button class="edit-btn" id="editBtn">
          <img src="/public/assets/edit_btn.png" id="editIcon" alt="edit-icon" />
        </button>


        <div class="profile-img-wrapper">
          <img src="/public/assets/account_icon.png" class="profile-img" id="profileImg" alt="Profile Image" />
          <input type="file" id="profileInput" accept="image/*" style="display: none" />
        </div>


        <h1 class="username">
          <span id="usernameDisplay">Armando Pacampara</span>
          <input type="text" class="username-input" id="usernameInput" value="Armando Pacampara" style="display: none;" />
        </h1>
        <h2 class="user-role">Customer</h2>
      </div>


      <div class="user-details">
        <div class="detail-row">
          <label class="label">EMAIL</label>
          <div>
            <input type="text" class="field-input" id="email" value="armando@example.com" disabled />
            <p class="error-msg"></p>
          </div>
        </div>


        <div class="detail-row">
          <label class="label">PHONE</label>
          <div>
            <input type="text" class="field-input" id="phone" value="09876543211" disabled />
            <p class="error-msg"></p>
          </div>
        </div>


        <div class="detail-row">
          <label class="label">AGE</label>
          <div>
            <input type="text" class="field-input" id="age" value="23" disabled />
            <p class="error-msg"></p>
          </div>
        </div>


        <div class="detail-row">
          <label class="label">ADDRESS</label>
          <div>
            <input type="text" class="field-input" id="address" value="Taguig City, Philippines" disabled />
            <p class="error-msg"></p>
          </div>
        </div>


        <div class="detail-row">
          <label class="label">GENRE</label>
          <div>
            <input type="text" class="field-input" id="genre" value="Action / Sci-Fi" disabled />
            <p class="error-msg"></p>
          </div>
        </div>
      </div>
    </div>


    <!-- RECORDS CONTAINER -->
    <div class="records-container">
      <h1>BOOKING RECORDS</h1>


      <div class="bookings">
        <div class="table-container">
          <div class="record-header">All Bookings</div>
          <table>
            <thead>
              <tr>
                <th>Schedule</th>
                <th>Qty</th>
                <th>Movie Name</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="bookingsBody">
              <tr>
                <td>Dec 15, 2024 - 6:10 PM</td>
                <td>2</td>
                <td>Avengers: Endgame</td>
                <td>Booked</td>
                <td><button class="view-btn">View</button></td>
              </tr>
              <tr>
                <td>Dec 10, 2024 - 8:30 PM</td>
                <td>4</td>
                <td>Spider-Man: No Way Home</td>
                <td>Completed</td>
                <td><button class="view-btn">View</button></td>
              </tr>
              <tr>
                <td>Dec 5, 2024 - 4:00 PM</td>
                <td>3</td>
                <td>The Dark Knight</td>
                <td>Canceled</td>
                <td><button class="view-btn">View</button></td>
              </tr>
              <tr>
                <td>Nov 28, 2024 - 7:15 PM</td>
                <td>2</td>
                <td>Inception</td>
                <td>Completed</td>
                <td><button class="view-btn">View</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>


  <!-- POPUP -->
  <div id="savePopup" class="popup-overlay">
    <div class="popup-box">
      <h2>Save Changes?</h2>
      <p>Are you sure you want to save the updated information?</p>


      <div class="popup-buttons">
        <button id="cancelSave" class="cancel-btn">Cancel</button>
        <button id="confirmSave" class="confirm-btn">Save</button>
      </div>
    </div>
  </div>


  <!-- JAVASCRIPT -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      /* -------------------------
       EDITING LOGIC
      --------------------------- */


      const editBtn = document.getElementById("editBtn");
      const editIcon = document.getElementById("editIcon");
      const inputs = document.querySelectorAll(".field-input");
      const profileImg = document.getElementById("profileImg");
      const profileInput = document.getElementById("profileInput");
      const usernameDisplay = document.getElementById("usernameDisplay");
      const usernameInput = document.getElementById("usernameInput");


      let editing = false;
      let tempProfileSrc = profileImg.src;


      editBtn.addEventListener("click", () => {
        editing = !editing;


        if (editing) {
          inputs.forEach((i) => {
            i.disabled = false;
            i.classList.add("editing");
          });
          // Show username input, hide display
          usernameDisplay.style.display = "none";
          usernameInput.style.display = "inline-block";
          usernameInput.classList.add("editing");
          // Change to save button image
          editIcon.src = "/public/assets/save_btn.png";
        } else {
          if (!validateFields()) {
            editing = true;
            return;
          }


          document.getElementById("savePopup").style.display = "flex";


          document.getElementById("confirmSave").onclick = () => {
            inputs.forEach((i) => {
              i.disabled = true;
              i.classList.remove("editing");
            });
            // Update username display and hide input
            usernameDisplay.textContent = usernameInput.value;
            usernameDisplay.style.display = "inline";
            usernameInput.style.display = "none";
            usernameInput.classList.remove("editing");
           
            tempProfileSrc = profileImg.src;
            // Change back to edit button image
            editIcon.src = "/public/assets/edit_btn.png";
            document.getElementById("savePopup").style.display = "none";
          };


          document.getElementById("cancelSave").onclick = () => {
            editing = true;
            inputs.forEach((i) => i.classList.remove("editing"));
            // Reset username input to original value
            usernameInput.value = usernameDisplay.textContent;
            usernameDisplay.style.display = "inline";
            usernameInput.style.display = "none";
            usernameInput.classList.remove("editing");
           
            profileImg.src = tempProfileSrc;
            document.getElementById("savePopup").style.display = "none";
          };
        }
      });


      function validateFields() {
        let isValid = true;


        document
          .querySelectorAll(".error-msg")
          .forEach((e) => (e.textContent = ""));
        inputs.forEach((i) => i.classList.remove("input-error"));


        const email = document.getElementById("email");
        const emailValue = email.value.trim();
        const phone = document.getElementById("phone");
        const age = document.getElementById("age");
        const address = document.getElementById("address");
        const genre = document.getElementById("genre");


        // Validate username
        if (usernameInput.value.trim() === "" || usernameInput.value.trim().length < 2) {
          alert("Username must be at least 2 characters long.");
          isValid = false;
        }


        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailValue)) {
          showError(email, "Invalid email.");
          isValid = false;
        }


        const phonePattern = /^(\+63|0)\s?\d{10}$/;
        if (!phonePattern.test(phone.value.trim())) {
          showError(phone, "Invalid phone number.");
          isValid = false;
        }


        if (!/^[0-9]+$/.test(age.value.trim())) {
          showError(age, "Age must be a number.");
          isValid = false;
        }


        if (address.value.trim().length < 5) {
          showError(address, "Address too short.");
          isValid = false;
        }


        if (genre.value.trim() === "") {
          showError(genre, "Genre cannot be empty.");
          isValid = false;
        }


        return isValid;
      }


      function showError(input, message) {
        input.classList.add("input-error");
        input.parentNode.querySelector(".error-msg").textContent = message;
      }


      profileImg.addEventListener("click", () => {
        if (editing) profileInput.click();
      });


      profileInput.addEventListener("change", () => {
        const file = profileInput.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            profileImg.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
</body>


</html>


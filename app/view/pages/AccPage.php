<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Account - MoviEase</title>

  <!-- External CSS -->
  <link rel="stylesheet" href="/public/styles/css/AccPage.css" />
</head>

<body>
  <div class="page-wrapper">
    <!-- ACCOUNT CONTAINER -->
    <div class="account-container">
      <div class="profile-section">
        <button class="edit-btn" id="editBtn">
          <img src="assets/images/edit_btn.png" id="editIcon" alt="edit-icon" />
        </button>

        <div class="profile-img-wrapper">
          <img src="assets/images/account_icon.png" class="profile-img" id="profileImg" alt="Profile Image" />
          <input type="file" id="profileInput" accept="image/*" style="display: none" />
        </div>

        <h1 class="username">Armando Pacampara</h1>
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
            <input type="text" class="field-input" id="phone" value="+63 912 345 6789" disabled />
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

      <div class="booking-classification">
        <h3 class="tab active" data-target="all">SHOW ALL</h3>
        <h3 class="tab" data-target="current">CURRENT</h3>
        <h3 class="tab" data-target="past">HISTORY</h3>
      </div>

      <div class="bookings">
        <!-- CURRENT -->
        <div class="table-container table-page active" id="current">
          <div class="record-header">Current Booking</div>
          <table>
            <thead>
              <tr>
                <th>Schedule</th>
                <th>Qty</th>
                <th>Movie Name</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="currentBody">
              <tr class="empty-row">
                <td colspan="4">No records yet</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAST -->
        <div class="table-container table-page active" id="past">
          <div class="record-header">Past Records</div>
          <table>
            <thead>
              <tr>
                <th>Schedule</th>
                <th>Qty</th>
                <th>Movie Name</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="pastBody">
              <tr class="empty-row">
                <td colspan="4">No records yet</td>
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
      TAB ANIMATION LOGIC
--------------------------- */

      const tabs = document.querySelectorAll(".booking-classification .tab");
      const pages = document.querySelectorAll(".table-page");

      tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
          const target = tab.getAttribute("data-target");

          tabs.forEach((t) => t.classList.remove("active"));
          tab.classList.add("active");

          pages.forEach((page) => {
            if (target === "all") {
              page.style.display = "block";
              setTimeout(() => page.classList.add("active"), 10);
            } else if (page.id === target) {
              page.style.display = "block";
              setTimeout(() => page.classList.add("active"), 10);
            } else {
              page.classList.remove("active");
              setTimeout(() => (page.style.display = "none"), 300);
            }
          });
        });
      });

      /* -------------------------
       EDITING LOGIC
--------------------------- */

      const editBtn = document.getElementById("editBtn");
      const editIcon = document.getElementById("editIcon");
      const inputs = document.querySelectorAll(".field-input");
      const profileImg = document.getElementById("profileImg");
      const profileInput = document.getElementById("profileInput");

      let editing = false;
      let tempProfileSrc = profileImg.src;

      editBtn.addEventListener("click", () => {
        editing = !editing;

        if (editing) {
          inputs.forEach((i) => {
            i.disabled = false;
            i.classList.add("editing");
          });
          editIcon.src = "assets/images/save_btn.png";
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
            tempProfileSrc = profileImg.src;
            editIcon.src = "assets/images/edit_btn.png";
            document.getElementById("savePopup").style.display = "none";
          };

          document.getElementById("cancelSave").onclick = () => {
            editing = true;
            inputs.forEach((i) => i.classList.remove("editing"));
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

        const emailValue = email.value.trim();
        const phone = document.getElementById("phone");
        const age = document.getElementById("age");
        const address = document.getElementById("address");
        const genre = document.getElementById("genre");

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